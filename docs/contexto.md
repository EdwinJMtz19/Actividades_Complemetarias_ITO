# Contexto del proyecto

> Lee este documento antes de modificar código. Aquí está el «qué» y el «por qué».
> El «cómo se trabaja» está en [`convenciones.md`](convenciones.md).

## 1. Qué es

Sistema web para administrar las **actividades complementarias** del ITO: catálogo de
departamentos, carreras, Instructors y alumnos; inscripción de alumnos a actividades; horarios,
inscripciones y calificaciones; y dashboards por rol.

Cada persona entra con un **rol** y opera solo lo que le corresponde:

| Rol | Qué puede hacer |
|---|---|
| `administrador` | Todo, sin restricciones de departamento. Único que administra usuarios, roles y catálogos. |
| `coordinador` | Ve y administra a los **instructores, actividades y grupos de su departamento**; consulta las calificaciones de ese departamento (Sprint 5). |
| `instructor` | Consulta **solo los grupos que imparte** y califica a sus alumnos (Sprints 3 y 5); no crea ni edita grupos. |
| `alumno` | Consulta el catálogo de grupos, se inscribe (Sprint 4, HU-17) y ve su calificación en el historial (Sprint 5). |

## 2. Cronograma de sprints

| Sprint | Alcance | Estado |
|---|---|---|
| 0 | Modelo de datos base (paquete `Sprint0_Base_de_Datos.zip`) | Parcial: solo las tablas del Sprint 1 |
| 1 | Usuarios, roles y permisos + acceso por departamento | **Entregado** ([detalle](sprints/sprint-01-usuarios-y-roles.md)) |
| 2 | Catálogos base y semestre: CRUD de departamentos, carreras y ubicaciones; semestres con cierre automático y validaciones | **Entregado** ([detalle](sprints/sprint-02-catalogos-y-semestres.md)) |
| 3 | Actividades, grupos y horarios (instructor) | **Entregado** ([detalle](sprints/sprint-03-actividades-y-grupos.md)) |
| 4 | Inscripción de alumnos + recuperación de contraseña (SMTP) | **Entregado** (HU-17, HU-18 y HU-19) |
| 5 | Calificaciones y créditos | **En curso** (HU-20 entregada; HU-21 pendiente) |
| 6 | Dashboards por rol | Pendiente |

## 3. Modelo de datos

### Tablas que ya existen

| Tabla | Qué guarda | Notas |
|---|---|---|
| `USUARIO` | Cuenta de acceso de toda persona | **Nombre y columna `contrasena` en mayúsculas**, fiel al modelo E-R. `password_reset_tokens` y `sessions` se crean en la misma migración. |
| `alumno` | Perfil de alumno | PK `id_alumno` = `USUARIO.id`. `num_control`, `id_carrera`, `semestre_cursando`, `creditos_acumulados`. |
| `instructor` | Perfil de instructor | PK `id_instructor` = `USUARIO.id`. `id_departamento`, `especialidad`. |
| `coordinador_departamento` | Qué coordinadores da qué departamento | `id_usuario` único y `id_departamento` único (un coordinador por departamento). |
| `departamento` | Catálogo de departamentos | `nombre`, `edificio`. CRUD en el Sprint 2. |
| `carrera` | Catálogo de carreras | `nombre`. CRUD en el Sprint 2. |
| `ubicacion` | Catálogo de espacios físicos | `espacio` (único) y `capacidad`. Sprint 2. |
| `semestre` | Periodos escolares | Columna `año` (con ñ), `periodo` (1 = ene–jun, 2 = ago–dic), fechas y horas de inscripción, `status` (`activo`/`inactivo`). Único `(año, periodo)`. Sprint 2. |
| `roles`, `permissions`, `model_has_*`, `role_has_*` | Spatie | Permisos y roles. |
| `cache`, `jobs`, `sessions` | Infraestructura Laravel | Necesarias porque `.env` usa drivers `database`. |
| `dia_semana` | Días de la semana (lunes a sábado) | Con acentos; `utf8mb4`. Sprint 3. |
| `actividad_complementaria` | Catálogo de actividades | `nombre` único, `creditos` 1 o 2, `disponible`, FK a departamento y semestre (nullable). Sprint 3. |
| `actividad_carrera` | Actividades ↔ carreras | Many-to-many con `cascade`. Sprint 3. |
| `grupo` | Grupos por actividad y semestre | `(id_actividad, grupo)` único; cupos, modalidad, estatus, fechas, instructor y ubicación. Sprint 3. |
| `horario` | Bloques de clase de un grupo | `id_grupo` con **cascade**; sin marcas de tiempo. Sprint 3. |

