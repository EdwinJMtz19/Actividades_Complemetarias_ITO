{{-- Formulario compartido de alta y edición de grupos. $grupo es null al crear. --}}
@php
    $campo = 'mov-campo mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500';
    $filas = old('horarios', $grupo
        ? $grupo->horarios->map(fn ($h) => ['id_dia' => $h->id_dia, 'hora_inicio' => $h->inicio_corto, 'hora_fin' => $h->fin_corto])->all()
        : []);
    $filas = $filas ?: [[]];
    $modalidadActual = old('modalidad', $grupo?->modalidad ?? 'presencial');
@endphp

<div data-grupo-form data-url="{{ route('grupos.conflictos') }}" data-grupo="{{ $grupo?->id_grupo }}" class="space-y-4">

    @if ($semestre)
        <p class="rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-600">
            Periodo: <strong>{{ $semestre->etiqueta }}</strong>
            (del {{ $semestre->fecha_inicio->format('d/m/Y') }} al {{ $semestre->fecha_fin->format('d/m/Y') }}).
            Las fechas del grupo deben caer dentro de ese rango.
        </p>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="sm:col-span-2">
            <label for="id_actividad" class="block text-sm font-medium text-slate-700">Actividad <span class="text-red-600">*</span></label>
            <select id="id_actividad" name="id_actividad" required data-actividad class="{{ $campo }}">
                <option value="">Selecciona una actividad</option>
                @foreach ($actividades as $actividad)
                    <option value="{{ $actividad->id_actividad }}" data-departamento="{{ $actividad->id_departamento }}"
                            @selected((string) old('id_actividad', $grupo?->id_actividad) === (string) $actividad->id_actividad)>{{ $actividad->nombre }}</option>
                @endforeach
            </select>
            @error('id_actividad')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <x-campo name="grupo" label="Grupo" :value="$grupo?->grupo" maxlength="10" placeholder="A" class="uppercase" required />
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-seleccion name="modalidad" label="Modalidad" :value="$modalidadActual" :vacio="null" :opciones="\App\Models\Grupo::MODALIDADES" data-modalidad required />
        <div>
            <label for="id_ubicacion" class="block text-sm font-medium text-slate-700">Ubicación</label>
            <select id="id_ubicacion" name="id_ubicacion" data-ubicacion class="{{ $campo }}">
                <option value="">Sin ubicación</option>
                @foreach ($ubicaciones as $ubicacion)
                    <option value="{{ $ubicacion->id_ubicacion }}" @selected((string) old('id_ubicacion', $grupo?->id_ubicacion) === (string) $ubicacion->id_ubicacion)>
                        {{ $ubicacion->espacio }} ({{ $ubicacion->capacidad }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Obligatoria en grupos presenciales o híbridos. Entre paréntesis, su capacidad.</p>
            @error('id_ubicacion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="id_instructor" class="block text-sm font-medium text-slate-700">Instructor</label>
            <select id="id_instructor" name="id_instructor" data-instructor class="{{ $campo }}">
                <option value="">Sin asignar</option>
                @foreach ($instructores as $instructor)
                    <option value="{{ $instructor->id_instructor }}" data-departamento="{{ $instructor->id_departamento }}"
                            @selected((string) old('id_instructor', $grupo?->id_instructor) === (string) $instructor->id_instructor)>
                        {{ $instructor->usuario->nombre_completo }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Solo instructores del departamento de la actividad.</p>
            @error('id_instructor')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-4">
        <x-campo name="cupo_minimo" type="number" min="1" label="Cupo mínimo" :value="$grupo?->cupo_minimo ?? 1" data-cupo-minimo required />
        <x-campo name="cupo_maximo" type="number" min="1" label="Cupo máximo" :value="$grupo?->cupo_maximo" data-cupo required />
        <x-campo name="fecha_inicio" type="date" label="Fecha de inicio" :value="$grupo?->fecha_inicio?->format('Y-m-d')"
                 :min="$semestre?->fecha_inicio->format('Y-m-d')" :max="$semestre?->fecha_fin->format('Y-m-d')" required />
        <x-campo name="fecha_fin" type="date" label="Fecha de fin" :value="$grupo?->fecha_fin?->format('Y-m-d')"
                 :min="$semestre?->fecha_inicio->format('Y-m-d')" :max="$semestre?->fecha_fin->format('Y-m-d')" required />
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-seleccion name="estatus" label="Estatus" :value="$grupo?->estatus ?? 'abierta'" :vacio="null" :opciones="\App\Models\Grupo::ESTATUS" data-estatus required
                     ayuda="Un grupo cancelado o finalizado no ocupa al instructor ni al espacio." />
        <div class="sm:col-span-2">
            <x-area name="materiales_requeridos" label="Materiales requeridos" :value="$grupo?->materiales_requeridos" rows="2" placeholder="Opcional" />
        </div>
    </div>

    <hr class="border-slate-200">

    {{-- Horarios: se agregan y quitan sin recargar; el aviso de conflictos se actualiza solo. --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-blue-700">Horarios</h3>
                <p class="mt-1 text-xs text-slate-500">Un grupo puede reunirse varios días. El borde no cuenta: 09:00–10:00 y 10:00–11:00 no se traslapan.</p>
            </div>
            <x-boton type="button" variante="secundario" icono="mas" gesto="giro" compacto data-agregar-horario>Agregar horario</x-boton>
        </div>

        <div class="space-y-3" data-horarios>
            @foreach ($filas as $i => $fila)
                @include('grupos._horario', ['i' => $i, 'h' => $fila, 'dias' => $dias])
            @endforeach
        </div>

        @error('horarios')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

        <template data-plantilla-horario>
            @include('grupos._horario', ['i' => '__INDEX__', 'h' => [], 'dias' => $dias])
        </template>

        {{-- Aviso en vivo: lo llena grupo-horarios.js; el servidor vuelve a revisar al guardar. --}}
        <div data-conflictos aria-live="polite" class="text-sm"></div>
    </div>
</div>
