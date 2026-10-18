# Bitácora de cambios

Registro cronológico del proyecto. **Solo se agrega al principio; nunca se reescribe una entrada.**

Formato de cada entrada: fecha · tipo · resumen · archivos · verificación.
Tipos: `sprint` · `cambio` · `fix` · `infra` · `documentación`.

---

## 2026-10-18 · documentación

**Resumen:** se documentó en el proyecto el documento `Requisitos del sistema.docx` (secciones 4.3.1
y 4.3.2, Tablas 6 y 7), con sus 15 requisitos funcionales y 6 no funcionales, y se cruzó cada uno
contra el código real.

**Contenido de `docs/requisitos.md`:** los RF y los RNF con la redacción original del documento más
dos columnas agregadas (**Estado** y **Evidencia**), una tabla de trazabilidad RF ↔ HU de las 27
historias del Product Backlog, una sección de brechas detectadas, dos requisitos que conviene proponer
(RF-16 recuperación de contraseña, RF-17 bitácora de accesos) y la guía de mantenimiento.

**Brechas registradas:**

1. **RF-04 / HU-07 «Permitir a cada usuario actualizar su perfil» NO está implementado.** Es el único
   faltante de los sprints 1 y 2: no existe ninguna ruta ni vista de perfil. Está declarado en la
   Tabla 6 y en la Tabla 8 del documento oficial, así que es visible para el asesor.
2. **RF-03 es parcial:** el coordinador se asigna al crear o editar el usuario
   (`UsuarioController.php:191`), pero no existe una acción para quitarlo, que es lo que pedía HU-06.
3. **RNF-03 se cumple solo en el módulo de usuarios.** Corregido en esta revisión: hay 4
   transacciones (`UsuarioController.php:67`, `:111`, `:142` y `RegistroController.php:26`) más las
   llaves foráneas de todas las migraciones. Falta extender el patrón a cupos (Sprint 4) y créditos
   (Sprint 5), que es donde el requisito busca evitar inconsistencias.
4. La recuperación de contraseña no figura en la Tabla 6, aunque el Sprint 1 la pospuso por
   depender de SMTP.
5. La tabla `log_acceso` del Sprint 0 no la pide ningún requisito, aunque HU-23 habla de reportes
   administrativos.

**Archivos:**
- Nuevo: `docs/requisitos.md`
- Modificados: `docs/README.md` (índice), `docs/bitacora.md`,
  `docs/sprints/sprint-01-usuarios-y-roles.md` y `docs/sprints/sprint-02-catalogos-y-semestres.md`
  (enlaces al documento de requisitos)

**Verificación:** los estados y evidencias se comprobaron contra el código (rutas, vistas,
controladores y nombres reales de las pruebas Pest). Enlaces de `docs/` revisados. Sin cambios de
código ni de base de datos; no aplica `php artisan test`.

---

## 2026-10-18 · documentación (formato de tablas)

**Resumen:** nuevo `docs/sprints/sprint-02-historias-de-usuario-y-crc-tablas.md` con el mismo contenido corregido
de las historias de usuario y las tarjetas CRC, pero con cada historia en **una sola tabla** (los criterios de
aceptación van dentro) y el encabezado en gris `#D1D5DB`. Las tablas son HTML porque Markdown no admite color ni
celdas combinadas; GitHub ignora el color de fondo, la vista previa de VS Code y las exportaciones lo respetan.

**Archivos:** nuevo `docs/sprints/sprint-02-historias-de-usuario-y-crc-tablas.md` y `docs/bitacora.md`.
No reemplaza a `sprint-02-historias-de-usuario-y-crc.md`.

**Verificación:** el contenido se copió del archivo ya contrastado con el código. Sin cambios de código ni de base de datos.

---

## 2026-10-18 · documentación

**Resumen:** se revisó `docs/sprints/sprint-02-historias-de-usuario-y-crc.md` contra el código del Sprint 2
(modelos, controladores, Form Requests, vistas, rutas, seeders y pruebas) y se corrigieron las
discrepancias. A partir del archivo corregido se generó el `.docx` de la sección 4.6.

**Correcciones (todas verificadas en el código):**

