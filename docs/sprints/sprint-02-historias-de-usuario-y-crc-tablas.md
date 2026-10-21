# Historias de usuario y tarjetas CRC · Sprint 2 (formato con criterios dentro de la tabla)

**Sección 4.6** del documento de requerimientos. Replica la estructura de la sección 4.5
(Sprint 1): cada historia es **una sola tabla** con sus criterios de aceptación **dentro** y el
encabezado en gris (`#D1D5DB`). El Product Backlog completo está en la sección 4.4 (Tabla 8).

> **Sobre el formato.** Markdown puro no permite color de fondo ni celdas combinadas, por eso las
> tablas están escritas en HTML. El gris se ve en la vista previa de VS Code, en HTML y al exportar a
> PDF o Word. **GitHub ignora los colores de fondo** (muestra la tabla sin gris, pero con el mismo contenido).
> Para cambiar el tono basta reemplazar `#D1D5DB` en todo el archivo.

---

## 4.6 Iteración 2: Sprint 2 - Catálogos base y semestres (semanas 5 y 6)

### 4.6.1 Planificación del Sprint

**Sprint Goal:** contar con los catálogos base que necesitarán las actividades y los grupos, y con un
semestre vigente que gobierne las inscripciones del sistema y se cierre solo cuando termina.

Se seleccionaron las historias HU-08 a HU-13, con un total de **19 puntos de historia**. El Sprint 3
(actividades, grupos y horarios) se apoya en las tres primeras: una ubicación en uso no podrá
eliminarse cuando existan horarios, y el semestre será al que se cuelguen grupos e inscripciones.

Tabla 10. Sprint Backlog del Sprint 2

<table>
<thead>
<tr>
<th align="left" width="9%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU</th>
<th align="left" width="63%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Tareas</th>
<th align="left" width="21%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsable</th>
<th align="center" width="7%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Horas</th>
</tr>
</thead>
<tbody>
<tr><td>HU-08</td><td>Form Request con nombre único; controlador con buscador por nombre o edificio; vistas de listado, alta y edición; pruebas del borrado protegido por instructores y coordinador (la tabla y el modelo <code>departamento</code> ya existían desde el Sprint 1).</td><td>Martínez Rodríguez Edwin Jovanny</td><td align="center">8</td></tr>
<tr><td>HU-09</td><td>Listado con número de alumnos (<code>withCount</code>); Form Request; controlador; vistas; prueba de la carrera con alumnos (la tabla y el modelo <code>carrera</code> ya existían desde el Sprint 1).</td><td>Martínez Rodríguez Edwin Jovanny</td><td align="center">7</td></tr>
<tr><td>HU-10</td><td>Migración y modelo <code>ubicacion</code>; validación de capacidad entre 1 y 9999; controlador con buscador por espacio; vistas; prueba de borrado.</td><td>Martínez Rodríguez Edwin Jovanny</td><td align="center">6</td></tr>
<tr><td>HU-11</td><td>Migración <code>semestre</code>; modelo con las reglas de periodo (activo, vencido, ventana de inscripciones); <code>GuardarSemestreRequest</code> con las validaciones de negocio (duración, ventana de inscripción, rango del semestre, un solo periodo activo, año + periodo único y periodo vencido); vistas de alta y edición con aviso en vivo.</td><td>Martínez Rodríguez Edwin Jovanny</td><td align="center">12</td></tr>
<tr><td>HU-12</td><td>Pantalla con el periodo activo arriba y el historial debajo; pantalla de detalle; reglas de edición con motivo de bloqueo; mensaje del estado de las inscripciones.</td><td>Martínez Rodríguez Edwin Jovanny</td><td align="center">7</td></tr>
<tr><td>HU-13</td><td>Método <code>cerrarVencidos</code> en el modelo; comando <code>semestres:cerrar-vencidos</code>; programación diaria a las 00:05; respaldo al abrir el listado y el formulario; pruebas del comando y de la programación.</td><td>Martínez Rodríguez Edwin Jovanny</td><td align="center">5</td></tr>
<tr><td>-</td><td>Pruebas del Sprint, revisión de las vistas nuevas e integración en GitHub.</td><td>Todo el equipo</td><td align="center">7</td></tr>
<tr><td></td><td align="right"><b>Total</b></td><td></td><td align="center"><b>52</b></td></tr>
</tbody>
</table>

