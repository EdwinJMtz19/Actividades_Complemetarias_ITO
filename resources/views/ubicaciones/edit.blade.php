<x-layouts.app titulo="Editar Ubicación">
    <h1 class="mb-6 text-2xl font-semibold">Ubicaciones</h1>

    <x-tarjeta-formulario titulo="Editar Ubicación" metodo="PUT" boton="Actualizar"
                          :accion="route('ubicaciones.update', $ubicacion)" :volver="route('ubicaciones.index')">
        @include('ubicaciones._form')
    </x-tarjeta-formulario>
</x-layouts.app>
