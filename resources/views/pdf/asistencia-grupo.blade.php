<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Lista de asistencia - {{ $grupo->actividad->nombre }} - Grupo {{ $grupo->grupo }}</title>
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
        .alumnos { width: 100%; border-collapse: collapse; }
        .alumnos thead { display: table-header-group; }
        .alumnos th { background: #e2e8f0; border: 1px solid #94a3b8; padding: 5px 6px; font-size: 9px; text-transform: uppercase; letter-spacing: .3px; }
        .alumnos td { border: 1px solid #cbd5e1; padding: 7px 6px; height: 24px; vertical-align: middle; }
        .alumnos tbody tr { page-break-inside: avoid; }
        .centro { text-align: center; }
        .vacio { text-align: center; color: #64748b; padding: 14px; }
        .indicacion { font-size: 9px; color: #475569; margin: 8px 0 4px; }
        .pie { margin-top: 8px; font-size: 9px; color: #475569; }
        .pie .total { font-weight: bold; color: #0f172a; }
        .firma-instructor { margin-top: 36px; width: 45%; text-align: center; }
        .firma-instructor .linea { border-top: 1px solid #0f172a; margin-bottom: 4px; }
        .firma-instructor .nombre { font-weight: bold; }
    </style>
</head>
<body>
    <div class="encabezado">
        <div class="institucion">Instituto Tecnológico de Oaxaca</div>
        <div class="titulo">Lista de asistencia</div>
        <div class="subtitulo">Actividades complementarias · {{ $grupo->actividad->departamento->nombre }}</div>
    </div>

    <table class="datos">
        <tr>
            <th>Actividad</th>
            <td>{{ $grupo->actividad->nombre }}</td>
            <th>Grupo</th>
            <td>{{ $grupo->grupo }}</td>
        </tr>
        <tr>
            <th>Periodo</th>
            <td>{{ $grupo->semestre->etiqueta }}</td>
            <th>Modalidad</th>
            <td>{{ \App\Models\Grupo::MODALIDADES[$grupo->modalidad] ?? $grupo->modalidad }}</td>
        </tr>
        <tr>
            <th>Instructor</th>
            <td>{{ $grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
            <th>Ubicación</th>
            <td>
                @if ($grupo->ubicacion)
                    {{ $grupo->ubicacion->espacio }}
                @else
                    {{ $grupo->modalidad === 'virtual' ? 'No aplica (virtual)' : 'Sin ubicación' }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Horario</th>
            <td>
                @forelse ($grupo->horarios as $horario)
                    {{ $horario->descripcion }}@if (! $loop->last) · @endif
                @empty
                    Sin horario
                @endforelse
            </td>
            <th>Fechas</th>
            <td>{{ $grupo->fecha_inicio->format('d/m/Y') }} al {{ $grupo->fecha_fin->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p class="indicacion">
        El alumno firma al entrar a cada sesión. La lista incluye únicamente alumnos con estatus Inscrito o Cursando.
    </p>

    <table class="alumnos">
        <thead>
            <tr>
                <th style="width: 6%;">No.</th>
                <th style="width: 14%;">No. control</th>
                <th style="width: 36%;">Alumno</th>
                <th style="width: 26%;">Carrera</th>
                <th style="width: 18%;">Firma</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripciones as $inscripcion)
                <tr>
                    <td class="centro">{{ $loop->iteration }}</td>
                    <td>{{ $inscripcion->alumno->num_control }}</td>
                    <td>{{ $inscripcion->alumno->usuario->nombre_completo }}</td>
                    <td>{{ $inscripcion->alumno->carrera?->nombre ?? 'Sin carrera' }}</td>
                    <td>&nbsp;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="vacio">Este grupo no tiene alumnos activos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">
        <span class="total">Total de alumnos activos: {{ $inscripciones->count() }}</span>
        <span> · Generado el {{ $generadoEl->format('d/m/Y H:i') }}</span>
    </div>

    <div class="firma-instructor">
        <div class="linea"></div>
        <div>Nombre y firma del instructor</div>
        <div class="nombre">{{ $grupo->instructor?->usuario->nombre_completo ?? '' }}</div>
    </div>
</body>
</html>