- **CRC `Departamento`:** «declarar qué instructor lo coordina» → «qué coordinador tiene asignado»
  (la relación es `CoordinadorDepartamento`, un usuario con rol coordinador, no un instructor).
- **CRC `Semestre`:** se quitó «Consola de artisan» de los colaboradores (la consola invoca al
  comando, no a la clase) y se distinguieron la etiqueta larga y la corta.
- **CRC `Carrera`:** el modelo solo declara la relación `alumnos()`; el conteo lo hace `withCount`
  en el listado.
- **CRC `SemestreController`:** el estado de las inscripciones se muestra en el listado, no en el detalle.
- **HU-11, criterio 1:** el formulario de alta se bloquea cuando hay un periodo activo, pero el servidor
  solo impide tener dos periodos *activos*; sí acepta registrar uno inactivo (la prueba
  «solo permite un periodo activo a la vez, pero sí uno inactivo» lo exige).
- **HU-11, criterios 3 y 7:** se precisó «meses completos» y que el límite de calendarios es solo de pantalla.
- **HU-12, criterio 4:** los periodos históricos muestran un candado de «solo lectura»; el motivo
  aparece al intentar abrir su edición.
- **HU-13, criterio 3:** el cierre de respaldo también corre al abrir la edición y antes de validar cualquier guardado.
- **Tabla 10:** HU-08 y HU-09 ya no listan «Migración y modelo» (existen desde el Sprint 1) y HU-11 ya no
  dice «cinco validaciones» (son seis reglas de negocio).
- **Notas:** `UsuarioPolicy` → `UserPolicy` (nombre real de la clase).

**Sin verificar (no se deduce del código):** reparto de horas, responsables, estimaciones de Planning Poker
y numeración de tablas; dependen del documento oficial de requerimientos.

**Archivos:** `docs/sprints/sprint-02-historias-de-usuario-y-crc.md` y `docs/bitacora.md`.
Se generó además `Sprint2_Historias_de_Usuario_y_CRC.docx` (fuera del repositorio).

**Verificación:** contraste manual con el código y con `SemestresTest`. Sin cambios de código ni de base de
datos; no aplica `php artisan test`.

---

## 2026-10-17 · documentación

**Resumen:** la documentación del Sprint 2 se rehízo para adoptar el formato del documento oficial de
requerimientos (`Historias de usuario y tarjetas crc Sprint 1.docx`), y se unificó en un solo archivo.

**Qué cambió respecto a la entrada anterior del mismo día:** los dos archivos markdown
(`sprint-02-historias-de-usuario.md` y `sprint-02-tarjetas-crc.md`) se sustituyen por uno solo,
`docs/sprints/sprint-02-historias-de-usuario-y-crc.md`, que replica la sección **4.6** del documento
oficial con la misma estructura de la sección 4.5 (Sprint 1):

- **4.6.1 Planificación del Sprint** — Sprint Goal y Tabla 10 (Sprint Backlog: HU, Tareas,
  Responsable, Horas). Total: 19 puntos de historia y 52 horas estimadas, asignadas a Edwin.
- **4.6.2 Historias de usuario** — las 6 historias reales del Product Backlog oficial (**HU-08 a
  HU-13**) en el formato de tabla `Como / Quiero / Para` + `Prioridad / Estimación` + criterios
  numerados. Se abandonó el formato Given/When/Then y los identificadores propios (CAT-01, SEM-02,
  VAL-03).
- **4.6.3 Tarjetas CRC** — 10 tarjetas en el formato de 2 columnas del documento oficial
  (`Responsabilidades` / `Colaboradores`), siguiendo la línea del Sprint 1: 4 modelos, 4
  controladores, `GuardarSemestreRequest` y el comando `CerrarSemestresVencidos`.

**Observación sobre el Sprint 1** que conviene revisar al reescribir su sección 4.5: el documento
oficial describe **HU-07 «Actualizar mi perfil»**, que no tiene implementación (no hay vistas de
perfil por rol), y nombra al middleware `VerificarUsuarioActivo`, mientras que en el código es
`EnsureUsuarioActivo`.

