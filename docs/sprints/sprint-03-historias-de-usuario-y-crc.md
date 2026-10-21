# Historias de usuario y tarjetas CRC · Sprint 3

**Sección 4.7** del documento de requerimientos. Replica la estructura de las secciones 4.5
(Sprint 1) y 4.6 (Sprint 2). El Product Backlog completo está en la sección 4.4 (Tabla 8).

> **Estado del documento:** este archivo nació como la planificación del sprint y **se actualizó
> al entregarlo** (2026-10-20) con las decisiones D1–D8 y las correcciones C1–C7 del
> [plan](sprint-03-plan.md). Lo realmente construido está en
> [`sprint-03-actividades-y-grupos.md`](sprint-03-actividades-y-grupos.md); si algo difiere, manda ese.

Esquema de referencia: migraciones del paquete `Sprint0_Base_de_Datos.zip` y los datos reales del
sistema anterior (base `actividades_complementarias` en el MySQL local).

---

## 4.7 Iteración 3: Sprint 3 - Actividades y grupos (semanas 7 y 8)

### 4.7.1 Planificación del Sprint

**Sprint Goal:** contar con las actividades del semestre vigente y sus grupos, con horario,
instructor y ubicación ya verificados, para que el Sprint 4 pueda abrir inscripciones sin
conflictos de agenda.

Se seleccionaron las historias HU-14 a HU-16, con un total de **18 puntos de historia**. El Sprint 4
se apoya en este: las inscripciones se hacen contra `grupo`, y un horario mal capturado produce
conflictos que en el sistema anterior se descubrían cuando el alumno ya estaba inscrito.

Este sprint crea **cinco tablas nuevas**: `dia_semana`, `actividad_complementaria`,
`actividad_carrera`, `grupo` y `horario`.

Tabla 11. Sprint Backlog del Sprint 3

| HU | Tareas | Responsable | Horas |
|---|---|---|---|
| HU-14 | Migraciones `dia_semana`, `actividad_complementaria` y `actividad_carrera`; modelos `DiaSemana` y `Actividad` con su relación many-to-many con `Carrera`; seeder de días y actividades de ejemplo; CRUD con buscador y filtros por semestre y departamento; permisos `actividades.*`. | Martínez Rodríguez Edwin Jovanny | 14 |
| HU-15 | Migraciones `grupo` y `horario`; modelos `Grupo` y `Horario`; asignación de instructor limitada al departamento de la actividad; cupos, modalidad y estatus; horarios capturados dentro del formulario del grupo; listado único `/grupos` con alcance por rol (el instructor solo ve los suyos); permisos `grupos.*`. | Martínez Rodríguez Edwin Jovanny | 21 |
| HU-16 | `ServicioConflictos` con las cuatro reglas (instructor, ubicación, traslape del propio grupo y cupo contra capacidad); validación en el servidor al guardar y endpoint JSON para el aviso en vivo. | Martínez Rodríguez Edwin Jovanny | 13 |

> Horas estimadas con la misma relación del Sprint 2 (≈2.7 h por punto de historia); confirmar el
> reparto con el equipo antes de la entrega.

### 4.7.2 Historias de usuario

| HU-14 · Gestionar actividades complementarias | |
|---|---|
| Como | Administrador o Coordinador |
| Quiero | registrar, consultar, editar y eliminar las actividades complementarias, dirigiéndolas a las carreras que correspondan |
| Para | tener el catálogo de lo que se imparte en el semestre y poder construir las actividades que después se abrirán en grupos |
| Prioridad / Estimación | Alta · 5 puntos de historia |
| Criterios de aceptación | 1. El listado es paginado de 10 en 10, con buscador por nombre y filtros por semestre y, solo para el administrador, por departamento.<br>2. El nombre es obligatorio, único y de máximo 150 caracteres. La descripción, los requisitos y el nivel (hasta 50 caracteres) son opcionales; los créditos son obligatorios y valen 1 o 2.<br>3. Cada actividad pertenece a un departamento y se asocia al semestre vigente; también puede quedar sin semestre asignado.<br>4. Se eligen una o más carreras a las que va dirigida, sin repetir la misma carrera.<br>5. El interruptor «disponible» permite retirarla de la oferta sin borrarla.<br>6. Un coordinador solo ve y administra las actividades de su departamento; el administrador ve todas.<br>7. No se elimina una actividad que ya tenga grupos; se avisa cuántos tiene.<br>8. Los días de la semana están catalogados (lunes a sábado) y se ofrecen como opciones al capturar el horario de un grupo. |

