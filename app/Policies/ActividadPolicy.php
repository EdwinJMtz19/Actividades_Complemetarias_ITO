<?php

namespace App\Policies;

use App\Models\Actividad;
use App\Models\User;

/**
 * Reglas de acceso a las actividades. El administrador pasa todas por Gate::before (AppServiceProvider).
 * Un coordinador solo ve y administra las actividades de SU departamento.
 */
class ActividadPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->can('actividades.ver');
    }

    public function create(User $auth): bool
    {
        return $auth->can('actividades.crear');
    }

    public function update(User $auth, Actividad $actividad): bool
    {
        return $auth->can('actividades.editar') && $this->dentroDelAmbito($auth, $actividad);
    }

    public function delete(User $auth, Actividad $actividad): bool
    {
        return $auth->can('actividades.eliminar') && $this->dentroDelAmbito($auth, $actividad);
    }

    private function dentroDelAmbito(User $auth, Actividad $actividad): bool
    {
        $departamento = $auth->ambitoDepartamento();

        // Sin departamento solo pasa el administrador (que ya entró por Gate::before).
        return $departamento !== null && (int) $departamento === (int) $actividad->id_departamento;
    }
}
