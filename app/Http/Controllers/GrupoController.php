<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarGrupoRequest;
use App\Models\Actividad;
use App\Models\DiaSemana;
use App\Models\Grupo;
use App\Models\Instructor;
use App\Models\Semestre;
use App\Models\Ubicacion;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Grupos de actividades con sus horarios. Cada rol ve lo suyo: el administrador todo, el coordinador los
 * grupos de su departamento y el instructor solo los que imparte (GrupoPolicy y el alcance `visiblesPara`).
 * Los horarios se capturan dentro del formulario del grupo y se guardan en la misma transacción.
 */
class GrupoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Grupo::class);

        $buscar = trim((string) $request->query('buscar'));
        $estatus = $request->query('estatus');
        $semestre = $request->query('semestre');

        $grupos = Grupo::query()
            ->visiblesPara($request->user())
            ->with(['actividad.departamento', 'semestre', 'instructor.usuario', 'ubicacion', 'horarios.dia'])
            ->when($buscar !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('grupo', 'like', "%{$buscar}%")
                ->orWhereHas('actividad', fn ($a) => $a->where('nombre', 'like', "%{$buscar}%"))))
            ->when($estatus, fn ($q) => $q->where('estatus', $estatus))
            ->when($semestre, fn ($q) => $q->where('id_semestre', $semestre))
            ->orderByDesc('id_semestre')
            ->orderBy('id_actividad')
            ->orderBy('grupo')
            ->paginate(10)
            ->withQueryString();

        return view('grupos.index', [
            'grupos' => $grupos,
            'buscar' => $buscar,
            'estatusElegido' => $estatus,
            'semestreElegido' => $semestre,
            'semestres' => Semestre::orderByDesc('año')->orderByDesc('periodo')->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create', Grupo::class);

        Semestre::cerrarVencidos();

        if (Semestre::activo() === null) {
            return redirect()->route('grupos.index')
                ->with('error', 'No hay un periodo escolar activo: no se pueden crear grupos.');
        }

        return view('grupos.create', $this->opcionesDelFormulario($request));
    }

    public function store(GuardarGrupoRequest $request): RedirectResponse
    {
        $grupo = DB::transaction(function () use ($request) {
            $grupo = Grupo::create($request->datosGrupo());
            $grupo->horarios()->createMany($request->horariosParaGuardar());

            return $grupo;
        });

        return redirect()->route('grupos.show', $grupo)->with('exito', 'Grupo creado correctamente.');
    }

    public function show(Grupo $grupo): View
    {
        Gate::authorize('view', $grupo);

        return view('grupos.show', [
            'grupo' => $grupo->load(['actividad.departamento', 'actividad.carreras', 'semestre', 'instructor.usuario', 'ubicacion', 'horarios.dia']),
        ]);
    }

    public function edit(Request $request, Grupo $grupo): View
    {
        Gate::authorize('update', $grupo);

        return view('grupos.edit', $this->opcionesDelFormulario($request, $grupo) + [
            'grupo' => $grupo->load('horarios'),
        ]);
    }

    public function update(GuardarGrupoRequest $request, Grupo $grupo): RedirectResponse
    {
        DB::transaction(function () use ($request, $grupo) {
            $grupo->update($request->datosGrupo());

            // Los horarios se reemplazan completos: el formulario siempre manda el conjunto final.
            $grupo->horarios()->delete();
            $grupo->horarios()->createMany($request->horariosParaGuardar());
        });

        return redirect()->route('grupos.show', $grupo)->with('exito', 'Grupo actualizado correctamente.');
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        Gate::authorize('delete', $grupo);

        if ($grupo->tieneInscripciones()) {
            return back()->with('error', "No se puede eliminar: el grupo {$grupo->grupo} ya tiene alumnos inscritos. Cámbialo a «Cancelada» en su lugar.");
        }

        try {
            $grupo->delete(); // sus horarios se van con él (llave foránea en cascada)
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar: el grupo tiene registros relacionados.');
        }

        return redirect()->route('grupos.index')->with('exito', 'Grupo eliminado correctamente.');
    }

    /** Listas para los selectores del formulario, recortadas a lo que el usuario puede elegir. */
    private function opcionesDelFormulario(Request $request, ?Grupo $grupo = null): array
    {
        $usuario = $request->user();
        $semestre = $grupo?->semestre ?? Semestre::activo();

        $actividades = Actividad::query()
            ->visiblesPara($usuario)
            ->where(fn ($q) => $q->whereNull('id_semestre')->orWhere('id_semestre', $semestre?->id_semestre))
            ->orderBy('nombre')
            ->get();

        $instructores = Instructor::query()
            ->with(['usuario', 'departamento'])
            ->when(! $usuario->esAdministrador(), fn ($q) => $q->where('id_departamento', $usuario->ambitoDepartamento()))
            ->whereHas('usuario', fn ($q) => $q->where('activo', true)->orWhere('id', $grupo?->id_instructor))
            ->get()
            ->sortBy(fn ($i) => $i->usuario->nombre_completo);

        return [
            'actividades' => $actividades,
            'instructores' => $instructores,
            'ubicaciones' => Ubicacion::orderBy('espacio')->get(),
            'dias' => DiaSemana::orderBy('id_dia')->get(),
            'semestre' => $semestre,
        ];
    }
}