| HU-15 · Gestionar grupos y horarios | |
|---|---|
| Como | Coordinador |
| Quiero | crear los grupos de cada actividad con su instructor, ubicación, cupos y horarios |
| Para | definir qué se imparte, quién lo imparte, dónde y cuándo, antes de abrir las inscripciones |
| Prioridad / Estimación | Alta · 8 puntos de historia |
| Criterios de aceptación | 1. El grupo pertenece a una actividad del semestre vigente y se identifica con un nombre de hasta 10 caracteres, único dentro de la misma actividad.<br>2. La modalidad es presencial, virtual o híbrida; la ubicación es obligatoria solo en presencial e híbrido, y su aforo debe ser suficiente para el cupo máximo (ver HU-16).<br>3. El cupo mínimo y el máximo son obligatorios, con el mínimo no mayor al máximo. El cupo ocupado se lleva por las inscripciones del Sprint 4 y no se captura a mano.<br>4. El estatus va de abierta, cerrada, cancelada o finalizada, y las fechas del grupo caen dentro del semestre del grupo (el vigente al crearlo).<br>5. El instructor se asigna de entre los del departamento de la actividad; un grupo puede quedar temporalmente sin instructor asignado.<br>6. Un grupo admite varios horarios, cada uno con día y hora de inicio y fin, y la hora de fin siempre posterior a la de inicio.<br>7. Un instructor solo puede consultar los grupos que tiene asignados; no puede crearlos ni editarlos.<br>8. No se elimina un grupo que ya tenga inscripciones. Mientras la tabla de inscripciones no exista (Sprint 3), el grupo se elimina junto con sus horarios (`cascade`); la verificación definitiva se activa en el Sprint 4 con la relación `inscripciones`. *(Criterio reescrito el 2026-10-20; ver [decisión](../decisiones/borrado-de-grupos.md).)* |


> La sección 4.4 asigna **8 puntos de historia** a esta historia, que es la estimación que se conserva aquí: los criterios cubren los grupos y sus horarios.

| HU-16 · Verificar conflictos de horario | |
|---|---|
| Como | Coordinador |
| Quiero | que el sistema me avise cuando un horario choca con otro ya capturado |
| Para | no ofrecer dos grupos que se pisen en el mismo espacio, con el mismo instructor, o que excedan el aforo del espacio |
| Prioridad / Estimación | Alta · 5 puntos de historia |
| Criterios de aceptación | 1. Se detecta que el **instructor** ya tiene otro grupo el mismo día con horas traslapadas.<br>2. Se detecta que la **ubicación** ya está ocupada el mismo día con horas traslapadas.<br>3. Se detecta que el **mismo grupo** tiene dos horarios que se traslapan el mismo día.<br>4. Se detecta que el **cupo máximo del grupo supera la capacidad de la ubicación**; en modalidad virtual no aplica esta regla.<br>5. Dos horarios que solo se tocan **no son conflicto**: de 08:30 a 09:00 y de 09:00 a 09:30 es válido. El traslape se compara en tiempo, no por número de horas.<br>6. Al editar un grupo, la validación **excluye sus propios horarios**, para que no se reporte a sí mismo.<br>7. El aviso aparece mientras se captura el horario, sin recargar la página, y además el servidor rechaza el guardado aunque el aviso se haya saltado.<br>8. El mensaje explica qué choca y con qué: por ejemplo, «El instructor ya tiene el grupo B de Programación Web el lunes de 10:00 a 12:00». |

### 4.7.3 Tarjetas CRC

| Clase: DiaSemana | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar el nombre del día.<br>• Ofrecerse como catálogo para los horarios, en orden de la semana. | — |

| Clase: Actividad | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar nombre, descripción, requisitos, nivel, créditos y si está disponible.<br>• Declarar el departamento que la ofrece.<br>• Declarar el semestre al que pertenece.<br>• Declarar los grupos que se imparten a partir de ella.<br>• Declarar las carreras a las que está dirigida.<br>• Saber si ya tiene grupos y cuántos. | • Departamento.<br>• Semestre.<br>• Carrera (relación belongsToMany mediante la tabla pivote `actividad_carrera`).<br>• Grupo. |