**Renombre:** los perfiles (`alumno`, `instructor`, `coordinador_departamento`, `departamento`,
`carrera`) son tablas **separadas** que comparten la llave con `USUARIO`. Un usuario solo tiene
perfil según su rol, y `User` lo expone con las relaciones `alumno()`, `instructor()` y `coordinacion()`.

### Tablas que faltan (Sprint 0, sprints 4-6)

| Tabla | Sprint | Estado |
|---|---|---|
| `inscripcion` | 4 | Implementada (HU-17) |
| `calificacion` | 5 | Implementada (HU-20) |
| `log_acceso` | 6 | Pendiente |

Vienen del paquete `Sprint0_Base_de_Datos.zip`. Las cinco tablas del Sprint 3
(`dia_semana`, `actividad_complementaria`, `actividad_carrera`, `grupo`, `horario`) ya están
implementadas y documentadas en [`sprints/sprint-03-actividades-y-grupos.md`](sprints/sprint-03-actividades-y-grupos.md).

> La migración de `inscripcion` (Sprint 4) usa una **columna generada de MySQL**, que exige MySQL 5.7
> o superior. Como el proyecto ya corre en **MySQL 8**, esa restricción del Sprint 0 quedó resuelta y
> la migración se puede copiar tal cual, sin adaptaciones.

## 4. Roles y permisos

Spatie es la **única fuente de verdad**: la columna `tipo_usuario` de `USUARIO` se deriva del rol
al crear la cuenta (`User::TIPO_POR_ROL`), no se elige a mano.

Permisos actuales (`database/seeders/RolesYPermisosSeeder.php`):

```
usuarios.ver    usuarios.crear    usuarios.editar    usuarios.eliminar
roles.ver       roles.editar
departamentos.ver  .crear  .editar  .eliminar      (Sprint 2)
carreras.ver       .crear  .editar  .eliminar      (Sprint 2)
ubicaciones.ver    .crear  .editar  .eliminar      (Sprint 2)
semestres.ver      .crear  .editar                 (Sprint 2; no existe .eliminar)
actividades.ver    .crear  .editar  .eliminar      (Sprint 3)
grupos.ver         .crear  .editar  .eliminar      (Sprint 3; los horarios van en el formulario del grupo)
inscripciones.ver  .crear  .baja                   (Sprint 4)
calificaciones.ver .editar                         (Sprint 5)
```

Asignación: `administrador` → todos (34) · `coordinador` → `usuarios.ver`, `ubicaciones.ver`,
`actividades.*`, `grupos.*`, `inscripciones.ver`/`inscripciones.baja` y `calificaciones.ver`
(recortados a su departamento) · `instructor` → `grupos.ver`, `calificaciones.ver` y
`calificaciones.editar` (solo sus grupos) · `alumno` → `actividades.ver` y `grupos.ver` (solo
lectura, acotados a su carrera) e `inscripciones.ver`/`inscripciones.crear`/`inscripciones.baja`
(HU-17; la baja solo aplica a su propia inscripción, lo valida la política). El alumno ve sus
calificaciones en el historial sin necesitar `calificaciones.ver`. Los permisos del Sprint 2 son
solo del administrador; se pueden dar a otro rol desde `/roles`.

Hay una pantalla (`/roles`) para modificar permisos por rol. Por eso el seeder usa
`Permission::firstOrCreate` y es **idempotente**: se puede volver a ejecutar sin borrar lo que se
ajustó a mano. **Cada sprint nuevo agrega aquí sus permisos.**

## 5. Reglas de negocio vigentes

1. **El rol se fija al crear la cuenta** y no se cambia después: los datos de.profile quedan ligados
   a inscripciones y grupos en sprints futuros.
2. **Alcance por departamento:** el coordinador solo opera sobre instructores de su departamento.
   Sin departamento asignado el middleware `departamento.asignado` le responde 403.
3. **Eliminar vs. desactivar:** si la cuenta ya tiene registros relacionados (inscripciones, grupos)
   la base de datos impedirá borrarla y el sistema sugiere desactivarla. **Desde el Sprint 3 en
   adelante, desactivar es lo recomendable.**
