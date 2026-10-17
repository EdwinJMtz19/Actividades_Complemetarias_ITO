<x-layouts.app titulo="Carreras">
    <h1 class="mb-6 text-2xl font-semibold">Carreras</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold">Listado de Carreras</h2>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('carreras.index') }}" class="flex">
                    <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre de la carrera..."
                           class="w-56 rounded-l-md border border-slate-300 px-3 py-1.5 text-sm">
                    <button class="rounded-r-md bg-blue-700 px-3 text-white hover:bg-blue-800" aria-label="Buscar"><x-icono nombre="buscar" /></button>
                </form>
                @can('carreras.crear')
                    <a href="{{ route('carreras.create') }}" class="inline-flex items-center gap-1 rounded-md bg-blue-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-800">
                        <x-icono nombre="mas" /> Nueva Carrera
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-left text-xs font-semibold uppercase tracking-wide text-slate-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nombre de la Carrera</th>
                        <th class="px-4 py-3 text-center">Alumnos</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($carreras as $carrera)
                        <tr class="odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 text-slate-500">{{ $carreras->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $carrera->nombre }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">{{ $carrera->alumnos_count }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('carreras.editar')
                                        <a href="{{ route('carreras.edit', $carrera) }}" title="Editar" aria-label="Editar"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-amber-400 text-white hover:bg-amber-500"><x-icono nombre="editar" /></a>
                                    @endcan
                                    @can('carreras.eliminar')
                                        <form method="POST" action="{{ route('carreras.destroy', $carrera) }}" onsubmit="return confirm('¿Eliminar esta carrera? Solo es posible si no tiene alumnos.')">
                                            @csrf @method('DELETE')
                                            <button title="Eliminar" aria-label="Eliminar"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-700"><x-icono nombre="eliminar" /></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay carreras registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">{{ $carreras->links() }}</div>
    </div>
</x-layouts.app>
