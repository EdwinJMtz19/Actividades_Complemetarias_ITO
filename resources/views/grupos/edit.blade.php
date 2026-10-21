<x-layouts.app titulo="Editar Grupo">
    <h1 class="mb-6 text-2xl font-semibold">Grupos</h1>

    <x-tarjeta-formulario titulo="Editar Grupo" ancho="max-w-4xl" metodo="PUT" boton="Actualizar Grupo"
                          :accion="route('grupos.update', $grupo)" :volver="route('grupos.show', $grupo)">
        @include('grupos._form')
    </x-tarjeta-formulario>
</x-layouts.app>
