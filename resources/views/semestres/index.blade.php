<x-layouts.app titulo="Gestión de Semestres">
    <h1 class="mb-6 text-2xl font-semibold">Gestión de Semestres</h1>

    {{-- Periodo activo --}}
    <section class="mb-6 rounded-lg border border-blue-700 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-t-lg bg-blue-700 px-4 py-3 text-white">
            <h2 class="font-semibold">Periodo Actual Activo</h2>
            @if (! $periodoActual)
                @can('semestres.crear')
                    <a href="{{ route('semestres.create') }}" class="inline-flex items-center gap-1 rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                        <x-icono nombre="mas" /> Iniciar Nuevo Periodo
                    </a>
                @endcan
            @endif
        </div>

        @if ($periodoActual)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Periodo</th>
                            <th class="px-4 py-3">Inicio Semestre</th>
                            <th class="px-4 py-3">Fin Semestre</th>
                            <th class="px-4 py-3">Inicio Inscripciones</th>
                            <th class="px-4 py-3">Fin Inscripciones</th>
                            <th class="px-4 py-3 text-center">Inscripciones</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-3">
                                <strong>{{ $periodoActual->etiqueta_corta }}</strong><br>
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">ACTIVO</span>
                            </td>
                            <td class="px-4 py-3"><x-fecha-hora :fecha="$periodoActual->fecha_inicio" /></td>
                            <td class="px-4 py-3"><x-fecha-hora :fecha="$periodoActual->fecha_fin" /></td>
                            <td class="px-4 py-3"><x-fecha-hora :fecha="$periodoActual->fecha_inicio_inscripciones" :hora="$periodoActual->hora_inicio_corta" /></td>
                            <td class="px-4 py-3"><x-fecha-hora :fecha="$periodoActual->fecha_fin_inscripciones" :hora="$periodoActual->hora_fin_corta" /></td>
                            <td class="px-4 py-3 text-center">
                                @if ($periodoActual->inscripcionesAbiertas())
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Abiertas</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">Cerradas</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                @can('semestres.editar')
                                    <a href="{{ route('semestres.edit', $periodoActual) }}" class="inline-flex items-center gap-1 rounded-md bg-amber-400 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-500">
                                        <x-icono nombre="editar" class="h-3.5 w-3.5" /> Editar / Finalizar
                                    </a>
                                @endcan
                                @can('semestres.ver')
                                    <a href="{{ route('semestres.show', $periodoActual) }}" class="inline-flex items-center gap-1 rounded-md bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-700">
                                        <x-icono nombre="ver" class="h-3.5 w-3.5" /> Ver detalle
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-6 text-center text-sm text-slate-500">
                No hay ningún periodo activo actualmente.
                @if ($ultimoInactivo)
                    @can('semestres.editar')
                        <p class="mt-2 text-xs">Puedes reactivar el período más reciente:</p>
                        <p class="font-semibold text-slate-700">{{ $ultimoInactivo->etiqueta }}</p>
                        <a href="{{ route('semestres.edit', $ultimoInactivo) }}" class="mt-2 inline-flex items-center gap-1 rounded-md border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                            Reactivar período más reciente
                        </a>
                    @endcan
                @endif
            </div>
        @endif
    </section>

    {{-- Historial --}}
    <section class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold">Historial de Periodos Pasados</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-800 text-left text-xs font-semibold uppercase tracking-wide text-slate-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Periodo</th>
                        <th class="px-4 py-3">Inicio Semestre</th>
                        <th class="px-4 py-3">Fin Semestre</th>
                        <th class="px-4 py-3">Inicio Inscripciones</th>
                        <th class="px-4 py-3">Fin Inscripciones</th>
                        <th class="px-4 py-3 text-center">Estatus</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($historial as $semestre)
                        <tr class="odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 text-slate-500">{{ $historial->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $semestre->etiqueta_corta }}</td>
                            <td class="px-4 py-3 text-xs"><x-fecha-hora :fecha="$semestre->fecha_inicio" /></td>
                            <td class="px-4 py-3 text-xs"><x-fecha-hora :fecha="$semestre->fecha_fin" /></td>
                            <td class="px-4 py-3 text-xs"><x-fecha-hora :fecha="$semestre->fecha_inicio_inscripciones" :hora="$semestre->hora_inicio_corta" /></td>
                            <td class="px-4 py-3 text-xs"><x-fecha-hora :fecha="$semestre->fecha_fin_inscripciones" :hora="$semestre->hora_fin_corta" /></td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold uppercase text-slate-700">{{ $semestre->status }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Ver detalle siempre disponible --}}
                                    @can('semestres.ver')
                                        <a href="{{ route('semestres.show', $semestre) }}" title="Ver detalle" aria-label="Ver detalle"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-sky-600 text-white hover:bg-sky-700"><x-icono nombre="ver" /></a>
                                    @endcan
                                    {{-- Editar/reactivar solo si es el más reciente y no hay uno activo --}}
                                    @if (! $periodoActual && $ultimoInactivo?->is($semestre))
                                        @can('semestres.editar')
                                            <a href="{{ route('semestres.edit', $semestre) }}" title="Editar / Reactivar" aria-label="Editar / Reactivar"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-amber-400 text-white hover:bg-amber-500"><x-icono nombre="editar" /></a>
                                        @endcan
                                    @else
                                        <span class="text-slate-400" title="Solo lectura"><x-icono nombre="candado" /></span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No hay registros en el historial.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($historial->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $historial->links() }}</div>
        @endif
    </section>
</x-layouts.app>
