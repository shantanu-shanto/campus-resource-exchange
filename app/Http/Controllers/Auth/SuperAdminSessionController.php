<?php

// app/Http/Controllers/Auth/SuperAdminSessionController.php
//
// Handles login and logout exclusively for the super admin.
// Deliberately separated from AuthenticatedSessionController
// so the super admin flow can never be affected by changes
// to the regular user login flow — and vice versa.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperAdminSessionController extends Controller
{
    /**
     * Show the super admin login page.
     * If already logged in as super admin, skip straight to dashboard.
     * If logged in as a different role, do NOT auto-redirect — force
     * them to use their own login, or they end up on a 403 page.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'super_admin') {
            return redirect()->route('super-admin.dashboard');
        }

        return view('auth.super-admin-login');
    }

    /**
     * Handle login form submission.
     *
     * Flow:
     * 1. Validate credentials format
     * 2. Attempt login via Auth::attempt()
     * 3. Hard-stop if authenticated user is not super_admin
     * 4. Regenerate session and redirect to dashboard
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Rate limiting — prevents brute force on this high-value endpoint
        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            // Increment rate limit hit counter
            $this->incrementRateLimiter($request);

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Auth succeeded — but must be super_admin. If someone accidentally
        // tries to log in here with a uni_admin or user account, boot them.
        if (Auth::user()->role !== 'super_admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This login is restricted to Super Admin accounts only.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('super-admin.dashboard'));
    }

    /**
     * Log the super admin out and return to super admin login page.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }

    // ----------------------------------------
    // Rate Limiting Helpers
    // Throttles to 5 attempts per minute per IP+email combo.
    // ----------------------------------------

    private function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($this->throttleKey($request));

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    private function incrementRateLimiter(Request $request): void
    {
        \Illuminate\Support\Facades\RateLimiter::hit($this->throttleKey($request), 60);
    }
}