<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Inscripcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Calificaciones de las inscripciones (Sprint 5, HU-20 y HU-21).
 * El instructor registra el desempeño, el estatus pasa a aprobado o reprobado y los créditos
 * acumulados del alumno se ajustan en la misma transacción.
 */
class CalificacionController extends Controller
{
    public function edit(Inscripcion $inscripcion): View
    {
        Gate::authorize('verCalificacion', $inscripcion);

        $inscripcion->load([
            'alumno.usuario',
            'alumno.carrera',
            'grupo.actividad',
            'grupo.semestre',
            'calificacion',
        ]);

        return view('calificaciones.edit', [
            'inscripcion' => $inscripcion,
            'calificacion' => $inscripcion->calificacion,
            'desempenios' => Calificacion::DESEMPENIOS,
        ]);
    }

    public function update(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        Gate::authorize('calificar', $inscripcion);

        $datos = $request->validate([
            'desempenio' => ['required', Rule::in(array_keys(Calificacion::DESEMPENIOS))],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $nuevoEstatus = in_array($datos['desempenio'], ['bueno', 'excelente'], true)
            ? 'aprobado'
            : 'reprobado';

        DB::transaction(function () use ($inscripcion, $datos, $nuevoEstatus) {
            // Bloquea la fila para que dos calificaciones simultáneas no dupliquen créditos.
            $bloqueada = Inscripcion::query()
                ->whereKey($inscripcion->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($bloqueada->estatus === 'dado_de_baja') {
                abort(403, 'La inscripción está dada de baja; no se puede calificar.');
            }

            $bloqueada->loadMissing('grupo.actividad');
            $estatusAnterior = $bloqueada->estatus;

            Calificacion::updateOrCreate(
                ['id_inscripcion' => $bloqueada->id_inscripcion],
                [
                    'desempenio' => $datos['desempenio'],
                    'observaciones' => $datos['observaciones'] ?? null,
                ]
            );

            $bloqueada->update(['estatus' => $nuevoEstatus]);

            $this->ajustarCreditos($bloqueada, $estatusAnterior, $nuevoEstatus);
        });

        return redirect()
            ->route('grupos.show', $inscripcion->id_grupo)
            ->with('exito', 'Calificación guardada correctamente.');
    }

    /**
     * Suma los créditos de la actividad cuando la inscripción pasa a aprobada por primera vez y
     * los descuenta si se revierte a reprobada. Cambiar entre desempeños aprobatorios no los
     * duplica. Nunca deja el acumulado en negativo.
     */
    private function ajustarCreditos(Inscripcion $inscripcion, string $estatusAnterior, string $nuevoEstatus): void
    {
        if ($estatusAnterior === $nuevoEstatus) {
            return;
        }

        if ($nuevoEstatus !== 'aprobado' && $estatusAnterior !== 'aprobado') {
            return;
        }

        $creditos = (int) ($inscripcion->grupo->actividad->creditos ?? 0);

        if ($creditos <= 0) {
            return;
        }

        $consulta = Alumno::query()->whereKey($inscripcion->id_alumno);

        if ($nuevoEstatus === 'aprobado') {
            $consulta->increment('creditos_acumulados', $creditos);
        } else {
            $consulta->where('creditos_acumulados', '>=', $creditos)
                ->decrement('creditos_acumulados', $creditos);
        }
    }
}
