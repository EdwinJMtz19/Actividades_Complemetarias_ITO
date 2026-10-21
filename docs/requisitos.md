# Requisitos del sistema

**Fuente:** `Requisitos del sistema.docx` (secciones **4.3.1 Requisitos funcionales** y **4.3.2
Requisitos no funcionales**; Tablas 6 y 7), recibido el 2026-10-18.

> Ese Word es un **fragmento**: empieza en 4.3.1 y termina en 4.3.2. No incluye el índice ni las
> secciones 4.1 y 4.2 del documento completo.

La redacción de cada requisito es la original del documento, sin alterar. Lo que se agregó son las
columnas **Estado** y **Evidencia**, que cruzan el requisito contra el código real.

Documentos relacionados:
[Documentación técnica del Sprint 3](sprints/sprint-03-actividades-y-grupos.md) ·
[Historias y CRC del Sprint 2](sprints/sprint-02-historias-de-usuario-y-crc.md) ·
[Documentación técnica del Sprint 2](sprints/sprint-02-catalogos-y-semestres.md) ·
[Contexto del proyecto](contexto.md)

---

## 1. Requisitos funcionales (Tabla 6)

**Estados usados:** `Cumplido` · `Parcial` · `Pendiente` · `No implementado`

| ID | Requisito | Actor | Sprint | Estado | Evidencia en el código |
|---|---|---|---|---|---|
| RF-01 | Autenticar usuarios mediante correo y contraseña, bloqueando cuentas desactivadas. | Todos | 1 | **Cumplido** | `Auth/LoginController.php`, `Auth/LoginRequest.php` (límite de 5 intentos por correo+IP), middleware `EnsureUsuarioActivo`. Pruebas: `inicia sesión con credenciales válidas y registra el último acceso`, `no deja entrar a una cuenta desactivada`, `bloquea el acceso tras 5 intentos fallidos`, `cierra la sesión de una cuenta que fue desactivada mientras estaba conectada` |
| RF-02 | Gestionar usuarios (alta, consulta, modificación, baja y activación) con cuatro roles. | Administrador | 1 | **Cumplido** | `UsuarioController.php`, `RolController.php`, `RolesYPermisosSeeder.php` (4 roles, 29 permisos al cierre del Sprint 3). Pruebas: `lista los usuarios para el administrador`, `crea un instructor con su departamento y sincroniza tipo_usuario`, `edita los datos y deja la contraseña igual si se envía en blanco`, `elimina a un instructor junto con su perfil`, `permite al administrador cambiar los permisos de un rol` |
| RF-03 | Asignar un coordinador por departamento. | Administrador | 1 | **Parcial** | `UsuarioController.php:191` crea o actualiza la asignación con `CoordinadorDepartamento::updateOrCreate()`; la unicidad la garantiza la base (`id_usuario` e `id_departamento` únicos). Pruebas: `crea un coordinador y le asigna un departamento`, `no permite dos coordinadores en el mismo departamento`, `elimina a un coordinador y libera su departamento`. **Falta:** una forma de *quitar* al coordinador sin editar el usuario completo (lo que pedía HU-06 del Sprint 1) |
| RF-04 | Permitir a cada usuario actualizar su perfil. | Todos | 1 | **No implementado** | No existe ninguna ruta ni vista de perfil (`perfil`/`profile` no aparecen en `routes/` ni en `resources/views/`). No hay prueba que lo cubra. **Es el único faltante de los sprints 1 y 2** |
| RF-05 | Gestionar departamentos, carreras y ubicaciones. | Administrador | 2 | **Cumplido** | `DepartamentoController`, `CarreraController`, `UbicacionController` + sus Form Requests y vistas. Pruebas: `lista los departamentos y busca por nombre o edificio`, `lista las carreras con su número de alumnos y busca por nombre`, `lista las ubicaciones con su capacidad y busca por nombre`, `no elimina un departamento con instructores`, `no elimina una carrera con alumnos registrados` |
| RF-06 | Gestionar semestres con ventana de inscripción y cierre automático. | Administrador / Sistema | 2 | **Cumplido** | `Models/Semestre.php` (`activo()`, `cerrarVencidos()`, `inscripcionesAbiertas()`), `Commands/CerrarSemestresVencidos.php`, `routes/console.php:12` (diario a las 00:05). Pruebas: `abre un periodo nuevo y lo deja activo`, `solo permite un periodo activo a la vez`, `valida la duración del semestre: de 3 a 5 meses`, `valida la ventana de inscripción: de 5 a 10 días`, `el comando programado cierra solo los semestres vencidos`, `programa el cierre automático todos los días` |
| RF-07 | Gestionar actividades complementarias asociadas al semestre activo y a carreras. | Administrador / Coordinador | 3 | **Cumplido** | `ActividadController`, `GuardarActividadRequest`, `Actividad`, `ActividadPolicy`, vistas `actividades/`, `ActividadesBaseSeeder`. Pruebas: `crea una actividad con sus carreras en el semestre activo`, `mueve una actividad al semestre vigente al editarla`, `limita al coordinador a las actividades de su departamento`, `ignora el departamento que un coordinador intente imponer al crear`, `no elimina una actividad que tiene grupos` |
| RF-08 | Gestionar grupos con horarios, instructor, ubicación y cupos, validando conflictos de horario. | Coordinador | 3 | **Cumplido** | `GrupoController`, `GuardarGrupoRequest`, `Grupo`, `Horario`, `GrupoPolicy`, `ServicioConflictos`, `HorarioController` (aviso en vivo) y vistas `grupos/`. Pruebas: `crea un grupo con sus horarios en el semestre activo`, `rechaza dos horarios que se traslapan y no deja nada a medias`, `deja al instructor ver solo los grupos que imparte`, `detecta que el instructor ya tiene otro grupo en ese horario`, `no elimina un grupo que ya tiene alumnos inscritos` |
| RF-09 | Inscribir alumnos validando cupo, semestre activo e inscripción única activa. | Alumno | 4 | Pendiente | Consumirá `Semestre::inscripcionesAbiertas()`, ya implementado en el Sprint 2 |
| RF-10 | Enviar correo de confirmación de inscripción. | Sistema | 4 | Pendiente | Ver RNF-06 y [RF propuesto](#5-requisitos-que-conviene-proponer) |
| RF-11 | Dar de baja a alumnos conservando el historial. | Coordinador | 4 | Pendiente | — |
| RF-12 | Registrar el desempeño del alumno y actualizar estatus y créditos. | Instructor / Sistema | 5 | Pendiente | Exige transacciones (ver RNF-03) |
| RF-13 | Mostrar paneles por rol y reportes administrativos. | Coordinador / Administrador | 6 | Pendiente | — |
| RF-14 | Exportar listas de alumnos en CSV y PDF. | Instructor | 6-7 | Pendiente | — |
| RF-15 | Generar horarios y constancias de acreditación en PDF. | Alumno | 7 | Pendiente | — |

---

## 2. Requisitos no funcionales (Tabla 7)

| ID | Tipo | Descripción | Cumplimiento | Evidencia o nota |
|---|---|---|---|---|
| RNF-01 | Seguridad | Contraseñas cifradas con Bcrypt, protección CSRF/XSS y control de acceso por rol en el servidor. | **Cumplido** | Cast `hashed` (Bcrypt) en `User.php:41`; `@csrf` en las 11 vistas con formularios; Blade escapa la salida por defecto; el acceso se valida en el servidor con permisos Spatie en rutas, `UserPolicy` y middleware. Prueba: `guarda la contraseña cifrada en la columna contrasena` |
| RNF-02 | Usabilidad | Interfaz en español, responsiva y con mensajes de validación claros. | **Cumplido** | `APP_LOCALE=es` y `APP_FALLBACK_LOCALE=es`; `lang/es/validation.php` con los mensajes en español; Tailwind 4 con diseño responsivo; errores junto a cada campo. Pruebas: `valida el registro: correo y número de control únicos, contraseña segura`, `valida el nombre y la capacidad` |
| RNF-03 | Integridad | Uso de transacciones y llaves foráneas para evitar cupos o créditos inconsistentes. | **Cumplido en usuarios, actividades y grupos · pendiente en cupos y créditos** | 6 transacciones: `UsuarioController.php:67` (alta), `:111` (edición), `:142` (baja), `RegistroController.php:26`, `ActividadController` (alta y edición con carreras) y `GrupoController` (grupo con horarios). Llaves foráneas en todas las migraciones, con `cascade` en `horario.id_grupo` y en `actividad_carrera`. Pruebas: `no crea nada si falla una parte del alta (transacción)`, `rechaza dos horarios que se traslapan y no deja nada a medias`. **Pendiente:** control de cupos (Sprint 4) y cálculo de créditos (Sprint 5) |
| RNF-04 | Portabilidad | Acceso desde navegadores modernos (Chrome, Firefox, Edge) sin instalación. | **Cumplido** | Blade + Tailwind 4 compilado por Vite; sin dependencias de JavaScript de terceros ni plugins que requieran instalación |
| RNF-05 | Mantenibilidad | Arquitectura MVC, migraciones versionadas y código en repositorio Git. | **Cumplido** | Separación MVC de Laravel; 16 migraciones versionadas con fecha; repositorio Git en GitHub con `main` siguiendo `origin/main` |
| RNF-06 | Disponibilidad | Si falla el servicio de correo, la operación principal se completa y el error se registra en el log. | **Aplica en el Sprint 4** | Hoy no aplica: no se envía correo. Cuando se implemente RF-10, la inscription debe confirmarse **antes** de notificar, con el envío en cola (`QUEUE_CONNECTION`) y el error registrado en `storage/logs` |

---

## 3. Trazabilidad RF ↔ HU

El Product Backlog oficial (sección 4.4, Tabla 8) tiene 27 historias con su estimación; la Tabla 6 tiene
15 requisitos. Ninguna tabla los vinculaba, así que aquí se cruzan.

| RF | Historias que lo cubren | Sprint |
|---|---|---|
| RF-01 | HU-01 Iniciar sesión | 1 |
| RF-02 | HU-02 Registrar usuarios con rol · HU-03 Consultar y buscar usuarios · HU-04 Editar y activar/desactivar · HU-05 Eliminar usuarios | 1 |
| RF-03 | HU-06 Asignar coordinadores a departamentos | 1 |
| RF-04 | HU-07 Actualizar mi perfil | 1 |
| RF-05 | HU-08 Gestionar departamentos · HU-09 Gestionar carreras · HU-10 Gestionar ubicaciones | 2 |
| RF-06 | HU-11 Registrar semestre y ventana de inscripción · HU-12 Consultar semestres · HU-13 Cierre automático del semestre | 2 |
| RF-07 | HU-14 Gestionar actividades complementarias | 3 |
| RF-08 | HU-15 Gestionar grupos y horarios · HU-16 Verificar conflictos de horario | 3 |
| RF-09 | HU-17 Inscribirse a un grupo | 4 |
| RF-10 | HU-18 Recibir correo de confirmación | 4 |
| RF-11 | HU-19 Dar de baja a alumnos | 4 |
| RF-12 | HU-20 Registrar calificación · HU-21 Actualizar créditos acumulados | 5 |
| RF-13 | HU-22 Consultar dashboard del departamento · HU-23 Consultar reportes administrativos | 6 |
| RF-14 | HU-24 Exportar lista de alumnos en CSV · HU-25 Descargar lista de asistencia en PDF | 6-7 |
| RF-15 | HU-26 Descargar horario en PDF · HU-27 Descargar constancia de acreditación | 7 |

**Balance de los sprints entregados:** 18 de 18 historias de los sprints 1, 2 y 3 (22 + 19 + 18 = 59
puntos) **salvo HU-07**, que quedó sin implementar y es el único faltante entre RF-01 y RF-08.

---

## 4. Brechas detectadas

| # | Brecha | Impacto | Qué se propone |
|---|---|---|---|
| 1 | **RF-04 / HU-07 «Actualizar mi perfil» no existe en el código.** | Es el único requisito de los sprints 1 y 2 incumplido, y está declarado tanto en la Tabla 6 como en la Tabla 8. Es visible para el asesor. | Implementarlo (pantallas de perfil por rol + cambio de contraseña) o moverlo formalmente a un sprint posterior con autorización del coordinador. **Decidir antes de la entrega** |
| 2 | **RF-03 es parcial:** se asigna coordinador al crear o editar el usuario, pero no hay acción para quitarlo sin editar la cuenta completa (lo que pedía HU-06). | Menor, pero la HU y el RF hablan de «asignar y remover». | Un panel o una acción específica en el listado de usuarios |
| 3 | **RNF-03 se cumple con transacciones en usuarios, actividades y grupos.** | El requisito menciona expresamente «cupos o créditos inconsistentes», que no existen todavía. | Reutilizar el patrón en el Sprint 4 (cupos) y el Sprint 5 (créditos) |
| 4 | **La recuperación de contraseña no está en la Tabla 6.** El README del Sprint 1 la dejó «fuera de alcance, requiere SMTP (Sprint 4)», pero ningún requisito formal la respalda. | Si el asesor la pide, no hay respaldo documental. | Ver RF propuesto abajo |
| 5 | **El `log_acceso` del Sprint 0 no lo pide ningún requisito**, aunque HU-23 habla de «reportes administrativos». | Requisito informal: la tabla existe en el modelo de datos pero nadie la pidió. | Ver RF propuesto abajo |
| 6 | **El criterio 8 de HU-15 («solo se comprueba que no tenga horarios») chocaba con el esquema.** La llave `horario.id_grupo` es `cascade` y el formulario exige al menos un horario, así que aplicarlo al pie de la letra haría indeleble a todo grupo. | Solo afectaba la documentación de la HU; el comportamiento es coherente con el resto del sprint. | **Resuelto:** se reescribió el criterio 8 (opción A, 2026-10-20). Ver [`decisiones/borrado-de-grupos.md`](decisiones/borrado-de-grupos.md) |

---

## 5. Requisitos que conviene proponer

Estos no están en el documento original; se proponen para que el equipo los valore y, si se aceptan,
se agreguen a la Tabla 6 antes de la entrega.

| ID propuesto | Requisito | Actor | Sprint sugerido | Motivo |
|---|---|---|---|---|
| **RF-16** | Recuperar la contraseña mediante correo con token de un solo uso. | Todos | 4 | El Sprint 1 lo pospuso por depender de SMTP (RF-10). Como el correo de confirmación se implementa en el Sprint 4, el servicio ya existirá y el requisito puede cerrarse ahí mismo |
| **RF-17** | Registrar los accesos al sistema en una bitácora consultable por el administrador. | Administrador | 6 | La tabla `log_acceso` viene del Sprint 0 y HU-23 pide reportes administrativos; hoy nada la exige |

---

## 6. Cómo mantener este documento

- **Al cerrar cada sprint:** actualiza la columna **Estado** de los requisitos del sprint y añade las
  brechas nuevas a la sección 4.
- **Si se implementa RF-04 o RF-16:** márcalos como `Cumplido` con su evidencia y ciérralos en la
  sección 4 o en la 5, según el caso.
- **Si el asesor acepta un requisito propuesto:** agrégalo como RF-16/RF-17 a la Tabla 6 del
  documento oficial.
- **Si cambia el alcance:** actualiza también `docs/bitacora.md` y el documento del sprint afectado.