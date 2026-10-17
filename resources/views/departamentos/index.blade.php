<x-layouts.app titulo="Departamentos">
    <h1 class="mb-6 text-2xl font-semibold">Departamentos</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold">Listado de Departamentos</h2>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('departamentos.index') }}" class="flex">
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre o edificio..."
                           class="w-56 rounded-l-md border border-slate-300 px-3 py-1.5 text-sm">
                    <button class="rounded-r-md bg-blue-700 px-3 text-white hover:bg-blue-800" aria-label="Buscar"><x-icono nombre="buscar" /></button>
                </form>
                @can('departamentos.crear')
                    <a href="{{ route('departamentos.create') }}" class="inline-flex items-center gap-1 rounded-md bg-blue-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-800">
                        <x-icono nombre="mas" /> Nuevo Departamento
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-left text-xs font-semibold uppercase tracking-wide text-slate-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nombre del Departamento</th>
                        <th class="px-4 py-3">Edificio</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($departamentos as $dep)
                        <tr class="odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 text-slate-500">{{ $departamentos->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $dep->nombre }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $dep->edificio ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('departamentos.editar')
                                        <a href="{{ route('departamentos.edit', $dep) }}" title="Editar" aria-label="Editar"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-amber-400 text-white hover:bg-amber-500"><x-icono nombre="editar" /></a>
                                    @endcan
                                    @can('departamentos.eliminar')
                                        <form method="POST" action="{{ route('departamentos.destroy', $dep) }}" onsubmit="return confirm('¿Eliminar este departamento?')">
                                            @csrf @method('DELETE')
                                            <button title="Eliminar" aria-label="Eliminar"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-700"><x-icono nombre="eliminar" /></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay departamentos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">{{ $departamentos->links() }}</div>
    </div>
</x-layouts.app>
