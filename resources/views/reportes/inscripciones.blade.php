<x-layouts.app titulo="Reporte de inscripciones">
    @php
        $colores = [
            'inscrito' => 'bg-sky-100 text-sky-800',
            'cursando' => 'bg-blue-100 text-blue-800',
            'aprobado' => 'bg-green-100 text-green-800',
            'reprobado' => 'bg-red-100 text-red-800',
            'dado_de_baja' => 'bg-slate-200 text-slate-700',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Monitor de inscripciones</h1>
            <p class="text-sm text-slate-600">Consulta de solo lectura con filtros por estatus, carrera, actividad, departamento y semestre.</p>
        </div>
        <a href="{{ route('reportes.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Volver a reportes</a>
    </div>

    <section class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($estatus as $clave => $etiqueta)
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $etiqueta }}</p>
                <p class="mt-1 text-2xl font-semibold">{{ $totales[$clave] ?? 0 }}</p>
            </div>
        @endforeach
    </section>

    <form method="GET" action="{{ route('reportes.inscripciones') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4">
        <label class="flex min-w-56 flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Buscar alumno</span>
            <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo o número de control"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Estatus</span>
            <select name="estatus" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
                <option value="">Todos</option>
                @foreach ($estatus as $clave => $etiqueta)
                    <option value="{{ $clave }}" @selected(($filtros['estatus'] ?? '') === $clave)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Carrera</span>
            <select name="carrera" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
                <option value="">Todas</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id_carrera }}" @selected((string) ($filtros['carrera'] ?? '') === (string) $carrera->id_carrera)>{{ $carrera->nombre }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Actividad</span>
            <select name="actividad" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
                <option value="">Todas</option>
                @foreach ($actividades as $actividad)
                    <option value="{{ $actividad->id_actividad }}" @selected((string) ($filtros['actividad'] ?? '') === (string) $actividad->id_actividad)>{{ $actividad->nombre }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Departamento</span>
            <select name="departamento" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
                <option value="">Todos</option>
                @foreach ($departamentos as $departamento)
                    <option value="{{ $departamento->id_departamento }}" @selected((string) ($filtros['departamento'] ?? '') === (string) $departamento->id_departamento)>{{ $departamento->nombre }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Semestre</span>
            <select name="semestre" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
                <option value="">Todos</option>
                @foreach ($semestres as $semestre)
                    <option value="{{ $semestre->id_semestre }}" @selected((string) ($filtros['semestre'] ?? '') === (string) $semestre->id_semestre)>{{ $semestre->etiqueta }}</option>
                @endforeach
            </select>
        </label>

        <x-boton compacto>Filtrar</x-boton>
        <x-boton :href="route('reportes.inscripciones')" variante="secundario" compacto>Limpiar</x-boton>
    </form>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold">Inscripciones</h2>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $inscripciones->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Alumno</th>
                        <th class="px-4 py-3">Carrera</th>
                        <th class="px-4 py-3">Actividad / grupo</th>
                        <th class="px-4 py-3">Instructor</th>
                        <th class="px-4 py-3">Departamento</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inscripciones as $inscripcion)
                        <tr class="odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $inscripcion->alumno->usuario->nombre_completo }}</span>
                                <span class="block text-xs text-slate-500">{{ $inscripcion->alumno->num_control }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $inscripcion->alumno->carrera?->nombre }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $inscripcion->grupo->actividad->nombre }}</span>
                                <span class="block text-xs text-slate-500">Grupo {{ $inscripcion->grupo->grupo }} · {{ $inscripcion->grupo->semestre?->etiqueta_corta }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $inscripcion->grupo->actividad->departamento?->nombre }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $inscripcion->fecha_inscripcion->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colores[$inscripcion->estatus] ?? 'bg-slate-200 text-slate-700' }}">
                                    {{ $estatus[$inscripcion->estatus] ?? $inscripcion->estatus }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No hay inscripciones que coincidan con el filtro.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inscripciones->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $inscripciones->links() }}</div>
        @endif
    </section>
</x-layouts.app>
