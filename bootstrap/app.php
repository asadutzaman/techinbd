<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The public site reaches the app through a Cloudflare Tunnel running on this machine. Trusting it
        // makes links https:// on the https site, so browsers don't block images and AJAX as mixed content.
        // Only the visitor's IP and scheme are taken from it: a forwarded host could poison reset links.
        $middleware->trustProxies(
            at: ['127.0.0.1', '::1'],
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // Lets the browser finish a page while its customer email is still being sent (see the class).
        // Outermost, so it measures the final response.
        $middleware->prepend(\App\Http\Middleware\SetContentLength::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
