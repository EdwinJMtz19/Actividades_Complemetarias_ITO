<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarCarreraRequest;
use App\Models\Carrera;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarreraController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));

        $carreras = Carrera::query()
            ->when($buscar !== '', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->withCount('alumnos')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('carreras.index', compact('carreras', 'buscar'));
    }

    public function create(): View
    {
        return view('carreras.create');
    }

    public function store(GuardarCarreraRequest $request): RedirectResponse
    {
        Carrera::create($request->validated());

        return redirect()->route('carreras.index')->with('exito', 'Carrera creada correctamente.');
    }

    public function edit(Carrera $carrera): View
    {
        return view('carreras.edit', compact('carrera'));
    }

    public function update(GuardarCarreraRequest $request, Carrera $carrera): RedirectResponse
    {
        $carrera->update($request->validated());

        return redirect()->route('carreras.index')->with('exito', 'Carrera actualizada correctamente.');
    }

    public function destroy(Carrera $carrera): RedirectResponse
    {
        $alumnos = $carrera->alumnos()->count();

        if ($alumnos > 0) {
            return back()->with('error', "No se puede eliminar: la carrera tiene {$alumnos} alumno(s) registrado(s).");
        }

        try {
            $carrera->delete();
        } catch (QueryException) {
            return back()->with('error', 'No se puede eliminar: la carrera tiene registros relacionados.');
        }

        return redirect()->route('carreras.index')->with('exito', 'Carrera eliminada correctamente.');
    }
}
