<?php

namespace App\Policies;

use App\Models\Inscripcion;
use App\Models\User;

/**
 * Reglas de acceso a las inscripciones.
 *  - Coordinador: consulta y da de baja las inscripciones de los grupos de SU departamento.
 *  - Alumno: consulta las suyas, se inscribe (la inscripción la autoriza GrupoPolicy) y puede
 *    cancelar su propia inscripción activa.
 *  - Instructor: sin acceso.
 * El administrador pasa todas las habilidades por Gate::before (AppServiceProvider).
 */
class InscripcionPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->can('inscripciones.ver');
    }

    /**
     * Baja lógica: la fila se conserva con estatus `dado_de_baja` para no perder el historial.
     * El alumno solo puede dar de baja su propia inscripción.
     */
    public function baja(User $auth, Inscripcion $inscripcion): bool
    {
        if (! $auth->can('inscripciones.baja')) {
            return false;
        }

        return $this->esPropia($auth, $inscripcion)
            || $this->esDeSuDepartamento($auth, $inscripcion);
    }

    private function esPropia(User $auth, Inscripcion $inscripcion): bool
    {
        return $auth->alumno !== null
            && (int) $auth->alumno->id_alumno === (int) $inscripcion->id_alumno;
    }

    private function esDeSuDepartamento(User $auth, Inscripcion $inscripcion): bool
    {
        $departamento = $auth->esCoordinador() ? $auth->ambitoDepartamento() : null;

        return $departamento !== null
            && (int) $departamento === (int) $inscripcion->grupo->actividad->id_departamento;
    }
}
