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
    ->withMiddleware(function (Middleware $middleware) {

        // Register middleware aliases so routes can use them by short name
        $middleware->alias([
            'role'           => \App\Http\Middleware\RoleMiddleware::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
        ]);

        // Append session timeout to every web request
        $middleware->web(append: [
            \App\Http\Middleware\SessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();