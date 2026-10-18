<x-layouts.app titulo="Nuevo usuario">
    <h1 class="mb-6 text-2xl font-semibold">Nuevo usuario</h1>

    <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6">
        @csrf
        @include('usuarios._form')
        <div class="flex justify-end gap-3">
            <x-boton :href="route('usuarios.index')" variante="secundario" icono="volver" gesto="izquierda">Cancelar</x-boton>
            <x-boton data-enviar class="mov-enviar">Guardar</x-boton>
        </div>
    </form>
</x-layouts.app>
