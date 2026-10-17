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
            <a href="{{ $volver }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">
                <x-icono nombre="volver" /> Cancelar
            </a>
            <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">{{ $boton }}</button>
        </div>
    </form>
</div>
