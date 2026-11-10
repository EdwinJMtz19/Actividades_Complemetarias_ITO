<x-layouts.app titulo="Inscripciones">
    @php
        $colores = [
            'inscrito' => 'bg-green-100 text-green-800',
            'cursando' => 'bg-sky-100 text-sky-800',
            'aprobado' => 'bg-emerald-100 text-emerald-800',
            'reprobado' => 'bg-red-100 text-red-800',
            'dado_de_baja' => 'bg-slate-200 text-slate-700',
        ];
    @endphp

    <h1 class="mb-1 text-2xl font-semibold">Inscripciones</h1>
    <p class="mb-6 text-sm text-slate-600">
        Consulta las inscripciones de los grupos y da de baja a un alumno. La baja conserva el historial y libera el lugar.
    </p>

    <form method="GET" action="{{ route('inscripciones.index') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4">
        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Actividad</span>
            <select name="actividad" class="rounded-md border border-slate-300 text-sm">
                <option value="">Todas</option>
                @foreach ($actividades as $actividad)
                    <option value="{{ $actividad->id_actividad }}" @selected((int) ($filtros['actividad'] ?? 0) === $actividad->id_actividad)>
                        {{ $actividad->nombre }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Estatus</span>
            <select name="estatus" class="rounded-md border border-slate-300 text-sm">
                <option value="">Todos</option>
                @foreach ($estatus as $clave => $etiqueta)
                    <option value="{{ $clave }}" @selected(($filtros['estatus'] ?? '') === $clave)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Buscar alumno</span>
            <input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Nombre, correo o número de control" class="w-full rounded-md border border-slate-300 text-sm sm:w-64">
        </label>

        <x-boton compacto>Filtrar</x-boton>
        <x-boton :href="route('inscripciones.index')" variante="secundario" compacto>Limpiar</x-boton>
    </form>

    <section class="rounded-lg border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Alumno</th>
                        <th class="px-4 py-3">Carrera</th>
                        <th class="px-4 py-3">Actividad</th>
                        <th class="px-4 py-3 text-center">Grupo</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inscripciones as $inscripcion)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $inscripcion->alumno->usuario->nombre_completo }}</span>
                                <span class="block text-xs text-slate-500">{{ $inscripcion->alumno->num_control }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $inscripcion->alumno->carrera->nombre }}</td>
                            <td class="px-4 py-3">{{ $inscripcion->grupo->actividad->nombre }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $inscripcion->grupo->grupo }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $inscripcion->fecha_inscripcion->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colores[$inscripcion->estatus] ?? 'bg-slate-200 text-slate-700' }}">
                                    {{ $estatus[$inscripcion->estatus] ?? $inscripcion->estatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($inscripcion->estaActiva())
                                    @can('baja', $inscripcion)
                                        <form method="POST" action="{{ route('inscripciones.baja', $inscripcion) }}" onsubmit="return confirm('¿Dar de baja a {{ e($inscripcion->alumno->usuario->nombre_completo) }}? Se conservará el historial.')">
                                            @csrf @method('PATCH')
                                            <button class="mov-enlace text-red-700">Dar de baja</button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                No hay inscripciones que coincidan con el filtro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inscripciones->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $inscripciones->links() }}
            </div>
        @endif
    </section>
</x-layouts.app>
