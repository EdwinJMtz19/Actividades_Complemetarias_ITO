{{-- Fecha dd/mm/aaaa con la hora (HH:MM) opcional como insignia. --}}
@props(['fecha', 'hora' => null])
<span>{{ $fecha->format('d/m/Y') }}@if ($hora)<span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">{{ $hora }}</span>@endif</span>
