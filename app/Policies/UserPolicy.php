<?php

namespace App\Policies;

use App\Models\User;

/**
 * Reglas de acceso al módulo de usuarios.
 * El administrador pasa todas por Gate::before (AppServiceProvider).
 * Un coordinador solo ve a los instructores de SU departamento.
 */
class UserPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->can('usuarios.ver');
    }

    public function view(User $auth, User $objetivo): bool
    {
        return $auth->can('usuarios.ver') && $this->dentroDelAmbito($auth, $objetivo);
    }

    public function create(User $auth): bool
    {
        return $auth->can('usuarios.crear');
    }

    public function update(User $auth, User $objetivo): bool
    {
        return $auth->can('usuarios.editar') && $this->dentroDelAmbito($auth, $objetivo);
    }

    public function delete(User $auth, User $objetivo): bool
    {
        return $auth->can('usuarios.eliminar')
            && ! $auth->is($objetivo)
            && $this->dentroDelAmbito($auth, $objetivo);
    }

    /** Activar o desactivar la cuenta (nunca la propia). */
    public function toggle(User $auth, User $objetivo): bool
    {
        return $auth->can('usuarios.editar')
            && ! $auth->is($objetivo)
            && $this->dentroDelAmbito($auth, $objetivo);
    }

    private function dentroDelAmbito(User $auth, User $objetivo): bool
    {
        $departamento = $auth->ambitoDepartamento();

        if ($departamento === null) {
            // Sin restricción de departamento solo si es administrador;
            // un usuario con departamento indefinido no ve nada.
            return $auth->esAdministrador();
        }

        return $objetivo->hasRole('instructor')
            && $objetivo->instructor?->id_departamento === $departamento;
    }
}
