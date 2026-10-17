<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarSemestreRequest;
use App\Models\Semestre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Semestres: se abre un periodo nuevo, se edita o reactiva el más reciente y
 * los vencidos se cierran solos. Un periodo registrado nunca se elimina
 * (más adelante cuelgan de él grupos, inscripciones y calificaciones).
 */
class SemestreController extends Controller
{
    public function index(): View
    {
        Semestre::cerrarVencidos();

        return view('semestres.index', [
            'periodoActual' => Semestre::activo(),
            'historial' => Semestre::where('status', Semestre::INACTIVO)
                ->orderByDesc('año')->orderByDesc('periodo')
                ->paginate(10),
            'ultimoInactivo' => Semestre::masRecienteInactivo(),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        Semestre::cerrarVencidos();

        if (Semestre::activo() !== null) {
            return redirect()->route('semestres.index')
                ->with('error', 'No se puede crear un nuevo periodo mientras exista uno activo.');
        }

        return view('semestres.create');
    }

    public function store(GuardarSemestreRequest $request): RedirectResponse
    {
        Semestre::create($request->validated());

        return redirect()->route('semestres.index')->with('exito', 'Semestre creado correctamente.');
    }

    public function show(Semestre $semestre): View
    {
        return view('semestres.show', compact('semestre'));
    }

    public function edit(Semestre $semestre): View|RedirectResponse
    {
        Semestre::cerrarVencidos();

        if ($motivo = $semestre->fresh()->motivoNoEditable()) {
            return redirect()->route('semestres.index')->with('error', $motivo);
        }

        return view('semestres.edit', ['semestre' => $semestre->fresh()]);
    }

    public function update(GuardarSemestreRequest $request, Semestre $semestre): RedirectResponse
    {
        if ($motivo = $semestre->fresh()->motivoNoEditable()) {
            return redirect()->route('semestres.index')->with('error', $motivo);
        }

        $semestre->update($request->validated());

        return redirect()->route('semestres.index')->with('exito', 'Periodo actualizado correctamente.');
    }
}