**Archivos:**
- Nuevo: `docs/sprints/sprint-02-historias-de-usuario-y-crc.md`
- Eliminados: `docs/sprints/sprint-02-historias-de-usuario.md`, `docs/sprints/sprint-02-tarjetas-crc.md`
- Modificados: `docs/README.md` (índice por sprint), `docs/bitacora.md`, y
  `docs/sprints/sprint-02-catalogos-y-semestres.md` (enlace «Ver también»)

**Verificación:** enlaces de `docs/` revisados. Sin cambios de código ni de base de datos; no aplica
`php artisan test`.

---

## 2026-10-17 · documentación

**Resumen:** documentación formal del Sprint 2 (Catálogos base y semestres): historias de usuario y
tarjetas CRC, para la entrega al asesor interno y al residente.

**Historias de usuario** (`docs/sprints/sprint-02-historias-de-usuario.md`): las 3 del cronograma
oficial — CAT-01 (CRUD de departamentos, carreras y ubicaciones), SEM-02 (control de semestres con
apertura y cierre automático) y VAL-03 (duración de 3 a 5 meses y ventana de inscripción de 5 a 10
días). Cada criterio de aceptación está en formato Given/When/Then y va ligado a su archivo
(`archivo:línea`) y a la prueba Pest que lo cubre, en una tabla de trazabilidad.

**Tarjetas CRC** (`docs/sprints/sprint-02-tarjetas-crc.md`): 5 tarjetas (Clase · Responsabilidad ·
Colaborador) extraídas del código real — `Semestre`, `Departamento`, `Carrera`, `Ubicacion` y el
comando `CerrarSemestresVencidos` — más un mapa de dónde vive cada regla del sprint, tres
observaciones de diseño y un diagrama de clases en Mermaid.

**Hallazgos documentados honestamente:**
1. No existe Policy para estos catálogos: el acceso es 100 % permisos Spatie en rutas más `Gate` en
   los Form Requests (el único archivo en `app/Policies/` es `UserPolicy` del Sprint 1).
2. Las reglas de borrado viven en los controladores, no en los modelos; por eso `Ubicacion` quedó sin
   comprobación previa de dependencias, a diferencia de `Departamento` y `Carrera`.
3. `GuardarSemestreRequest` escribe en la base de datos durante la validación
   (`prepareForValidation()` y `validarEstatus()`).

**Archivos:**
- Nuevos: `docs/sprints/sprint-02-historias-de-usuario.md`, `docs/sprints/sprint-02-tarjetas-crc.md`
- Modificados: `docs/README.md` (índice por sprint), `docs/bitacora.md`

**Verificación:** sin cambios de código ni de base de datos; enlaces de `docs/` revisados.
No aplica `php artisan test`.

---

## 2026-10-18 · cambio

**Resumen:** mejora visual de las vistas con animación de botones y logo del ITO como marca de agua en el acceso.

**Incluye:**
- Componentes `x-boton`, `x-accion` y `x-barra-catalogo`: color, pulsación y gesto del icono unificados; los
  listados de departamentos, carreras y ubicaciones usan cabecera gris, total, «Limpiar» en la búsqueda y
  mensaje de vacío que dice qué se buscó. Editar pasa a botón secundario y el ámbar queda solo para periodos
  (regla de `DESIGN.md`); eliminar usa el rojo de peligro.
- Movimiento opt-in por clase `mov-*` (botones, enlaces de la barra y de las tablas, foco del buscador, banda de
  aviso en el layout `app`) con ruta en `prefers-reduced-motion`. Los «Guardar» de todos los formularios (incluido
  usuarios y roles) acusan el envío como el login. `DESIGN.md` amplía su sección **Motion** y `PRODUCT.md`
  registra que ya hay logo.
- Marca de agua: `resources/logos/…png` como fondo fijo de login y registro (`.fondo-logo`, opacidad 0.08,
  `multiply`). Vite procesa la ruta; no se copia nada a `public/`.

**Archivos:**
- Nuevos: `components/{boton,accion,barra-catalogo}.blade.php`
- Modificados: `resources/css/app.css`, `components/{icono,tarjeta-formulario}`, `components/layouts/{app,guest}`,
  `{departamentos,carreras,ubicaciones}/index`, `semestres/{index,show}`, `usuarios/{index,create,edit,show}`,
  `roles/index`, `auth/{login,registro}`, `DESIGN.md`, `PRODUCT.md`

