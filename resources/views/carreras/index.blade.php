<x-layouts.app titulo="Carreras">
    <h1 class="mb-6 text-2xl font-semibold">Carreras</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <x-barra-catalogo titulo="Listado de Carreras" :total="$carreras->total()"
                          :accion="route('carreras.index')" :buscar="$buscar" placeholder="Nombre de la carrera...">
            @can('carreras.crear')
                <x-boton :href="route('carreras.create')" icono="mas" gesto="giro">Nueva Carrera</x-boton>
            @endcan
        </x-barra-catalogo>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nombre de la Carrera</th>
                        <th class="px-4 py-3 text-center">Alumnos</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($carreras as $carrera)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 text-slate-500">{{ $carreras->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $carrera->nombre }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $carrera->alumnos_count }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('carreras.editar')
                                        <x-accion tipo="editar" :href="route('carreras.edit', $carrera)" />
                                    @endcan
                                    @can('carreras.eliminar')
                                        <form method="POST" action="{{ route('carreras.destroy', $carrera) }}" onsubmit="return confirm('¿Eliminar esta carrera? Solo es posible si no tiene alumnos.')">
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
                                {{ $buscar !== '' ? 'Ninguna carrera coincide con «'.$buscar.'».' : 'No hay carreras registradas.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($carreras->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $carreras->links() }}</div>
        @endif
    </div>
</x-layouts.app>