> Las horas son una estimación basada en la del Sprint 1 (≈2.7 h por punto de historia); confirmar el
> reparto con el equipo antes de la entrega.

### 4.6.2 Historias de usuario

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU-08 · Gestionar departamentos</th></tr>
</thead>
<tbody>
<tr><td width="20%"><b>Como</b></td><td>Administrador</td></tr>
<tr><td><b>Quiero</b></td><td>registrar, consultar, editar y eliminar los departamentos</td></tr>
<tr><td><b>Para</b></td><td>tener el catálogo base al que pertenecen instructores y coordinadores en los siguientes sprints</td></tr>
<tr><td><b>Prioridad / Estimación</b></td><td>Alta · 3 puntos de historia</td></tr>
<tr><td><b>Criterios de aceptación</b></td><td>
1. El listado es paginado de 10 en 10 y se puede buscar por nombre o por edificio.<br>
2. El nombre es obligatorio, único y de máximo 100 caracteres; el edificio es opcional.<br>
3. Editar un departamento no falla cuando conserva su propio nombre.<br>
4. No se elimina un departamento que tenga instructores o un coordinador asignado, y el sistema indica el motivo.<br>
5. El acceso se controla por permiso: si el permiso <code>departamentos.ver</code> se quita en la pantalla de roles, el menú y las rutas quedan bloqueados.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU-09 · Gestionar carreras</th></tr>
</thead>
<tbody>
<tr><td width="20%"><b>Como</b></td><td>Administrador</td></tr>
<tr><td><b>Quiero</b></td><td>registrar, consultar, editar y eliminar las carreras</td></tr>
<tr><td><b>Para</b></td><td>saber en qué carrera estudia cada alumno al inscribirlo</td></tr>
<tr><td><b>Prioridad / Estimación</b></td><td>Alta · 3 puntos de historia</td></tr>
<tr><td><b>Criterios de aceptación</b></td><td>
1. El listado es paginado de 10 en 10, muestra cuántos alumnos tiene cada carrera y se puede buscar por nombre.<br>
2. El nombre es obligatorio, único y de máximo 100 caracteres.<br>
3. Editar una carrera no falla cuando conserva su propio nombre.<br>
4. No se elimina una carrera que tenga alumnos registrados, y el sistema indica cuántos son.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU-10 · Gestionar ubicaciones</th></tr>
</thead>
<tbody>
<tr><td width="20%"><b>Como</b></td><td>Administrador</td></tr>
<tr><td><b>Quiero</b></td><td>registrar, consultar, editar y eliminar las ubicaciones donde se imparten actividades</td></tr>
<tr><td><b>Para</b></td><td>tener espacios y aforos disponibles para los horarios del Sprint 3</td></tr>
<tr><td><b>Prioridad / Estimación</b></td><td>Media · 2 puntos de historia</td></tr>
<tr><td><b>Criterios de aceptación</b></td><td>
1. El listado es paginado de 10 en 10, ordenado alfabéticamente y con buscador por nombre de espacio.<br>
2. El nombre del espacio es obligatorio, único y de máximo 100 caracteres.<br>
3. La capacidad es obligatoria, entera y va de 1 a 9999 personas.<br>
4. Cuando una ubicación tenga registros relacionados la base de datos impide borrarla y se informa al usuario.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU-11 · Registrar semestre y ventana de inscripción</th></tr>
</thead>
<tbody>
<tr><td width="20%"><b>Como</b></td><td>Administrador</td></tr>
<tr><td><b>Quiero</b></td><td>registrar un periodo escolar con su ventana de inscripción</td></tr>
<tr><td><b>Para</b></td><td>definir cuándo se imparte el semestre y cuándo pueden inscribirse los alumnos</td></tr>
<tr><td><b>Prioridad / Estimación</b></td><td>Alta · 5 puntos de historia</td></tr>
<tr><td><b>Criterios de aceptación</b></td><td>
1. El formulario de alta solo se abre cuando no existe un periodo activo; si lo hay, el sistema regresa al listado con un aviso.<br>
2. El periodo captura año, periodo (1 = enero–junio, 2 = agosto–diciembre), fecha de inicio y fin del semestre, y la ventana de inscripción con su hora de apertura y de cierre.<br>
3. La duración del semestre debe ser de 3 a 5 meses completos (desde 3 meses exactos hasta antes de cumplir 6).<br>
4. La ventana de inscripción debe durar de 5 a 10 días y quedar dentro del rango del semestre.<br>
5. Solo puede haber un periodo activo a la vez y el par año + periodo es único.<br>
6. No se puede activar un periodo cuya fecha de fin ya pasó, porque se cerraría de inmediato.<br>
7. El formulario indica en vivo si la duración y la ventana son válidas, y limita los calendarios a enero–junio o agosto–diciembre (límite solo de pantalla; el servidor no lo exige).<br>
8. Los semestres no se eliminan: conservan el historial del que dependen grupos, inscripciones y calificaciones.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU-12 · Consultar semestres</th></tr>
</thead>
<tbody>
<tr><td width="20%"><b>Como</b></td><td>Administrador</td></tr>
<tr><td><b>Quiero</b></td><td>consultar el semestre vigente, el historial de los anteriores y el detalle de cada uno</td></tr>
<tr><td><b>Para</b></td><td>saber en qué periodo está el sistema y cuándo fueron las inscripciones</td></tr>
<tr><td><b>Prioridad / Estimación</b></td><td>Media · 3 puntos de historia</td></tr>
<tr><td><b>Criterios de aceptación</b></td><td>
1. La pantalla muestra primero el periodo activo y debajo el historial de los inactivos, paginado de 10 en 10.<br>
2. Se puede abrir el detalle de cualquier periodo.<br>
3. Se indica si las inscripciones del periodo activo están abiertas o cerradas.<br>
4. Solo el periodo activo se puede editar y finalizar. Si no hay periodo activo, únicamente se puede reactivar el inactivo más reciente; los demás se muestran con un candado de «solo lectura» y, si se intenta abrir su edición, el sistema indica el motivo.<br>
5. Los enlaces del menú aparecen solo para quien tiene el permiso <code>semestres.ver</code>.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">HU-13 · Cierre automático del semestre</th></tr>
</thead>
<tbody>
<tr><td width="20%"><b>Como</b></td><td>Sistema</td></tr>
<tr><td><b>Quiero</b></td><td>cerrar el periodo cuando termina su fecha de fin</td></tr>
<tr><td><b>Para</b></td><td>que las inscripciones se bloqueen solas y no dependan de que alguien lo recuerde</td></tr>
<tr><td><b>Prioridad / Estimación</b></td><td>Alta · 3 puntos de historia</td></tr>
<tr><td><b>Criterios de aceptación</b></td><td>
1. El comando <code>semestres:cerrar-vencidos</code> marca como inactivo todo periodo activo cuya fecha de fin ya pasó e informa cuántos cerró.<br>
2. El comando está programado para ejecutarse todos los días a las 00:05.<br>
3. Como respaldo, el cierre se ejecuta también al abrir el listado y los formularios de alta y edición, y antes de validar el guardado de un periodo.<br>
4. Un periodo que termina el día de hoy permanece activo: el cierre ocurre a partir del día siguiente.<br>
5. Las decisiones de ventana de inscripción solo aplican a periodos activos.
</td></tr>
</tbody>
</table>

