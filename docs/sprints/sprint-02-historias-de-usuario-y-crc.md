# Historias de usuario y tarjetas CRC · Sprint 2

**Sección 4.6** del documento de requerimientos. Replica la estructura de la sección 4.5
(Sprint 1). El Product Backlog completo está en la sección 4.4 (Tabla 8), donde estas seis
historias ya están registradas y estimadas con Planning Poker.

---

## 4.6 Iteración 2: Sprint 2 - Catálogos base y semestres (semanas 5 y 6)

### 4.6.1 Planificación del Sprint

**Sprint Goal:** contar con los catálogos base que necesitarán las actividades y los grupos, y con un
semestre vigente que gobierne las inscripciones del sistema y se cierre solo cuando termina.

Se seleccionaron las historias HU-08 a HU-13, con un total de **19 puntos de historia**. El Sprint 3
(actividades, grupos y horarios) se apoya en las tres primeras: una ubicación en uso no podrá
eliminarse cuando existan horarios, y el semestre será al que se cuelguen grupos e inscripciones.

Tabla 10. Sprint Backlog del Sprint 2

| HU | Tareas | Responsable | Horas |
|---|---|---|---|
| HU-08 | Form Request con nombre único; controlador con buscador por nombre o edificio; vistas de listado, alta y edición; pruebas del borrado protegido por instructores y coordinador (la tabla y el modelo `departamento` ya existían desde el Sprint 1). | Martínez Rodríguez Edwin Jovanny | 8 |
| HU-09 | Listado con número de alumnos (`withCount`); Form Request; controlador; vistas; prueba de la carrera con alumnos (la tabla y el modelo `carrera` ya existían desde el Sprint 1). | Martínez Rodríguez Edwin Jovanny | 7 |
| HU-10 | Migración y modelo `ubicacion`; validación de capacidad entre 1 y 9999; controlador con buscador por espacio; vistas; prueba de borrado. | Martínez Rodríguez Edwin Jovanny | 6 |
| HU-11 | Migración `semestre`; modelo con las reglas de periodo (activo, vencido, ventana de inscripciones); `GuardarSemestreRequest` con las validaciones de negocio (duración, ventana de inscripción, rango del semestre, un solo periodo activo, año + periodo único y periodo vencido); vistas de alta y edición con aviso en vivo. | Martínez Rodríguez Edwin Jovanny | 12 |
| HU-12 | Pantalla con el periodo activo arriba y el historial debajo; pantalla de detalle; reglas de edición con motivo de bloqueo; mensaje del estado de las inscripciones. | Martínez Rodríguez Edwin Jovanny | 7 |
| HU-13 | Método `cerrarVencidos` en el modelo; comando `semestres:cerrar-vencidos`; programación diaria a las 00:05; respaldo al abrir el listado y el formulario; pruebas del comando y de la programación. | Martínez Rodríguez Edwin Jovanny | 5 |
| - | Pruebas del Sprint, revisión de las vistas nuevas e integración en GitHub. | Todo el equipo | 7 |

> Las horas son una estimación basada en la del Sprint 1 (≈2.7 h por punto de historia); confirmar el
> reparto con el equipo antes de la entrega.

### 4.6.2 Historias de usuario

| HU-08 · Gestionar departamentos | |
|---|---|
| Como | Administrador |
| Quiero | registrar, consultar, editar y eliminar los departamentos |
| Para | tener el catálogo base al que pertenecen instructores y coordinadores en los siguientes sprints |
| Prioridad / Estimación | Alta · 3 puntos de historia |
| Criterios de aceptación | 1. El listado es paginado de 10 en 10 y se puede buscar por nombre o por edificio.<br>2. El nombre es obligatorio, único y de máximo 100 caracteres; el edificio es opcional.<br>3. Editar un departamento no falla cuando conserva su propio nombre.<br>4. No se elimina un departamento que tenga instructores o un coordinador asignado, y el sistema indica el motivo.<br>5. El acceso se controla por permiso: si el permiso `departamentos.ver` se quita en la pantalla de roles, el menú y las rutas quedan bloqueados. |

| HU-09 · Gestionar carreras | |
|---|---|
| Como | Administrador |
| Quiero | registrar, consultar, editar y eliminar las carreras |
| Para | saber en qué carrera estudia cada alumno al inscribirlo |
| Prioridad / Estimación | Alta · 3 puntos de historia |
| Criterios de aceptación | 1. El listado es paginado de 10 en 10, muestra cuántos alumnos tiene cada carrera y se puede buscar por nombre.<br>2. El nombre es obligatorio, único y de máximo 100 caracteres.<br>3. Editar una carrera no falla cuando conserva su propio nombre.<br>4. No se elimina una carrera que tenga alumnos registrados, y el sistema indica cuántos son. |

