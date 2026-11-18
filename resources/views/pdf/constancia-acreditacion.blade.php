<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Constancia de acreditación - {{ $inscripcion->alumno->usuario->nombre_completo }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 40px 48px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #0f172a; margin: 0; line-height: 1.5; }
        .marco { border: 2px solid #0f172a; padding: 26px 30px; }
        .encabezado { text-align: center; border-bottom: 1px solid #94a3b8; padding-bottom: 10px; }
        .institucion { font-size: 15px; font-weight: bold; letter-spacing: .5px; }
        .departamento { font-size: 11px; color: #475569; margin-top: 2px; }
        .titulo { font-size: 20px; font-weight: bold; letter-spacing: 1px; text-align: center; margin: 24px 0 18px; text-transform: uppercase; }
        .cuerpo { text-align: justify; }
        .alumno { font-weight: bold; }
        .datos { width: 100%; border-collapse: collapse; margin: 18px 0; }
        .datos th { width: 22%; text-align: left; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 5px 7px; font-size: 9px; text-transform: uppercase; color: #475569; }
        .datos td { border: 1px solid #cbd5e1; padding: 5px 7px; }
        .nota { font-size: 10px; color: #475569; margin-top: 14px; }
        .firmas { width: 100%; margin-top: 60px; }
        .firmas td { width: 50%; text-align: center; vertical-align: bottom; }
        .firmas .linea { border-top: 1px solid #0f172a; margin: 0 18px 4px; }
        .firmas .cargo { font-size: 9px; color: #475569; }
        .firmas .nombre { font-weight: bold; }
        .pie { margin-top: 26px; font-size: 8px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="marco">
        <div class="encabezado">
            <div class="institucion">Instituto Tecnológico de Oaxaca</div>
            <div class="departamento">Departamento de {{ $inscripcion->grupo->actividad->departamento->nombre }} · Actividades complementarias</div>
        </div>

        <div class="titulo">Constancia de acreditación</div>

        <p class="cuerpo">
            El Instituto Tecnológico de Oaxaca, a través del Departamento de
            {{ $inscripcion->grupo->actividad->departamento->nombre }}, hace constar que
            <span class="alumno">{{ $inscripcion->alumno->usuario->nombre_completo }}</span>,
            con número de control <span class="alumno">{{ $inscripcion->alumno->num_control }}</span>,
            de la carrera de {{ $inscripcion->alumno->carrera?->nombre ?? 'la carrera registrada' }},
            acreditó la actividad complementaria «{{ $inscripcion->grupo->actividad->nombre }}»,
            cursada en el grupo {{ $inscripcion->grupo->grupo }}, con valor de
            {{ $inscripcion->grupo->actividad->creditos }} crédito(s), durante el periodo
            {{ $inscripcion->grupo->semestre->etiqueta }}.
        </p>

        <table class="datos">
            <tr>
                <th>Actividad</th>
                <td>{{ $inscripcion->grupo->actividad->nombre }}</td>
                <th>Créditos</th>
                <td>{{ $inscripcion->grupo->actividad->creditos }}</td>
            </tr>
            <tr>
                <th>Periodo</th>
                <td>{{ $inscripcion->grupo->semestre->etiqueta }}</td>
                <th>Grupo</th>
                <td>{{ $inscripcion->grupo->grupo }}</td>
            </tr>
            <tr>
                <th>Instructor</th>
                <td>{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? 'Sin asignar' }}</td>
                <th>Desempeño</th>
                <td>{{ \App\Models\Calificacion::DESEMPENIOS[$inscripcion->calificacion->desempenio] ?? $inscripcion->calificacion->desempenio }}</td>
            </tr>
            <tr>
                <th>Fechas del grupo</th>
                <td>{{ $inscripcion->grupo->fecha_inicio->format('d/m/Y') }} al {{ $inscripcion->grupo->fecha_fin->format('d/m/Y') }}</td>
                <th>Acreditación</th>
                <td>{{ $inscripcion->calificacion->updated_at?->format('d/m/Y') ?? 'Registrada' }}</td>
            </tr>
        </table>

        @if ($inscripcion->calificacion->observaciones)
            <p class="nota"><strong>Observaciones:</strong> {{ $inscripcion->calificacion->observaciones }}</p>
        @endif

        <p class="cuerpo">Se expide la presente constancia para los fines que al interesado convengan.</p>

        <table class="firmas">
            <tr>
                <td>
                    <div class="linea"></div>
                    <div class="nombre">{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? '' }}</div>
                    <div class="cargo">Nombre y firma del instructor</div>
                </td>
                <td>
                    <div class="linea"></div>
                    <div class="nombre">&nbsp;</div>
                    <div class="cargo">Jefatura del Departamento de {{ $inscripcion->grupo->actividad->departamento->nombre }}</div>
                </td>
            </tr>
        </table>

        <div class="pie">Expedida el {{ $generadoEl->format('d/m/Y H:i') }} · Documento generado por el Sistema de Actividades Complementarias.</div>
    </div>
</body>
</html>
