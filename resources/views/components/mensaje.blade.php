@props(['tipo' => 'info'])

@php
    $variantes = [
        'exito' => 'border-green-200 bg-green-50 text-green-800',
        'error' => 'border-red-200 bg-red-50 text-red-800',
        'advertencia' => 'border-amber-200 bg-amber-50 text-amber-800',
        'info' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];

    $clase = $variantes[$tipo] ?? $variantes['info'];
@endphp

@if (! $slot->isEmpty())
    <div {{ $attributes->merge(['class' => 'mb-4 rounded-md border px-4 py-3 text-sm '.$clase]) }}
         role="{{ $tipo === 'exito' ? 'status' : 'alert' }}">
        {{ $slot }}
    </div>
@endif