4. **Nadie puede eliminarse ni desactivarse a sí mismo**, ni el administrador (para no dejar el
   sistema sin admins). Se aplica en `UserPolicy` y en un `Gate::before` en `AppServiceProvider`.
5. **Cuentas inactivas:** la sesión se cierra automáticamente y no pueden entrar (`usuario.activo`).
6. **Registro público solo para alumnos.**
7. **Límite de intentos:** 5 por combinación correo + IP en el login.
8. **Duración del semestre:** de 3 a 5 meses **completos**. Mínimo: `fecha_fin ≥ inicio + 3 meses`;
   máximo: `fecha_fin < inicio + 6 meses` (es decir, se acepta hasta 5 meses y 29 días, igual que el
   proyecto anterior). Se valida en `GuardarSemestreRequest` y se avisa en vivo en el formulario.
9. **Ventana de inscripción:** de 5 a 10 días entre inicio y fin, y dentro del rango del semestre.
   Abre el día de inicio a la `hora_inicio_inscripciones` y cierra el día de fin a la
   `hora_fin_inscripciones` (`Semestre::inscripcionesAbiertas()`, que usará el Sprint 4).
10. **Un solo periodo activo a la vez**, y un solo semestre por año y periodo. No se puede activar un
    periodo cuya fecha de fin ya pasó.
11. **Cierre automático:** un periodo activo pasa a inactivo cuando su `fecha_fin` es anterior a hoy.
    Lo hace el comando `semestres:cerrar-vencidos` (programado a las 00:05) y, por si el programador
    no corre, también al entrar a las pantallas de semestres y al validar el formulario.
12. **Edición de semestres:** se edita el periodo activo; sin ninguno activo, solo el inactivo más
    reciente (para reactivarlo). Los semestres **no se eliminan**.
13. **Catálogos con registros relacionados:** un departamento con instructores o coordinador, o una
    carrera con alumnos, no se puede eliminar (mensaje explicativo).
14. **Actividades:** nombre único y de hasta 150 caracteres, créditos 1 o 2 y al menos una carrera.
    Nacen en el semestre activo y se pueden mover con «Asignar al semestre vigente» al editarlas. Un
    coordinador solo las crea y edita en su departamento (el formulario no puede imponer otro). No se
    elimina una actividad con grupos.
15. **Grupos:** nombre en mayúsculas y único por actividad; cupo mínimo ≥ 1 y máximo ≥ mínimo; fechas
    dentro del semestre; ubicación obligatoria salvo en modalidad virtual; instructor del departamento
    de la actividad (puede quedar sin instructor); al menos un horario con fin posterior al inicio. El
    grupo nace en el semestre activo y no cambia de semestre al editarse. Guardar el grupo y sus
    horarios es atómico.
16. **Conflictos de horario (HU-16):** solo cuentan los grupos del mismo semestre con estatus
    `abierta` o `cerrada`; el traslape es estricto (tocarse en el borde no es choque). Se revisan
    cuatro reglas: horarios internos, instructor, ubicación y cupo máximo contra la capacidad. Al
    editar se excluye el propio grupo. La misma regla vive en `ServicioConflictos` y la usan la
    validación al guardar y el aviso en vivo.
17. **Borrado de grupos:** no se elimina un grupo con `cupo_ocupado > 0`; sus horarios se van en
    cascada. El criterio 8 de HU-15 se **reescribió el 2026-10-20** para reflejar esa decisión de
    esquema (opción A): ver [`decisiones/borrado-de-grupos.md`](decisiones/borrado-de-grupos.md).
    Desde el Sprint 4 el borrado se bloquea si el grupo tiene filas en `inscripcion`
    (`Grupo::tieneInscripciones()` consulta la relación).
18. **Visibilidad del alumno:** ve las actividades `disponible` dirigidas a su carrera y los grupos de
    esas actividades (`Actividad::scopeVisiblesPara`, `Grupo::scopeVisiblesPara` y
    `GrupoPolicy@view`). Es solo consulta: no puede crear, editar ni eliminar actividades ni grupos.
19. **Inscripciones (HU-17):** solo el alumno se inscribe, y solo dentro de la ventana del periodo
    activo (`Semestre::inscripcionesAbiertas()`). El grupo debe ser del periodo vigente, estar
    `abierta`, pertenecer a una actividad `disponible` dirigida a la carrera del alumno y tener cupo.
    Se permite **una sola inscripción activa por alumno** (`inscrito` o `cursando`), garantizada por
    un índice único (columna generada en MySQL, índice parcial en SQLite). El alta descuenta el cupo
    de forma atómica y registra `fecha_inscripcion`; el grupo y su cupo no se capturan a mano.
    Tras confirmar el alta se encola un correo de confirmación (`InscripcionConfirmada`); si el
    envío falla, la inscripción se conserva y el error se registra en el log (RNF-06).
