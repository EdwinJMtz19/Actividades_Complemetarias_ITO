# Sprint 3 · Actividades, grupos y horarios

**Estado:** entregado · **Fecha:** 2026-10-20 · **Puntos:** 18 · **Horas estimadas:** 48
**Historias:** HU-14, HU-15 y HU-16 · **Requisitos:** RF-07 y RF-08

Documentos base:
[Historias de usuario y CRC](sprint-03-historias-de-usuario-y-crc.md) ·
[Plan del Sprint 3](sprint-03-plan.md) ·
[Decisión abierta: borrado de grupos](../decisiones/borrado-de-grupos.md)

---

## 1. Qué se entregó

| Historia | Resultado |
|---|---|
| **HU-14 · Gestionar actividades complementarias** | CRUD completo con buscador, filtros por semestre y departamento (este último solo para el administrador), carreras asociadas, créditos 1 o 2, interruptor «disponible», alta en el semestre vigente y opción «Asignar al semestre vigente» al editar. |
| **HU-15 · Gestionar grupos y horarios** | CRUD de grupos con nombre único por actividad (en mayúsculas), cupos, modalidad, ubicación, instructor, estatus, fechas dentro del semestre y varios horarios capturados en el mismo formulario. Listado y detalle con alcance por rol. |
| **HU-16 · Verificar conflictos de horario** | `ServicioConflictos` con las cuatro reglas, usado tanto al guardar (validación del servidor) como en el aviso en vivo por JSON mientras se captura. |

---

## 2. Modelo de datos (5 tablas nuevas)

| Tabla | Contenido | Notas |
|---|---|---|
| `dia_semana` | Lunes a sábado | Con acentos (`miércoles`, `sábado`); `utf8mb4`. |
| `actividad_complementaria` | Catálogo de actividades | `nombre` único, `creditos` 1 o 2, `disponible`, FK a `departamento` y `semestre` (nullable). |
| `actividad_carrera` | Many-to-many actividad ↔ carrera | PK compuesta; `cascade` en ambos lados. |
| `grupo` | Grupos por actividad y semestre | `(id_actividad, grupo)` único; cupos, modalidad, estatus, fechas; FKs a instructor y ubicación. |
| `horario` | Bloques de clase de un grupo | `id_grupo` con **cascade** (decisión deliberada, ver la decisión pendiente); sin marcas de tiempo. |

Migraciones: `2026_10_19_100000` a `2026_10_19_100400`. **Total del proyecto: 16 migraciones.**

---

## 3. Decisiones del plan, ya aplicadas

| # | Decisión | Cómo quedó |
|---|---|---|
| D1 | Horarios dentro del formulario del grupo | Arreglo `horarios[]` guardado en la **misma transacción**; `HorarioController` solo verifica conflictos. **29 permisos**, no 33. |
| D2 | Contra qué grupos se compara un conflicto | Solo el **mismo semestre** y estatus `abierta` o `cerrada`; `cancelada`, `finalizada` y semestres pasados no ocupan a nadie. |
| D3 | Semestre de la actividad y del grupo | La actividad nace en el activo y puede moverse con la casilla de edición; el grupo toma el activo al crearse y lo conserva al editarse. |
| D4 | Créditos | Solo **1 o 2** (`Actividad::CREDITOS_PERMITIDOS`). |
| D5 | Instructor «de su departamento» | Se valida contra el **departamento de la actividad**, no contra el del usuario. |
| D6 | Pantalla del instructor | Un solo `/grupos` con alcance por rol: el instructor ve solo los suyos y no tiene acciones de escritura. |
| D7 | Grupos al cerrar el semestre | Sin cambios: siguen `abierta`. Pasarlos a `finalizada` queda para el Sprint 5. |
| D8 | Nombre del grupo | Se guarda en **mayúsculas**; unicidad `(actividad, grupo)` también en la base. |
| C1-C7 | Correcciones de redacción y alcance | Aplicadas al implementar (nivel opcional, filtro de departamento solo para el administrador, etc.). |

---

## 4. Rutas y permisos

