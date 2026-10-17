<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // El administrador tiene acceso a todo, con una excepción: nadie puede
        // eliminarse ni desactivarse a sí mismo (evita quedarse sin administradores).
        Gate::before(function (User $usuario, string $habilidad, array $argumentos) {
            if (! $usuario->hasRole('administrador')) {
                return null;
            }

            $objetivo = $argumentos[0] ?? null;

            if (in_array($habilidad, ['delete', 'toggle'], true)
                && $objetivo instanceof User
                && $objetivo->is($usuario)) {
                return false;
            }

            return true;
        });
    }
}
