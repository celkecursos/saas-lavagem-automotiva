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
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'car-wash' => \App\Http\Middleware\SetCurrentCarWash::class,
            'parking-active' => \App\Http\Middleware\EnsureParkingActive::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);

        // Bloqueia conta suspensa em QUALQUER área, mesmo com sessão já
        // ativa (task-22, seção 4) — roda em toda request web, depois
        // de StartSession resolver o usuário autenticado.
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsNotSuspended::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
