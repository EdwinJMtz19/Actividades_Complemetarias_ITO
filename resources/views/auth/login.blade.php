<x-layouts.guest titulo="Iniciar sesión">
    <h2 class="mb-4 text-lg font-semibold">Iniciar sesión</h2>

    {{-- `novalidate`: los mensajes los pone el servidor, siempre en español. --}}
    <form method="POST" action="{{ url('login') }}" novalidate class="space-y-4">
        @csrf
        <x-campo name="email" label="Correo electrónico" type="email" required autofocus autocomplete="username" />
        <x-campo-contrasena name="contrasena" label="Contraseña" required ayuda="Mínimo 8 caracteres." />

        <label class="mt-6 flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
            Mantener la sesión iniciada
        </label>

        <button type="submit" data-enviar class="mov-boton mov-enviar w-full rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Entrar</button>
    </form>

    <details class="mov-desglose mt-4 rounded-md border border-slate-200 px-4 py-3 text-sm text-slate-600">
        <summary class="cursor-pointer font-medium text-slate-700">¿No puedo entrar?</summary>
        <div class="mt-2 space-y-2">
            @if (config('contacto.soporte_email'))
                <p>
                    Si olvidaste tu contraseña o tu cuenta está desactivada, escríbele al
                    <a href="mailto:{{ config('contacto.soporte_email') }}" class="font-medium text-blue-700 hover:underline">{{ config('contacto.soporte_email') }}</a>.
                </p>
            @else
                <p>
                    Si olvidaste tu contraseña o tu cuenta está desactivada, comunícate con el
                    administrador del sistema para que reactive tu cuenta.
                </p>
            @endif
            <p>Las cuentas de personal las habilita el administrador; las de alumno se registran desde «Regístrate».</p>
        </div>
    </details>

    <p class="mt-4 text-center text-sm text-slate-600">
        ¿Eres alumno y aún no tienes cuenta?
        <a href="{{ route('registro') }}" class="font-medium text-blue-700 hover:underline">Regístrate</a>
    </p>
</x-layouts.guest>
