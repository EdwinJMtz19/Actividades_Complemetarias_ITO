<x-layouts.app titulo="Actividades">
    <h1 class="mb-6 text-2xl font-semibold">Actividades complementarias</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <x-barra-catalogo titulo="Listado de Actividades" :total="$actividades->total()"
                          :accion="route('actividades.index')" :buscar="$buscar" placeholder="Nombre de la actividad...">
            <x-slot:filtros>
                <label for="semestre" class="sr-only">Semestre</label>
                <select id="semestre" name="semestre" onchange="this.form.submit()"
                        class="mov-campo rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todos los semestres</option>
                    @foreach ($semestres as $s)
                        <option value="{{ $s->id_semestre }}" @selected((string) $semestreElegido === (string) $s->id_semestre)>{{ $s->etiqueta }}</option>
                    @endforeach
                </select>
                @if ($departamentos->isNotEmpty())
                    <label for="departamento" class="sr-only">Departamento</label>
                    <select id="departamento" name="departamento" onchange="this.form.submit()"
                            class="mov-campo rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Todos los departamentos</option>
                        @foreach ($departamentos as $d)
                            <option value="{{ $d->id_departamento }}" @selected((string) $departamentoElegido === (string) $d->id_departamento)>{{ $d->nombre }}</option>
                        @endforeach
                    </select>
                @endif
            </x-slot:filtros>

            @can('actividades.crear')
                <x-boton :href="route('actividades.create')" icono="mas" gesto="giro">Nueva Actividad</x-boton>
            @endcan
        </x-barra-catalogo>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Actividad</th>
                        <th class="px-4 py-3">Departamento</th>
                        <th class="px-4 py-3">Carreras</th>
                        <th class="px-4 py-3 text-center">Créditos</th>
                        <th class="px-4 py-3">Semestre</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-center">Grupos</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($actividades as $actividad)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $actividad->nombre }}</span>
                                @if ($actividad->nivel_actividad)
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600">{{ $actividad->nivel_actividad }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $actividad->departamento->nombre }}</td>
                            <td class="px-4 py-3 text-slate-600" title="{{ $actividad->carreras->pluck('nombre')->implode(', ') }}">
                                {{ $actividad->carreras->count() }} {{ $actividad->carreras->count() === 1 ? 'carrera' : 'carreras' }}
                            </td>
                            <td class="px-4 py-3 text-center">{{ $actividad->creditos }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if ($actividad->semestre)
                                    {{ $actividad->semestre->etiqueta_corta }}
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Sin semestre</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($actividad->disponible)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Disponible</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">No disponible</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $actividad->grupos_count }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('update', $actividad)
                                        <x-accion tipo="editar" :href="route('actividades.edit', $actividad)" />
                                    @endcan
                                    @can('delete', $actividad)
                                        <form method="POST" action="{{ route('actividades.destroy', $actividad) }}" onsubmit="return confirm('¿Eliminar esta actividad? Solo es posible si no tiene grupos.')">
                                            @csrf @method('DELETE')
                                            <x-accion tipo="eliminar" />
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                {{ $buscar !== '' ? 'Ninguna actividad coincide con «'.$buscar.'».' : 'No hay actividades registradas.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($actividades->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $actividades->links() }}</div>
        @endif
    </div>
</x-layouts.app>
