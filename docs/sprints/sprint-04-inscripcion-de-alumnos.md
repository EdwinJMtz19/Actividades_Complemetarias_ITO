# Sprint 4 · Inscripción de alumnos

**Fecha:** 2026-11-03
**Estado:** entregado
**Historias:** HU-17, HU-18 y HU-19 (RF-09, RF-10 y RF-11).

## Alcance

El alumno consulta las actividades y grupos de su carrera, se inscribe a un grupo del periodo
vigente y recibe un correo de confirmación. El coordinador consulta las inscripciones de su
departamento y da de baja a un alumno conservando el historial; el alumno también puede cancelar
su propia inscripción.

## HU-17 · Inscribirse a un grupo

- Solo el alumno con perfil y permiso `inscripciones.crear` puede inscribirse.
- La ventana de inscripción debe estar abierta (`Semestre::inscripcionesAbiertas()`).
- El grupo debe ser del periodo activo, estar `abierta`, pertenecer a una actividad `disponible`
  dirigida a la carrera del alumno y tener cupo.
- Se permite **una sola inscripción activa por alumno** (`inscrito` o `cursando`): índice único con
  columna generada en MySQL e índice parcial en SQLite.
- El alta descuenta `cupo_ocupado` de forma atómica dentro de una transacción.
- El catálogo vive en `resources/views/inscripciones/index.blade.php`.

## HU-18 · Correo de confirmación

- `App\Mail\InscripcionConfirmada` se encola después de confirmar el alta.
- Si el correo falla, la inscripción se conserva y el error se registra con `Log::error` (RNF-06).

## HU-19 · Dar de baja a alumnos

- El coordinador ve las inscripciones de los grupos de su departamento en
  `resources/views/inscripciones/gestion.blade.php`, con filtros por actividad, estatus y alumno.
- La baja es **lógica**: cambia el estatus a `dado_de_baja`, conserva la fila y libera un lugar del
  cupo en la misma transacción (`InscripcionController@baja`).
- Solo se dan de baja inscripciones activas; una segunda baja devuelve un mensaje de error y no
  descuenta el cupo otra vez.
- La autorización vive en `InscripcionPolicy@baja`: permiso `inscripciones.baja` y, o bien la
  inscripción es del propio alumno, o bien el coordinador es del departamento de la actividad. El
  administrador pasa por `Gate::before`.
- El alumno ve su inscripción activa (con el botón «Cancelar mi inscripción»), su historial
  (inscripciones no activas) y puede volver a inscribirse después de la baja.

## Modelo de datos

- Tabla `inscripcion` (`id_inscripcion`, `id_alumno`, `id_grupo`, `fecha_inscripcion`, `estatus`).
- Estatus: `inscrito`, `cursando`, `aprobado`, `reprobado`, `dado_de_baja`.
- `Inscripcion::ACTIVOS` = `inscrito`, `cursando`.
- `Grupo::inscripciones()` y `Alumno::inscripciones()`.

## Permisos

- `inscripciones.ver`, `inscripciones.crear` e `inscripciones.baja` (32 permisos en total).
- `alumno`: `actividades.ver`, `grupos.ver`, `inscripciones.ver`, `inscripciones.crear` e
  `inscripciones.baja` (esta última solo para cancelar su propia inscripción).
- `coordinador`: además de sus permisos de actividades y grupos, `inscripciones.ver` e
  `inscripciones.baja`.
- `administrador`: todos.

## Ajustes de soporte

- **Visibilidad del alumno:** `Actividad::scopeVisiblesPara`, `Grupo::scopeVisiblesPara` y
  `GrupoPolicy@view` permiten al alumno consultar las actividades y grupos de su carrera.
- **Rejilla semanal de horarios:** el formulario de grupos captura el horario con bloques de una
  hora (07:00-20:00, lunes a sábado). Es una mejora de interfaz: el servidor sigue aceptando
  cualquier intervalo válido.

## Pruebas

- `tests/Feature/InscripcionesTest.php` (HU-17 y HU-18).
- `tests/Feature/BajaInscripcionTest.php` (HU-19, 14 pruebas).
- `tests/Feature/VisibilidadAlumnoTest.php` (visibilidad del alumno).
- `tests/Feature/RolesPermisosTest.php` (`TOTAL_PERMISOS` = 32).

**Verificación:** `php artisan test` → **240 pruebas, 929 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones.