| HU-10 · Gestionar ubicaciones | |
|---|---|
| Como | Administrador |
| Quiero | registrar, consultar, editar y eliminar las ubicaciones donde se imparten actividades |
| Para | tener espacios y aforos disponibles para los horarios del Sprint 3 |
| Prioridad / Estimación | Media · 2 puntos de historia |
| Criterios de aceptación | 1. El listado es paginado de 10 en 10, ordenado alfabéticamente y con buscador por nombre de espacio.<br>2. El nombre del espacio es obligatorio, único y de máximo 100 caracteres.<br>3. La capacidad es obligatoria, entera y va de 1 a 9999 personas.<br>4. Cuando una ubicación tenga registros relacionados la base de datos impide borrarla y se informa al usuario. |

| HU-11 · Registrar semestre y ventana de inscripción | |
|---|---|
| Como | Administrador |
| Quiero | registrar un periodo escolar con su ventana de inscripción |
| Para | definir cuándo se imparte el semestre y cuándo pueden inscribirse los alumnos |
| Prioridad / Estimación | Alta · 5 puntos de historia |
| Criterios de aceptación | 1. El formulario de alta solo se abre cuando no existe un periodo activo; si lo hay, el sistema regresa al listado con un aviso.<br>2. El periodo captura año, periodo (1 = enero–junio, 2 = agosto–diciembre), fecha de inicio y fin del semestre, y la ventana de inscripción con su hora de apertura y de cierre.<br>3. La duración del semestre debe ser de 3 a 5 meses completos (desde 3 meses exactos hasta antes de cumplir 6).<br>4. La ventana de inscripción debe durar de 5 a 10 días y quedar dentro del rango del semestre.<br>5. Solo puede haber un periodo activo a la vez y el par año + periodo es único.<br>6. No se puede activar un periodo cuya fecha de fin ya pasó, porque se cerraría de inmediato.<br>7. El formulario indica en vivo si la duración y la ventana son válidas, y limita los calendarios a enero–junio o agosto–diciembre (límite solo de pantalla; el servidor no lo exige).<br>8. Los semestres no se eliminan: conservan el historial del que dependen grupos, inscripciones y calificaciones. |

| HU-12 · Consultar semestres | |
|---|---|
| Como | Administrador |
| Quiero | consultar el semestre vigente, el historial de los anteriores y el detalle de cada uno |
| Para | saber en qué periodo está el sistema y cuándo fueron las inscripciones |
| Prioridad / Estimación | Media · 3 puntos de historia |
| Criterios de aceptación | 1. La pantalla muestra primero el periodo activo y debajo el historial de los inactivos, paginado de 10 en 10.<br>2. Se puede abrir el detalle de cualquier periodo.<br>3. Se indica si las inscripciones del periodo activo están abiertas o cerradas.<br>4. Solo el periodo activo se puede editar y finalizar. Si no hay periodo activo, únicamente se puede reactivar el inactivo más reciente; los demás se muestran con un candado de «solo lectura» y, si se intenta abrir su edición, el sistema indica el motivo.<br>5. Los enlaces del menú aparecen solo para quien tiene el permiso `semestres.ver`. |

| HU-13 · Cierre automático del semestre | |
|---|---|
| Como | Sistema |
| Quiero | cerrar el periodo cuando termina su fecha de fin |
| Para | que las inscripciones se bloqueen solas y no dependan de que alguien lo recuerde |
| Prioridad / Estimación | Alta · 3 puntos de historia |
| Criterios de aceptación | 1. El comando `semestres:cerrar-vencidos` marca como inactivo todo periodo activo cuya fecha de fin ya pasó e informa cuántos cerró.<br>2. El comando está programado para ejecutarse todos los días a las 00:05.<br>3. Como respaldo, el cierre se ejecuta también al abrir el listado y los formularios de alta y edición, y antes de validar el guardado de un periodo.<br>4. Un periodo que termina el día de hoy permanece activo: el cierre ocurre a partir del día siguiente.<br>5. Las decisiones de ventana de inscripción solo aplican a periodos activos. |

### 4.6.3 Tarjetas CRC

