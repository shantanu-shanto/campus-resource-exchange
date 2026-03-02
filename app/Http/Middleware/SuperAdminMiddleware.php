<?php

// app/Http/Middleware/SuperAdminMiddleware.php
//
// CHANGE FROM ORIGINAL:
// Previously redirected unauthenticated users to route('login').
// Now aborts with 403 for ALL non-super-admin access — whether the
// visitor is a guest, a regular user, or a uni_admin.
//
// Rationale: the super admin panel is a hidden, restricted surface.
// Redirecting to a login page would advertise its existence.
// A 403 gives nothing away.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Not logged in — 403, no redirect
        if (! auth()->check()) {
            abort(403, 'Access denied.');
        }

        // Logged in but wrong role — 403
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Super Admin access required.');
        }

        return $next($request);
    }
}