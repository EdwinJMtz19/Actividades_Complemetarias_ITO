{{--
  Botón de texto del sistema (primario, secundario…), con o sin icono. Si recibe `href` es un enlace.
  `gesto` es el movimiento del icono al pasar el cursor: giro | izquierda | inclinar | ampliar.
--}}
@props(['variante' => 'primario', 'href' => null, 'icono' => null, 'gesto' => null, 'compacto' => false, 'type' => 'submit'])
@php
    $variantes = [
        'primario' => 'bg-blue-700 font-semibold text-white hover:bg-blue-800',
        'secundario' => 'border border-slate-300 bg-white font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-800',
        'consulta' => 'bg-sky-600 font-semibold text-white hover:bg-sky-700',
        'periodo' => 'bg-amber-400 font-semibold text-white hover:bg-amber-500',
        'sobre-azul' => 'bg-white font-semibold text-blue-700 hover:bg-blue-50',
    ];

    $clase = 'mov-boton inline-flex items-center justify-center gap-1.5 rounded-md '
        .($compacto ? 'px-3 py-1.5 text-xs ' : 'px-4 py-2 text-sm ')
        .$variantes[$variante];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clase]) }}>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $clase]) }}>
@endif
    @if ($icono)<x-icono :nombre="$icono" :data-gesto="$gesto" :class="$compacto ? 'h-3.5 w-3.5' : 'h-4 w-4'" />@endif
    {{ $slot }}
@if ($href)
    </a>
@else
    </button>
@endif
