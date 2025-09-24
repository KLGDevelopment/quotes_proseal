<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Confiar SIEMPRE en los proxies, pero solo en los headers X-Forwarded-*
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        // Fuerza la URL base si está definida (evita nulos)
        $appUrl = env('APP_URL');
        if (is_string($appUrl) && $appUrl !== '') {
            URL::forceRootUrl($appUrl);
            // Si APP_URL trae https, forzar https
            if (stripos($appUrl, 'https://') === 0) {
                URL::forceScheme('https');
            }
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    });
