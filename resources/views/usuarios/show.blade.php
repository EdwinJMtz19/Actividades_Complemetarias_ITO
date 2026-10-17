<x-layouts.app titulo="Usuario">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">{{ $usuario->nombre_completo }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('usuarios.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">Volver</a>
            @can('update', $usuario)
                <a href="{{ route('usuarios.edit', $usuario) }}" class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Editar</a>
            @endcan
        </div>
    </div>

    <dl class="grid gap-x-8 gap-y-3 rounded-lg border border-slate-200 bg-white p-6 text-sm sm:grid-cols-2">
        <div><dt class="text-slate-500">Correo</dt><dd class="font-medium">{{ $usuario->email }}</dd></div>
        <div><dt class="text-slate-500">Teléfono</dt><dd class="font-medium">{{ $usuario->telefono ?: '—' }}</dd></div>
        <div><dt class="text-slate-500">Rol</dt><dd class="font-medium capitalize">{{ $usuario->roles->first()?->name }}</dd></div>
        <div><dt class="text-slate-500">Estado</dt><dd class="font-medium">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</dd></div>
        <div><dt class="text-slate-500">Último acceso</dt><dd class="font-medium">{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca' }}</dd></div>
        @if ($usuario->alumno)
            <div><dt class="text-slate-500">Número de control</dt><dd class="font-medium">{{ $usuario->alumno->num_control }}</dd></div>
            <div><dt class="text-slate-500">Carrera</dt><dd class="font-medium">{{ $usuario->alumno->carrera?->nombre }}</dd></div>
            <div><dt class="text-slate-500">Semestre</dt><dd class="font-medium">{{ $usuario->alumno->semestre_cursando }}</dd></div>
            <div><dt class="text-slate-500">Créditos acumulados</dt><dd class="font-medium">{{ $usuario->alumno->creditos_acumulados }}</dd></div>
        @endif
        @if ($usuario->instructor)
            <div><dt class="text-slate-500">Departamento</dt><dd class="font-medium">{{ $usuario->instructor->departamento?->nombre }}</dd></div>
            <div><dt class="text-slate-500">Especialidad</dt><dd class="font-medium">{{ $usuario->instructor->especialidad ?: '—' }}</dd></div>
        @endif
        @if ($usuario->coordinacion)
            <div><dt class="text-slate-500">Departamento que coordina</dt><dd class="font-medium">{{ $usuario->coordinacion->departamento?->nombre }}</dd></div>
        @endif
    </dl>
</x-layouts.app>