**Verificación:**
- Sprint 2 sobre un sandbox con PHP 8.4: 122 pruebas en verde; `/login` y `/registro` renderizan con el logo
- `vite build` del CSS: compila, el logo queda con huella y existen las clases nuevas
- Pendiente: `npm run build` o `npm run dev` y revisión visual en navegador (no se pudo en el sandbox); correr la
  suite completa en local, porque el sandbox no tenía las pruebas de login más recientes

---

## 2026-10-17 · cambio

**Resumen:** las pantallas de acceso (login y registro) reciben un lenguaje de movimiento propio: un único
momento con autoría —la banda de aviso se barreja y la tarjeta se asienta encima— y los estados que lo
sostienen (envío en curso, foco, error de campo, Bloq Mayús, icono de la contraseña, desglose de soporte).
`DESIGN.md` gana una sección **Motion** que antes le contradecía al prohibir las microinteracciones.

- Tokens de tiempo y curva en el bloque `@theme` de `resources/css/app.css`: `--dur-feedback` (`120ms`),
  `--dur-estado` (`200ms`), `--dur-llegada` (`360ms`), `--ease-llegada` y `--ease-retirada`. Cero
  dependencias: nada de librerías de animación.
- Momento focal: `clip-path` sobre la banda (`mov-banda`) y `translateY(-0.5rem)` con sombra que se cierra
  sobre la tarjeta (`mov-tarjeta`). En un rebote por credenciales la secuencia cuenta lo que pasó.
- `mov-enviar`: al enviar, el botón toma `aria-busy="true"`, se deshabilita (bloquea el doble envío) y su
  borde inferior se ilumina con una barra indeterminada de 2px. El texto no cambia, cambia su estado.
- Los iconos del botón de contraseña se apilan en la misma celda (`grid`) y se cruzan por opacidad en vez de
  `hidden`: el botón deja de cambiar de ancho y el estado se lee en `aria-pressed`. `app.js` deja de
  alternar `hidden`.
- Gancho de alcance `data-acceso` en el `<main>` del layout de invitado: el movimiento de acceso no puede
  filtrarse a las pantallas de administración. `x-campo` y `x-campo-contrasena` solo suman los atributos
  `data-error-de-campo` y `data-bloq-mayus`, que CSS usa únicamente dentro de `[data-acceso]`.
- Estados por defecto visibles: ninguna animación depende de JavaScript para revelar contenido, y todas se
  montan con `backwards`. Ruta completa en `prefers-reduced-motion: reduce`: sin desplazamiento ni barrido,
  con los estados que significan algo intactos.
- Superficies del navegador tematizadas en el mismo ámbito: `caret-color` y `accent-color` en `azul-ito`,
  `::selection` en `azul-ito-tenue`.

**Fuera de alcance:** no hay movimiento en listados, formularios de administración ni en la banda del periodo
activo; esas pantallas conservan la quietud que fija `DESIGN.md`. Tampoco se usó View Transitions entre login y
registro: la regla `@view-transition { navigation: auto; }` es global y habría alterado la navegación de todo
el producto para ganar continuidad en dos pantallas.

**Archivos:**
- Nuevos: `tests/Feature/AccesoMovimientoTest.php`
- Modificados: `resources/css/app.css`, `resources/js/app.js`, `resources/views/auth/login.blade.php`,
  `resources/views/auth/registro.blade.php`, `resources/views/components/layouts/guest.blade.php`,
  `resources/views/components/campo.blade.php`, `resources/views/components/campo-contrasena.blade.php`,
  `DESIGN.md`

**Verificación:** `php artisan test` en verde (139 pruebas, antes 132), `vendor\bin\pint --test` sin
observaciones, `npm run build` compila y los tokens y reglas sobreviven a Tailwind en el CSS generado,
detector de diseño sin hallazgos. Sin navegador de automatización disponible en el entorno, la revisión
visual se hizo sobre el HTML servido por `php artisan serve` y el CSS compilado, no sobre capturas.

---

## 2026-10-17 · cambio

**Resumen:** se rehacen las pantallas de acceso (login y registro) después de una revisión de diseño con
herramienta: el error de autenticación ya no cuelga bajo el campo de correo, existe una salida para quien no
puede entrar y el servidor es la única fuente de mensajes.

