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
        $middleware->prepend(\App\Http\Middleware\NormalizeForwardedScheme::class);

        // bootstrap/app.php runs before the config repository is fully bootstrapped.
        $shouldTrustProxies = filter_var(env('TRUST_PROXIES', false), FILTER_VALIDATE_BOOL);

        if ($shouldTrustProxies) {
            $trustedProxies = env('TRUSTED_PROXIES', '*');
            $trustedProxies = $trustedProxies === '*' ? '*' : array_map('trim', explode(',', $trustedProxies));

            $middleware->trustProxies(at: $trustedProxies);
        }

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
