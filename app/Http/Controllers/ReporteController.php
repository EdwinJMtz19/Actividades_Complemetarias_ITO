<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Reportes administrativos de solo lectura (HU-23): padrón de alumnos,
 * monitor de inscripciones y últimos accesos. El acceso se controla con el
 * permiso `reportes.ver`, asignado únicamente al administrador.
 */
class ReporteController extends Controller
{
    public function index(): View
    {
        Gate::authorize('reportes.ver');

        return view('reportes.index', [
            'totales' => [
                'alumnos' => Alumno::count(),
                'inscripciones' => Inscripcion::count(),
                'grupos' => Grupo::count(),
                'actividades' => Actividad::count(),
            ],
        ]);
    }

    /** Padrón de alumnos con búsqueda por nombre, correo o número de control. */
    public function alumnos(Request $request): View
    {
        Gate::authorize('reportes.ver');

        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'carrera' => ['nullable', 'integer', Rule::exists(Carrera::class, 'id_carrera')],
        ]);

        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        $alumnos = Alumno::query()
            ->with(['usuario', 'carrera'])
            ->withCount([
                'inscripciones as inscripciones_activas' => fn ($q) => $q->whereIn('estatus', Inscripcion::ACTIVOS),
            ])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $texto = "%{$buscar}%";

                $q->where(fn ($w) => $w
                    ->where('num_control', 'like', $texto)
                    ->orWhereHas('usuario', fn ($usuario) => $usuario
                        ->where('nombre', 'like', $texto)
                        ->orWhere('apellido_paterno', 'like', $texto)
                        ->orWhere('apellido_materno', 'like', $texto)
                        ->orWhere('email', 'like', $texto)));
            })
            ->when($filtros['carrera'] ?? null, fn ($q, $id) => $q->where('id_carrera', $id))
            ->orderBy('id_alumno')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.alumnos', [
            'alumnos' => $alumnos,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'filtros' => $filtros,
            'buscar' => $buscar,
        ]);
    }

    /** Monitor de inscripciones con filtros por estatus, carrera, actividad, departamento y semestre. */
    public function inscripciones(Request $request): View
    {
        Gate::authorize('reportes.ver');

        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estatus' => ['nullable', Rule::in(array_keys(Inscripcion::ESTATUS))],
            'carrera' => ['nullable', 'integer', Rule::exists(Carrera::class, 'id_carrera')],
            'actividad' => ['nullable', 'integer', Rule::exists(Actividad::class, 'id_actividad')],
            'departamento' => ['nullable', 'integer', Rule::exists(Departamento::class, 'id_departamento')],
            'semestre' => ['nullable', 'integer', Rule::exists(Semestre::class, 'id_semestre')],
        ]);

        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        $base = Inscripcion::query()
            ->when($buscar !== '', function ($q) use ($buscar) {
                $texto = "%{$buscar}%";

                $q->where(fn ($w) => $w
                    ->whereHas('alumno', fn ($alumno) => $alumno->where('num_control', 'like', $texto))
                    ->orWhereHas('alumno.usuario', fn ($usuario) => $usuario
                        ->where('nombre', 'like', $texto)
                        ->orWhere('apellido_paterno', 'like', $texto)
                        ->orWhere('apellido_materno', 'like', $texto)
                        ->orWhere('email', 'like', $texto)));
            })
            ->when($filtros['carrera'] ?? null, fn ($q, $id) => $q->whereHas(
                'alumno',
                fn ($alumno) => $alumno->where('id_carrera', $id)
            ))
            ->when($filtros['actividad'] ?? null, fn ($q, $id) => $q->whereHas(
                'grupo',
                fn ($grupo) => $grupo->where('id_actividad', $id)
            ))
            ->when($filtros['departamento'] ?? null, fn ($q, $id) => $q->whereHas(
                'grupo.actividad',
                fn ($actividad) => $actividad->where('id_departamento', $id)
            ))
            ->when($filtros['semestre'] ?? null, fn ($q, $id) => $q->whereHas(
                'grupo',
                fn ($grupo) => $grupo->where('id_semestre', $id)
            ));

        $totales = (clone $base)
            ->selectRaw('estatus, count(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');

        $inscripciones = (clone $base)
            ->when($filtros['estatus'] ?? null, fn ($q, $estatus) => $q->where('estatus', $estatus))
            ->with([
                'alumno.usuario',
                'alumno.carrera',
                'grupo.actividad.departamento',
                'grupo.semestre',
                'grupo.instructor.usuario',
            ])
            ->orderByDesc('fecha_inscripcion')
            ->orderByDesc('id_inscripcion')
            ->paginate(15)
            ->withQueryString();

        return view('reportes.inscripciones', [
            'inscripciones' => $inscripciones,
            'totales' => $totales,
            'estatus' => Inscripcion::ESTATUS,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'actividades' => Actividad::orderBy('nombre')->get(),
            'departamentos' => Departamento::orderBy('nombre')->get(),
            'semestres' => Semestre::orderByDesc('año')->orderByDesc('periodo')->get(),
            'filtros' => $filtros,
            'buscar' => $buscar,
        ]);
    }

    /** Últimos accesos registrados en la cuenta de cada usuario. */
    public function accesos(Request $request): View
    {
        Gate::authorize('reportes.ver');

        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', Rule::in(array_keys(User::TIPO_POR_ROL))],
        ]);

        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        $usuarios = User::query()
            ->with('roles')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $texto = "%{$buscar}%";

                $q->where(fn ($w) => $w
                    ->where('nombre', 'like', $texto)
                    ->orWhere('apellido_paterno', 'like', $texto)
                    ->orWhere('apellido_materno', 'like', $texto)
                    ->orWhere('email', 'like', $texto));
            })
            ->when($filtros['rol'] ?? null, fn ($q, $rol) => $q->role($rol))
            ->orderByRaw('ultimo_acceso IS NULL')
            ->orderByDesc('ultimo_acceso')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('reportes.accesos', [
            'usuarios' => $usuarios,
            'resumen' => [
                'total' => User::count(),
                'activos' => User::where('activo', true)->count(),
                'sinAcceso' => User::whereNull('ultimo_acceso')->count(),
                'recientes' => User::where('ultimo_acceso', '>=', now()->subDays(7))->count(),
            ],
            'roles' => array_keys(User::TIPO_POR_ROL),
            'filtros' => $filtros,
            'buscar' => $buscar,
        ]);
    }
}