| Clase: Grupo | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar su nombre, modalidad, cupos, estatus y fechas.<br>• Declarar la actividad y el semestre a los que pertenece.<br>• Declarar el instructor que lo imparte y la ubicación donde se da.<br>• Declarar sus horarios.<br>• Saber si su cupo máximo cabe en la capacidad de su ubicación.<br>• Saber si ya tiene inscripciones. | • Actividad.<br>• Semestre.<br>• Instructor.<br>• Ubicacion.<br>• Horario.<br>• Inscripcion (Sprint 4). |

| Clase: Horario | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar el día y la franja horaria de un grupo.<br>• Saber si su franja se traslapa con otra del mismo día.<br>• Describir su día y su hora en texto para los mensajes de error. | • DiaSemana.<br>• Grupo. |

| Clase: ActividadController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Recibir las peticiones de listado, alta, edición y baja.<br>• Filtrar el listado por nombre, semestre y departamento, y paginarlo.<br>• Recortar el listado al departamento del coordinador cuando no es administrador.<br>• Impedir la baja si hay grupos, indicando cuántos.<br>• Traducir los errores de integridad de la base a un mensaje comprensible. | • Actividad.<br>• GuardarActividadRequest.<br>• Semestre.<br>• Departamento.<br>• Vistas de actividades. |

| Clase: GrupoController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Recibir las peticiones de listado, alta, edición y baja.<br>• Recortar el listado a las actividades del departamento del coordinador.<br>• Asignar y quitar el instructor, validando que sea del departamento de la actividad.<br>• Mostrar el detalle del grupo con sus horarios.<br>• Impedir la baja si ya hay inscripciones (por ahora, cupo ocupado); los horarios se eliminan en cascada. | • Grupo.<br>• GuardarGrupoRequest.<br>• ServicioConflictos.<br>• Horario.<br>• Instructor.<br>• Semestre.<br>• Vistas de grupos. |

| Clase: HorarioController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Exponer el endpoint JSON `POST grupos/conflictos` del aviso en vivo.<br>• Consultar a `ServicioConflictos` los conflictos de los horarios capturados y devolverlos como JSON.<br>• No guarda ni borra horarios: eso lo hace `GrupoController` dentro de la transacción del grupo. | • VerificarHorariosRequest.<br>• ServicioConflictos.<br>• Grupo. |

| Clase: GuardarActividadRequest | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Autorizar la operación según el permiso de crear o de editar.<br>• Validar los campos y que el nombre no esté repetido, ignorando la propia actividad al editar.<br>• Exigir al menos una carrera dirigida.<br>• Impedir que un coordinador registre una actividad de otro departamento. | • Actividad.<br>• Gate (permisos de Spatie).<br>• Semestre. |

| Clase: GuardarGrupoRequest | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Autorizar la operación según el permiso de crear o de editar.<br>• Validar que el cupo mínimo no exceda al máximo y que las fechas caigan dentro del semestre.<br>• Exigir ubicación cuando la modalidad sea presencial o híbrida.<br>• Validar que el instructor sea del departamento de la actividad.<br>• Delegar la revisión de horarios a `ServicioConflictos`. | • Grupo.<br>• ServicioConflictos.<br>• Gate (permisos de Spatie). |

| Clase: VerificarHorariosRequest | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Autorizar la operación según el permiso de crear o de editar.<br>• Validar el arreglo `horarios[]` del formulario: que el día exista en el catálogo y que la hora de fin sea posterior a la de inicio.<br>• Pedir a `ServicioConflictos` el resultado y devolverlo como JSON para el aviso en vivo. | • Horario.<br>• DiaSemana.<br>• ServicioConflictos.<br>• Gate (permisos de Spatie). |

| Clase: ServicioConflictos | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Detectar que el instructor esté ocupado el mismo día.<br>• Detectar que la ubicación esté ocupada el mismo día.<br>• Detectar que dos horarios del mismo grupo se traslapen.<br>• Detectar que el cupo máximo exceda la capacidad de la ubicación.<br>• Calcular el traslape como solapamiento estricto, de modo que dos franjas que solo se tocen no se reporten.<br>• Excluir el grupo que se está editando de la búsqueda.<br>• Devolver la lista de conflictos con una descripción legible de cuál es y con qué choca. | • Grupo.<br>• Horario.<br>• DiaSemana.<br>• Actividad.<br>• Instructor.<br>• Ubicacion. |

