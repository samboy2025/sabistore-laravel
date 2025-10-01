<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

/**
 * Class AuthenticatedSessionController
 *
 * Handles user authentication sessions, including login and logout.
 *
 * @package App\Http\Controllers\Auth
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return View Returns the login page view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Authenticates the user, regenerates the session, and redirects them based on their role.
     *
     * @param LoginRequest $request The request object containing login credentials.
     * @return RedirectResponse Redirects the user to their respective dashboard.
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
     * Destroy an authenticated session (logout).
     *
     * Logs the user out, invalidates their session, and regenerates the CSRF token.
     *
     * @param Request $request The incoming request.
     * @return RedirectResponse Redirects the user to the homepage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect users after a successful login based on their role.
     *
     * @param User $user The authenticated user.
     * @return RedirectResponse The appropriate redirect response based on the user's role.
     */
    private function redirectAfterLogin(User $user): RedirectResponse
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
