<x-layouts.app titulo="Reporte de alumnos">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Padrón de alumnos</h1>
            <p class="text-sm text-slate-600">Consulta de solo lectura de los alumnos registrados.</p>
        </div>
        <a href="{{ route('reportes.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Volver a reportes</a>
    </div>

    <form method="GET" action="{{ route('reportes.alumnos') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4">
        <label class="flex min-w-56 flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Buscar</span>
            <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo o número de control"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </label>

        <label class="flex min-w-56 flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Carrera</span>
            <select name="carrera" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Todas las carreras</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id_carrera }}" @selected((string) ($filtros['carrera'] ?? '') === (string) $carrera->id_carrera)>
                        {{ $carrera->nombre }}
                    </option>
                @endforeach
            </select>
        </label>

        <x-boton compacto>Filtrar</x-boton>
        <x-boton :href="route('reportes.alumnos')" variante="secundario" compacto>Limpiar</x-boton>
    </form>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold">Alumnos</h2>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $alumnos->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">No. de control</th>
                        <th class="px-4 py-3">Alumno</th>
                        <th class="px-4 py-3">Carrera</th>
                        <th class="px-4 py-3 text-center">Semestre</th>
                        <th class="px-4 py-3 text-center">Créditos</th>
                        <th class="px-4 py-3 text-center">Inscripciones activas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($alumnos as $alumno)
                        <tr class="odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 font-medium">{{ $alumno->num_control }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $alumno->usuario->nombre_completo }}</span>
                                <span class="block text-xs text-slate-500">{{ $alumno->usuario->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $alumno->carrera?->nombre }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $alumno->semestre_cursando }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $alumno->creditos_acumulados }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $alumno->inscripciones_activas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">No hay alumnos que coincidan con el filtro.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($alumnos->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $alumnos->links() }}</div>
        @endif
    </section>
</x-layouts.app>
