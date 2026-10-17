<x-layouts.app titulo="Editar Carrera">
    <h1 class="mb-6 text-2xl font-semibold">Carreras</h1>

    <x-tarjeta-formulario titulo="Editar Carrera" metodo="PUT" boton="Actualizar"
                          :accion="route('carreras.update', $carrera)" :volver="route('carreras.index')">
        @include('carreras._form')
    </x-tarjeta-formulario>
</x-layouts.app>
