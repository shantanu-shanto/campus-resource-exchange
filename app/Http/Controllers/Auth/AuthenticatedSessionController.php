<?php

// app/Http/Controllers/Auth/AuthenticatedSessionController.php
//
// CHANGE: Added a block that prevents super_admin and uni_admin
// from logging in through the shared /login page.
// They each have their own dedicated login routes:
//   Super Admin → /super-admin/login
//   Uni Admin   → /uni-admin/login

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

        $user = Auth::user();

        // ── Block admins from using the shared login ──────────────
        // Super admins and uni admins have dedicated login pages.
        // If they land here, log them out and redirect them correctly.
        if ($user->role === 'super_admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('super-admin.login')->withErrors([
                'email' => 'Please use the Super Admin login page.',
            ]);
        }

        if ($user->role === 'uni_admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('uni-admin.login')->withErrors([
                'email' => 'Please use the University Admin login page.',
            ]);
        }

        // ── Regular user status checks ────────────────────────────
        if ($user->status === 'pending') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is pending approval by your University Admin.',
            ]);
        }

        if ($user->status === 'rejected') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account registration was rejected. Please contact your University Admin.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}