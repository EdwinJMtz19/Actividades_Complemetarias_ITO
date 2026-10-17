<x-layouts.app titulo="Panel">
    <h1 class="text-2xl font-semibold">Hola, {{ $usuario->nombre }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        Sesión iniciada como <span class="font-semibold capitalize">{{ $rol }}</span>.
    </p>

    @if ($usuario->esCoordinador() && ! $usuario->esAdministrador() && $usuario->departamentoId() === null)
        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Aún no tienes un departamento asignado. Pide al administrador que lo configure para poder trabajar.
        </div>
    @endif

    <section class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Mi cuenta</h2>
            <dl class="mt-3 space-y-1 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Correo</dt><dd>{{ $usuario->email }}</dd></div>
                @if ($usuario->alumno)
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">No. de control</dt><dd>{{ $usuario->alumno->num_control }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Carrera</dt><dd>{{ $usuario->alumno->carrera?->nombre }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Semestre</dt><dd>{{ $usuario->alumno->semestre_cursando }}</dd></div>
                @endif
                @if ($usuario->instructor)
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Departamento</dt><dd>{{ $usuario->instructor->departamento?->nombre }}</dd></div>
                @endif
                @if ($usuario->coordinacion)
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Coordina</dt><dd>{{ $usuario->coordinacion->departamento?->nombre }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Módulos disponibles</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @can('viewAny', App\Models\User::class)
                    <li><a href="{{ route('usuarios.index') }}" class="font-medium text-blue-700 hover:underline">Usuarios</a>
                        @unless ($usuario->esAdministrador())<span class="text-slate-500"> (instructores de tu departamento)</span>@endunless</li>
                @endcan
                @can('roles.ver')
                    <li><a href="{{ route('roles.index') }}" class="font-medium text-blue-700 hover:underline">Roles y permisos</a></li>
                @endcan
                @can('semestres.ver')
                    <li><a href="{{ route('semestres.index') }}" class="font-medium text-blue-700 hover:underline">Semestres</a></li>
                @endcan
                @can('departamentos.ver')
                    <li><a href="{{ route('departamentos.index') }}" class="font-medium text-blue-700 hover:underline">Departamentos</a></li>
                @endcan
                @can('carreras.ver')
                    <li><a href="{{ route('carreras.index') }}" class="font-medium text-blue-700 hover:underline">Carreras</a></li>
                @endcan
                @can('ubicaciones.ver')
                    <li><a href="{{ route('ubicaciones.index') }}" class="font-medium text-blue-700 hover:underline">Ubicaciones</a></li>
                @endcan
                @cannot('viewAny', App\Models\User::class)
                    <li class="text-slate-500">Los demás módulos se habilitan en los siguientes sprints.</li>
                @endcannot
            </ul>
        </div>
    </section>
</x-layouts.app>
