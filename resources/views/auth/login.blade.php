<x-layouts.guest titulo="Iniciar sesión">
    <h2 class="mb-4 text-lg font-semibold">Iniciar sesión</h2>

    <form method="POST" action="{{ url('login') }}" class="space-y-4">
        @csrf
        <x-campo name="email" label="Correo electrónico" type="email" required autofocus autocomplete="username" />
        <x-campo name="contrasena" label="Contraseña" type="password" required autocomplete="current-password" />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
            Mantener la sesión iniciada
        </label>

        <button type="submit" class="w-full rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Entrar</button>
    </form>

    <p class="mt-4 text-center text-sm text-slate-600">
        ¿Eres alumno y aún no tienes cuenta?
        <a href="{{ route('registro') }}" class="font-medium text-blue-700 hover:underline">Regístrate</a>
    </p>
</x-layouts.guest>
