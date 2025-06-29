<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Role-based redirect after login
        return $this->redirectAfterLogin($user);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect users after successful login based on their role
     */
    private function redirectAfterLogin($user): RedirectResponse
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, Admin!');
            
            case 'vendor':
                // Check if vendor has paid membership
                if (!$user->membership_active) {
                    return redirect()->route('membership.payment')
                        ->with('warning', 'Please complete your membership payment to access vendor features.');
                }
                
                return redirect()->intended(route('vendor.dashboard'))
                    ->with('success', 'Welcome back to your vendor dashboard!');
            
            case 'buyer':
                return redirect()->intended(route('buyer.dashboard'))
                    ->with('success', 'Welcome back!');
            
            default:
                return redirect()->intended(route('home'));
        }
    }
}
