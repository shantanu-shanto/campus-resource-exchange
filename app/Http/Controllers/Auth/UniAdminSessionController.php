<?php

// app/Http/Controllers/Auth/UniAdminSessionController.php
//
// Handles login and logout exclusively for university admins.
// Mirrors the pattern of SuperAdminSessionController — deliberately
// separated so each role's auth flow is independent.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UniAdminSessionController extends Controller
{
    /**
     * Show the uni admin login page.
     * If already logged in as uni_admin, redirect straight to dashboard.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'uni_admin') {
            return redirect()->route('uni-admin.dashboard');
        }

        return view('auth.uni-admin-login');
    }

    /**
     * Handle login form submission.
     *
     * Flow:
     * 1. Validate credentials format
     * 2. Check rate limiting
     * 3. Attempt login
     * 4. Boot anyone who is not uni_admin
     * 5. Check uni_admin account is verified (not suspended)
     * 6. Regenerate session and redirect to dashboard
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $this->incrementRateLimiter($request);

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Must be uni_admin — boot anyone else
        if (Auth::user()->role !== 'uni_admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'This login is restricted to University Admin accounts only.',
            ])->onlyInput('email');
        }

        // Uni admin account must be verified (super admin could have suspended it)
        if (Auth::user()->status !== 'verified') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your university admin account has been suspended. Please contact the Super Admin.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        return redirect()->intended(route('uni-admin.dashboard'));
    }

    /**
     * Log the uni admin out and redirect to their login page.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('uni-admin.login');
    }

    // ── Rate Limiting ─────────────────────────────────────────────
    // 5 attempts per minute per IP + email combo

    private function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    private function incrementRateLimiter(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), 60);
    }
}