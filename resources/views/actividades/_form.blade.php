{{-- Formulario compartido de alta y edición de actividades. $actividad es null al crear. --}}
@php
    $carrerasElegidas = collect(old('carreras', $actividad?->carreras->pluck('id_carrera')->all() ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<x-campo name="nombre" label="Nombre de la actividad" :value="$actividad?->nombre" maxlength="150" placeholder="Ej: Ajedrez" required />

<x-area name="descripcion" label="Descripción" :value="$actividad?->descripcion" rows="3" placeholder="¿De qué trata la actividad?" />
<x-area name="requisitos" label="Requisitos" :value="$actividad?->requisitos" rows="2" placeholder="Materiales o conocimientos previos (opcional)" />

<div class="grid gap-4 sm:grid-cols-3">
    <x-seleccion name="creditos" label="Créditos" :value="$actividad?->creditos" :opciones="[1 => '1 crédito', 2 => '2 créditos']" required />
    <x-campo name="nivel_actividad" label="Nivel" :value="$actividad?->nivel_actividad" maxlength="50" placeholder="Ej: Principiante" />
    <div class="flex items-end">
        <input type="hidden" name="disponible" value="0">
        <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
            <input type="checkbox" name="disponible" value="1" class="rounded border-slate-300"
                   @checked(old('disponible', $actividad?->disponible ?? true))>
            Disponible para inscripción
        </label>
    </div>
</div>

@if ($departamentos->isNotEmpty())
    <x-seleccion name="id_departamento" label="Departamento" :value="$actividad?->id_departamento"
                 :opciones="$departamentos->pluck('nombre', 'id_departamento')" required />
@else
    <div>
        <span class="block text-sm font-medium text-slate-700">Departamento</span>
        <p class="mt-1 rounded-md bg-slate-100 px-3 py-2 text-sm">{{ $departamentoPropio?->nombre }}</p>
        <p class="mt-1 text-xs text-slate-500">Las actividades se registran en tu departamento.</p>
    </div>
@endif

<fieldset>
    <legend class="block text-sm font-medium text-slate-700">Carreras a las que se dirige <span class="text-red-600">*</span></legend>
    <div class="mt-2 grid gap-2 rounded-md border border-slate-200 p-3 sm:grid-cols-2">
        @foreach ($carreras as $carrera)
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="carreras[]" value="{{ $carrera->id_carrera }}" class="rounded border-slate-300"
                       @checked(in_array($carrera->id_carrera, $carrerasElegidas, true))>
                {{ $carrera->nombre }}
            </label>
        @endforeach
    </div>
    @error('carreras')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('carreras.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</fieldset>

{{-- Semestre: al crear se toma el activo; al editar se puede mover al vigente. --}}
@if ($actividad === null)
    <p class="rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-600">
        @if ($semestreActivo)
            La actividad se asignará al semestre activo: <strong>{{ $semestreActivo->etiqueta }}</strong>.
        @else
            No hay un periodo activo: la actividad quedará sin semestre y podrás asignarla al editarla.
        @endif
    </p>
@elseif ($semestreActivo && $actividad->id_semestre !== $semestreActivo->id_semestre)
    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <input type="hidden" name="asignar_semestre_vigente" value="0">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="asignar_semestre_vigente" value="1" class="rounded border-slate-300"
                   @checked(old('asignar_semestre_vigente'))>
            Asignar al semestre vigente (<strong>{{ $semestreActivo->etiqueta }}</strong>)
        </label>
        <p class="mt-1 text-xs">Ahora está en: {{ $actividad->semestre?->etiqueta ?? 'ningún semestre' }}. Solo así podrás crearle grupos en este periodo.</p>
    </div>
@endif
