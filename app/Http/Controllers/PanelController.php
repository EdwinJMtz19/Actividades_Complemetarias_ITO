<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Departamento;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user()->load(['alumno.carrera', 'instructor.departamento', 'coordinacion.departamento']);

        return view('panel', [
            'usuario' => $usuario,
            'rol' => $usuario->getRoleNames()->first(),
            'dashboard' => $this->dashboardDepartamento($usuario),
        ]);
    }

    /**
     * Resumen del departamento para el coordinador (HU-22). El administrador,
     * que no tiene un departamento propio, ve el resumen de todos. Si un
     * coordinador no tiene departamento asignado, no hay dashboard.
     *
     * @return array<string, mixed>|null
     */
    private function dashboardDepartamento(User $usuario): ?array
    {
        if (! $usuario->can('dashboard.ver')) {
            return null;
        }

        $departamentoId = $usuario->ambitoDepartamento();
        $esAdministrador = $usuario->esAdministrador();

        if (! $esAdministrador && $departamentoId === null) {
            return null;
        }

        $grupos = Grupo::query()
            ->when($departamentoId !== null, fn ($q) => $q->whereHas(
                'actividad',
                fn ($actividad) => $actividad->where('id_departamento', $departamentoId)
            ));

        $inscripciones = Inscripcion::query()
            ->when($departamentoId !== null, fn ($q) => $q->whereHas(
                'grupo.actividad',
                fn ($actividad) => $actividad->where('id_departamento', $departamentoId)
            ));

        $activas = (clone $inscripciones)->whereIn('estatus', Inscripcion::ACTIVOS);

        return [
            'departamento' => $departamentoId === null ? null : Departamento::find($departamentoId),
            'titulo' => $esAdministrador ? 'Dashboard general' : 'Dashboard del departamento',
            'subtitulo' => $esAdministrador ? 'Todos los departamentos' : null,
            'totalActividades' => Actividad::query()
                ->when($departamentoId !== null, fn ($q) => $q->where('id_departamento', $departamentoId))
                ->count(),
            'totalGrupos' => (clone $grupos)->count(),
            'gruposSinInstructor' => (clone $grupos)->whereNull('id_instructor')->count(),
            'totalInstructores' => Instructor::query()
                ->when($departamentoId !== null, fn ($q) => $q->where('id_departamento', $departamentoId))
                ->count(),
            'totalAlumnos' => (clone $activas)->distinct()->count('id_alumno'),
            'totalInscripcionesActivas' => (clone $activas)->count(),
            'aprobados' => (clone $inscripciones)->where('estatus', 'aprobado')->count(),
            'reprobados' => (clone $inscripciones)->where('estatus', 'reprobado')->count(),
            'gruposRecientes' => (clone $grupos)
                ->with(['actividad', 'instructor.usuario', 'semestre', 'horarios.dia'])
                ->orderByDesc('id_grupo')
                ->limit(5)
                ->get(),
        ];
    }
}
