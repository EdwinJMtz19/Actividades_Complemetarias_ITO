@php
    $editando = isset($usuario);
    $rolActual = $editando ? $usuario->getRoleNames()->first() : old('rol');
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    @if ($editando)
        <div>
            <span class="block text-sm font-medium text-slate-700">Rol</span>
            <p class="mt-1 rounded-md bg-slate-100 px-3 py-2 text-sm capitalize">{{ $rolActual }}</p>
            <p class="mt-1 text-xs text-slate-500">El rol no se puede cambiar después de crear la cuenta.</p>
        </div>
    @else
        <x-seleccion name="rol" label="Rol" :opciones="collect($roles)->mapWithKeys(fn ($r) => [$r => ucfirst($r)])" :value="$rolActual" required />
    @endif
    <div class="flex items-end">
        <input type="hidden" name="activo" value="0">
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="activo" value="1" class="rounded border-slate-300"
                   @checked(old('activo', $editando ? $usuario->activo : true))
                   @disabled($editando && auth()->user()->is($usuario))>
            Cuenta activa
        </label>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <x-campo name="nombre" label="Nombre(s)" :value="$usuario->nombre ?? null" required />
    <x-campo name="apellido_paterno" label="Apellido paterno" :value="$usuario->apellido_paterno ?? null" required />
    <x-campo name="apellido_materno" label="Apellido materno" :value="$usuario->apellido_materno ?? null" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-campo name="email" label="Correo electrónico" type="email" :value="$usuario->email ?? null" required autocomplete="off" />
    <x-campo name="telefono" label="Teléfono" type="tel" :value="$usuario->telefono ?? null" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-campo name="contrasena" label="Contraseña" type="password" :required="! $editando" autocomplete="new-password"
             :ayuda="$editando ? 'Déjala en blanco para conservar la actual.' : 'Mínimo 8 caracteres, con letras y números.'" />
    <x-campo name="contrasena_confirmation" label="Confirmar contraseña" type="password" autocomplete="new-password" />
</div>

{{-- Datos propios de cada rol: solo se muestra (y se envía) el bloque del rol elegido. --}}
<fieldset data-rol="alumno" @disabled($rolActual !== 'alumno') class="{{ $rolActual === 'alumno' ? '' : 'hidden' }} space-y-4 rounded-md border border-slate-200 p-4">
    <legend class="px-1 text-sm font-semibold text-slate-600">Datos del alumno</legend>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-campo name="num_control" label="Número de control" :value="$usuario->alumno->num_control ?? null" required ayuda="8 o 9 caracteres" />
        <x-seleccion name="id_carrera" label="Carrera" :opciones="$carreras->pluck('nombre', 'id_carrera')" :value="$usuario->alumno->id_carrera ?? null" required />
        <x-campo name="semestre_cursando" label="Semestre" type="number" min="1" max="12" :value="$usuario->alumno->semestre_cursando ?? null" required />
    </div>
</fieldset>

<fieldset data-rol="instructor" @disabled($rolActual !== 'instructor') class="{{ $rolActual === 'instructor' ? '' : 'hidden' }} space-y-4 rounded-md border border-slate-200 p-4">
    <legend class="px-1 text-sm font-semibold text-slate-600">Datos del instructor</legend>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-seleccion name="id_departamento" label="Departamento" :opciones="$departamentos->pluck('nombre', 'id_departamento')" :value="$usuario->instructor->id_departamento ?? null" required />
        <x-campo name="especialidad" label="Especialidad" :value="$usuario->instructor->especialidad ?? null" />
    </div>
</fieldset>

<fieldset data-rol="coordinador" @disabled($rolActual !== 'coordinador') class="{{ $rolActual === 'coordinador' ? '' : 'hidden' }} space-y-4 rounded-md border border-slate-200 p-4">
    <legend class="px-1 text-sm font-semibold text-slate-600">Departamento que coordina</legend>
    <x-seleccion name="id_departamento" id="id_departamento_coord" label="Departamento" :opciones="$departamentos->pluck('nombre', 'id_departamento')" :value="$usuario->coordinacion->id_departamento ?? null" required />
</fieldset>

@unless ($editando)
    @push('scripts')
        <script>
            (function () {
                const selector = document.getElementById('rol');
                const bloques = document.querySelectorAll('fieldset[data-rol]');
                function actualizar() {
                    bloques.forEach(function (b) {
                        const activo = b.dataset.rol === selector.value;
                        b.disabled = !activo;
                        b.classList.toggle('hidden', !activo);
                    });
                }
                selector.addEventListener('change', actualizar);
                actualizar();
            })();
        </script>
    @endpush
@endunless
