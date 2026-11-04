<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inscripción confirmada</title>
</head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
        <div style="background:#1d4ed8;color:#ffffff;padding:20px 24px;">
            <h1 style="margin:0;font-size:20px;">Inscripción confirmada</h1>
        </div>
        <div style="padding:24px;">
            <p style="margin:0 0 16px;">Hola {{ $inscripcion->alumno->usuario->nombre_completo }},</p>
            <p style="margin:0 0 16px;">Tu inscripción a la actividad complementaria quedó registrada.</p>

            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Actividad</td>
                    <td style="padding:6px 0;font-weight:bold;">{{ $inscripcion->grupo->actividad->nombre }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Grupo</td>
                    <td style="padding:6px 0;">{{ $inscripcion->grupo->grupo }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Instructor</td>
                    <td style="padding:6px 0;">{{ $inscripcion->grupo->instructor?->usuario->nombre_completo ?? 'Por asignar' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Lugar</td>
                    <td style="padding:6px 0;">{{ $inscripcion->grupo->modalidad === 'virtual' ? 'Virtual' : ($inscripcion->grupo->ubicacion?->espacio ?? 'Por definir') }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Horario</td>
                    <td style="padding:6px 0;">
                        @forelse ($inscripcion->grupo->horarios as $horario)
                            {{ $horario->descripcion_corta }}@if (! $loop->last)<br>@endif
                        @empty
                            Por definir
                        @endforelse
                    </td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Periodo</td>
                    <td style="padding:6px 0;">{{ $inscripcion->grupo->semestre->etiqueta }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:#64748b;">Fecha de inscripción</td>
                    <td style="padding:6px 0;">{{ $inscripcion->fecha_inscripcion->format('d/m/Y H:i') }}</td>
                </tr>
            </table>

            <p style="margin:16px 0 0;font-size:13px;color:#64748b;">
                Si no reconoces esta inscripción, contacta con tu coordinador.
            </p>
        </div>
    </div>
</body>
</html>
