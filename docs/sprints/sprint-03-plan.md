# Plan del Sprint 3 · Actividades y grupos

**Estado:** **ejecutado** · entregado el 2026-10-20 · ver
[`sprint-03-actividades-y-grupos.md`](sprint-03-actividades-y-grupos.md)
**Fecha del plan:** 2026-10-18
**Base:** [`sprint-03-historias-de-usuario-y-crc.md`](sprints/sprint-03-historias-de-usuario-y-crc.md)
(historias, CRC, esquema y anexo de implementación) y el cronograma.

Este plan **no reemplaza** ese documento: lo contrasta con el código real del Sprint 1 y 2 y con el
sistema anterior, señala lo que conviene ajustar y ordena el trabajo. Las decisiones D1–D8 y las
correcciones C1–C7 se aplicaron tal como se recomienda aquí. La decisión sobre el borrado de grupos
quedó **resuelta el 2026-10-20** (opción A): [`decisiones/borrado-de-grupos.md`](../decisiones/borrado-de-grupos.md).

---

## 1. Alcance

| Historia | Qué entrega | Puntos | Horas |
|---|---|---|---|
| HU-14 | CRUD de actividades complementarias, con carreras y semestre | 5 | 14 |
| HU-15 | CRUD de grupos con horarios, instructor, ubicación y cupos | 8 | 21 |
| HU-16 | Verificación de conflictos de horario (servidor y aviso en vivo) | 5 | 13 |
| | **Total** | **18** | **48** |

Cinco tablas nuevas: `dia_semana`, `actividad_complementaria`, `actividad_carrera`, `grupo`, `horario`.
Cubre **RF-07 y RF-08**. El cronograma coincide con este alcance (CRUD de actividades y grupos,
asignación de instructores y conflictos en tiempo real con AJAX).

---

## 2. Qué cambiar en el documento antes de empezar

### 2.1 Correcciones sin discusión

| # | Dónde | Problema | Corrección |
|---|---|---|---|
| C1 | Sprint Backlog, HU-14 y HU-15 | Texto roto: «relaciónMany-toMany» y «limited al departamento» | «relación many-to-many» y «limitada al departamento» |
| C2 | Sprint Backlog, HU-15 y HU-16 | `horarios.*` aparece en las dos filas | Dejarlo en una sola (o ninguna: ver D1) |
| C3 | Anexo B §10 | Nombre de archivo `sprint-03-catalogos...` | `sprint-03-actividades-y-grupos.md` |
| C4 | Anexo B §1 | La frase sobre «el segundo bloque de fechas» no se entiende | Decir solo que el orden de las migraciones sigue las llaves foráneas |
| C5 | Anexo B §9 | «los apoyo de» | «los apoyos de» |
| C6 | HU-14 | El esquema tiene `nivel_actividad` y el CRC lo menciona, pero ningún criterio lo pide | Añadir un criterio: nivel opcional, hasta 50 caracteres |
| C7 | HU-14 criterio 1 | Pide filtro por departamento, pero un coordinador solo ve el suyo | Mostrar ese filtro solo al administrador |

### 2.2 Decisiones que necesito de ti

Cada una trae mi recomendación. Con tu visto bueno actualizo el documento del sprint.

