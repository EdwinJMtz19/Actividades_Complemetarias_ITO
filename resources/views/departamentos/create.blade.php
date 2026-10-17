<x-layouts.app titulo="Nuevo Departamento">
    <h1 class="mb-6 text-2xl font-semibold">Departamentos</h1>

    <x-tarjeta-formulario titulo="Nuevo Departamento" :accion="route('departamentos.store')" :volver="route('departamentos.index')">
        @include('departamentos._form', ['departamento' => null])
    </x-tarjeta-formulario>
</x-layouts.app>
