<?php

// app/Http/Middleware/VerifiedUserMiddleware.php
// Run: php artisan make:middleware VerifiedUserMiddleware

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Admins bypass this middleware (they have their own)
        if (in_array($user->role, ['super_admin', 'uni_admin'])) {
            return $next($request);
        }

        // Regular users must be verified by their uni admin
        if ($user->status !== 'verified') {
            abort(403, 'Your account is pending approval by your University Admin.');
        }

        return $next($request);
    }
}