| Ruta | Permiso |
|---|---|
| `actividades.index` / `show` no existe | `actividades.ver` |
| `actividades.create`, `store` | `actividades.crear` |
| `actividades.edit`, `update` | `actividades.editar` |
| `actividades.destroy` | `actividades.eliminar` |
| `grupos.index`, `show` | `grupos.ver` |
| `grupos.create`, `store` | `grupos.crear` |
| `grupos.edit`, `update` | `grupos.editar` |
| `grupos.destroy` | `grupos.eliminar` |
| `POST grupos/conflictos` (JSON) | `grupos.crear` **o** `grupos.editar` |

Reparto por rol (`RolesYPermisosSeeder`): administrador **29**, coordinador con `actividades.*` y
`grupos.*` (recortados a su departamento), instructor solo `grupos.ver`, alumno ninguno.

---

## 5. Reglas de negocio implementadas

### Actividades

1. Nombre obligatorio, único y de hasta 150 caracteres.
2. Créditos 1 o 2; al menos una carrera; descripción, requisitos y nivel son opcionales.
3. El coordinador trabaja solo en su departamento: el `id_departamento` que mande el formulario se
   sustituye por el suyo (`GuardarActividadRequest::prepareForValidation`).
4. Al crear, la actividad se asigna al semestre activo (o queda sin semestre si no hay ninguno).
5. Al editar se puede marcar «Asignar al semestre vigente».
6. No se elimina una actividad con grupos.

### Grupos y horarios

1. Nombre en mayúsculas, hasta 10 caracteres, único dentro de la actividad.
2. `cupo_minimo ≥ 1` y `cupo_maximo ≥ cupo_minimo`.
3. Las fechas del grupo caen dentro del semestre.
4. La ubicación es obligatoria en presencial e híbrida; en virtual se guarda `null`.
5. El instructor debe pertenecer al departamento de la actividad (puede quedar sin instructor).
6. Al menos un horario; cada bloque con día, hora de inicio y hora de fin posterior.
7. El grupo nace en el semestre activo y no cambia de semestre al editarse.
8. Crear o actualizar el grupo y reemplazar sus horarios es **atómico**.
9. No se elimina un grupo con `cupo_ocupado > 0`; sus horarios se van en cascada.
   **El criterio 8 de HU-15 se reescribió el 2026-10-20** para reflejar esta decisión de esquema:
   [`docs/decisiones/borrado-de-grupos.md`](../decisiones/borrado-de-grupos.md).

### Conflictos (HU-16)

Solo cuentan los grupos del **mismo semestre** con estatus `abierta` o `cerrada`. El traslape es
estricto: dos bloques que solo se tocan en el borde (`09:00–10:00` y `10:00–11:00`) no chocan.

| Regla | Campo que se marca |
|---|---|
| Dos horarios del propio grupo se traslapan | `horarios.{n}.hora_inicio` (`tipo: grupo`) |
| El instructor ya tiene otro grupo | `id_instructor` (`tipo: instructor`) |
| El espacio ya está ocupado | `id_ubicacion` (`tipo: ubicacion`) |
| El cupo máximo supera la capacidad del espacio | `cupo_maximo` (`tipo: cupo`) |

Al editar se excluye el propio grupo. El aviso en vivo es solo una ayuda: la validación que decide
es la de `GuardarGrupoRequest`, con el mismo servicio.

---

## 6. Pruebas

**Total del proyecto: 204 pruebas en verde** (795 aserciones). El Sprint 3 agregó **65 casos**:

| Archivo | Casos | Qué cubre |
|---|---|---|
| `tests/Feature/ActividadesCrudTest.php` | 13 | Listado, filtros, paginación, alta, validaciones, edición, semestre, borrado con grupos, alcance del coordinador |
| `tests/Feature/GruposCrudTest.php` | 15 | Alta con horarios, mayúsculas, cupos, fechas, ubicación/virtual, instructor, unicidad, semestre, traslapes, detalle, edición atómica, borrado |
| `tests/Feature/AccesoActividadesYGruposTest.php` | 6 | Invitados, alumno, administrador, coordinador, instructor y enlaces del panel |
| `tests/Feature/ConflictosEnVivoTest.php` | 7 | Endpoint JSON: sin conflictos, instructor, espacio, cupo, exclusión propia, aviso sin semestre, formato y permisos |
| `tests/Feature/ServicioConflictosTest.php` | 24 | Reglas puras del servicio, incluidos los bordes de traslape (17 `it` con datasets) |

