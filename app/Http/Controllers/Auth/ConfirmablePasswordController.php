<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     *
     * FIX: was redirecting to hardcoded 'dashboard' route which doesn't
     *      exist for super_admin and uni_admin roles, causing a 404.
     *      Now redirects each role to their correct dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email'    => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        $user = $request->user();

        $destination = match($user->role) {
            'super_admin' => route('super-admin.dashboard'),
            'uni_admin'   => route('uni-admin.dashboard'),
            default       => route('home'),
        };

        return redirect()->intended($destination);
    }
}