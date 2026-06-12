<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (getenv('APP_ENV') === 'testing') {
    $_ENV = array_intersect_key($_ENV, array_flip(['APP_KEY', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_NAME', 'APP_LOCALE', 'LOG_CHANNEL', 'LOG_LEVEL']));

    $cachedConfig = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.php';
    if (file_exists($cachedConfig)) {
        @unlink($cachedConfig);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
