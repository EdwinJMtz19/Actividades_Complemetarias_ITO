<x-layouts.app titulo="Nuevo usuario">
    <h1 class="mb-6 text-2xl font-semibold">Nuevo usuario</h1>

    <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6">
        @csrf
        @include('usuarios._form')
        <div class="flex justify-end gap-3">
            <a href="{{ route('usuarios.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">Cancelar</a>
            <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Guardar</button>
        </div>
    </form>
</x-layouts.app>
