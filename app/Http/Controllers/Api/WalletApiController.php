<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class WalletApiController
 *
 * Handles all API-based wallet operations, including fetching wallet data, funding,
 * processing payments, and handling reseller links.
 *
 * @package App\Http\Controllers\Api
 */
class WalletApiController extends Controller
{
    /**
     * The Paystack secret key for API authentication.
     * @var string
     */
    private $paystackSecretKey;

    /**
     * WalletApiController constructor.
     *
     * Sets up middleware and initializes the Paystack secret key.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->paystackSecretKey = env('PAYSTACK_SECRET_KEY', 'sk_test_de5caee37f77fba3c2db5bc87461c55de7d36d8f');
    }

    /**
     * Get the authenticated user's wallet balance and recent transactions.
     *
     * @return JsonResponse A JSON response containing the wallet balance and a list of recent transactions.
     */
    public function getWallet(): JsonResponse
    {
        $user = auth()->user();
        $wallet = $user->getOrCreateWallet();
        
        $transactions = $user->walletTransactions()
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'formatted_balance' => $wallet->formatted_balance,
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'formatted_amount' => $transaction->formatted_amount,
                        'description' => $transaction->description,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at->format('M d, Y H:i'),
                        'color' => $transaction->color,
                    ];
                })
            ]
        ]);
    }

    /**
     * Initiate wallet funding via Paystack.
     *
     * Creates a Paystack transaction and returns an authorization URL for the user to complete the payment.
     *
     * @param Request $request The request object containing the amount to fund.
     * @return JsonResponse A JSON response with the Paystack authorization URL.
     */
    public function fundWallet(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:500', // Minimum ₦500
        ]);

        $user = auth()->user();
        $amount = $request->amount * 100; // Convert to kobo
        $reference = 'wallet_' . Str::random(10) . '_' . time();

        try {
            // Initialize Paystack transaction
            $response = Http::withToken($this->paystackSecretKey)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'amount' => $amount,
                    'email' => $user->email,
                    'reference' => $reference,
                    'callback_url' => route('api.wallet.callback'),
                    'metadata' => [
                        'user_id' => $user->id,
                        'purpose' => 'wallet_funding',
                        'original_amount' => $request->amount
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Create pending transaction record
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'funding',
                    'amount' => $request->amount,
                    'balance_after' => $user->wallet_balance, // Current balance
                    'reference' => $reference,
                    'description' => "Wallet funding of ₦" . number_format($request->amount, 2),
                    'status' => 'pending',
                    'metadata' => $data
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'authorization_url' => $data['data']['authorization_url'],
                        'reference' => $reference
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize payment'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Wallet funding error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed'
            ], 500);
        }
    }

    /**
     * Handle the Paystack callback after a wallet funding attempt.
     *
     * Verifies the transaction with Paystack and updates the user's wallet if the payment was successful.
     *
     * @param Request $request The request object containing the transaction reference.
     * @return JsonResponse A JSON response indicating the outcome of the transaction.
     */
    public function walletFundingCallback(Request $request): JsonResponse
    {
        $reference = $request->reference;
        
        if (!$reference) {
            return response()->json(['success' => false, 'message' => 'No reference provided'], 400);
        }

        try {
            // Verify transaction with Paystack
            $response = Http::withToken($this->paystackSecretKey)
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['data']['status'] === 'success') {
                    // Find the pending transaction
                    $transaction = WalletTransaction::where('reference', $reference)
                        ->where('status', 'pending')
                        ->first();

                    if ($transaction) {
                        $user = $transaction->user;

                        DB::transaction(function () use ($transaction, $data) {
                            // Get user wallet
                            $wallet = $transaction->user->getOrCreateWallet();

                            // Credit wallet
                            $wallet->credit(
                                $transaction->amount,
                                'funding',
                                $transaction->description,
                                $transaction->reference
                            );

                            // Update transaction status
                            $transaction->update([
                                'status' => 'completed',
                                'balance_after' => $wallet->fresh()->balance,
                                'metadata' => array_merge($transaction->metadata ?? [], $data)
                            ]);
                        });

                        // Process any pending commissions if user is a vendor
                        $pendingCommissionsProcessed = [];
                        if ($user->isVendor()) {
                            $commissionService = app(\App\Services\CommissionService::class);
                            $pendingCommissionsProcessed = $commissionService->processPendingCommissions($user);
                        }

                        return response()->json([
                            'success' => true,
                            'message' => 'Wallet funded successfully',
                            'pending_commissions_processed' => $pendingCommissionsProcessed
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Wallet callback error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed'
            ], 500);
        }
    }

    /**
     * Purchase a product using the wallet balance.
     *
     * Handles the entire purchase process, including debiting the buyer, crediting the vendor,
     * and handling reseller commissions.
     *
     * @param Request $request The request object containing purchase details like quantity.
     * @param Product $product The product being purchased.
     * @return JsonResponse A JSON response indicating the outcome of the purchase.
     */
    public function buyProduct(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10',
            'shipping_address' => 'required_if:product.type,physical|nullable|string',
            'reseller_code' => 'nullable|string'
        ]);

        $buyer = auth()->user();
        $quantity = $request->quantity ?? 1;
        $totalPrice = $product->price * $quantity;

        // Check if buyer has sufficient balance
        if (!$buyer->hasSufficientWalletBalance($totalPrice)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance',
                'required' => $totalPrice,
                'available' => $buyer->wallet_balance
            ], 400);
        }

        try {
            return DB::transaction(function () use ($buyer, $product, $quantity, $totalPrice, $request) {
                // Handle reseller logic
                $reseller = null;
                $resellerCommission = 0;
                
                if ($request->reseller_code) {
                    $resellerLink = \App\Models\ResellerLink::where('code', $request->reseller_code)
                        ->where('product_id', $product->id)
                        ->where('is_active', true)
                        ->first();
                    
                    if ($resellerLink) {
                        $reseller = $resellerLink->reseller;
                        $resellerCommission = ($totalPrice * $resellerLink->commission_rate) / 100;
                    }
                }

                // Create order
                $order = Order::create([
                    'buyer_id' => $buyer->id,
                    'product_id' => $product->id,
                    'shop_id' => $product->shop_id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total_price' => $totalPrice,
                    'payment_status' => 'paid',
                    'payment_method' => 'wallet',
                    'status' => $product->type === 'digital' ? 'completed' : 'processing',
                    'shipping_address' => $request->shipping_address,
                    'reseller_id' => $reseller?->id,
                    'reseller_commission' => $resellerCommission,
                ]);

                // Debit buyer's wallet
                $buyerWallet = $buyer->getOrCreateWallet();
                $buyerWallet->debit(
                    $totalPrice,
                    'purchase',
                    "Purchase of {$product->title}",
                    "order_{$order->id}",
                    $order->id
                );

                // Credit vendor's wallet (minus reseller commission)
                $vendorAmount = $totalPrice - $resellerCommission;
                $vendorWallet = $product->shop->vendor->getOrCreateWallet();
                $vendorWallet->credit(
                    $vendorAmount,
                    'commission',
                    "Sale of {$product->title}",
                    "order_{$order->id}",
                    $order->id
                );

                // Credit reseller if applicable
                if ($reseller && $resellerCommission > 0) {
                    $resellerWallet = $reseller->getOrCreateWallet();
                    $resellerWallet->credit(
                        $resellerCommission,
                        'commission',
                        "Reseller commission for {$product->title}",
                        "order_{$order->id}",
                        $order->id
                    );

                    // Update reseller link stats
                    if (isset($resellerLink)) {
                        $resellerLink->recordSale($resellerCommission);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Product purchased successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'total_paid' => $totalPrice,
                        'new_balance' => $buyer->fresh()->wallet_balance
                    ]
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Product purchase error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track a reseller link click and set a session cookie.
     *
     * Records a click on the reseller link and stores the reseller code in the user's session
     * to attribute a future purchase.
     *
     * @param Request $request The incoming request.
     * @param string $code The unique code of the reseller link.
     * @return JsonResponse A JSON response with the product URL to redirect the user to.
     */
    public function trackResellerLink(Request $request, $code): JsonResponse
    {
        $resellerLink = \App\Models\ResellerLink::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$resellerLink) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reseller link'
            ], 404);
        }

        // Record click
        $resellerLink->recordClick();

        // Set session for tracking
        session(['reseller_code' => $code]);

        return response()->json([
            'success' => true,
            'data' => [
                'product_url' => $resellerLink->product->url,
                'reseller_code' => $code
            ]
        ]);
    }
}
