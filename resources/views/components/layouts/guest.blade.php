@props(['titulo' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 text-slate-800 antialiased">
    {{-- Marca de agua del ITO: decorativa, fija al fondo y muy tenue (ver `.fondo-logo` en app.css). --}}
    <div class="fondo-logo" aria-hidden="true"></div>
    {{-- `data-acceso` es el gancho del lenguaje de movimiento de las pantallas de acceso. --}}
    <main class="w-full max-w-md" data-acceso>
        <h1 class="mb-1 text-center text-xl font-semibold text-blue-700">{{ config('app.name') }}</h1>
        <p class="mb-6 text-center text-sm text-slate-500">Instituto Tecnológico de Oaxaca</p>

        {{-- Región de mensajes: todo aviso que no pertenece a un campo vive aquí. --}}
        <x-mensaje tipo="exito" class="mov-banda">{{ session('exito') }}</x-mensaje>
        <x-mensaje tipo="error" class="mov-banda">{{ session('error') }}</x-mensaje>
        <x-mensaje tipo="advertencia" class="mov-banda">{{ session('aviso') }}</x-mensaje>
        <x-mensaje tipo="error" class="mov-banda">{{ $errors->first('credenciales') }}</x-mensaje>

        <div class="mov-tarjeta rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
