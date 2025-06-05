<?php

use App\Console\Commands\UpdateProductFlags;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        // Register custom artisan commands here
        UpdateProductFlags::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Register admin middleware
        $middleware->alias(
            [
                'admin' => \App\Http\Middleware\IsAdmin::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
