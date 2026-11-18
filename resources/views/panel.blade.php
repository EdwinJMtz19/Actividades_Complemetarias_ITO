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

    @if ($dashboard)
        <section class="mt-6">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold">{{ $dashboard['titulo'] }}</h2>
                    <p class="text-sm text-slate-600">
                        {{ $dashboard['subtitulo'] ?? $dashboard['departamento']?->nombre }}
                    </p>
                </div>
                <a href="{{ route('grupos.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Ver grupos</a>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Actividades</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $dashboard['totalActividades'] }}</p>
                    <a href="{{ route('actividades.index') }}" class="mt-2 inline-block text-xs font-medium text-blue-700 hover:underline">Consultar</a>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Grupos</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $dashboard['totalGrupos'] }}</p>
                    <a href="{{ route('grupos.index') }}" class="mt-2 inline-block text-xs font-medium text-blue-700 hover:underline">Consultar</a>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Instructores</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $dashboard['totalInstructores'] }}</p>
                    <a href="{{ route('usuarios.index') }}" class="mt-2 inline-block text-xs font-medium text-blue-700 hover:underline">Consultar</a>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alumnos inscritos</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $dashboard['totalAlumnos'] }}</p>
                    <a href="{{ route('inscripciones.index') }}" class="mt-2 inline-block text-xs font-medium text-blue-700 hover:underline">Consultar</a>
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Inscripciones activas</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $dashboard['totalInscripcionesActivas'] }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aprobados</p>
                    <p class="mt-1 text-2xl font-semibold text-green-700">{{ $dashboard['aprobados'] }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reprobados</p>
                    <p class="mt-1 text-2xl font-semibold text-red-700">{{ $dashboard['reprobados'] }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Grupos sin instructor</p>
                    <p class="mt-1 text-2xl font-semibold text-amber-700">{{ $dashboard['gruposSinInstructor'] }}</p>
                </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between gap-2 border-b border-slate-200 px-4 py-3">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Grupos recientes</h3>
                    <a href="{{ route('grupos.index') }}" class="text-xs font-medium text-blue-700 hover:underline">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Actividad</th>
                                <th class="px-4 py-3 text-center">Grupo</th>
                                <th class="px-4 py-3">Instructor</th>
                                <th class="px-4 py-3">Horario</th>
                                <th class="px-4 py-3 text-center">Cupo</th>
                                <th class="px-4 py-3 text-center">Estatus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($dashboard['gruposRecientes'] as $grupo)
                                <tr class="odd:bg-white even:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <span class="font-medium">{{ $grupo->actividad->nombre }}</span>
                                        <span class="block text-xs text-slate-500">{{ $grupo->semestre->etiqueta_corta }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ $grupo->grupo }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-600">
                                        @foreach ($grupo->horarios as $horario)
                                            <span class="block whitespace-nowrap">{{ $horario->descripcion_corta }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $grupo->cupo_ocupado }} / {{ $grupo->cupo_maximo }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                            {{ \App\Models\Grupo::ESTATUS[$grupo->estatus] ?? $grupo->estatus }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aún no hay grupos registrados en el departamento.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
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
                @can('actividades.ver')
                    <li><a href="{{ route('actividades.index') }}" class="font-medium text-blue-700 hover:underline">Actividades</a></li>
                @endcan
                @can('grupos.ver')
                    <li><a href="{{ route('grupos.index') }}" class="font-medium text-blue-700 hover:underline">Grupos</a></li>
                @endcan
                @can('inscripciones.ver')
                    <li><a href="{{ route('inscripciones.index') }}" class="font-medium text-blue-700 hover:underline">Inscripciones</a></li>
                @endcan
                @can('reportes.ver')
                    <li><a href="{{ route('reportes.index') }}" class="font-medium text-blue-700 hover:underline">Reportes administrativos</a></li>
                @endcan
                @cannot('viewAny', App\Models\User::class)
                    <li class="text-slate-500">Los demás módulos se habilitan en los siguientes sprints.</li>
                @endcannot
            </ul>
        </div>
    </section>
</x-layouts.app>
