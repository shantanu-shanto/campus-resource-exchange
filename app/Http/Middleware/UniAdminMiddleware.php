<?php

// app/Http/Middleware/UniAdminMiddleware.php
//
// CHANGE: Now returns 403 for all non-uni-admin access,
// matching the pattern of SuperAdminMiddleware.
// The uni admin has their own login page at /uni-admin/login.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UniAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            abort(403, 'Access denied.');
        }

        if (auth()->user()->role !== 'uni_admin') {
            abort(403, 'University Admin access required.');
        }

        return $next($request);
    }
}