<x-layouts.app titulo="Detalle del Periodo">
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold">Periodo: {{ $semestre->etiqueta }}</h1>
        @if ($semestre->status === 'activo')
            <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-semibold text-green-800">ACTIVO</span>
        @else
            <span class="rounded-full bg-slate-200 px-2.5 py-0.5 text-sm font-semibold text-slate-700">INACTIVO</span>
        @endif
    </div>

    <x-boton :href="route('semestres.index')" variante="secundario" icono="volver" gesto="izquierda" class="mb-4">Volver al listado</x-boton>

    <section class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="font-semibold">Información del Periodo</h2>
        </div>
        <dl class="grid gap-x-8 gap-y-4 p-5 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="font-semibold">Inicio del semestre:</dt><dd><x-fecha-hora :fecha="$semestre->fecha_inicio" /></dd></div>
            <div><dt class="font-semibold">Fin del semestre:</dt><dd><x-fecha-hora :fecha="$semestre->fecha_fin" /></dd></div>
            <div><dt class="font-semibold">Inicio inscripciones:</dt><dd><x-fecha-hora :fecha="$semestre->fecha_inicio_inscripciones" :hora="$semestre->hora_inicio_corta" /></dd></div>
            <div><dt class="font-semibold">Fin inscripciones:</dt><dd><x-fecha-hora :fecha="$semestre->fecha_fin_inscripciones" :hora="$semestre->hora_fin_corta" /></dd></div>
        </dl>
    </section>

    {{-- Pendiente (Sprints 3 a 5): aquí van las actividades, los grupos, los alumnos inscritos y sus calificaciones. --}}
    <p class="mt-4 text-sm text-slate-500">
        Los grupos, alumnos inscritos y calificaciones de este periodo se mostrarán aquí cuando se habiliten los módulos de actividades e inscripciones.
    </p>
</x-layouts.app>