### 4.6.3 Tarjetas CRC

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: Semestre</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Saber cuál es el periodo activo del sistema.<br>
• Saber cuál es el periodo inactivo más reciente, el único que se puede reactivar.<br>
• Cerrar en bloque los periodos vencidos.<br>
• Determinar si un periodo ya venció.<br>
• Determinar si las inscripciones están abiertas, dentro de la ventana de fecha y hora y con ambos extremos incluidos.<br>
• Explicar por qué un periodo no se puede editar.<br>
• Presentarse en texto legible, en forma larga («Enero–Junio 2026», «Agosto–Diciembre 2026») y corta («Ene–Jun 2026», «Ago–Dic 2026»).
</td><td>
• Reloj del sistema (Carbon).
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: Departamento</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Guardar el nombre y el edificio.<br>
• Declarar qué coordinador tiene asignado.<br>
• Declarar qué instructores pertenecen a él.
</td><td>
• CoordinadorDepartamento.<br>
• Instructor.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: Carrera</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Guardar el nombre.<br>
• Declarar qué alumnos estudian en ella, relación con la que se cuentan en el listado.
</td><td>
• Alumno.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: Ubicacion</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Guardar el nombre del espacio y su capacidad como entero.
</td><td>
• —
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: DepartamentoController</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Recibir las peticiones de listado, alta, edición y baja.<br>
• Filtrar el listado por nombre o edificio y paginarlo de 10 en 10.<br>
• Impedir la baja si hay instructores o un coordinador, indicando el motivo.<br>
• Traducir los errores de integridad de la base de datos a un mensaje comprensible.
</td><td>
• Departamento.<br>
• GuardarDepartamentoRequest.<br>
• Vistas de departamentos.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: CarreraController</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Recibir las peticiones de listado, alta, edición y baja.<br>
• Mostrar el número de alumnos de cada carrera en el listado.<br>
• Impedir la baja si la carrera tiene alumnos, indicando cuántos.
</td><td>
• Carrera.<br>
• GuardarCarreraRequest.<br>
• Alumno.<br>
• Vistas de carreras.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: UbicacionController</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Recibir las peticiones de listado, alta, edición y baja.<br>
• Filtrar el listado por nombre de espacio y paginarlo.<br>
• Informar cuando la base de datos impide borrar una ubicación en uso.
</td><td>
• Ubicacion.<br>
• GuardarUbicacionRequest.<br>
• Vistas de ubicaciones.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: SemestreController</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Mostrar el periodo activo y el historial de los inactivos.<br>
• Abrir un periodo nuevo cuando no hay uno activo.<br>
• Bloquear el alta y la edición cuando corresponde, con un mensaje que explica el motivo.<br>
• Editar y finalizar el periodo activo, y reactivar el inactivo más reciente.<br>
• Consultar el detalle de un periodo.<br>
• Mostrar si las inscripciones del periodo activo están abiertas o cerradas.
</td><td>
• Semestre.<br>
• GuardarSemestreRequest.<br>
• Vistas de semestres.
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: GuardarSemestreRequest</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Autorizar la operación según el permiso de crear o de editar.<br>
• Validar los campos obligatorios, el año, el periodo y el formato de las horas.<br>
• Validar que la duración del semestre sea de 3 a 5 meses.<br>
• Validar que la ventana de inscripción dure de 5 a 10 días y quepa dentro del semestre.<br>
• Impedir que haya dos periodos activos y que se repita el par año + periodo.<br>
• Impedir activar un periodo ya vencido.
</td><td>
• Semestre.<br>
• Gate (permisos de Spatie).
</td></tr>
</tbody>
</table>

<table>
<thead>
<tr><th colspan="2" align="left" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Clase: CerrarSemestresVencidos (comando)</th></tr>
<tr><th align="left" width="65%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Responsabilidades</th><th align="left" width="35%" bgcolor="#D1D5DB" style="background-color:#D1D5DB">Colaboradores</th></tr>
</thead>
<tbody>
<tr><td>
• Pedir el cierre de los periodos vencidos al ser invocado.<br>
• Informar en consola cuántos se cerraron o que no había ninguno.<br>
• Terminar la ejecución con éxito.
</td><td>
• Semestre.<br>
• Planificador de Laravel.<br>
• Consola de artisan.
</td></tr>
</tbody>
</table>

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

---
