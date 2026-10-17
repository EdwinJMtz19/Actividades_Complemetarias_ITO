<x-layouts.app titulo="Nueva Carrera">
    <h1 class="mb-6 text-2xl font-semibold">Carreras</h1>

    <x-tarjeta-formulario titulo="Nueva Carrera" :accion="route('carreras.store')" :volver="route('carreras.index')">
        @include('carreras._form', ['carrera' => null])
    </x-tarjeta-formulario>
</x-layouts.app>
