<x-layouts.app titulo="Editar usuario">
    <h1 class="mb-6 text-2xl font-semibold">Editar usuario</h1>

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6">
        @csrf @method('PUT')
        @include('usuarios._form')
        <div class="flex justify-end gap-3">
            <a href="{{ route('usuarios.show', $usuario) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">Cancelar</a>
            <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Guardar cambios</button>
        </div>
    </form>
</x-layouts.app>
