# Changelog

Historial de cambios del sistema **Actividades Complementarias ITO**, pensado para quien usa o
recibe el sistema (no reemplaza a [`docs/bitacora.md`](docs/bitacora.md), que es el registro técnico
interno). Formato basado en [Keep a Changelog](https://keepachangelog.com/es/).

Las versiones corresponden a los sprints entregados; la fecha es la fecha de entrega.

## [0.4.0] - 2026-11-03

### Agregado

- **Inscripción de alumnos:** el alumno consulta las actividades y grupos de su carrera, se inscribe
  a un grupo del periodo vigente con cupo y una sola inscripción activa, y recibe un correo de
  confirmación.
- **Baja de alumnos:** el coordinador consulta las inscripciones de su departamento y da de baja a un
  alumno; la baja conserva el historial y libera el lugar. El alumno también puede cancelar su propia
  inscripción desde su pantalla de inscripciones.
- **Historial del alumno:** el alumno ve sus inscripciones anteriores (bajas, aprobadas o
  reprobadas) y puede volver a inscribirse.
- **Rejilla semanal de horarios:** el formulario de grupos captura los horarios con bloques de una
  hora, con leyenda y aviso de conflictos.

### Corregido

- **Visibilidad del alumno:** ya puede consultar las actividades y grupos de su carrera.

## [0.3.0] - 2026-10-20

### Agregado

- **Actividades:** catálogo de actividades complementarias asociadas a carreras y al semestre en curso.
- **Grupos:** creación de grupos por actividad con instructor, ubicación, cupo y días/horarios.
- **Detección de conflictos:** el servidor rechaza horarios que se traslapen para el mismo instructor
  o ubicación, y el formulario avisa en vivo mientras se captura el horario.
- **Consulta de grupos del instructor:** el rol instructor ya puede ver sus grupos asignados.

## [0.2.0] - 2026-10-17

### Agregado

- **Catálogos base:** CRUD de departamentos, carreras y ubicaciones.
- **Semestres:** alta y gestión de periodos con validación de duración (3 a 5 meses) y de la ventana
  de inscripción (5 a 10 días antes del inicio).
- **Cierre automático:** los semestres vencidos se cierran solos (tarea programada diaria).

## [0.1.0] - 2026-10-17

### Agregado

- **Acceso:** inicio de sesión con límite de 5 intentos fallidos; registro disponible solo para
  alumnos.
- **Usuarios:** administración de usuarios separada por rol (administrador, coordinador, instructor,
  alumno) con Spatie Permissions.
- **Permisos por departamento:** cada coordinador ve y gestiona solo a los instructores de su
  departamento.

[0.4.0]: https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO/commits/main
[0.3.0]: https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO/commits/main
[0.2.0]: https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO/commits/main
[0.1.0]: https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO/commits/main