**Por qué una clase aparte:** las cuatro reglas son el único criterio real del sprint y se necesitan
en dos sitios (el Request al guardar y el endpoint JSON del aviso en vivo). Si vivieran en el
Request, el endpoint tendría que duplicarlas.

---

## Anexo A · Esquema de las tablas nuevas

Transcrito del paquete `Sprint0_Base_de_Datos.zip`. El orden importa: cada tabla referencia a las
anteriores.

### 1. `dia_semana`

| Columna | Tipo | Notas |
|---|---|---|
| `id_dia` | id, PK | |
| `nombre_dia` | string, **único** | Catalogado: lunes, martes, **miércoles**, jueves, viernes, **sábado** |
| `created_at`, `updated_at` | timestamp | |

### 2. `actividad_complementaria`

| Columna | Tipo | Notas |
|---|---|---|
| `id_actividad` | id, PK | |
| `nombre` | string(150) | Obligatorio y único en el formulario |
| `descripcion` | text | Opcional |
| `id_categoria` | unsignedBigInteger, **nullable** | **Se conserva** por fidelidad al modelo E-R. No existe tabla `categoria`, así que el formulario no la pide y queda en `NULL`. Si más adelante se agrega el catálogo, se engancha aquí |
| `id_departamento` | unsignedBigInteger, FK → `departamento` | **Obligatorio**: la ofrece un departamento |
| `id_semestre` | unsignedBigInteger, FK → `semestre`, nullable, **cascade** | Se asocia al semestre vigente |
| `requisitos` | text | Opcional |
| `nivel_actividad` | string(50), nullable | Ejemplos del sistema anterior: «Básico», «Intermedio» |
| `disponible` | boolean, default `true` | Interruptor de oferta |
| `creditos` | integer | Obligatorio |
| `created_at`, `updated_at` | timestamp | |

### 3. `actividad_carrera` (pivote)

| Columna | Tipo | Notas |
|---|---|---|
| `id_actividad` | unsignedBigInteger, FK → `actividad_complementaria` | **PK compuesta** |
| `id_carrera` | unsignedBigInteger, FK → `carrera` | **PK compuesta** |

### 4. `grupo`

| Columna | Tipo | Notas |
|---|---|---|
| `id_grupo` | id, PK | |
| `id_actividad` | unsignedBigInteger, FK → `actividad_complementaria` | |
| `id_semestre` | unsignedBigInteger, FK → `semestre` | |
| `id_instructor` | unsignedBigInteger, FK → `instructor`, **nullable**, **SET NULL** | Un grupo puede quedar sin instructor; si se borra al instructor, el grupo sobrevive |
| `id_ubicacion` | unsignedBigInteger, FK → `ubicacion`, nullable | Obligatorio solo si la modalidad es presencial o híbrida |
| `grupo` | string(10) | Nombre del grupo («A», «B»…). Único dentro de la misma actividad |
| `cupo_minimo` | integer, default `1` | |
| `cupo_maximo` | integer | |
| `cupo_ocupado` | integer, default `0` | **Lo actualiza el Sprint 4** al inscribir; no se captura a mano |
| `modalidad` | enum(`presencial`,`virtual`,`hibrida`) | |
| `materiales_requeridos` | text, nullable | |
| `estatus` | enum(`abierta`,`cerrada`,`cancelada`,`finalizada`), default `abierta` | |
| `fecha_inicio`, `fecha_fin` | date | Dentro del semestre de la actividad |

### 5. `horario`

| Columna | Tipo | Notas |
|---|---|---|
| `id_horario` | id, PK | |
| `id_grupo` | unsignedBigInteger, FK → `grupo`, **cascade** | **Cambio deliberado** frente al esquema anterior: al borrar un grupo sus horarios se van con él |
| `id_dia` | unsignedBigInteger, FK → `dia_semana` | |
| `hora_inicio`, `hora_fin` | time | `hora_fin` siempre posterior a `hora_inicio` |

### Decisiones que difieren del esquema anterior

