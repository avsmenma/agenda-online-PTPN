<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SECURITY: Add security headers globally
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'autologin' => \App\Http\Middleware\AutoLoginMiddleware::class, // SECURITY: Now only checks auth, no auto-login
            'prevent.url.manipulation' => \App\Http\Middleware\PreventUrlManipulation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
