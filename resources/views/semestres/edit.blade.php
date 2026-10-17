<x-layouts.app titulo="Editar Periodo Escolar">
    <h1 class="mb-6 text-2xl font-semibold">Gestión de Semestres</h1>

    <x-tarjeta-formulario titulo="Editar Periodo Escolar" ancho="max-w-3xl" metodo="PUT" boton="Actualizar Semestre"
                          :accion="route('semestres.update', $semestre)" :volver="route('semestres.index')">
        @if ($semestre->status === 'inactivo')
            <x-slot:insignia>
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Reactivación del período más reciente</span>
            </x-slot:insignia>

            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Estás editando un periodo <strong>inactivo</strong>. Cambia el estatus a <strong>Activo</strong> para reactivarlo.
            </div>
        @endif

        @include('semestres._form')
    </x-tarjeta-formulario>
</x-layouts.app>
