<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to access this area.');
        }

        // Check if user is a vendor
        if ($user->isVendor()) {
            // Check if membership is active and payment date is valid
            if (!$user->membership_active || !$user->membership_paid_at) {
                // If this is an AJAX request, return JSON error
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Membership payment required',
                        'message' => 'Please complete your ₦1,000 membership payment to access vendor features.',
                        'redirect_url' => route('membership.payment')
                    ], 403);
                }

                return redirect()->route('membership.payment')
                    ->with('error', 'Please complete your ₦1,000 membership payment to access vendor features.')
                    ->with('info', 'Your membership enables you to upload products, manage orders, and access your public shop.');
            }

            // Additional check: Ensure payment is recent (within last year)
            if ($user->membership_paid_at && $user->membership_paid_at->diffInDays(now()) > 365) {
                $user->update(['membership_active' => false]);
                
                return redirect()->route('membership.payment')
                    ->with('warning', 'Your membership has expired. Please renew to continue accessing vendor features.');
            }
        }

        return $next($request);
    }
} 