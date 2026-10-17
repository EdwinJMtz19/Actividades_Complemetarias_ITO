<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller
{
    public function index(): View
    {
        $permisos = Permission::orderBy('name')->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0]);

        return view('roles.index', [
            'roles' => Role::with('permissions')->orderBy('id')->get(),
            'permisos' => $permisos,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // El administrador siempre tiene acceso total (Gate::before): no se edita.
        abort_if($role->name === 'administrador', 403, 'Los permisos del administrador no se pueden modificar.');

        $datos = $request->validate([
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->syncPermissions($datos['permisos'] ?? []);

        return back()->with('exito', "Permisos del rol «{$role->name}» actualizados.");
    }
}
