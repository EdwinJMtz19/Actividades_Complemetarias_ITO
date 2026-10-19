{{-- Tarjeta con formulario de alta/edición: título, campos (slot), Cancelar y Guardar. --}}
@props(['titulo', 'accion', 'volver', 'boton' => 'Guardar', 'metodo' => 'POST', 'ancho' => 'max-w-xl'])
<div {{ $attributes->class(['mx-auto rounded-lg border border-slate-200 bg-white', $ancho]) }}>
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 px-5 py-3">
        <h2 class="text-base font-semibold">{{ $titulo }}</h2>
        @isset($insignia){{ $insignia }}@endisset
    </div>
    <form method="POST" action="{{ $accion }}" class="space-y-4 p-5">
        @csrf
        @if ($metodo !== 'POST') @method($metodo) @endif

        {{ $slot }}

        <div class="flex items-center justify-between pt-2">
            <x-boton variante="secundario" :href="$volver" icono="volver" gesto="izquierda">Cancelar</x-boton>
            {{-- `data-enviar`: al enviar, el botón acusa recibo y no admite un segundo clic (resources/js/app.js). --}}
            <x-boton data-enviar class="mov-enviar">{{ $boton }}</x-boton>
        </div>
    </form>
</div>
