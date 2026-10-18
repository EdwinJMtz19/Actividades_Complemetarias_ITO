<x-layouts.app titulo="Roles y permisos">
    <h1 class="mb-2 text-2xl font-semibold">Roles y permisos</h1>
    <p class="mb-6 text-sm text-slate-600">
        El administrador siempre tiene acceso total. Para los demás roles, marca los permisos que deben tener.
        Los coordinadores, además, solo ven a los instructores de su departamento.
    </p>

    <div class="space-y-6">
        @foreach ($roles as $rol)
            @php $bloqueado = $rol->name === 'administrador'; @endphp
            <form method="POST" action="{{ route('roles.update', $rol) }}" class="rounded-lg border border-slate-200 bg-white p-5">
                @csrf @method('PUT')
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold capitalize">{{ $rol->name }}</h2>
                    @if ($bloqueado)
                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">Acceso total (no editable)</span>
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($permisos as $modulo => $lista)
                        <div>
                            <h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $modulo }}</h3>
                            @foreach ($lista as $permiso)
                                <label class="flex items-center gap-2 py-0.5 text-sm">
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}"
                                           class="rounded border-slate-300"
                                           @checked($bloqueado || $rol->hasPermissionTo($permiso->name))
                                           @disabled($bloqueado)>
                                    {{ $permiso->name }}
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                @unless ($bloqueado)
                    <div class="mt-4 flex justify-end">
                        <x-boton data-enviar class="mov-enviar">Guardar permisos</x-boton>
                    </div>
                @endunless
            </form>
        @endforeach
    </div>
</x-layouts.app>
