<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarGrupoRequest;
use App\Models\Actividad;
use App\Models\Calificacion;
use App\Models\DiaSemana;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Instructor;
use App\Models\Semestre;
use App\Models\Ubicacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function show(Request $request, Grupo $grupo): View
    {
        Gate::authorize('view', $grupo);

        $grupo->load(['actividad.departamento', 'actividad.carreras', 'semestre', 'instructor.usuario', 'ubicacion', 'horarios.dia']);

        // El instructor, el coordinador y el administrador consultan las calificaciones del grupo;
        // el alumno no ve a sus compañeros.
        $inscripciones = $request->user()->can('calificaciones.ver')
            ? $grupo->inscripciones()
                ->with(['alumno.usuario', 'alumno.carrera', 'calificacion'])
                ->orderBy('id_inscripcion')
                ->get()
                ->each(fn (Inscripcion $inscripcion) => $inscripcion->setRelation('grupo', $grupo))
            : collect();

        return view('grupos.show', [
            'grupo' => $grupo,
            'inscripciones' => $inscripciones,
        ]);
    }

    /**
     * Exporta la lista de alumnos del grupo en CSV (HU-24). Incluye todas las
     * inscripciones con su estatus y calificación; el alcance lo aplica GrupoPolicy
     * y el permiso `calificaciones.ver` lo exige la ruta.
     */
    public function exportarAlumnos(Grupo $grupo): StreamedResponse
    {
        Gate::authorize('view', $grupo);

        $grupo->loadMissing('actividad');

        $inscripciones = $grupo->inscripciones()
            ->with(['alumno.usuario', 'alumno.carrera', 'calificacion'])
            ->orderBy('id_inscripcion')
            ->get();

        $slug = Str::slug($grupo->actividad->nombre.'-'.$grupo->grupo) ?: 'grupo';

        return response()->streamDownload(function () use ($inscripciones) {
            $salida = fopen('php://output', 'w');

            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, [
                'No. control',
                'Nombre',
                'Correo',
                'Carrera',
                'Estatus',
                'Calificación',
                'Observaciones',
                'Fecha de inscripción',
            ], ',', '"', '');

            foreach ($inscripciones as $inscripcion) {
                $desempenio = $inscripcion->calificacion
                    ? (Calificacion::DESEMPENIOS[$inscripcion->calificacion->desempenio] ?? $inscripcion->calificacion->desempenio)
                    : 'Sin calificar';

                fputcsv($salida, [
                    $this->celdaCsv($inscripcion->alumno->num_control),
                    $this->celdaCsv($inscripcion->alumno->usuario->nombre_completo),
                    $this->celdaCsv($inscripcion->alumno->usuario->email),
                    $this->celdaCsv($inscripcion->alumno->carrera?->nombre),
                    Inscripcion::ESTATUS[$inscripcion->estatus] ?? $inscripcion->estatus,
                    $desempenio,
                    $this->celdaCsv($inscripcion->calificacion?->observaciones),
                    $inscripcion->fecha_inscripcion?->format('d/m/Y H:i') ?? '',
                ], ',', '"', '');
            }

            fclose($salida);
        }, "alumnos-{$slug}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Descarga la lista de asistencia del grupo en PDF (HU-25). Solo incluye
     * alumnos activos; el alcance lo aplica GrupoPolicy y el permiso
     * `calificaciones.ver` lo exige la ruta.
     */
    public function descargarAsistencia(Grupo $grupo): Response
    {
        Gate::authorize('view', $grupo);

        $grupo->load([
            'actividad.departamento',
            'semestre',
            'instructor.usuario',
            'ubicacion',
            'horarios.dia',
        ]);

        $inscripciones = $grupo->inscripciones()
            ->with(['alumno.usuario', 'alumno.carrera'])
            ->whereIn('estatus', Inscripcion::ACTIVOS)
            ->orderBy('id_inscripcion')
            ->get();

        $slug = Str::slug($grupo->actividad->nombre.'-'.$grupo->grupo) ?: 'grupo';

        return Pdf::loadView('pdf.asistencia-grupo', [
            'grupo' => $grupo,
            'inscripciones' => $inscripciones,
            'generadoEl' => now(),
        ])->setPaper('letter')->download("asistencia-{$slug}.pdf");
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

    /** Evita que un valor de texto se interprete como fórmula al abrir el CSV. */
    private function celdaCsv(?string $valor): string
    {
        $valor = (string) $valor;

        return preg_match('/^[=+\-@]/', $valor) === 1 ? "'{$valor}" : $valor;
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