| Cambio | Por qué |
|---|---|
| `horario.id_grupo` con **cascade** | En el sistema anterior el borrado de un grupo con horarios fallaba por la llave foránea. Con cascade, borrar un grupo se lleva sus horarios |
| `grupo.grupo` **único por actividad** | El sistema anterior tenía cinco grupos llamados «A» sin ninguna restricción; el nombre repetido dentro de una misma actividad era un error |
| `dia_semana` con **acentos** | `utf8mb4` los soporta; `miercoles` y `sabado` quedaban mal en las pantallas |
| `id_categoria` **se conserva** | Es parte del modelo E-R. Sin tabla `categoria` queda como campo opcional |

---

## Anexo B · Instrucciones de implementación

Orden sugerido. Cada paso deja el proyecto en verde: `php artisan test` y `vendor\bin\pint --test`.

### 1. Migraciones

Cinco archivos nuevos, en este orden (el orden sigue las llaves foráneas, que dependen de tablas
anteriores):

```
database/migrations/2026_XX_XX_000000_create_dia_semana_table.php
database/migrations/2026_XX_XX_000001_create_actividad_complementaria_table.php
database/migrations/2026_XX_XX_000002_create_actividad_carrera_table.php
database/migrations/2026_XX_XX_000003_create_grupo_table.php
database/migrations/2026_XX_XX_000004_create_horario_table.php
```

Reglas: **no se edita ninguna migración ya aplicada**; `down()` con `dropIfExists`; el pivote con
`$table->primary(['id_actividad', 'id_carrera'])`. Verificar con `php artisan migrate --seed`.

### 2. Modelos

`DiaSemana`, `Actividad`, `Grupo`, `Horario`. Con el estilo del proyecto:

```php
#[Table(name: 'actividad_complementaria')]
#[Fillable(['nombre', 'descripcion', 'id_categoria', 'id_departamento', 'id_semestre', 'requisitos', 'nivel_actividad', 'disponible', 'creditos'])]
class Actividad extends Model
```

Relaciones que hay que declarar:

| Modelo | Relaciones |
|---|---|
| `Actividad` | `departamento()` belongsTo · `semestre()` belongsTo · `carreras()` belongsToMany (con `actividad_carrera`) · `grupos()` hasMany |
| `Grupo` | `actividad()` belongsTo · `semestre()` belongsTo · `instructor()` belongsTo · `ubicacion()` belongsTo · `horarios()` hasMany |
| `Horario` | `grupo()` belongsTo · `dia()` belongsTo (`DiaSemana`) |
| `DiaSemana` | `horarios()` hasMany |

En `Grupo`, un método de apoyo como `cupoCabeEnUbicacion()` mantiene la regla del aforo lejos de las
vistas.

### 3. Permisos

Agregar a `RolesYPermisosSeeder` (es idempotente, se puede volver a correr):

```
actividades.ver      actividades.crear      actividades.editar      actividades.eliminar
grupos.ver           grupos.crear           grupos.editar           grupos.eliminar
(no existen permisos `horarios.*`: los horarios se guardan con `grupos.crear` y `grupos.editar`;
el total del proyecto es 29 permisos)
```

Asignación por rol:

| Rol | Permisos |
|---|---|
| `administrador` | Todos |
| `coordinador` | Todos los de `actividades.*` y `grupos.*` (con el recorte por departamento) |
| `instructor` | Solo `grupos.ver`, para su pantalla de solo lectura |
| `alumno` | Ninguno |

### 4. Form Requests

`GuardarActividadRequest`, `GuardarGrupoRequest`, `VerificarHorariosRequest`, con el patrón ya usado en
los sprints anteriores: `authorize()` decide entre el permiso de crear y el de editar según haya o
no modelo en la ruta, y `messages()` en español.

### 5. `ServicioConflictos`

La pieza más importante del sprint. Reglas:

1. **Traslape estricto:** hay conflicto cuando `inicioA < finB` y `inicioB < finA`. Con esta
   comparación, dos franjas que solo se tocan (`08:30–09:00` y `09:00–09:30`) **no** se reportan.
   Es el error más fácil de cometer y el que más falsos positivos genera.
2. **Mismo día:** comparar solo horarios del mismo `id_dia`.
3. **Instructor:** buscar en los horarios de los grupos donde `id_instructor` sea el capturado,
   excluyendo el grupo que se edita.
4. **Ubicación:** igual, sobre `grupo.id_ubicacion`.
5. **Traslape interno:** los horarios del propio grupo contra sí mismos.
6. **Cupo contra capacidad:** `grupo.cupo_maximo > ubicacion.capacidad`, solo en modalidad
   presencial o híbrida.

