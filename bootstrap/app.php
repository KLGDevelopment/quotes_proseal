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
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        // Captura APP_URL una sola vez y evita null
        $appUrl = env('APP_URL', '');

        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl); // usa APP_URL como base

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https'); // fuerza https siempre
            }
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
