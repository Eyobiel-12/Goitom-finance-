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
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Security middleware
        $middleware->alias([
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'audit.log' => \App\Http\Middleware\AuditLogMiddleware::class,
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);

        // Apply rate limiting to sensitive routes
        $middleware->group('api', [
            'rate.limit',
        ]);

        // Apply audit logging to financial operations
        $middleware->group('financial', [
            'audit.log',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
