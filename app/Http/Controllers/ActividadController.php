<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarActividadRequest;
use App\Models\Actividad;
use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\Semestre;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Actividades complementarias. El administrador ve todas; el coordinador, solo las de su
 * departamento (ActividadPolicy y el alcance `visiblesPara`).
 */
class ActividadController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Actividad::class);

        $usuario = $request->user();
        $buscar = trim((string) $request->query('buscar'));
        $semestre = $request->query('semestre');
        $departamento = $usuario->esAdministrador() ? $request->query('departamento') : null;

        $actividades = Actividad::query()
            ->visiblesPara($usuario)
            ->with(['departamento', 'semestre', 'carreras'])
            ->withCount('grupos')
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->when($semestre, fn ($q) => $q->where('id_semestre', $semestre))
            ->when($departamento, fn ($q) => $q->where('id_departamento', $departamento))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('actividades.index', [
            'actividades' => $actividades,
            'buscar' => $buscar,
            'semestreElegido' => $semestre,
            'departamentoElegido' => $departamento,
            'semestres' => Semestre::orderByDesc('año')->orderByDesc('periodo')->get(),
            'departamentos' => $usuario->esAdministrador() ? Departamento::orderBy('nombre')->get() : collect(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Actividad::class);

        return view('actividades.create', $this->opcionesDelFormulario($request));
    }

    public function store(GuardarActividadRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $actividad = Actividad::create($request->safe()->except(['carreras', 'asignar_semestre_vigente']) + [
                // Nace en el semestre activo; sin ninguno queda sin semestre y se asigna al editarla.
                'id_semestre' => Semestre::activo()?->id_semestre,
            ]);

            $actividad->carreras()->sync($request->validated('carreras'));
        });

        return redirect()->route('actividades.index')->with('exito', 'Actividad creada correctamente.');
    }

    public function edit(Request $request, Actividad $actividad): View
    {
        Gate::authorize('update', $actividad);

        return view('actividades.edit', $this->opcionesDelFormulario($request) + [
            'actividad' => $actividad->load('carreras'),
        ]);
    }

    public function update(GuardarActividadRequest $request, Actividad $actividad): RedirectResponse
    {
        DB::transaction(function () use ($request, $actividad) {
            $actividad->update($request->safe()->except(['carreras', 'asignar_semestre_vigente']));

            if ($request->boolean('asignar_semestre_vigente') && ($activo = Semestre::activo())) {
                $actividad->update(['id_semestre' => $activo->id_semestre]);
            }

            $actividad->carreras()->sync($request->validated('carreras'));
        });

        return redirect()->route('actividades.index')->with('exito', 'Actividad actualizada correctamente.');
    }

    public function destroy(Actividad $actividad): RedirectResponse
    {
        Gate::authorize('delete', $actividad);

        $grupos = $actividad->grupos()->count();

        if ($grupos > 0) {
            return back()->with('error', "No se puede eliminar: la actividad tiene {$grupos} grupo(s). Quita o cancela sus grupos primero.");
        }

        try {
            $actividad->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar: la actividad tiene registros relacionados.');
        }

        return redirect()->route('actividades.index')->with('exito', 'Actividad eliminada correctamente.');
    }

    private function opcionesDelFormulario(Request $request): array
    {
        $usuario = $request->user();

        return [
            'carreras' => Carrera::orderBy('nombre')->get(),
            'semestreActivo' => Semestre::activo(),
            // El coordinador no elige departamento: trabaja en el suyo.
            'departamentos' => $usuario->esAdministrador() ? Departamento::orderBy('nombre')->get() : collect(),
            'departamentoPropio' => $usuario->esAdministrador() ? null : Departamento::find($usuario->ambitoDepartamento()),
        ];
    }
}
