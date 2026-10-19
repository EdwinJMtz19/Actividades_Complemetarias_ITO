<?php

use App\Http\Middleware\EnsureDepartamentoAsignado;
use App\Http\Middleware\EnsureUsuarioActivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // El servidor (Railway, proxy inverso) termina el TLS y reenvía la petición por HTTP.
        // Sin esto, Laravel creería que la petición es http:// y generaría URLs http://,
        // que el navegador bloquea como contenido mixto (la página se vería sin estilos).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            // Spatie Permission
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            // Propios
            'usuario.activo' => EnsureUsuarioActivo::class,
            'departamento.asignado' => EnsureDepartamentoAsignado::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('panel'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
