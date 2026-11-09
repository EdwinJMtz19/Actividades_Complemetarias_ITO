<x-layouts.app titulo="Calificar alumno">
    @php
        $descripciones = [
            'excelente' => 'Superó las expectativas',
            'bueno' => 'Cumplió satisfactoriamente',
            'malo' => 'No cumplió los requisitos',
        ];
        $estatus = \App\Models\Inscripcion::ESTATUS;
    @endphp

    <h1 class="mb-4 text-2xl font-semibold">{{ $calificacion ? 'Editar calificación' : 'Calificar alumno' }}</h1>

    <section class="mx-auto mb-4 max-w-3xl rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-3"><h2 class="font-semibold">Datos del alumno</h2></div>
        <dl class="grid gap-x-8 gap-y-4 p-5 text-sm sm:grid-cols-2">
            <div>
                <dt class="font-semibold">Nombre</dt>
                <dd>{{ $inscripcion->alumno->usuario->nombre_completo }}</dd>
            </div>
            <div>
                <dt class="font-semibold">No. control</dt>
                <dd>{{ $inscripcion->alumno->num_control }}</dd>
            </div>
            <div>
                <dt class="font-semibold">Carrera</dt>
                <dd>{{ $inscripcion->alumno->carrera->nombre }}</dd>
            </div>
            <div>
                <dt class="font-semibold">Actividad</dt>
                <dd>{{ $inscripcion->grupo->actividad->nombre }} · Grupo {{ $inscripcion->grupo->grupo }}</dd>
            </div>
            <div>
                <dt class="font-semibold">Semestre</dt>
                <dd>{{ $inscripcion->grupo->semestre->etiqueta }}</dd>
            </div>
            <div>
                <dt class="font-semibold">Estatus</dt>
                <dd>{{ $estatus[$inscripcion->estatus] }}</dd>
            </div>
        </dl>
    </section>

    @if ($calificacion)
        <div class="mx-auto mb-4 max-w-3xl">
            <x-mensaje tipo="advertencia">
                Este alumno ya tiene una calificación registrada:
                <strong>{{ \App\Models\Calificacion::DESEMPENIOS[$calificacion->desempenio] ?? $calificacion->desempenio }}</strong>.
                Puedes modificarla a continuación.
            </x-mensaje>
        </div>
    @endif

    @if ($inscripcion->estatus === 'dado_de_baja')
        <div class="mx-auto mb-4 max-w-3xl">
            <x-mensaje tipo="advertencia">La inscripción está dada de baja; no se puede calificar.</x-mensaje>
        </div>
    @endif

    @can('calificar', $inscripcion)
        <x-tarjeta-formulario :titulo="$calificacion ? 'Modificar calificación' : 'Registrar calificación'"
                              ancho="max-w-3xl" metodo="PUT"
                              :boton="$calificacion ? 'Actualizar calificación' : 'Guardar calificación'"
                              :accion="route('calificaciones.update', $inscripcion)"
                              :volver="route('grupos.show', $inscripcion->id_grupo)">
            <fieldset>
                <legend class="block text-sm font-medium text-slate-700">
                    Desempeño del alumno <span class="text-red-600">*</span>
                </legend>
                <div class="mt-2 grid gap-3 sm:grid-cols-3">
                    @foreach ($desempenios as $valor => $texto)
                        <label class="block cursor-pointer">
                            <input type="radio" name="desempenio" value="{{ $valor }}" class="peer sr-only"
                                   @checked(old('desempenio', $calificacion?->desempenio) === $valor)>
                            <span class="block rounded-lg border border-slate-300 bg-white p-4 text-center transition hover:border-blue-400 peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                <span class="block font-semibold text-slate-800">{{ $texto }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $descripciones[$valor] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('desempenio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </fieldset>

            <x-area name="observaciones" label="Observaciones" :value="$calificacion?->observaciones"
                    rows="3" maxlength="255" placeholder="Notas adicionales (opcional)" />
        </x-tarjeta-formulario>
    @else
        @if ($inscripcion->estatus !== 'dado_de_baja')
            <div class="mx-auto max-w-3xl">
                <x-mensaje tipo="info">Solo el instructor del grupo puede modificar esta calificación.</x-mensaje>
            </div>
        @endif
    @endcan
</x-layouts.app>