| # | Tema | El problema | Mi recomendación |
|---|---|---|---|
| **D1** | **Cómo se capturan los horarios** | El documento propone a la vez un `HorarioController` con alta, edición y baja anidado en `grupos/{grupo}/horarios`, y horarios capturados «en bloques» dentro del formulario del grupo. Son dos diseños: al **crear** un grupo todavía no existe, así que no se le puede colgar un horario por ruta anidada. | Un solo diseño: los horarios viajan **dentro del formulario del grupo** (arreglo `horarios[]`) y se guardan en la **misma transacción** que el grupo. `HorarioController` se reduce a un endpoint JSON que verifica conflictos. Consecuencia: sobran los 4 permisos `horarios.*` (se usa `grupos.crear/editar`), el total queda en **29** permisos en vez de 33. |
| **D2** | **Contra qué grupos se compara un conflicto** | El documento no lo dice. Sin acotar, el instructor «chocaría» con grupos de semestres cerrados o cancelados. El sistema anterior ya acotaba: solo el **semestre activo** y estatus **abierta o cerrada**. | Conservar esa regla. Los grupos `cancelada` y `finalizada` no generan conflicto. No se comparan los rangos de fechas del grupo (queda como mejora). |
| **D3** | **Semestre de la actividad y del grupo** | `actividad.id_semestre` puede ser nulo y `grupo.id_semestre` es obligatorio; ¿cuál manda? Además, al abrir un semestre nuevo las actividades quedan atadas al anterior (el sistema viejo las fijaba al crear y nunca las movía), y el alumno solo vería las del semestre activo. | El grupo toma el **semestre activo** al crearse (y se exige que haya uno, como antes). La actividad se asigna al activo al crearse y en la edición se ofrece **«Asignar al semestre vigente»**, para no recrear el catálogo cada semestre. |
| **D4** | **Rango de los créditos** | El documento dice solo «créditos obligatorios». El sistema anterior validaba `in:1,2`. Los créditos alimentan el Sprint 5. | Aceptar **1 o 2**, como antes. Si el reglamento permite otros valores, dímelo. |
| **D5** | **Instructor «de su departamento»** | El CRC valida que sea «del departamento del coordinador», pero el administrador no tiene departamento. | Validar contra el **departamento de la actividad**: funciona igual para administrador y coordinador. |
| **D6** | **Pantalla del instructor** | El documento pide una pantalla de solo lectura aparte con el permiso `grupos.ver`, que es el mismo del coordinador. | Un **solo listado `/grupos`** con alcance por rol (instructor: los suyos; coordinador: su departamento; administrador: todos). El instructor no ve botones porque no tiene `crear/editar/eliminar`. Menos pantallas y sin riesgo de que un instructor vea grupos ajenos. |
| **D7** | **Qué pasa con los grupos al cerrarse el semestre** | `cerrarVencidos()` solo cambia el semestre; los grupos seguirían `abierta`. | Dejarlo para el **Sprint 5** (calificaciones), donde tiene sentido pasarlos a `finalizada`. Basta con anotarlo. |
| **D8** | **Nombre del grupo** | «Único por actividad» y el sistema anterior lo guardaba en mayúsculas. | Guardar en **mayúsculas** y exigir unicidad `(actividad, grupo)` también en la base de datos. |

---

## 3. Con qué se cuenta (ya construido)

| Pieza | Dónde | Para qué sirve en este sprint |
|---|---|---|
| `User::ambitoDepartamento()` | `User.php:106` | Recortar actividades y grupos al departamento del coordinador (`null` = administrador) |
| `Semestre::activo()` y `fecha_inicio/fecha_fin` | `Semestre.php` | Semestre vigente y validación de fechas del grupo |
| `ubicacion.capacidad` | tabla `ubicacion` | Regla de aforo (cupo máximo ≤ capacidad) |
| `instructor.id_departamento`, PK `id_instructor` = id del usuario | `Instructor.php` | FK de `grupo.id_instructor` y filtro por departamento |
| Componentes `x-boton`, `x-accion`, `x-barra-catalogo`, `x-tarjeta-formulario`, `x-campo`, `x-seleccion` | `resources/views/components` | Las vistas nuevas no necesitan botones ni tablas a mano |
| `Route::resource(...)->middlewareFor(...)` | `routes/web.php` | Un permiso por acción, igual que los catálogos |
| `Escenarios` y `TOTAL_PERMISOS` | `tests/` | Ampliar con `actividad()`, `grupo()`, `horario()` y subir el total de permisos |

Lo que **no** hay y hay que crear: modelos, `ServicioConflictos`, JavaScript del aviso en vivo
(en `resources/js/app.js`, sin librerías) y la vista de grupos.

---

## 4. Orden de trabajo propuesto

Cada fase termina con `php artisan test` y `vendor\bin\pint --test` en verde.
Las horas reparten las 48 del Sprint Backlog; hay que confirmarlas con el equipo.

