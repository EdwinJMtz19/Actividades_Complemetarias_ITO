@props(['titulo' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('panel') }}" class="text-base font-semibold text-blue-700">{{ config('app.name') }}</a>
                <nav class="mov-nav flex flex-wrap items-center gap-4 text-sm font-medium text-slate-600">
                    <a href="{{ route('panel') }}" class="hover:text-blue-700">Panel</a>
                    @can('viewAny', App\Models\User::class)
                        <a href="{{ route('usuarios.index') }}" class="hover:text-blue-700">Usuarios</a>
                    @endcan
                    @can('roles.ver')
                        <a href="{{ route('roles.index') }}" class="hover:text-blue-700">Roles y permisos</a>
                    @endcan
                    @can('semestres.ver')
                        <a href="{{ route('semestres.index') }}" class="hover:text-blue-700">Semestres</a>
                    @endcan
                    @can('departamentos.ver')
                        <a href="{{ route('departamentos.index') }}" class="hover:text-blue-700">Departamentos</a>
                    @endcan
                    @can('carreras.ver')
                        <a href="{{ route('carreras.index') }}" class="hover:text-blue-700">Carreras</a>
                    @endcan
                    @can('ubicaciones.ver')
                        <a href="{{ route('ubicaciones.index') }}" class="hover:text-blue-700">Ubicaciones</a>
                    @endcan
                </nav>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden text-slate-600 sm:inline">{{ auth()->user()->nombre_completo }}</span>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold capitalize text-blue-800">{{ auth()->user()->getRoleNames()->first() }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mov-boton rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-100">Salir</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('exito'))
            <x-mensaje tipo="exito" class="mov-banda">{{ session('exito') }}</x-mensaje>
        @endif
        @if (session('error'))
            <x-mensaje tipo="error" class="mov-banda">{{ session('error') }}</x-mensaje>
        @endif

        {{ $slot }}
    </main>

    @stack('scripts')
<!-- impeccable-live-start -->
<script src="http://localhost:8400/live.js?token=c98c4f6a-eb6d-4dc4-bc14-2df95ac644a9"></script>
<!-- impeccable-live-end -->
</body>
</html>
