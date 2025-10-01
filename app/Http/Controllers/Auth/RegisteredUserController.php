<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Class RegisteredUserController
 *
 * Handles the registration process for new users.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return View Returns the registration page view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Validates the registration data, creates a new user, logs them in, and redirects them based on their role.
     *
     * @param Request $request The request object containing registration data.
     * @return RedirectResponse Redirects the user to the appropriate page after registration.
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:vendor,buyer'],
            'whatsapp_number' => ['required_if:role,vendor', 'string', 'max:20'],
            'bvn_nin' => ['required_if:role,vendor', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'whatsapp_number' => $request->whatsapp_number,
            'bvn_nin' => $request->bvn_nin,
            'membership_active' => $request->role === 'buyer', // Buyers get immediate access
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Role-based redirect
        return $this->redirectAfterRegistration($user);
    }

    /**
     * Redirect users after successful registration based on their role.
     *
     * @param User $user The newly registered user.
     * @return RedirectResponse The appropriate redirect response based on the user's role.
     */
    private function redirectAfterRegistration(User $user): RedirectResponse
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            
            case 'vendor':
                // Vendors must pay membership before accessing dashboard
                return redirect()->route('membership.payment')
                    ->with('success', 'Registration successful! Please complete your membership payment to access vendor features.');
            
            case 'buyer':
                return redirect()->route('buyer.dashboard')
                    ->with('success', 'Welcome to SabiStore! You can now browse and order from vendors.');
            
            default:
                return redirect()->route('home');
        }
    }
}
