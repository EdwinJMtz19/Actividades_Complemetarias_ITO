{{--
  Rejilla semanal del horario del grupo (bloques de una hora, 07:00–20:00).
  $dias trae los días de la semana y $intervalos los horarios previos (old() o los del grupo).
  El JavaScript del formulario traduce los bloques marcados a `horarios[i][...]`.
--}}
@php
    $horas = [];
    for ($hora = 7; $hora < 20; $hora++) {
        $horas[] = sprintf('%02d:00', $hora);
    }
@endphp

<div class="space-y-3" data-rejilla-horario>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wide text-blue-700">Horario semanal</h3>
            <p class="mt-1 text-xs text-slate-500">
                Haz clic en los bloques de una hora en que se reúne el grupo. Cada bloque se guarda como un horario.
            </p>
        </div>
        <x-boton type="button" variante="secundario" icono="eliminar" gesto="inclinar" compacto data-limpiar-horario>Limpiar</x-boton>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="w-full min-w-[42rem] border-collapse text-sm" data-rejilla>
            <thead>
                <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th scope="col" class="border-b border-slate-200 px-3 py-2 text-left">Hora</th>
                    @foreach ($dias as $dia)
                        <th scope="col" class="border-b border-slate-200 px-3 py-2 text-center">{{ $dia->nombre_dia }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($horas as $hora)
                    @php $fin = sprintf('%02d:00', (int) substr($hora, 0, 2) + 1); @endphp
                    <tr>
                        <th scope="row" class="whitespace-nowrap border-b border-slate-100 bg-slate-50 px-3 py-1 text-left text-xs font-semibold text-slate-500">
                            {{ $hora }}–{{ $fin }}
                        </th>
                        @foreach ($dias as $dia)
                            <td class="border-b border-slate-100 p-0">
                                <button type="button"
                                        data-slot
                                        data-dia="{{ $dia->id_dia }}"
                                        data-hora="{{ $hora }}"
                                        aria-pressed="false"
                                        aria-label="{{ ucfirst($dia->nombre_dia) }} de {{ $hora }} a {{ $fin }}"
                                        class="mov-rejilla-slot flex h-9 w-full items-center justify-center bg-white text-transparent focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-600">
                                    <x-icono nombre="palomita" class="h-4 w-4" />
                                </button>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-4 w-4 rounded border border-blue-700 bg-blue-600"></span> Seleccionado
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-4 w-4 rounded border border-slate-300 bg-white"></span> Disponible
        </span>
    </div>

    <p data-rejilla-aviso class="hidden text-xs text-amber-700"></p>

    {{-- Aquí el JavaScript deja los campos que espera GuardarGrupoRequest. --}}
    <div data-horarios></div>

    <script type="application/json" data-horarios-iniciales>@json($intervalos)</script>
</div>
