<x-layouts.app titulo="Ubicaciones">
    <h1 class="mb-6 text-2xl font-semibold">Ubicaciones</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <x-barra-catalogo titulo="Listado de Ubicaciones" :total="$ubicaciones->total()"
                          :accion="route('ubicaciones.index')" :buscar="$buscar" placeholder="Nombre del espacio...">
            @can('ubicaciones.crear')
                <x-boton :href="route('ubicaciones.create')" icono="mas" gesto="giro">Nueva Ubicación</x-boton>
            @endcan
        </x-barra-catalogo>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Espacio / Nombre</th>
                        <th class="px-4 py-3 text-center">Capacidad Máx.</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ubicaciones as $ub)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 text-slate-500">{{ $ubicaciones->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $ub->espacio }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $ub->capacidad }} personas</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('ubicaciones.editar')
                                        <x-accion tipo="editar" :href="route('ubicaciones.edit', $ub)" />
                                    @endcan
                                    @can('ubicaciones.eliminar')
                                        <form method="POST" action="{{ route('ubicaciones.destroy', $ub) }}" onsubmit="return confirm('¿Eliminar esta ubicación?')">
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
                                {{ $buscar !== '' ? 'Ninguna ubicación coincide con «'.$buscar.'».' : 'No hay ubicaciones registradas.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($ubicaciones->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $ubicaciones->links() }}</div>
        @endif
    </div>
</x-layouts.app>
