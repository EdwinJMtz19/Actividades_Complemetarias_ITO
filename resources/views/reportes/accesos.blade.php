<x-layouts.app titulo="Reporte de accesos">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Últimos accesos</h1>
            <p class="text-sm text-slate-600">Consulta de solo lectura del último acceso registrado por usuario.</p>
        </div>
        <a href="{{ route('reportes.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Volver a reportes</a>
    </div>

    <section class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Usuarios</p>
            <p class="mt-1 text-2xl font-semibold">{{ $resumen['total'] }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cuentas activas</p>
            <p class="mt-1 text-2xl font-semibold text-green-700">{{ $resumen['activos'] }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sin acceso</p>
            <p class="mt-1 text-2xl font-semibold text-slate-600">{{ $resumen['sinAcceso'] }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Accesos recientes</p>
            <p class="mt-1 text-2xl font-semibold text-blue-700">{{ $resumen['recientes'] }}</p>
        </div>
    </section>

    <form method="GET" action="{{ route('reportes.accesos') }}" class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4">
        <label class="flex min-w-56 flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Buscar</span>
            <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Nombre o correo"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </label>

        <label class="flex min-w-48 flex-col gap-1 text-sm">
            <span class="font-medium text-slate-700">Rol</span>
            <select name="rol" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
                <option value="">Todos los roles</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol }}" @selected(($filtros['rol'] ?? '') === $rol)>{{ ucfirst($rol) }}</option>
                @endforeach
            </select>
        </label>

        <x-boton compacto>Filtrar</x-boton>
        <x-boton :href="route('reportes.accesos')" variante="secundario" compacto>Limpiar</x-boton>
    </form>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-2 border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold">Usuarios</h2>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $usuarios->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3 text-center">Rol</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3">Último acceso</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($usuarios as $usuario)
                        @php
                            $reciente = $usuario->ultimo_acceso && $usuario->ultimo_acceso->greaterThanOrEqualTo(now()->subDays(7));
                        @endphp
                        <tr class="odd:bg-white even:bg-slate-50 hover:bg-blue-50">
                            <td class="px-4 py-3 font-medium">{{ $usuario->nombre_completo }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $usuario->email }}</td>
                            <td class="px-4 py-3 text-center capitalize">{{ $usuario->roles->first()?->name }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $usuario->tipo_usuario }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if ($usuario->ultimo_acceso)
                                    <span title="{{ $usuario->ultimo_acceso->format('d/m/Y H:i:s') }}">{{ $usuario->ultimo_acceso->diffForHumans() }}</span>
                                @else
                                    <span class="text-slate-400">Nunca</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if (! $usuario->activo)
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">Inactiva</span>
                                @elseif (! $usuario->ultimo_acceso)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Sin acceso</span>
                                @elseif ($reciente)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Reciente</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Sin acceso reciente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">No hay usuarios que coincidan con el filtro.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $usuarios->links() }}</div>
        @endif
    </section>
</x-layouts.app>
