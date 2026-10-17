<x-layouts.app titulo="Usuarios">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Usuarios</h1>
            @unless (auth()->user()->esAdministrador())
                <p class="text-sm text-slate-500">Mostrando los instructores de tu departamento.</p>
            @endunless
        </div>
        @can('create', App\Models\User::class)
            <a href="{{ route('usuarios.create') }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Nuevo usuario</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o correo"
               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-72">
        @if (auth()->user()->esAdministrador())
            <select name="rol" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos los roles</option>
                @foreach ($roles as $r)
                    <option value="{{ $r }}" @selected(request('rol') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        @endif
        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">Filtrar</button>
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Correo</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3">Detalle</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($usuarios as $u)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $u->nombre_completo }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-3 capitalize">{{ $u->roles->first()?->name }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $u->alumno?->carrera?->nombre ?? $u->instructor?->departamento?->nombre ?? $u->coordinacion?->departamento?->nombre }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->activo)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Activo</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                @can('view', $u)<a href="{{ route('usuarios.show', $u) }}" class="text-blue-700 hover:underline">Ver</a>@endcan
                                @can('update', $u)<a href="{{ route('usuarios.edit', $u) }}" class="text-blue-700 hover:underline">Editar</a>@endcan
                                @can('toggle', $u)
                                    <form method="POST" action="{{ route('usuarios.toggle', $u) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-amber-700 hover:underline">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                                    </form>
                                @endcan
                                @can('delete', $u)
                                    <form method="POST" action="{{ route('usuarios.destroy', $u) }}" onsubmit="return confirm('¿Eliminar a {{ e($u->nombre_completo) }}? Esta acción no se puede deshacer.')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-700 hover:underline">Eliminar</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay usuarios que mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $usuarios->links() }}</div>
</x-layouts.app>
