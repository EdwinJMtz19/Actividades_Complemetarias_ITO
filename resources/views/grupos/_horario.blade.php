{{--
  Un bloque de horario del formulario del grupo (día + hora de inicio + hora de fin).
  $i es la posición (o __INDEX__ en la plantilla que clona el JavaScript); $h trae los valores previos.
--}}
@php
    $campo = 'mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500';
@endphp
<div class="rounded-md border border-slate-200 p-3" data-horario>
    <div class="grid gap-3 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end">
        <div>
            <label class="block text-xs font-medium text-slate-600">Día</label>
            <select name="horarios[{{ $i }}][id_dia]" data-campo="id_dia" class="mov-campo {{ $campo }}">
                <option value="">Selecciona…</option>
                @foreach ($dias as $dia)
                    <option value="{{ $dia->id_dia }}" @selected((string) ($h['id_dia'] ?? '') === (string) $dia->id_dia)>{{ $dia->nombre_dia }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Hora de inicio</label>
            <input type="time" name="horarios[{{ $i }}][hora_inicio]" data-campo="hora_inicio" value="{{ $h['hora_inicio'] ?? '' }}" class="mov-campo {{ $campo }}">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600">Hora de fin</label>
            <input type="time" name="horarios[{{ $i }}][hora_fin]" data-campo="hora_fin" value="{{ $h['hora_fin'] ?? '' }}" class="mov-campo {{ $campo }}">
        </div>
        <button type="button" data-quitar-horario title="Quitar horario" aria-label="Quitar horario"
                class="mov-boton inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 bg-white text-red-600 hover:border-red-600 hover:bg-red-600 hover:text-white">
            <x-icono nombre="eliminar" data-gesto="inclinar" />
        </button>
    </div>
    @foreach (['id_dia', 'hora_inicio', 'hora_fin'] as $clave)
        @error("horarios.$i.$clave")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    @endforeach
</div>