- `LoginRequest::ERROR_CREDENCIALES` (`credenciales`): credenciales incorrectas, cuenta desactivada y bloqueo
  por límite de intentos viajan en su propia clave de error, no en `email`. Antes el mensaje de bloqueo
  —«Demasiados intentos de acceso…»— aparecía bajo el campo de correo, dirigiendo al usuario a revisar lo que
  estaba bien.
- Nuevo componente `x-mensaje` (banda con borde, `role="status"` para éxito y `role="alert"` para el resto).
  Los layouts `app` y `guest` lo usan como única región de avisos; `app` deja de tener las bandas escritas a mano.
- `guest.blade.php` gana región de mensajes, landmark `<main>` y baja el nombre de la aplicación de
  `text-2xl` a `text-xl`: era el texto más grande del producto en la pantalla con menos contenido útil, y
  `DESIGN.md` fija `1rem`–`1.5rem` como techo de la escala.
- Login y registro llevan `novalidate`: el navegador ya no lanza su burbuja en el idioma del navegador, y todo
  el texto de error sale de `lang/es`, ya traducido.
- Login suma un bloque `<details>` «¿No puedo entrar?» con el camino de salida. El correo de soporte viene de
  `config('contacto.soporte_email')` (`CONTACTO_SOPORTE_EMAIL` en `.env.example`, vacío por defecto): sin valor
  la pantalla explica a quién acudir sin inventar una dirección. La recuperación por SMTP sigue pendiente del
  Sprint 4 y este bloque es su sitio.
- `x-campo` y `x-seleccion` conectan su mensaje de error con el control (`aria-invalid`, `aria-describedby`,
  `id` en el `<p>` de error y en la ayuda). Antes el error era un `<p>` suelto: un lector de pantalla no
  anunciaba nada tras un envío fallido.

**Archivos:**
- Nuevos: `resources/views/components/mensaje.blade.php`, `config/contacto.php`
- Modificados: `resources/views/auth/login.blade.php`, `resources/views/auth/registro.blade.php`,
  `resources/views/components/layouts/guest.blade.php`, `resources/views/components/layouts/app.blade.php`,
  `resources/views/components/campo.blade.php`, `resources/views/components/seleccion.blade.php`,
  `app/Http/Requests/Auth/LoginRequest.php`, `tests/Feature/AutenticacionTest.php`,
  `.env.example`, `DESIGN.md`, `docs/contexto.md`
- Sin cambios: `.env` (no se toca)

**Verificación:** `php artisan test` → 130 pruebas en verde (8 nuevas); `vendor\bin\pint --test` sin
observaciones; `impeccable detect` sobre las seis vistas modificadas → sin hallazgos. Sin migraciones: el
cambio es de vistas, componentes y manejo de errores.

---

## 2026-10-17 · documentación

**Resumen:** se documentó el sistema de diseño del producto en `DESIGN.md` y `.impeccable/design.json`.

Tokens extraídos del código real (Tailwind 4 + Instrument Sans + familia `slate`, con `blue-700` como
azul institucional) y decisiones de voz acordadas: norte creativo «El Orden del Día», elevación «plano con
sombra funcional», componentes «directos y sin adornos». Queda marcado como pendiente el logotipo del
ITO: sigue sin haber archivo en el repositorio, así que el header actual conserva el nombre de la
aplicación.

**Archivos:**
- Nuevos: `DESIGN.md`, `.impeccable/design.json`
- Modificados: `docs/bitacora.md`

**Verificación:** sin cambios de código; no aplica `php artisan test` ni `pint --test`.

---

## 2026-10-17 · sprint

**Resumen:** integración del **Sprint 2 · Catálogos base y semestre**.

**Incluye:** CRUD de departamentos, carreras y ubicaciones (con buscador y bloqueo de eliminación si hay
registros relacionados); semestres con periodo activo, historial, reactivación del más reciente,
cierre automático de vencidos (`semestres:cerrar-vencidos`, diario 00:05) y estado de inscripciones;
validación de duración del semestre (3 a 5 meses) y de la ventana de inscripción (5 a 10 días).
Las vistas se copiaron del proyecto anterior (Stisla) y se reprodujeron en Tailwind. Detalle en
[`sprints/sprint-02-catalogos-y-semestres.md`](sprints/sprint-02-catalogos-y-semestres.md).

