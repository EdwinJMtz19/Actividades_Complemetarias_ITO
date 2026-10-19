@props(['name', 'label', 'required' => false, 'autocomplete' => 'current-password', 'ayuda' => null, 'id' => null])
@php $id ??= $name; @endphp
<div>
    <x-campo :name="$name" :label="$label" type="password" :required="$required"
             :autocomplete="$autocomplete" :ayuda="$ayuda" :id="$id" :adorno="true">
        <x-slot:adorno>
            {{-- Botón de mostrar/ocultar: escribe a ciegas es el fallo más común en el móvil. --}}
            <button type="button" data-ver-contrasena="{{ $id }}"
                    aria-pressed="false" aria-controls="{{ $id }}"
                    class="grid h-8 w-8 place-items-center rounded text-slate-500 transition-colors duration-100 hover:text-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <span class="sr-only" data-ver-texto>Ocultar contraseña</span>
                <span class="col-start-1 row-start-1" data-ver-icono="mostrar"><x-icono nombre="ojo" /></span>
                <span class="col-start-1 row-start-1 opacity-0" data-ver-icono="ocultar"><x-icono nombre="ojo-tachado" /></span>
            </button>
        </x-slot:adorno>
    </x-campo>
    <p id="{{ $id }}-bloq-mayus" data-bloq-mayus hidden class="mt-1 text-xs text-amber-700">
        El Bloq Mayús está activado.
    </p>
</div>
