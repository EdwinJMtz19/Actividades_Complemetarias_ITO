<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Horario del alumno - {{ $alumno->usuario->nombre_completo }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 28px 30px 34px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #0f172a; margin: 0; }
        .encabezado { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        .institucion { font-size: 14px; font-weight: bold; letter-spacing: .4px; }
        .titulo { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .subtitulo { font-size: 10px; color: #475569; margin-top: 2px; }
        .datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .datos th { width: 15%; text-align: left; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 6px; font-size: 9px; text-transform: uppercase; color: #475569; }
        .datos td { width: 35%; border: 1px solid #cbd5e1; padding: 4px 6px; }
        .horario { width: 100%; border-collapse: collapse; }
        .horario thead { display: table-header-group; }
        .horario th { background: #e2e8f0; border: 1px solid #94a3b8; padding: 5px 6px; font-size: 9px; text-transform: uppercase; letter-spacing: .3px; }
        .horario td { border: 1px solid #cbd5e1; padding: 7px 6px; vertical-align: middle; }
        .horario tbody tr { page-break-inside: avoid; }
        .centro { text-align: center; }
        .vacio { text-align: center; color: #64748b; padding: 14px; }
        .pie { margin-top: 8px; font-size: 9px; color: #475569; }
        .pie .total { font-weight: bold; color: #0f172a; }
    </style>
</head>
<body>
    <div class="encabezado">
        <div class="institucion">Instituto Tecnológico de Oaxaca</div>
        <div class="titulo">Horario del alumno</div>
        <div class="subtitulo">Actividades complementarias · {{ $inscripcion->grupo->actividad->departamento->nombre }}</div>
    </div>

    <table class="datos">
        <tr>
            <th>Alumno</th>
            <td>{{ $alumno->usuario->nombre_completo }}</td>
            <th>No. control</th>
            <td>{{ $alumno->num_control }}</td>
        </tr>
        <tr>
            <th>Carrera</th>
            <td>{{ $alumno->carrera?->nombre ?? 'Sin carrera' }}</td>
            <th>Semestre</th>
            <td>{{ $alumno->semestre_cursando }}°</td>
        </tr>
        <tr>
            <th>Actividad</th>
            <td>{{ $inscripcion->grupo->actividad->nombre }}</td>
            <th>Grupo</th>
            <td>{{ $inscripcion->grupo->grupo }}</td>
        </tr>
        <tr>
            <th>Periodo</th>
            <td>{{ $inscripcion->grupo->semestre->etiqueta }}</td>
            <th>Modalidad</th>
            <td>{{ \App\Models\Grupo::MODALIDADES[$inscripcion->grupo->modalidad] ?? $inscripcion->grupo->modalidad }}</td>
        </tr>
        <tr>
            <th>Instructor</th>
            <td>{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
            <th>Lugar</th>
            <td>
                @if ($inscripcion->grupo->ubicacion)
                    {{ $inscripcion->grupo->ubicacion->espacio }}
                @else
                    {{ $inscripcion->grupo->modalidad === 'virtual' ? 'No aplica (virtual)' : 'Sin ubicación' }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Fechas</th>
            <td colspan="3">{{ $inscripcion->grupo->fecha_inicio->format('d/m/Y') }} al {{ $inscripcion->grupo->fecha_fin->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="horario">
        <thead>
            <tr>
                <th style="width: 15%;">Día</th>
                <th style="width: 17%;">Hora</th>
                <th style="width: 27%;">Actividad</th>
                <th style="width: 9%;">Grupo</th>
                <th style="width: 19%;">Instructor</th>
                <th style="width: 13%;">Lugar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripcion->grupo->horarios as $horario)
                <tr>
                    <td>{{ $horario->dia->nombre_dia }}</td>
                    <td class="centro">{{ $horario->inicio_corto }} a {{ $horario->fin_corto }}</td>
                    <td>{{ $inscripcion->grupo->actividad->nombre }}</td>
                    <td class="centro">{{ $inscripcion->grupo->grupo }}</td>
                    <td>{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
                    <td>
                        @if ($inscripcion->grupo->ubicacion)
                            {{ $inscripcion->grupo->ubicacion->espacio }}
                        @else
                            {{ $inscripcion->grupo->modalidad === 'virtual' ? 'Virtual' : 'Sin ubicación' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="vacio">Este grupo no tiene horarios registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">
        <span class="total">Bloques de clase: {{ $inscripcion->grupo->horarios->count() }}</span>
        <span> · Generado el {{ $generadoEl->format('d/m/Y H:i') }}</span>
    </div>
</body>
</html>
