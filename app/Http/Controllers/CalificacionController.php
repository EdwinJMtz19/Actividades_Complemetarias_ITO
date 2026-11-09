<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Inscripcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Calificaciones de las inscripciones (Sprint 5, HU-20).
 * El instructor registra el desempeño y el estatus pasa a aprobado o reprobado.
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

        DB::transaction(function () use ($inscripcion, $datos) {
            Calificacion::updateOrCreate(
                ['id_inscripcion' => $inscripcion->id_inscripcion],
                [
                    'desempenio' => $datos['desempenio'],
                    'observaciones' => $datos['observaciones'] ?? null,
                ]
            );

            $inscripcion->update([
                'estatus' => in_array($datos['desempenio'], ['bueno', 'excelente'], true)
                    ? 'aprobado'
                    : 'reprobado',
            ]);
        });

        return redirect()
            ->route('grupos.show', $inscripcion->id_grupo)
            ->with('exito', 'Calificación guardada correctamente.');
    }
}
