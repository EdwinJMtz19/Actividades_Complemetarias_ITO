<x-layouts.app titulo="Editar Departamento">
    <h1 class="mb-6 text-2xl font-semibold">Departamentos</h1>

    <x-tarjeta-formulario titulo="Editar Departamento" metodo="PUT" boton="Actualizar"
                          :accion="route('departamentos.update', $departamento)" :volver="route('departamentos.index')">
        @include('departamentos._form')
    </x-tarjeta-formulario>
</x-layouts.app>