Además se corrigió `TOTAL_PERMISOS` en `tests/Feature/RolesPermisosTest.php` (21 → **29**) y se
amplió `tests/Support/Escenarios.php` con `actividad()` y `grupo()`.

---

## 7. Verificación

| Comprobación | Resultado |
|---|---|
| `php artisan test` | **204 pruebas, 795 aserciones, todas en verde** |
| `vendor\bin\pint --test` | Sin observaciones |
| `php artisan migrate:fresh --seed` en **MySQL 8** | 16 migraciones y 5 seeders sin errores |
| Datos sembrados | 29 permisos, 6 días, 5 actividades; sin semestre activo (se crea desde la pantalla) |
| `npm run build` | Compilado correctamente (Tailwind 4 + Vite) |

---

## 8. Archivos

**Nuevos**

```
app/Http/Controllers/ActividadController.php
app/Http/Controllers/GrupoController.php
app/Http/Controllers/HorarioController.php
app/Http/Requests/GuardarActividadRequest.php
app/Http/Requests/GuardarGrupoRequest.php
app/Http/Requests/VerificarHorariosRequest.php
app/Models/Actividad.php
app/Models/DiaSemana.php
app/Models/Grupo.php
app/Models/Horario.php
app/Policies/ActividadPolicy.php
app/Policies/GrupoPolicy.php
app/Services/ServicioConflictos.php
database/migrations/2026_10_19_100000_create_dia_semana_table.php
database/migrations/2026_10_19_100100_create_actividad_complementaria_table.php
database/migrations/2026_10_19_100200_create_actividad_carrera_table.php
database/migrations/2026_10_19_100300_create_grupo_table.php
database/migrations/2026_10_19_100400_create_horario_table.php
database/seeders/ActividadesBaseSeeder.php
database/seeders/DiasSemanaSeeder.php
resources/js/grupo-horarios.js
resources/views/actividades/ (index, create, edit, _form)
resources/views/grupos/ (index, create, edit, show, _form, _horario)
tests/Feature/ActividadesCrudTest.php
tests/Feature/GruposCrudTest.php
tests/Feature/AccesoActividadesYGruposTest.php
tests/Feature/ConflictosEnVivoTest.php
tests/Feature/ServicioConflictosTest.php
```

**Modificados**

```
routes/web.php
database/seeders/DatabaseSeeder.php
database/seeders/RolesYPermisosSeeder.php
resources/js/app.js
resources/views/components/barra-catalogo.blade.php
resources/views/components/layouts/app.blade.php
resources/views/panel.blade.php
tests/Feature/RolesPermisosTest.php
tests/Support/Escenarios.php
```

---

## 9. Pendientes y deuda conocida

1. **Borrado de grupos con horarios:** decisión **resuelta** (opción A: corregir la documentación)
   en [`docs/decisiones/borrado-de-grupos.md`](../decisiones/borrado-de-grupos.md). El código
   permite borrar un grupo y arrastra sus horarios en cascada; `cupo_ocupado` se sustituirá por la
   relación `inscripciones()` en el Sprint 4.
2. **Grupos al cerrar el semestre:** quedan `abierta` (D7). Pasarlos a `finalizada` corresponde al
   Sprint 5.
3. **Sin pantalla `show` de actividades:** el listado y la edición bastan para el alcance; el
   recurso se declaró con `except('show')`.
4. **El aviso en vivo no bloquea el guardado:** es una ayuda visual; la garantía es la validación
   del servidor.
5. **Pruebas en SQLite, operación en MySQL:** la suite usa SQLite en memoria y la verificación con
   MySQL se hizo con `migrate:fresh --seed` y recorrido manual de las pantallas.
