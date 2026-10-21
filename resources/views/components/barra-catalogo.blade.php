{{--
  Encabezado de un listado de catálogo: título con total, buscador y, en el slot, la acción de alta.
  El buscador conserva el texto y ofrece «Limpiar» cuando hay una búsqueda activa.
--}}
@props(['titulo', 'accion', 'total' => null, 'buscar' => '', 'placeholder' => 'Buscar...'])
<div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
    <div class="flex items-center gap-2">
        <h2 class="text-base font-semibold">{{ $titulo }}</h2>
        @if ($total !== null)
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $total }}</span>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ $accion }}" role="search" class="flex flex-wrap items-center gap-3">
            {{-- Filtros opcionales (selectores) que viajan junto con la búsqueda. --}}
            @isset($filtros){{ $filtros }}@endisset
            <div class="flex">
                <label for="buscar" class="sr-only">Buscar</label>
                <input id="buscar" type="search" name="buscar" value="{{ $buscar }}" placeholder="{{ $placeholder }}"
                       class="mov-campo w-56 rounded-l-md border border-slate-300 px-3 py-1.5 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <button type="submit" aria-label="Buscar"
                        class="mov-boton inline-flex items-center rounded-r-md bg-blue-700 px-3 text-white hover:bg-blue-800">
                    <x-icono nombre="buscar" data-gesto="ampliar" />
                </button>
            </div>
            @if ($buscar !== '')
                <a href="{{ $accion }}" class="mov-enlace text-sm text-slate-600 hover:text-blue-700">Limpiar</a>
            @endif
        </form>

        {{ $slot }}
    </div>
</div>
