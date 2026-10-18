<x-layouts.app titulo="Editar usuario">
    <h1 class="mb-6 text-2xl font-semibold">Editar usuario</h1>

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6">
        @csrf @method('PUT')
        @include('usuarios._form')
        <div class="flex justify-end gap-3">
            <x-boton :href="route('usuarios.show', $usuario)" variante="secundario" icono="volver" gesto="izquierda">Cancelar</x-boton>
            <x-boton data-enviar class="mov-enviar">Guardar cambios</x-boton>
        </div>
    </form>
</x-layouts.app>
