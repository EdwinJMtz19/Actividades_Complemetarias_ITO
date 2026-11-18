# Sprint 7 — Generación de documentos (CSV y PDF)

**Fecha de entrega:** en curso
**Estado:** en curso (HU-24 y HU-25 entregadas; HU-26 y HU-27 pendientes)
**Historias:** HU-24 (RF-14) — Exportar lista de alumnos en CSV; HU-25 (RF-14) — Descargar lista de asistencia en PDF; HU-26 (RF-15) — Descargar horario en PDF; HU-27 (RF-15) — Descargar constancia de acreditación.

## Alcance

El sistema genera documentos descargables a partir de la información que ya existe: la lista de
alumnos de un grupo en CSV, la lista de asistencia en PDF, el horario del alumno en PDF y su
constancia de acreditación. HU-24 y HU-25 ya están entregadas.

## HU-24 — Exportar lista de alumnos en CSV

- `GrupoController@exportarAlumnos` responde a `GET grupos/{grupo}/alumnos.csv`.
- La ruta exige el permiso `calificaciones.ver` y el controlador refuerza con
  `Gate::authorize('view', $grupo)`: el instructor solo descarga sus grupos, el coordinador los de
  su departamento y el administrador todos.
- El CSV incluye número de control, nombre, correo, carrera, estatus, calificación, observaciones
  y fecha de inscripción de todas las inscripciones del grupo.
- Usa BOM UTF-8 para que Excel reconozca los acentos y neutraliza valores que empiezan con `=`,
  `+`, `-` o `@` para evitar inyección de fórmulas.
- `grupos/show` muestra el botón «Exportar CSV» dentro de la sección de alumnos inscritos.
- No agrega permisos, migraciones ni tablas.

## HU-25 · Descargar lista de asistencia en PDF

- `GrupoController@descargarAsistencia` responde a `GET grupos/{grupo}/asistencia.pdf`.
- La ruta exige el permiso `calificaciones.ver` y el controlador refuerza con
  `Gate::authorize('view', $grupo)`: el instructor solo descarga sus grupos, el coordinador los de
  su departamento y el administrador todos.
- El PDF incluye actividad, grupo, periodo, instructor, ubicación, horario, fechas, una tabla con
  número de control, alumno, carrera y espacio para la firma, además del total de alumnos activos.
- Solo incluye alumnos con estatus `inscrito` o `cursando`; las bajas y los ya calificados quedan
  fuera de la hoja de asistencia.
- Usa `barryvdh/laravel-dompdf` con la vista `pdf/asistencia-grupo`. La caché de fuentes se guarda
  en `storage/fonts`.
- `grupos/show` muestra el botón «Lista de asistencia PDF» junto a «Exportar CSV».
- No agrega permisos, migraciones ni tablas.

## Pendiente

- HU-26: horario del alumno en PDF.
- HU-27: constancia de acreditación.

## Pruebas

- `tests/Feature/ExportarAlumnosCsvTest.php` (7 pruebas): login obligatorio, descarga del CSV con
  contenido y nombre de archivo, acceso de administrador y coordinador, bloqueo de alumno,
  instructor ajeno y otro departamento, grupo vacío, neutralización de fórmulas y botón visible solo
  para quien puede ver la lista.
- `tests/Feature/DescargarAsistenciaPdfTest.php` (7 pruebas): login obligatorio, descarga del PDF,
  contenido con datos del grupo y solo alumnos activos, acceso de administrador y coordinador,
  bloqueo de alumno, instructor ajeno y otro departamento, grupo sin alumnos activos y botón visible
  solo para quien puede ver la lista.
- `tests/Feature/RolesPermisosTest.php` (`TOTAL_PERMISOS` = 36; no cambia).

**Verificación:** `php artisan test` → **294 pruebas, 1214 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones.
