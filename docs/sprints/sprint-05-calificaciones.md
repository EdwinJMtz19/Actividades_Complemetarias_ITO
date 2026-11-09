# Sprint 5 · Calificaciones y créditos

**Fecha:** 2026-11-03
**Estado:** en curso (HU-20 entregada; HU-21 pendiente)
**Historias:** HU-20 (RF-12) y HU-21 (RF-12).

## Alcance

El instructor califica a los alumnos inscritos en sus grupos con un desempeño cualitativo
(`excelente`, `bueno` o `malo`) y observaciones opcionales. La inscripción pasa a `aprobado` o
`reprobado` según el desempeño. El coordinador consulta las calificaciones de su departamento y el
alumno ve la suya en su historial. El cálculo de créditos acumulados queda para HU-21.

## HU-20 · Registrar calificación

- Solo el instructor del grupo puede registrar o modificar la calificación (`calificaciones.editar`
  y `InscripcionPolicy@calificar`). El coordinador consulta en modo lectura
  (`calificaciones.ver`) y el administrador pasa por `Gate::before`.
- La calificación es **única por inscripción**: `calificacion.id_inscripcion` es único y el
  controlador usa `updateOrCreate`, así que modificar no duplica la fila.
- `desempenio` es obligatorio y debe pertenecer al catálogo `Calificacion::DESEMPENIOS`.
  `observaciones` es opcional, de hasta 255 caracteres.
- Al guardar, la inscripción cambia de estatus en la misma transacción: `bueno` y `excelente` →
  `aprobado`; `malo` → `reprobado`.
- No se califica una inscripción `dado_de_baja`.
- La lista de alumnos aparece en `resources/views/grupos/show.blade.php` solo para quien tiene
  `calificaciones.ver`; el alumno no ve a sus compañeros.
- El formulario vive en `resources/views/calificaciones/edit.blade.php` y reutiliza
  `x-tarjeta-formulario` y tarjetas de radio con `peer-checked`.
- El alumno ve la calificación y las observaciones en la columna «Calificación» del historial de
  `inscripciones/index`.

## HU-21 · Actualizar créditos acumulados (pendiente)

- Al aprobar una actividad se sumarán sus créditos a `alumno.creditos_acumulados`.
- Debe ser idempotente y transaccional: modificar una calificación no debe duplicar créditos.
- Se implementará en una entrega posterior del mismo sprint.

## Modelo de datos

- Tabla `calificacion` (`id_calificacion`, `id_inscripcion` único, `desempenio` nullable,
  `observaciones` de 255, timestamps). FK a `inscripcion`.
- `Calificacion::DESEMPENIOS` = `excelente`, `bueno`, `malo`.
- `Inscripcion::calificacion()` (HasOne) y `Calificacion::inscripcion()` (BelongsTo).
- El estatus `aprobado`/`reprobado` sigue viviendo en `inscripcion`.

## Permisos

- `calificaciones.ver` y `calificaciones.editar` (34 permisos en total).
- `instructor`: `calificaciones.ver` y `calificaciones.editar` (solo sus grupos).
- `coordinador`: `calificaciones.ver` (solo su departamento).
- `administrador`: todos.
- `alumno`: sin permisos nuevos; ve su calificación en el historial.

## Rutas

- `GET /inscripciones/{inscripcion}/calificacion` → `CalificacionController@edit`
  (`permission:calificaciones.ver`, nombre `calificaciones.edit`).
- `PUT /inscripciones/{inscripcion}/calificacion` → `CalificacionController@update`
  (`permission:calificaciones.editar`, nombre `calificaciones.update`).

## Pruebas

- `tests/Feature/CalificacionesTest.php` (12 pruebas): alcance del instructor, visibilidad del
  alumno, registro de excelente/bueno/malo, modificación sin duplicar, instructor de otro grupo,
  alumno y coordinador sin edición, inscripción dada de baja, validación del desempeño, lectura del
  alumno en el historial, lectura del coordinador y administrador en cualquier departamento.
- `tests/Feature/RolesPermisosTest.php` (`TOTAL_PERMISOS` = 34).

**Verificación:** `php artisan test` → **252 pruebas, 978 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones; `npm run build` correcto.
