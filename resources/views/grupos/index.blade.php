<x-layouts.app titulo="Grupos">
    <h1 class="mb-6 text-2xl font-semibold">Grupos</h1>

    <div class="rounded-lg border border-slate-200 bg-white">
        <x-barra-catalogo titulo="Listado de Grupos" :total="$grupos->total()"
                          :accion="route('grupos.index')" :buscar="$buscar" placeholder="Actividad o grupo...">
            <x-slot:filtros>
                <label for="semestre" class="sr-only">Semestre</label>
                <select id="semestre" name="semestre" onchange="this.form.submit()"
                        class="mov-campo rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todos los semestres</option>
                    @foreach ($semestres as $s)
                        <option value="{{ $s->id_semestre }}" @selected((string) $semestreElegido === (string) $s->id_semestre)>{{ $s->etiqueta }}</option>
                    @endforeach
                </select>
                <label for="estatus" class="sr-only">Estatus</label>
                <select id="estatus" name="estatus" onchange="this.form.submit()"
                        class="mov-campo rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todos los estatus</option>
                    @foreach (\App\Models\Grupo::ESTATUS as $clave => $texto)
                        <option value="{{ $clave }}" @selected($estatusElegido === $clave)>{{ $texto }}</option>
                    @endforeach
                </select>
            </x-slot:filtros>

            @can('grupos.crear')
                <x-boton :href="route('grupos.create')" icono="mas" gesto="giro">Nuevo Grupo</x-boton>
            @endcan
        </x-barra-catalogo>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Actividad</th>
                        <th class="px-4 py-3 text-center">Grupo</th>
                        <th class="px-4 py-3">Instructor</th>
                        <th class="px-4 py-3">Horario</th>
                        <th class="px-4 py-3">Lugar</th>
                        <th class="px-4 py-3 text-center">Cupo</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($grupos as $grupo)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $grupo->actividad->nombre }}</span>
                                <span class="block text-xs text-slate-500">{{ $grupo->semestre->etiqueta_corta }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $grupo->grupo }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                @foreach ($grupo->horarios as $horario)
                                    <span class="block whitespace-nowrap">{{ $horario->descripcion_corta }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $grupo->modalidad === 'virtual' ? 'Virtual' : $grupo->ubicacion?->espacio }}
                                @if ($grupo->modalidad === 'hibrida')
                                    <span class="block text-xs text-slate-500">Híbrida</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $grupo->cupo_ocupado }} / {{ $grupo->cupo_maximo }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $colores = [
                                        'abierta' => 'bg-green-100 text-green-800',
                                        'cancelada' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $colores[$grupo->estatus] ?? 'bg-slate-200 text-slate-700' }}">
                                    {{ \App\Models\Grupo::ESTATUS[$grupo->estatus] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @can('view', $grupo)
                                        <x-accion tipo="ver" :href="route('grupos.show', $grupo)" />
                                    @endcan
                                    @can('update', $grupo)
                                        <x-accion tipo="editar" :href="route('grupos.edit', $grupo)" />
                                    @endcan
                                    @can('delete', $grupo)
                                        <form method="POST" action="{{ route('grupos.destroy', $grupo) }}" onsubmit="return confirm('¿Eliminar este grupo y sus horarios?')">
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
                                {{ $buscar !== '' ? 'Ningún grupo coincide con «'.$buscar.'».' : 'No hay grupos registrados.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($grupos->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $grupos->links() }}</div>
        @endif
    </div>
</x-layouts.app>