20. **Bajas de inscripción (HU-19):** el coordinador (o el administrador) da de baja inscripciones
    `inscrito` o `cursando` de los grupos de su departamento; el alumno también puede cancelar su
    propia inscripción activa. La baja es lógica: cambia el estatus a `dado_de_baja`, conserva la fila
    (historial) y libera un lugar del cupo en la misma transacción. Una inscripción ya inactiva no se
    vuelve a dar de baja. El alumno puede inscribirse de nuevo después de la baja.
21. **Calificaciones (HU-20):** el instructor del grupo registra un desempeño cualitativo
    (`excelente`, `bueno` o `malo`) y, opcionalmente, observaciones de hasta 255 caracteres. La
    calificación es única por inscripción (`id_inscripcion` único) y se puede modificar sin duplicar
    la fila. Al guardarla, la inscripción pasa a `aprobado` si el desempeño es `bueno` o `excelente`,
    y a `reprobado` si es `malo`; ambos cambios ocurren en la misma transacción. No se califica una
    inscripción `dado_de_baja`. El coordinador consulta las calificaciones de su departamento en modo
    lectura; el alumno ve la suya en el historial de `inscripciones/index`. El cálculo de
    `creditos_acumulados` queda para HU-21.

## 6. Decisiones técnicas

- **Laravel 13, no 8.** El cronograma dice Laravel 8, pero el proyecto es 13.37.0. Por eso las
  vistas son Blade + Tailwind 4 (**no Stisla**, que es de Laravel 8) y los middleware se registran
  en `bootstrap/app.php`. *Conviene corregir ese dato en la documentación del residente.*
- **Atributos en los modelos:** el proyecto usa los atributos nativos de PHP
  (`#[Table]`, `#[Fillable]`, `#[Hidden]`) en vez de `$table`/`$fillable`/`$hidden`.
- **Vistas del Sprint 2 copiadas del proyecto anterior:** las originales eran Stisla/Bootstrap 4
  con FontAwesome. Se reprodujeron con los mismos campos, columnas, botones, textos y flujos, pero en
  Tailwind 4 y componentes Blade (`x-tarjeta-formulario`, `x-fecha-hora`, `x-icono` con SVG en línea).
- **Zona horaria:** el cierre automático compara contra la fecha de la aplicación. Con
  `APP_TIMEZONE=UTC` (valor por defecto) un periodo se cierra a las 18:00 hora de Oaxaca del día
  anterior; conviene fijar `APP_TIMEZONE=America/Mexico_City` en `.env`.
- **Programador de tareas:** para que el cierre corra solo en producción hace falta un cron
  `* * * * * php artisan schedule:run`; en local, `php artisan schedule:work`.
- **Contraseña en columna `contrasena`:** el modelo sobrescribe `getAuthPassword()` y
  `getAuthPasswordName()`; el cast `hashed` se encarga de cifrar.
- **Sesiones en base de datos:** `SESSION_DRIVER=database`, por eso `sessions` está en la migración
  de usuarios.
- **Correo de confirmación (Sprint 4):** `App\Mail\InscripcionConfirmada` se encola con
  `Mail::to(...)->queue(...)` después de confirmar la inscripción. Con `QUEUE_CONNECTION=database`
  el envío ocurre en el worker; en pruebas se usa `MAIL_MAILER=array`. Si el encolado falla, el
  controlador registra el error con `Log::error` y la inscripción no se deshace (RNF-06).
- **MySQL 8 como motor objetivo** (migrado el 2026-10-18, ver §8). Durante el desarrollo anterior se
  usó SQLite, por eso el código evita funciones exclusivas de MySQL (columnas generadas, por ejemplo)
  y las pruebas siguen corriendo en SQLite en memoria.
- **Horarios dentro del formulario del grupo (Sprint 3):** no hay rutas anidadas
  `grupos/{grupo}/horarios`; el formulario manda `horarios[]` y `GrupoController` reemplaza el
  conjunto completo dentro de una transacción. `HorarioController` solo expone el endpoint JSON
  `POST grupos/conflictos`. Por eso no existen permisos `horarios.*` (el total es 34).
