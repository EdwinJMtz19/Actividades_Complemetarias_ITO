<x-layouts.app titulo="Reportes">
    <h1 class="mb-1 text-2xl font-semibold">Reportes administrativos</h1>
    <p class="mb-6 text-sm text-slate-600">
        Consulta de solo lectura sobre alumnos, inscripciones y accesos al sistema.
    </p>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alumnos</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totales['alumnos'] }}</p>
            <a href="{{ route('reportes.alumnos') }}" class="mt-3 inline-block text-sm font-medium text-blue-700 hover:underline">Abrir padrón</a>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Inscripciones</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totales['inscripciones'] }}</p>
            <a href="{{ route('reportes.inscripciones') }}" class="mt-3 inline-block text-sm font-medium text-blue-700 hover:underline">Abrir monitor</a>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Grupos</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totales['grupos'] }}</p>
            <a href="{{ route('grupos.index') }}" class="mt-3 inline-block text-sm font-medium text-blue-700 hover:underline">Ver grupos</a>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Actividades</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totales['actividades'] }}</p>
            <a href="{{ route('actividades.index') }}" class="mt-3 inline-block text-sm font-medium text-blue-700 hover:underline">Ver actividades</a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 lg:grid-cols-3">
        <a href="{{ route('reportes.alumnos') }}" class="rounded-lg border border-slate-200 bg-white p-5 transition-colors hover:border-blue-300 hover:bg-blue-50">
            <h2 class="text-base font-semibold">Padrón de alumnos</h2>
            <p class="mt-2 text-sm text-slate-600">Busca por nombre, correo o número de control y filtra por carrera.</p>
        </a>
        <a href="{{ route('reportes.inscripciones') }}" class="rounded-lg border border-slate-200 bg-white p-5 transition-colors hover:border-blue-300 hover:bg-blue-50">
            <h2 class="text-base font-semibold">Monitor de inscripciones</h2>
            <p class="mt-2 text-sm text-slate-600">Consulta los estatus y filtra por carrera, actividad, departamento y semestre.</p>
        </a>
        <a href="{{ route('reportes.accesos') }}" class="rounded-lg border border-slate-200 bg-white p-5 transition-colors hover:border-blue-300 hover:bg-blue-50">
            <h2 class="text-base font-semibold">Últimos accesos</h2>
            <p class="mt-2 text-sm text-slate-600">Revisa el último acceso registrado por usuario y su estado actual.</p>
        </a>
    </section>
</x-layouts.app>
