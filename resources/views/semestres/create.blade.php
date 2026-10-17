<x-layouts.app titulo="Nuevo Periodo Escolar">
    <h1 class="mb-6 text-2xl font-semibold">Gestión de Semestres</h1>

    <x-tarjeta-formulario titulo="Abrir Nuevo Periodo Escolar" ancho="max-w-3xl" boton="Guardar Semestre"
                          :accion="route('semestres.store')" :volver="route('semestres.index')">
        @include('semestres._form', ['semestre' => null])
    </x-tarjeta-formulario>
</x-layouts.app>
