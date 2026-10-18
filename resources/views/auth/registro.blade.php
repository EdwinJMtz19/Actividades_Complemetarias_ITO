<x-layouts.guest titulo="Registro de alumno">
    <h2 class="mb-4 text-lg font-semibold">Registro de alumno</h2>

    @if ($errors->any() && ! $errors->has('credenciales'))
        <x-mensaje tipo="info" class="mov-banda">Revisa los campos marcados con <span class="text-red-600">*</span>.</x-mensaje>
    @endif

    {{-- `novalidate`: los mensajes los pone el servidor, siempre en español. --}}
    <form method="POST" action="{{ url('registro') }}" novalidate class="space-y-4">
        @csrf
        <x-campo name="nombre" label="Nombre(s)" required />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-campo name="apellido_paterno" label="Apellido paterno" required />
            <x-campo name="apellido_materno" label="Apellido materno" />
        </div>
        <x-campo name="email" label="Correo electrónico" type="email" required autocomplete="username" />
        <x-campo name="telefono" label="Teléfono" type="tel" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-campo name="num_control" label="Número de control" required ayuda="8 o 9 caracteres" />
            <x-campo name="semestre_cursando" label="Semestre" type="number" min="1" max="12" required />
        </div>
        <x-seleccion name="id_carrera" label="Carrera" :opciones="$carreras->pluck('nombre', 'id_carrera')" required />
        <x-campo-contrasena name="contrasena" label="Contraseña" required autocomplete="new-password" ayuda="Mínimo 8 caracteres, con letras y números." />
        <x-campo name="contrasena_confirmation" label="Confirmar contraseña" type="password" required autocomplete="new-password" />

        <button type="submit" data-enviar class="mov-boton mov-enviar w-full rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Crear cuenta</button>
    </form>

    <p class="mt-4 text-center text-sm text-slate-600">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="font-medium text-blue-700 hover:underline">Inicia sesión</a>
    </p>
</x-layouts.guest>
