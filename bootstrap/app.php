<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureTwoFactorForElevatedRoles;
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
        $middleware->alias([
            'elevated.2fa' => EnsureTwoFactorForElevatedRoles::class,
        ]);

        // Global en vez de por-ruta: así cubre también rutas nuevas que se
        // agreguen después sin acordarse de repetir el middleware ahí.
        $middleware->web(append: [EnsureAccountIsActive::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
