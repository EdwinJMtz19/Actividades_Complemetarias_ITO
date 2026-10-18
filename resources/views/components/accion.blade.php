{{--
  Botón de icono (2rem) para las acciones de una fila: ver, editar, eliminar y editar-periodo.
  Con `href` es un enlace; sin él, un botón de envío (para dentro de un <form>).
  Colores según DESIGN.md: azul de consulta para leer, ámbar solo para cambiar el estado de un periodo,
  secundario de borde para editar, y rojo de peligro para eliminar.
--}}
@props(['tipo' => 'editar', 'href' => null, 'etiqueta' => null])
@php
    $tipos = [
        'ver' => ['ver', 'Ver detalle', 'bg-sky-600 text-white hover:bg-sky-700', 'ampliar'],
        'editar' => ['editar', 'Editar', 'border border-slate-300 bg-white text-slate-600 hover:border-blue-500 hover:text-blue-700', 'inclinar'],
        'periodo' => ['editar', 'Editar / Reactivar', 'bg-amber-400 text-white hover:bg-amber-500', 'inclinar'],
        'eliminar' => ['eliminar', 'Eliminar', 'border border-slate-300 bg-white text-red-600 hover:border-red-600 hover:bg-red-600 hover:text-white', 'inclinar'],
    ];

    [$icono, $texto, $color, $gesto] = $tipos[$tipo];
    $texto = $etiqueta ?? $texto;
    $clase = 'mov-boton inline-flex h-8 w-8 items-center justify-center rounded-md '.$color;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clase, 'title' => $texto, 'aria-label' => $texto]) }}>
        <x-icono :nombre="$icono" :data-gesto="$gesto" />
    </a>
@else
    <button type="submit" {{ $attributes->merge(['class' => $clase, 'title' => $texto, 'aria-label' => $texto]) }}>
        <x-icono :nombre="$icono" :data-gesto="$gesto" />
    </button>
@endif
