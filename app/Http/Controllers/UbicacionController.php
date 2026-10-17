<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarUbicacionRequest;
use App\Models\Ubicacion;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UbicacionController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));

        $ubicaciones = Ubicacion::query()
            ->when($buscar !== '', fn ($q) => $q->where('espacio', 'like', "%{$buscar}%"))
            ->orderBy('espacio')
            ->paginate(10)
            ->withQueryString();

        return view('ubicaciones.index', compact('ubicaciones', 'buscar'));
    }

    public function create(): View
    {
        return view('ubicaciones.create');
    }

    public function store(GuardarUbicacionRequest $request): RedirectResponse
    {
        Ubicacion::create($request->validated());

        return redirect()->route('ubicaciones.index')->with('exito', 'Ubicación creada correctamente.');
    }

    public function edit(Ubicacion $ubicacion): View
    {
        return view('ubicaciones.edit', compact('ubicacion'));
    }

    public function update(GuardarUbicacionRequest $request, Ubicacion $ubicacion): RedirectResponse
    {
        $ubicacion->update($request->validated());

        return redirect()->route('ubicaciones.index')->with('exito', 'Ubicación actualizada correctamente.');
    }

    public function destroy(Ubicacion $ubicacion): RedirectResponse
    {
        try {
            $ubicacion->delete();
        } catch (QueryException) {
            // Cuando existan grupos y horarios (Sprint 3) la base impedirá borrar una ubicación en uso.
            return back()->with('error', 'No se puede eliminar: la ubicación tiene registros relacionados.');
        }

        return redirect()->route('ubicaciones.index')->with('exito', 'Ubicación eliminada correctamente.');
    }
}
