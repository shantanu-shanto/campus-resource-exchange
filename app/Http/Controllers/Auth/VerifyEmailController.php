<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * FIX: was redirecting to hardcoded 'dashboard' route which doesn't
     *      exist for super_admin and uni_admin roles, causing a 404.
     *      Now redirects each role to their correct dashboard.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $destination = match($user->role) {
            'super_admin' => route('super-admin.dashboard') . '?verified=1',
            'uni_admin'   => route('uni-admin.dashboard') . '?verified=1',
            default       => route('home') . '?verified=1',
        };

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($destination);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($destination);
    }
}