Devuelve una lista de conflictos con descripción legible, para poder pintar el mensaje en la vista y
devolverlo como JSON.

### 6. Controladores y rutas

Tres controladores: `ActividadController`, `GrupoController` y `HorarioController` (anidado dentro del
grupo: `grupos/{grupo}/horarios`). Registrar en `routes/web.php` dentro del grupo de middleware que ya
existe (`auth`, `usuario.activo`, `departamento.asignado`), con un permiso por acción, igual que los
catálogos del Sprint 2.

El aviso en vivo del Sprint 3 es un endpoint que devuelve JSON (`application/json`), protegido con
CSRF. En el cliente, JavaScript plano en `resources/js/app.js`: el proyecto no usa librerías de UI ni
de JavaScript externas.

El recorte por departamento del coordinador se apoya en lo que ya existe desde el Sprint 1
(`ambitoDepartamento()`), no en una condición nueva.

### 7. Vistas

Blade con Tailwind 4, reutilizando los componentes que ya hay (`campo`, `seleccion`, `mensaje`,
`tarjeta-formulario`, `icono`, `accion`, `boton`, `barra-catalogo`) y el layout `app`. Las vistas de
horario se capturan en bloques que se agregan y se quitan sin recargar, para poder mostrar el aviso
de conflicto junto al campo.

Mensajes de sesión `exito` y `error`, como en el resto del sistema.

### 8. Seeders

Un seeder nuevo, `ActividadesBaseSeeder`, con los seis días de la semana y algunas actividades de
ejemplo (con su pivote de carreras), llamado desde `DatabaseSeeder` después de `CatalogosBaseSeeder`.
`firstOrCreate` en todo, para que sea idempotente.

Ejemplos de actividades tomados del sistema anterior: «Programación Web con Laravel» (2 créditos,
nivel Intermedio), «Inglés Técnico», «Fútbol Soccer», «Emprendimiento e Innovación», «Diseño Gráfico
Digital».

### 9. Pruebas

Pest, en `tests/Feature/`. Cada archivo con `uses(RefreshDatabase::class)` y `$this->withoutVite()`.
Agregar a `Escenarios` los apoyos de `actividad()`, `grupo()` y `horario()` en vez de crear datos a
mano en cada prueba.

Casos que no deben faltar:

- El administrador entra a todo; el coordinador solo a su departamento; el instructor y el alumno no.
- Alta de actividad con sus carreras y edición sin falsos positivos de unicidad.
- No se borra una actividad con grupos.
- Alta de grupo validando cupo mínimo/máximo, fechas dentro del semestre y ubicación obligatoria.
- Asignación de instructor limitada al departamento.
- El instructor solo ve sus grupos y no puede modificarlos.
- Un horario con hora de fin anterior a la de inicio se rechaza.
- **Las cuatro reglas de conflicto**, incluyendo el caso de borde: dos horarios que se tocan **no**
  generan conflicto, y al editar un grupo no se reporta a sí mismo.

### 10. Documentación

Al cerrar el sprint: crear `docs/sprints/sprint-03-actividades-y-grupos.md` con el detalle técnico real, actualizar
el estado en `docs/contexto.md` y `docs/requisitos.md` (RF-07 y RF-08 pasan de *Pendiente* a
*Cumplido*), y agregar la entrada correspondiente en `docs/bitacora.md`.

---

## Notas y riesgos

- **RF-07 y RF-08** se cubren con estas tres historias. RF-09 y RF-10 (inscripción y correo) son del
  Sprint 4.
- **Ningún grupo del sistema anterior tiene instructor asignado**, así que no hay datos de referencia
  para esa parte: es trabajo nuevo y hay que probarlo bien.
- **`cupo_ocupado` y `grupo.fecha_inicio/fecha_fin`** los consume el Sprint 4. Si el alcance se mueve,
  conviene avisarlo antes de empezar.
- **`inscripcion`** (Sprint 4) usa una columna generada que exige MySQL 5.7 o superior. Ahora que el
  proyecto corre en MySQL 8, esa restricción del Sprint 0 quedó resuelta y la migración se puede
  copiar tal cual.
- **RF-04 «permitir a cada usuario actualizar su perfil»** sigue sin implementarse desde el Sprint 1.
