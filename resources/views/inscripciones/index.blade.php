<x-layouts.app titulo="Inscripciones">
    @php
        $coloresHistorial = [
            'aprobado' => 'bg-emerald-100 text-emerald-800',
            'reprobado' => 'bg-red-100 text-red-800',
            'dado_de_baja' => 'bg-slate-200 text-slate-700',
        ];
    @endphp

    <h1 class="mb-1 text-2xl font-semibold">Inscripciones</h1>
    <p class="mb-6 text-sm text-slate-600">Elige una actividad complementaria y asegura tu lugar en uno de sus grupos.</p>

    @if ($semestre === null)
        <x-mensaje tipo="advertencia">No hay un periodo escolar activo. Vuelve cuando el administrador abra el siguiente.</x-mensaje>
    @elseif (! $abierta)
        <x-mensaje tipo="advertencia">
            La ventana de inscripción está cerrada. Abre del {{ $semestre->fecha_inicio_inscripciones->format('d/m/Y') }}
            a las {{ $semestre->hora_inicio_corta }} al {{ $semestre->fecha_fin_inscripciones->format('d/m/Y') }}
            a las {{ $semestre->hora_fin_corta }}.
        </x-mensaje>
    @else
        <x-mensaje tipo="info">
            La ventana de inscripción está abierta hasta el {{ $semestre->fecha_fin_inscripciones->format('d/m/Y') }}
            a las {{ $semestre->hora_fin_corta }}.
        </x-mensaje>
    @endif

    @if ($inscripcion)
        <section class="mb-6 rounded-lg border border-green-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-green-100 bg-green-50 px-4 py-3">
                <h2 class="font-semibold text-green-900">Mi inscripción</h2>
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                    {{ \App\Models\Inscripcion::ESTATUS[$inscripcion->estatus] }}
                </span>
            </div>
            <dl class="grid gap-x-8 gap-y-4 p-5 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-semibold">Actividad</dt>
                    <dd>{{ $inscripcion->grupo->actividad->nombre }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Grupo</dt>
                    <dd>{{ $inscripcion->grupo->grupo }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Instructor</dt>
                    <dd>{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Lugar</dt>
                    <dd>{{ $inscripcion->grupo->modalidad === 'virtual' ? 'Virtual' : $inscripcion->grupo->ubicacion?->espacio }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Horario</dt>
                    <dd>
                        @forelse ($inscripcion->grupo->horarios as $horario)
                            <span class="block">{{ $horario->descripcion_corta }}</span>
                        @empty
                            Sin horario
                        @endforelse
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold">Fecha de inscripción</dt>
                    <dd>{{ $inscripcion->fecha_inscripcion->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
            @can('baja', $inscripcion)
                <div class="flex justify-end border-t border-green-100 px-5 py-3">
                    <form method="POST" action="{{ route('inscripciones.baja', $inscripcion) }}"
                          onsubmit="return confirm('¿Cancelar tu inscripción a {{ e($inscripcion->grupo->actividad->nombre) }}? Se conservará en tu historial.')">
                        @csrf @method('PATCH')
                        <button class="mov-enlace text-red-700">Cancelar mi inscripción</button>
                    </form>
                </div>
            @endcan
        </section>
    @endif

    @if ($historial->isNotEmpty())
        <section class="mb-6 rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="font-semibold">Historial</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Actividad</th>
                            <th class="px-4 py-3 text-center">Grupo</th>
                            <th class="px-4 py-3">Semestre</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3 text-center">Estatus</th>
                            <th class="px-4 py-3">Calificación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($historial as $registro)
                            <tr class="odd:bg-white even:bg-slate-50">
                                <td class="px-4 py-3 font-medium">{{ $registro->grupo->actividad->nombre }}</td>
                                <td class="px-4 py-3 text-center">{{ $registro->grupo->grupo }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $registro->grupo->semestre->etiqueta }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $registro->fecha_inscripcion->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $coloresHistorial[$registro->estatus] ?? 'bg-slate-200 text-slate-700' }}">
                                        {{ \App\Models\Inscripcion::ESTATUS[$registro->estatus] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($registro->calificacion)
                                        <span class="font-medium">{{ \App\Models\Calificacion::DESEMPENIOS[$registro->calificacion->desempenio] ?? $registro->calificacion->desempenio }}</span>
                                        @if ($registro->calificacion->observaciones)
                                            <span class="block text-xs text-slate-500">{{ $registro->calificacion->observaciones }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold">Grupos disponibles</h2>
        </div>

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
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($grupos as $grupo)
                        <tr class="transition-colors odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $grupo->actividad->nombre }}</span>
                                <span class="block text-xs text-slate-500">{{ $grupo->actividad->creditos }} crédito(s)</span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $grupo->grupo }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                @foreach ($grupo->horarios as $horario)
                                    <span class="block whitespace-nowrap">{{ $horario->descripcion_corta }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $grupo->modalidad === 'virtual' ? 'Virtual' : $grupo->ubicacion?->espacio }}
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $grupo->cupo_ocupado }} / {{ $grupo->cupo_maximo }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($abierta && $inscripcion === null)
                                    @can('inscribirse', $grupo)
                                        <form method="POST" action="{{ route('inscripciones.store', $grupo) }}">
                                            @csrf
                                            <x-boton compacto>Inscribirme</x-boton>
                                        </form>
                                    @endcan
                                @elseif ($inscripcion !== null)
                                    <span class="text-xs text-slate-500">Ya tienes una inscripción activa</span>
                                @else
                                    <span class="text-xs text-slate-500">Ventana cerrada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                No hay grupos disponibles para tu carrera en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
