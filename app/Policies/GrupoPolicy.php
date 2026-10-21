<?php

namespace App\Policies;

use App\Models\Grupo;
use App\Models\User;

/**
 * Reglas de acceso a los grupos. El administrador pasa todas por Gate::before (AppServiceProvider).
 *  - Coordinador: ve y administra los grupos de las actividades de SU departamento.
 *  - Instructor: solo ve los grupos que tiene asignados; no los modifica.
 */
class GrupoPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->can('grupos.ver');
    }

    public function view(User $auth, Grupo $grupo): bool
    {
        return $auth->can('grupos.ver')
            && ($this->esDeSuDepartamento($auth, $grupo) || $this->loImparte($auth, $grupo));
    }

    public function create(User $auth): bool
    {
        return $auth->can('grupos.crear');
    }

    public function update(User $auth, Grupo $grupo): bool
    {
        return $auth->can('grupos.editar') && $this->esDeSuDepartamento($auth, $grupo);
    }

    public function delete(User $auth, Grupo $grupo): bool
    {
        return $auth->can('grupos.eliminar') && $this->esDeSuDepartamento($auth, $grupo);
    }

    private function esDeSuDepartamento(User $auth, Grupo $grupo): bool
    {
        $departamento = $auth->esCoordinador() ? $auth->ambitoDepartamento() : null;

        return $departamento !== null && (int) $departamento === (int) $grupo->actividad->id_departamento;
    }

    private function loImparte(User $auth, Grupo $grupo): bool
    {
        return $auth->hasRole('instructor') && (int) $grupo->id_instructor === (int) $auth->getKey();
    }
}