**Archivos:**
- Nuevos: 4 controladores, 4 requests, 2 modelos (`Ubicacion`, `Semestre`), 1 comando, 2 migraciones,
  17 vistas, 3 componentes Blade y 5 archivos de pruebas
- Modificados: `routes/web.php`, `routes/console.php`, `RolesYPermisosSeeder` (+15 permisos),
  `CatalogosBaseSeeder` (+5 ubicaciones), `lang/es/validation.php`, layout, panel, `seleccion`,
  `Escenarios`, `RolesPermisosTest` (totales de permisos 6 → 21)

**Verificación:**
- `php artisan migrate:fresh --seed` sin errores
- `php artisan test` → 122 pruebas, 474 aserciones, todas en verde (51 previas + 71 nuevas)
- `vendor/bin/pint --test` sin observaciones
- Pendiente: revisar las pantallas en navegador con `npm run dev` y fijar `APP_TIMEZONE` en `.env`

---

## 2026-10-17 · documentación

**Resumen:** se creó `docs/` con la documentación que leen las demás IAs y el residente, y se
versionaron `AGENTS.md` y `CLAUDE.md` con un puntero a esa documentación.

**Archivos:**
- Nuevos: `docs/README.md`, `docs/contexto.md`, `docs/convenciones.md`,
  `docs/sprints/sprint-01-usuarios-y-roles.md`, `docs/bitacora.md`
- Modificados: `README.md` (sustituido el de fábrica de Laravel), `AGENTS.md`, `CLAUDE.md`,
  `.gitignore` (se dejan de ignorar `AGENTS.md` y `CLAUDE.md`)

**Verificación:** enlaces internos de `docs/` revisados, `vendor\bin\pint --test` sin observaciones.

---

## 2026-10-17 · sprint

**Resumen:** integración del **Sprint 1 · Usuarios y Roles** sobre el esqueleto de Laravel.

**Incluye:** autenticación (login con límite de 5 intentos por correo+IP, logout, recordar sesión,
registro público solo para alumnos, cierre de sesión de cuentas desactivadas), CRUD de usuarios
para el administrador, 4 roles y 6 permisos con Spatie, y acceso por rol y por departamento
(`UserPolicy` + middleware). Detalle completo en
[`sprints/sprint-01-usuarios-y-roles.md`](sprints/sprint-01-usuarios-y-roles.md).

**Archivos:**
- 40 archivos del paquete `Sprint1_Usuarios_y_Roles` (controladores, requests, middleware, política,
  5 modelos, 3 seeders, 14 vistas, `lang/es`, `config/permission.php`, 4 archivos de prueba)
- `composer.json` / `composer.lock` — se agregó `spatie/laravel-permission` 8.3.0
- `.env` y `.env.example` — `APP_NAME="Actividades Complementarias ITO"`, `APP_LOCALE=es`
- `tests/Feature/ExampleTest.php` — ahora espera redirección a `/login` en vez de `200`

**Verificación:**
- `php artisan migrate:fresh --seed` → 9 migraciones y 3 seeders sin errores; 4 usuarios, 4 roles,
  6 permisos, 5 departamentos, 10 carreras
- `php artisan test` → 51 pruebas, 172 aserciones, todas en verde
- `vendor\bin\pint` → aplicado (sin observaciones)
- Recorrido HTTP real: login de `admin@gmail.com` → panel con lista de usuarios (200)
- `npm run build` → assets compilados

---

## 2026-10-17 · infra

**Resumen:** el proyecto Laravel 13 se subió por primera vez a
`https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO` (commit inicial `12356fb`).
El repositorio git estaba en la carpeta padre, así que se movió a la raíz del proyecto para que los
archivos queden en la raíz del repo. `AGENTS.md`, `CLAUDE.md` y `.agents/` se ignoraron en ese
momento (el 2026-10-17 se revertió para `AGENTS.md` y `CLAUDE.md`).

**Verificación:** `git status` limpio y `main` siguiendo `origin/main`.