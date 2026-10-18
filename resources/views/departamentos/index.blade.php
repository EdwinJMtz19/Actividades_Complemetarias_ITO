<x-layouts.app titulo="Departamentos">
    <h1 class="mb-6 text-2xl font-semibold">Departamentos</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <x-barra-catalogo titulo="Listado de Departamentos" :total="$departamentos->total()"
                          :accion="route('departamentos.index')" :buscar="$buscar" placeholder="Nombre o edificio...">
            @can('departamentos.crear')
                <x-boton :href="route('departamentos.create')" icono="mas" gesto="giro">Nuevo Departamento</x-boton>
            @endcan
        </x-barra-catalogo>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nombre del Departamento</th>
                        <th class="px-4 py-3">Edificio</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($departamentos as $dep)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 text-slate-500">{{ $departamentos->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $dep->nombre }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $dep->edificio ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('departamentos.editar')
                                        <x-accion tipo="editar" :href="route('departamentos.edit', $dep)" />
                                    @endcan
                                    @can('departamentos.eliminar')
                                        <form method="POST" action="{{ route('departamentos.destroy', $dep) }}" onsubmit="return confirm('¿Eliminar este departamento?')">
                                            @csrf @method('DELETE')
                                            <x-accion tipo="eliminar" />
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                                {{ $buscar !== '' ? 'Ningún departamento coincide con «'.$buscar.'».' : 'No hay departamentos registrados.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departamentos->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $departamentos->links() }}</div>
        @endif
    </div>
</x-layouts.app>
