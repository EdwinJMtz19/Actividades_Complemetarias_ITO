<x-layouts.app :titulo="'Grupo '.$grupo->grupo.' · '.$grupo->actividad->nombre">
    @php
        $colores = ['abierta' => 'bg-green-100 text-green-800', 'cancelada' => 'bg-red-100 text-red-800'];
    @endphp

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold">{{ $grupo->actividad->nombre }} · Grupo {{ $grupo->grupo }}</h1>
        <span class="rounded-full px-2.5 py-0.5 text-sm font-semibold {{ $colores[$grupo->estatus] ?? 'bg-slate-200 text-slate-700' }}">
            {{ \App\Models\Grupo::ESTATUS[$grupo->estatus] }}
        </span>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <x-boton :href="route('grupos.index')" variante="secundario" icono="volver" gesto="izquierda">Volver al listado</x-boton>
        @can('update', $grupo)
            <x-boton :href="route('grupos.edit', $grupo)" icono="editar" gesto="inclinar">Editar</x-boton>
        @endcan
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-lg border border-slate-200 bg-white lg:col-span-2">
            <div class="border-b border-slate-200 px-4 py-3"><h2 class="font-semibold">Información del grupo</h2></div>
            <dl class="grid gap-x-8 gap-y-4 p-5 text-sm sm:grid-cols-2">
                <div><dt class="font-semibold">Actividad</dt><dd>{{ $grupo->actividad->nombre }}</dd></div>
                <div><dt class="font-semibold">Departamento</dt><dd>{{ $grupo->actividad->departamento->nombre }}</dd></div>
                <div><dt class="font-semibold">Semestre</dt><dd>{{ $grupo->semestre->etiqueta }}</dd></div>
                <div><dt class="font-semibold">Créditos</dt><dd>{{ $grupo->actividad->creditos }}</dd></div>
                <div>
                    <dt class="font-semibold">Instructor</dt>
                    <dd>{{ $grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Modalidad</dt>
                    <dd>{{ \App\Models\Grupo::MODALIDADES[$grupo->modalidad] }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Ubicación</dt>
                    <dd>
                        @if ($grupo->ubicacion)
                            {{ $grupo->ubicacion->espacio }} <span class="text-slate-500">(capacidad {{ $grupo->ubicacion->capacidad }})</span>
                        @else
                            {{ $grupo->modalidad === 'virtual' ? 'No aplica (virtual)' : 'Sin ubicación' }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold">Cupo</dt>
                    <dd>{{ $grupo->cupo_ocupado }} inscritos · mínimo {{ $grupo->cupo_minimo }} · máximo {{ $grupo->cupo_maximo }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Fechas</dt>
                    <dd>{{ $grupo->fecha_inicio->format('d/m/Y') }} al {{ $grupo->fecha_fin->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">Carreras</dt>
                    <dd>{{ $grupo->actividad->carreras->pluck('nombre')->implode(', ') }}</dd>
                </div>
                @if ($grupo->materiales_requeridos)
                    <div class="sm:col-span-2">
                        <dt class="font-semibold">Materiales requeridos</dt>
                        <dd class="whitespace-pre-line">{{ $grupo->materiales_requeridos }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3"><h2 class="font-semibold">Horarios</h2></div>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse ($grupo->horarios as $horario)
                    <li class="px-5 py-3">
                        <span class="font-medium">{{ $horario->dia->nombre_dia }}</span>
                        <span class="text-slate-600">{{ $horario->inicio_corto }} a {{ $horario->fin_corto }}</span>
                    </li>
                @empty
                    <li class="px-5 py-3 text-slate-500">Este grupo no tiene horarios.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.app>