| Fase | Qué se hace | Horas | Historia |
|---|---|---|---|
| **1. Datos y permisos** | 5 migraciones; modelos `DiaSemana`, `Actividad`, `Grupo`, `Horario` con relaciones; permisos y asignación por rol; seeder `ActividadesBaseSeeder` (6 días, actividades de ejemplo) | 6 | HU-14/15 |
| **2. `ServicioConflictos`** | Las cuatro reglas como lógica pura, **con pruebas primero** (traslape estricto, mismo día, exclusión del propio grupo, aforo). Se hace antes que los grupos porque el formulario depende de él | 8 | HU-16 |
| **3. Actividades** | Controlador, request, vistas (`index` con buscador y filtros, `create`, `edit`, `_form` con carreras), interruptor «disponible», bloqueo al borrar con grupos, alcance por departamento | 10 | HU-14 |
| **4. Grupos y horarios** | Controlador, request, vistas con bloques de horario que se agregan y quitan, instructor del departamento de la actividad, cupos, estatus y fechas dentro del semestre, `show` del grupo, listado por rol | 16 | HU-15 |
| **5. Aviso en vivo** | Endpoint JSON de verificación y JavaScript en `app.js` que consulta mientras se captura; el servidor vuelve a validar al guardar | 5 | HU-16 |
| **6. Cierre** | Revisión visual, pruebas de acceso por rol, documentación del sprint, `contexto.md`, `requisitos.md` (RF-07 y RF-08), bitácora | 3 | todas |
| | **Total** | **48** | |

### Rutas previstas

| Ruta | Permiso |
|---|---|
| `resource actividades` (sin `show`) | `actividades.ver / crear / editar / eliminar` |
| `resource grupos` (con `show`) | `grupos.ver / crear / editar / eliminar` |
| `POST grupos/conflictos` → JSON | `grupos.crear` o `grupos.editar` |

Todas dentro del grupo `auth` + `usuario.activo` + `departamento.asignado`.

### Permisos por rol (con D1)

| Rol | Permisos |
|---|---|
| `administrador` | Todos (29) |
| `coordinador` | `actividades.*` y `grupos.*`, recortados a su departamento; conserva `usuarios.ver` |
| `instructor` | `grupos.ver` (solo los suyos) |
| `alumno` | Ninguno |

---

## 5. Pruebas que no pueden faltar

Además de la lista del Anexo B:

- Traslape estricto: `08:30–09:00` y `09:00–09:30` **no** chocan; `08:30–09:30` y `09:00–10:00` **sí**.
- Un grupo `cancelada` o `finalizada`, o de otro semestre, **no** genera conflicto (D2).
- Modalidad virtual: no exige ubicación ni compara aforo, pero el instructor sí se valida.
- Al editar, el grupo no choca consigo mismo, ni sus horarios entre sí por ser los suyos de antes.
- Un coordinador **no puede** crear ni editar actividades o grupos de otro departamento, aunque
  manipule el formulario (el sistema anterior lo permitía al editar).
- El instructor ve solo sus grupos y recibe 403 al intentar crear, editar o borrar.
- Guardar el grupo y sus horarios es **atómico**: si un horario falla, no queda el grupo a medias.
- Nombre de grupo repetido en la misma actividad se rechaza, aunque difiera en mayúsculas.

---

## 6. Riesgos

| Riesgo | Cómo se mitiga |
|---|---|
| **Error de borde en el traslape** (`<` contra `≤`) | Regla única `inicioA < finB y inicioB < finA` en un solo lugar, con pruebas de borde primero |
| **Las pruebas corren en SQLite y producción en MySQL 8** (`enum`, `time`, llaves foráneas con `cascade` y `set null`) | Recorrer a mano las pantallas con la base MySQL al cerrar cada fase y correr `migrate:fresh --seed` allí |
| **Dos coordinadores guardando a la vez** el mismo instructor u horario | Verificar y guardar dentro de una transacción; el aviso en vivo es una ayuda, no la garantía |
| **Aviso en vivo que miente** (respuesta lenta que llega tarde) | Descartar respuestas antiguas en el JavaScript y no bloquear el guardado por el aviso |
| **Ningún grupo del sistema anterior tiene instructor**: no hay datos de referencia | Sembrar ejemplos con instructor en el seeder de desarrollo para poder probar la asignación |
| **Se mezclan semestres** (D3) | Decidir D3 antes de la fase 1, porque cambia las migraciones y las validaciones |

---

## 7. Definición de hecho

- Las tres historias cumplen todos sus criterios de aceptación (con los ajustes aprobados en §2).
- `php artisan test` y `vendor\bin\pint --test` en verde; `TOTAL_PERMISOS` actualizado.
- `php artisan migrate:fresh --seed` sin errores en MySQL 8.
- Pantallas revisadas en navegador con los cuatro roles de prueba.
- Documentados: `sprints/sprint-03-actividades-y-grupos.md`, `contexto.md`, `requisitos.md`
  (RF-07 y RF-08 a *Cumplido*) y `bitacora.md`.
