<?php

namespace App\Http\Controllers;

use App\Mail\InscripcionConfirmada;
use App\Models\Actividad;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Inscripción de alumnos a los grupos del periodo vigente (HU-17).
 * Solo hay una inscripción activa por alumno; el cupo se descuenta de forma atómica.
 */
class InscripcionController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $alumno = $usuario->alumno;

        // El coordinador (y el administrador) ven la gestión; el alumno, su catálogo.
        if ($alumno === null) {
            if ($usuario->can('inscripciones.baja')) {
                return $this->gestion($request);
            }

            abort(403, 'No tienes acceso a las inscripciones.');
        }

        Semestre::cerrarVencidos();
        $semestre = Semestre::activo();
        $abierta = $semestre?->inscripcionesAbiertas() ?? false;

        $inscripcion = Inscripcion::query()
            ->activas()
            ->where('id_alumno', $alumno->id_alumno)
            ->with(['grupo.actividad.departamento', 'grupo.semestre', 'grupo.instructor.usuario', 'grupo.ubicacion', 'grupo.horarios.dia'])
            ->first();

        // Las bajas no se borran: se conservan para que el alumno consulte su historial.
        $historial = Inscripcion::query()
            ->where('id_alumno', $alumno->id_alumno)
            ->whereNotIn('estatus', Inscripcion::ACTIVOS)
            ->with(['grupo.actividad', 'grupo.semestre'])
            ->orderByDesc('fecha_inscripcion')
            ->get();

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
            'historial' => $historial,
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
            $inscripcion = DB::transaction(function () use ($alumno, $grupo) {
                // Descuenta el cupo solo si aún hay lugar: una sola sentencia atómica.
                $afectados = Grupo::query()
                    ->whereKey($grupo->getKey())
                    ->whereColumn('cupo_ocupado', '<', 'cupo_maximo')
                    ->increment('cupo_ocupado');

                if ($afectados === 0) {
                    return null;
                }

                return Inscripcion::create([
                    'id_alumno' => $alumno->id_alumno,
                    'id_grupo' => $grupo->id_grupo,
                    'fecha_inscripcion' => now(),
                    'estatus' => 'inscrito',
                ]);
            });
        } catch (QueryException) {
            return back()->with('error', 'No se pudo completar la inscripción: ya tienes una inscripción activa.');
        }

        if ($inscripcion === null) {
            return back()->with('error', 'El grupo ya no tiene cupo disponible.');
        }

        // La inscripción ya quedó confirmada: si el correo falla, se registra y no se deshace el alta.
        try {
            Mail::to($request->user())->queue(new InscripcionConfirmada($inscripcion));
        } catch (Throwable $error) {
            Log::error('No se pudo encolar el correo de confirmación de inscripción.', [
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'error' => $error->getMessage(),
            ]);
        }

        return redirect()->route('inscripciones.index')
            ->with('exito', "Te inscribiste a «{$grupo->actividad->nombre}» en el grupo {$grupo->grupo}.");
    }

    /**
     * Listado de inscripciones para coordinadores y administradores (HU-19).
     */
    private function gestion(Request $request): View
    {
        $usuario = $request->user();
        $departamento = $usuario->ambitoDepartamento();

        // Un coordinador sin departamento no debe ver las inscripciones de todo el sistema.
        if ($usuario->esCoordinador() && $departamento === null) {
            abort(403, 'No tienes un departamento asignado.');
        }

        $filtros = $request->validate([
            'actividad' => ['nullable', 'integer', Rule::exists(Actividad::class, 'id_actividad')],
            'estatus' => ['nullable', Rule::in(array_keys(Inscripcion::ESTATUS))],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $inscripciones = Inscripcion::query()
            ->with(['alumno.usuario', 'alumno.carrera', 'grupo.actividad', 'grupo.semestre'])
            ->when($departamento !== null, fn ($q) => $q->whereHas(
                'grupo.actividad',
                fn ($actividad) => $actividad->where('id_departamento', $departamento)
            ))
            ->when($filtros['actividad'] ?? null, fn ($q, $id) => $q->whereHas(
                'grupo',
                fn ($grupo) => $grupo->where('id_actividad', $id)
            ))
            ->when($filtros['estatus'] ?? null, fn ($q, $estatus) => $q->where('estatus', $estatus))
            ->when($filtros['buscar'] ?? null, function ($q, $buscar) {
                $q->whereHas('alumno', fn ($alumno) => $alumno
                    ->where('num_control', 'like', "%{$buscar}%")
                    ->orWhereHas('usuario', fn ($usuario) => $usuario
                        ->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                        ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")));
            })
            ->orderByDesc('fecha_inscripcion')
            ->paginate(15)
            ->withQueryString();

        $actividades = Actividad::query()
            ->when($departamento !== null, fn ($q) => $q->where('id_departamento', $departamento))
            ->orderBy('nombre')
            ->get();

        return view('inscripciones.gestion', [
            'inscripciones' => $inscripciones,
            'actividades' => $actividades,
            'estatus' => Inscripcion::ESTATUS,
            'filtros' => $filtros,
            'departamento' => $departamento,
        ]);
    }

    /**
     * Baja lógica de una inscripción (HU-19): cambia el estatus y libera el cupo en una transacción.
     * La puede hacer el coordinador de la actividad o el propio alumno.
     */
    public function baja(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        Gate::authorize('baja', $inscripcion);

        $inscripcion->loadMissing(['alumno.usuario', 'grupo.actividad']);

        $baja = DB::transaction(function () use ($inscripcion) {
            $bloqueada = Inscripcion::query()
                ->whereKey($inscripcion->getKey())
                ->lockForUpdate()
                ->first();

            if ($bloqueada === null || ! $bloqueada->estaActiva()) {
                return false;
            }

            $bloqueada->update(['estatus' => 'dado_de_baja']);

            // Libera un lugar sin permitir que el contador baje de cero.
            Grupo::query()
                ->whereKey($bloqueada->id_grupo)
                ->where('cupo_ocupado', '>', 0)
                ->decrement('cupo_ocupado');

            return true;
        });

        if (! $baja) {
            return back()->with('error', 'La inscripción ya no está activa; no se puede dar de baja.');
        }

        $actividad = $inscripcion->grupo->actividad->nombre;
        $esPropia = $request->user()->alumno !== null
            && (int) $request->user()->getKey() === (int) $inscripcion->id_alumno;

        if ($esPropia) {
            return back()->with('exito', "Cancelaste tu inscripción a «{$actividad}».");
        }

        $nombre = $inscripcion->alumno?->usuario?->nombre_completo ?? 'El alumno';

        return back()->with('exito', "Se dio de baja a {$nombre} de «{$actividad}».");
    }
}
