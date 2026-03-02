<?php

// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Old single 'admin' alias removed — replaced with role-specific middleware
            'super_admin'   => \App\Http\Middleware\SuperAdminMiddleware::class,
            'uni_admin'     => \App\Http\Middleware\UniAdminMiddleware::class,
            'verified_user' => \App\Http\Middleware\VerifiedUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();