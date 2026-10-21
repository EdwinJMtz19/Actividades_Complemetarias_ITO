<x-layouts.app titulo="Nuevo Grupo">
    <h1 class="mb-6 text-2xl font-semibold">Grupos</h1>

    <x-tarjeta-formulario titulo="Nuevo Grupo" ancho="max-w-4xl" boton="Guardar Grupo"
                          :accion="route('grupos.store')" :volver="route('grupos.index')">
        @include('grupos._form', ['grupo' => null])
    </x-tarjeta-formulario>
</x-layouts.app>