| Clase: Semestre | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Saber cuál es el periodo activo del sistema.<br>• Saber cuál es el periodo inactivo más reciente, el único que se puede reactivar.<br>• Cerrar en bloque los periodos vencidos.<br>• Determinar si un periodo ya venció.<br>• Determinar si las inscripciones están abiertas, dentro de la ventana de fecha y hora y con ambos extremos incluidos.<br>• Explicar por qué un periodo no se puede editar.<br>• Presentarse en texto legible, en forma larga («Enero–Junio 2026», «Agosto–Diciembre 2026») y corta («Ene–Jun 2026», «Ago–Dic 2026»). | • Reloj del sistema (Carbon). |

| Clase: Departamento | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar el nombre y el edificio.<br>• Declarar qué coordinador tiene asignado.<br>• Declarar qué instructores pertenecen a él. | • CoordinadorDepartamento.<br>• Instructor. |

| Clase: Carrera | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar el nombre.<br>• Declarar qué alumnos estudian en ella, relación con la que se cuentan en el listado. | • Alumno. |

| Clase: Ubicacion | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Guardar el nombre del espacio y su capacidad como entero. | • — |

| Clase: DepartamentoController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Recibir las peticiones de listado, alta, edición y baja.<br>• Filtrar el listado por nombre o edificio y paginarlo de 10 en 10.<br>• Impedir la baja si hay instructores o un coordinador, indicando el motivo.<br>• Traducir los errores de integridad de la base de datos a un mensaje comprensible. | • Departamento.<br>• GuardarDepartamentoRequest.<br>• Vistas de departamentos. |

| Clase: CarreraController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Recibir las peticiones de listado, alta, edición y baja.<br>• Mostrar el número de alumnos de cada carrera en el listado.<br>• Impedir la baja si la carrera tiene alumnos, indicando cuántos. | • Carrera.<br>• GuardarCarreraRequest.<br>• Alumno.<br>• Vistas de carreras. |

| Clase: UbicacionController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Recibir las peticiones de listado, alta, edición y baja.<br>• Filtrar el listado por nombre de espacio y paginarlo.<br>• Informar cuando la base de datos impide borrar una ubicación en uso. | • Ubicacion.<br>• GuardarUbicacionRequest.<br>• Vistas de ubicaciones. |

| Clase: SemestreController | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Mostrar el periodo activo y el historial de los inactivos.<br>• Abrir un periodo nuevo cuando no hay uno activo.<br>• Bloquear el alta y la edición cuando corresponde, con un mensaje que explica el motivo.<br>• Editar y finalizar el periodo activo, y reactivar el inactivo más reciente.<br>• Consultar el detalle de un periodo.<br>• Mostrar si las inscripciones del periodo activo están abiertas o cerradas. | • Semestre.<br>• GuardarSemestreRequest.<br>• Vistas de semestres. |

| Clase: GuardarSemestreRequest | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Autorizar la operación según el permiso de crear o de editar.<br>• Validar los campos obligatorios, el año, el periodo y el formato de las horas.<br>• Validar que la duración del semestre sea de 3 a 5 meses.<br>• Validar que la ventana de inscripción dure de 5 a 10 días y quepa dentro del semestre.<br>• Impedir que haya dos periodos activos y que se repita el par año + periodo.<br>• Impedir activar un periodo ya vencido. | • Semestre.<br>• Gate (permisos de Spatie). |

| Clase: CerrarSemestresVencidos (comando) | |
|---|---|
| **Responsabilidades** | **Colaboradores** |
| • Pedir el cierre de los periodos vencidos al ser invocado.<br>• Informar en consola cuántos se cerraron o que no había ninguno.<br>• Terminar la ejecución con éxito. | • Semestre.<br>• Planificador de Laravel.<br>• Consola de artisan. |

---

## Notas de implementación

- **Sin Policy para los catálogos.** El control de acceso es con permisos de Spatie en las rutas y
  `Gate` dentro de los Form Requests; la única política del proyecto es `UserPolicy`, del Sprint 1.
- **Las reglas de borrado viven en los controladores**, no en los modelos. Por eso `Ubicacion` no
  avisa antes del borrado y depende del error de la base de datos.
- **Tecnología.** Es Laravel 13 (no Laravel 8) con Blade y Tailwind 4; el sistema anterior usaba
  Stisla y Bootstrap. Cambió la tecnología, no el contenido de las pantallas.
- **Base de datos.** SQLite en desarrollo; los nombres de las tablas siguen el modelo E-R
  (`departamento`, `carrera`, `ubicacion`, `semestre`).

La documentación técnica del sprint, con archivos, rutas, migraciones y deuda técnica, está en
[`sprint-02-catalogos-y-semestres.md`](sprint-02-catalogos-y-semestres.md).