- **Baja de inscripciones (Sprint 4):** la baja es lógica (`estatus = 'dado_de_baja'`), no un
  `DELETE`; así se conserva el historial y el índice único de inscripción activa permite volver a
  inscribirse. `InscripcionController@baja` bloquea la fila, cambia el estatus y descuenta
  `cupo_ocupado` en una transacción. La autorización vive en `InscripcionPolicy@baja`: el permiso
  `inscripciones.baja` y, o bien la inscripción es del propio alumno, o bien el coordinador es del
  departamento de la actividad. La vista `inscripciones/gestion` lista las inscripciones con filtros;
  el alumno ve su inscripción activa (con el botón «Cancelar mi inscripción») y su historial en
  `inscripciones/index`.
- **Calificaciones (Sprint 5):** la tabla `calificacion` guarda el desempeño y las observaciones; el
  estatus `aprobado`/`reprobado` vive en `inscripcion` y lo actualiza `CalificacionController@update`
  dentro de una transacción con `updateOrCreate`. La autorización está en `InscripcionPolicy`
  (`verCalificacion` y `calificar`): el instructor solo califica los grupos que imparte, el
  coordinador consulta su departamento y el administrador pasa por `Gate::before`. El formulario
  reutiliza `x-tarjeta-formulario` y tarjetas de radio con `peer-checked`; la lista de alumnos se
  muestra en `grupos/show` solo a quien tiene `calificaciones.ver`.
- **Rejilla semanal del horario (Sprint 4):** el formulario de grupos captura el horario con una
  rejilla de bloques de una hora (07:00–20:00, lunes a sábado), como el proyecto anterior. El
  JavaScript traduce cada bloque marcado a un `horarios[i]` con `hora_fin = hora_inicio + 1 h`; si
  el grupo ya tenía un horario que no cae en bloques exactos, la rejilla lo ajusta a los bloques que
  cubre y lo avisa antes de guardar. El servidor sigue aceptando cualquier intervalo válido: la
  rejilla es la interfaz, no una regla de negocio nueva.
- **Una sola fuente para los conflictos (Sprint 3):** `app/Services/ServicioConflictos.php` concentra
  las cuatro reglas; lo llaman `GuardarGrupoRequest` al guardar y `HorarioController` para el aviso en
  vivo. El aviso es una ayuda visual: la garantía es la validación del servidor.

## 7. Cuentas de prueba

Están en `database/seeders/UsuariosPruebaSeeder.php` y **solo se siembran fuera de producción**
(la condición está en `DatabaseSeeder`). Contraseña: `Actividades2026`.

| Correo | Rol | Perfil |
|---|---|---|
| admin@gmail.com | administrador | — |
| coordinador@gmail.com | coordinador | Sistemas y Computación |
| instructor@gmail.com | instructor | Sistemas y Computación, especialidad Programación |
| alumno@gmail.com | alumno | ISC, semestre 5, núm. de control 20230001 |

## 8. Entorno local

- PHP 8.5.0 y Composer 2.8.12 vía **Herd Lite**, en `C:\Users\em556\.config\herd-lite\bin`
  (no están en el PATH de la terminal).
- Node 24.11.1 y npm 11.21.0 disponibles.
- Base de datos: **MySQL 8.0.46** en `127.0.0.1:3306`, base `ActividadesComplementarias2026`, con `utf8mb4` / `utf8mb4_unicode_ci` (necesario para la columna `año`). Se reconstruye con `php artisan migrate --seed`. La base `actividades_complementarias` es el **sistema anterior** (Laravel 8: 109 usuarios, 101 alumnos y las tablas `actividad_complementaria`, `grupo`, `horario`, `inscripcion`): **no tocarla nunca.**
- **Las pruebas siguen en SQLite en memoria**: `phpunit.xml` fija `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:`, así que la suite no toca MySQL. Para comprobar MySQL hay que levantar la aplicación y recorrer las pantallas.
- **Zona horaria:** `APP_TIMEZONE=America/Mexico_City` en `.env` y `.env.example`. Sin esto el cierre automático de semestres se dispararía 6 horas antes de medianoche local.
- MySQL local: hay dos servicios (`MySQL80` parado y `MySQL802` corriendo) apuntando al mismo `datadir`; si se arranca `MySQL80` fallará por conflicto.
- No está instalado el CLI de GitHub (`gh`); la autenticación de Git es con Git Credential Manager.
