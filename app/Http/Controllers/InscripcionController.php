<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Inscripción de alumnos a los grupos del periodo vigente (HU-17).
 * Solo hay una inscripción activa por alumno; el cupo se descuenta de forma atómica.
 */
class InscripcionController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = $request->user()->alumno;

        if ($alumno === null) {
            abort(403, 'Solo los alumnos pueden consultar sus inscripciones.');
        }

        Semestre::cerrarVencidos();
        $semestre = Semestre::activo();
        $abierta = $semestre?->inscripcionesAbiertas() ?? false;

        $inscripcion = Inscripcion::query()
            ->activas()
            ->where('id_alumno', $alumno->id_alumno)
            ->with(['grupo.actividad.departamento', 'grupo.semestre', 'grupo.instructor.usuario', 'grupo.ubicacion', 'grupo.horarios.dia'])
            ->first();

        $grupos = $semestre === null
            ? collect()
            : Grupo::query()
                ->inscribiblesPara($alumno, $semestre)
                ->with(['actividad.departamento', 'semestre', 'instructor.usuario', 'ubicacion', 'horarios.dia'])
                ->orderBy('id_actividad')
                ->orderBy('grupo')
                ->get();

        return view('inscripciones.index', [
            'alumno' => $alumno,
            'semestre' => $semestre,
            'abierta' => $abierta,
            'inscripcion' => $inscripcion,
            'grupos' => $grupos,
        ]);
    }

    public function store(Request $request, Grupo $grupo): RedirectResponse
    {
        Gate::authorize('inscribirse', $grupo);

        /** @var Alumno $alumno */
        $alumno = $request->user()->alumno;
        $grupo->loadMissing('actividad.carreras');

        Semestre::cerrarVencidos();
        $semestre = Semestre::activo();

        if ($semestre === null || ! $semestre->inscripcionesAbiertas()) {
            return back()->with('error', 'La ventana de inscripción no está abierta en este momento.');
        }

        if ((int) $grupo->id_semestre !== (int) $semestre->id_semestre) {
            return back()->with('error', 'El grupo no pertenece al periodo escolar vigente.');
        }

        if ($grupo->estatus !== 'abierta') {
            return back()->with('error', 'El grupo no está abierto para inscripciones.');
        }

        if (! $grupo->actividad->disponible) {
            return back()->with('error', 'La actividad no está disponible en este periodo.');
        }

        if (! $grupo->actividad->carreras->contains('id_carrera', $alumno->id_carrera)) {
            return back()->with('error', 'La actividad no está dirigida a tu carrera.');
        }

        $activa = Inscripcion::query()
            ->activas()
            ->where('id_alumno', $alumno->id_alumno)
            ->with('grupo.actividad')
            ->first();

        if ($activa !== null) {
            return back()->with('error', "Ya tienes una inscripción activa en «{$activa->grupo->actividad->nombre}». Solo se permite una a la vez.");
        }

        try {
            $inscrito = DB::transaction(function () use ($alumno, $grupo) {
                // Descuenta el cupo solo si aún hay lugar: una sola sentencia atómica.
                $afectados = Grupo::query()
                    ->whereKey($grupo->getKey())
                    ->whereColumn('cupo_ocupado', '<', 'cupo_maximo')
                    ->increment('cupo_ocupado');

                if ($afectados === 0) {
                    return false;
                }

                Inscripcion::create([
                    'id_alumno' => $alumno->id_alumno,
                    'id_grupo' => $grupo->id_grupo,
                    'fecha_inscripcion' => now(),
                    'estatus' => 'inscrito',
                ]);

                return true;
            });
        } catch (QueryException) {
            return back()->with('error', 'No se pudo completar la inscripción: ya tienes una inscripción activa.');
        }

        if (! $inscrito) {
            return back()->with('error', 'El grupo ya no tiene cupo disponible.');
        }

        return redirect()->route('inscripciones.index')
            ->with('exito', "Te inscribiste a «{$grupo->actividad->nombre}» en el grupo {$grupo->grupo}.");
    }
}
