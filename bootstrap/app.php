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
        // The application's web routes use Laravel's standard session and CSRF middleware.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Keep exception handling at Laravel's defaults.
    })
    ->create();
