<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MembershipPaymentController extends Controller
{
    private $paystackSecretKey;
    private $paystackPublicKey;

    public function __construct()
    {
        // Temporarily hardcode working test keys to bypass .env issues
        $this->paystackSecretKey = 'sk_test_de5caee37f77fba3c2db5bc87461c55de7d36d8f';
        $this->paystackPublicKey = 'pk_test_a6398b03e7b36dda137bb07664c1301259595dee';
    }

    /**
     * Show the membership payment page
     */
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        // Check if user is vendor and hasn't paid
        if (!$user || !$user->isVendor()) {
            abort(403, 'Access denied. Only vendors can access this page.');
        }

        if ($user->membership_active) {
            return redirect()->route('vendor.dashboard')
                ->with('info', 'Your membership is already active.');
        }

        $membershipFee = env('MEMBERSHIP_FEE', 100000); // ₦1,000 in kobo

        return view('payment.membership', compact('user', 'membershipFee'));
    }

    /**
     * Process the membership payment
     */
    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric|min:100000', // Minimum ₦1,000
        ]);

        $user = Auth::user();

        if (!$user->isVendor()) {
            return back()->with('error', 'Only vendors can make membership payments.');
        }

        if ($user->membership_active) {
            return redirect()->route('vendor.dashboard')
                ->with('info', 'Your membership is already active.');
        }

        try {
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'reference' => 'SAB_MEM_' . time() . '_' . $user->id,
                'type' => 'membership',
                'amount' => $request->amount / 100, // Convert from kobo to naira
                'currency' => 'NGN',
                'status' => 'pending',
                'gateway' => 'paystack',
            ]);

            // Use hardcoded test keys for now to bypass .env issues
            $testSecretKey = 'sk_test_de5caee37f77fba3c2db5bc87461c55de7d36d8f';
            
            Log::info('Payment initialization attempt', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'reference' => $payment->reference,
                'email' => $request->email
            ]);

            // Initialize Paystack payment
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $testSecretKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $request->email,
                'amount' => $request->amount, // Amount in kobo
                'reference' => $payment->reference,
                'callback_url' => route('membership.callback'),
                'metadata' => [
                    'user_id' => $user->id,
                    'payment_id' => $payment->id,
                    'payment_type' => 'membership',
                ],
            ]);

            Log::info('Paystack API response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status']) {
                    // Store gateway response
                    $payment->update([
                        'gateway_response' => $data
                    ]);

                    // Redirect to Paystack
                    return redirect($data['data']['authorization_url']);
                } else {
                    Log::error('Paystack returned false status', $data);
                    return back()->with('error', 'Payment gateway error: ' . ($data['message'] ?? 'Unknown error'));
                }
            } else {
                Log::error('Paystack API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return back()->with('error', 'Unable to connect to payment gateway. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Membership payment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Payment initialization failed. Please try again.');
        }
    }

    /**
     * Handle Paystack callback
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('membership.payment')
                ->with('error', 'Invalid payment reference.');
        }

        try {
            // Verify payment with Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] && $data['data']['status'] === 'success') {
                    // Find payment record
                    $payment = Payment::where('reference', $reference)->first();

                    if ($payment) {
                        // Update payment status
                        $payment->markAsSuccessful();

                        // Update payment gateway response
                        $payment->update([
                            'gateway_response' => $data
                        ]);

                        return redirect()->route('vendor.dashboard')
                            ->with('success', 'Membership payment successful! You can now access all vendor features.');
                    }
                }
            }

            return redirect()->route('membership.payment')
                ->with('error', 'Payment verification failed.');

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return redirect()->route('membership.payment')
                ->with('error', 'Payment verification failed.');
        }
    }

    /**
     * Handle Paystack webhooks (for additional security)
     */
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('x-paystack-signature');
        $body = $request->getContent();
        
        if (!$signature || $signature !== hash_hmac('sha512', $body, $this->paystackSecretKey)) {
            return response('Unauthorized', 401);
        }

        $event = $request->json()->all();

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $payment = Payment::where('reference', $reference)->first();

            if ($payment && $payment->status !== 'success') {
                $payment->markAsSuccessful();
                $payment->update(['gateway_response' => $event]);

                Log::info("Membership payment confirmed via webhook: {$reference}");
            }
        }

        return response('OK', 200);
    }
}
