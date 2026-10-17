<x-layouts.app titulo="Nueva Ubicación">
    <h1 class="mb-6 text-2xl font-semibold">Ubicaciones</h1>

    <x-tarjeta-formulario titulo="Nueva Ubicación" :accion="route('ubicaciones.store')" :volver="route('ubicaciones.index')">
        @include('ubicaciones._form', ['ubicacion' => null])
    </x-tarjeta-formulario>
</x-layouts.app>
