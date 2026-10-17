<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarDepartamentoRequest;
use App\Models\Departamento;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartamentoController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));

        $departamentos = Departamento::query()
            ->when($buscar !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('edificio', 'like', "%{$buscar}%")))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('departamentos.index', compact('departamentos', 'buscar'));
    }

    public function create(): View
    {
        return view('departamentos.create');
    }

    public function store(GuardarDepartamentoRequest $request): RedirectResponse
    {
        Departamento::create($request->validated());

        return redirect()->route('departamentos.index')->with('exito', 'Departamento creado correctamente.');
    }

    public function edit(Departamento $departamento): View
    {
        return view('departamentos.edit', compact('departamento'));
    }

    public function update(GuardarDepartamentoRequest $request, Departamento $departamento): RedirectResponse
    {
        $departamento->update($request->validated());

        return redirect()->route('departamentos.index')->with('exito', 'Departamento actualizado correctamente.');
    }

    public function destroy(Departamento $departamento): RedirectResponse
    {
        $instructores = $departamento->instructores()->count();

        if ($instructores > 0 || $departamento->coordinacion()->exists()) {
            $motivos = array_filter([
                $instructores > 0 ? "{$instructores} instructor(es)" : null,
                $departamento->coordinacion()->exists() ? 'un coordinador asignado' : null,
            ]);

            return back()->with('error', 'No se puede eliminar: el departamento tiene '.implode(' y ', $motivos).'.');
        }

        try {
            $departamento->delete();
        } catch (QueryException) {
            // Registros de módulos posteriores (actividades, grupos) que referencien al departamento.
            return back()->with('error', 'No se puede eliminar: el departamento tiene registros relacionados.');
        }

        return redirect()->route('departamentos.index')->with('exito', 'Departamento eliminado correctamente.');
    }
}
