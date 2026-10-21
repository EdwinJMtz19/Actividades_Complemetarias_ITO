<x-layouts.app titulo="Editar Actividad">
    <h1 class="mb-6 text-2xl font-semibold">Actividades complementarias</h1>

    <x-tarjeta-formulario titulo="Editar Actividad" ancho="max-w-3xl" metodo="PUT" boton="Actualizar"
                          :accion="route('actividades.update', $actividad)" :volver="route('actividades.index')">
        @include('actividades._form')
    </x-tarjeta-formulario>
</x-layouts.app>
