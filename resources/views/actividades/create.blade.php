<x-layouts.app titulo="Nueva Actividad">
    <h1 class="mb-6 text-2xl font-semibold">Actividades complementarias</h1>

    <x-tarjeta-formulario titulo="Nueva Actividad" ancho="max-w-3xl"
                          :accion="route('actividades.store')" :volver="route('actividades.index')">
        @include('actividades._form', ['actividad' => null])
    </x-tarjeta-formulario>
</x-layouts.app>
