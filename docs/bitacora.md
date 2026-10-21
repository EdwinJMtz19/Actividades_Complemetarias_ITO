# Bitácora de cambios

Registro cronológico del proyecto. **Solo se agrega al principio; nunca se reescribe una entrada.**

Formato de cada entrada: fecha · tipo · resumen · archivos · verificación.
Tipos: `sprint` · `cambio` · `fix` · `infra` · `documentación`.

---

## 2026-10-20 · documentación

**Resumen:** resolución de la decisión abierta sobre el borrado de grupos (**opción A: corregir la
documentación; el código no cambia**) y cierre del único pendiente del Sprint 3.

**Cambios:**

| Archivo | Qué cambió |
|---|---|
| `tests/Feature/GruposCrudTest.php` | Nueva prueba `elimina un grupo con horarios y se lleva sus horarios en cascada` (el borrado de un grupo con horarios es válido y el `cascade` limpia `horario`). |
| `docs/sprints/sprint-03-historias-de-usuario-y-crc.md` | Criterio 8 de HU-15 reescrito: mientras no exista `inscripcion` (Sprint 3) el grupo se elimina con sus horarios; la verificación contra inscripciones se activa en el Sprint 4. |
| `docs/decisiones/borrado-de-grupos.md` | Estado «resuelta · opción A»; resolución actualizada con la prueba ejecutada. |
| `docs/sprints/sprint-03-actividades-y-grupos.md` | «Decisión pendiente» → decisión resuelta; pendiente #1 del §9 marcado como resuelto. |
| `docs/sprints/sprint-03-plan.md` | Ya no queda «una decisión abierta»: la decisión quedó resuelta. |
| `docs/contexto.md`, `docs/requisitos.md` | Regla 17 y requisito informal 6 actualizados a «resuelto». |
| `docs/README.md`, `AGENTS.md`, `CLAUDE.md`, `README.md` | Se actualiza el conteo a **205 pruebas** y el enlace a la decisión (resuelta, ya no «abierta»). |

**Verificación:** `php artisan test` → **205 pruebas, 801 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones.

---

## 2026-10-20 · documentación

**Resumen:** mejora general de la documentación siguiendo el patrón «docs en el repo, contexto IA
corto y changelog para el usuario». **No hubo cambios de código.**

**Cambios:**

| Archivo | Qué cambió |
|---|---|
| `.gitignore` | Se dejaron de ignorar `*.md`, `/docs`, `/AGENTS.md` y `/CLAUDE.md`: ahora la documentación se versiona. Siguen ignorados los artefactos de herramientas (`/.agents`, `/.opencode`, `/.claude`, `boost.json`, `.mcp.json`, `/skills-lock.json`, `/.impeccable`). |
| `README.md` (raíz) | Actualizado: sprints 1–3 entregados, 204 pruebas, PHP 8.4.1+, MySQL 8 como motor, alcance real de instructor y alumno, enlaces a `docs/`, `CHANGELOG.md`, `PRODUCT.md`, `DESIGN.md` y `CONTRIBUTING.md`; quitado el `composer require` manual y añadida la vía Docker. |
| `AGENTS.md` / `CLAUDE.md` | Recortados: comandos detallados, cuentas de prueba y la ruta de Herd Lite ahora se referencian a `docs/README.md` en lugar de duplicarse; se añadió el recordatorio de reflejar cambios visibles en `CHANGELOG.md`. `CLAUDE.md` es copia idéntica. |
| `CHANGELOG.md` | **Nuevo.** Historial público por sprint (0.1.0, 0.2.0, 0.3.0) en lenguaje para quien usa el sistema; la bitácora sigue siendo el registro técnico. |
| `CONTRIBUTING.md` | **Nuevo.** Flujo corto de contribución (leer contexto → cambiar con prueba → verificar → documentar) que apunta a `docs/convenciones.md`. |
| `.github/workflows/ci.yml` | **Nuevo.** CI en GitHub Actions: PHP 8.4, `composer install`, `.env` de prueba, `php artisan test` y `vendor/bin/pint --test` en cada push a `main` y en cada PR. |
| `docs/` | `sprint-03-plan.md` y `resumen-sprint-2-y-mejoras.md` movidos de la raíz de `docs/` a `docs/sprints/`; corregidos sus enlaces relativos y los que apuntaban a ellos. |
| `docs/bitacora.md` | La entrada que estaba sin encabezado (pendientes del Sprint 2) recibió `## 2026-10-18 · documentación`; esta entrada cierra el cambio. |
| `docs/README.md` | Índice actualizado: enlaces a `README.md`, `CHANGELOG.md` y `CONTRIBUTING.md` de la raíz, y el documento de resumen del Sprint 2 enlazado en su fila. |

**Verificación:** `php artisan test` → **204 pruebas, 795 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones; `git ls-files -o --exclude-standard` confirma que
`docs/`, `AGENTS.md`, `CLAUDE.md`, `DESIGN.md`, `PRODUCT.md`, `CHANGELOG.md`, `CONTRIBUTING.md` y
`.github/` quedan visibles para git. **No se hizo commit** (pendiente de confirmación del equipo).

---

## 2026-10-20 · sprint

**Resumen:** se cerró el **Sprint 3 · Actividades, grupos y horarios** (HU-14, HU-15 y HU-16; 18
puntos). El código ya estaba implementado; en esta sesión se completó la cobertura de pruebas, se
desbloqueó la suite y se puso la documentación al día. Detalle completo en
[`sprints/sprint-03-actividades-y-grupos.md`](sprints/sprint-03-actividades-y-grupos.md).

**Pruebas (65 casos nuevos, 139 → 204):**

| Archivo | Casos |
|---|---|
| `tests/Feature/ActividadesCrudTest.php` | 13 |
| `tests/Feature/GruposCrudTest.php` | 15 |
| `tests/Feature/AccesoActividadesYGruposTest.php` | 6 |
| `tests/Feature/ConflictosEnVivoTest.php` | 7 |
| `tests/Feature/ServicioConflictosTest.php` | 24 (17 `it` con datasets) |

También se corrigió `TOTAL_PERMISOS` en `RolesPermisosTest` (21 → 29) y se documentó en
`contexto.md` y `convenciones.md` que las pruebas que pasan por formularios deben fijar la fecha con
`travelTo` para que `Semestre::cerrarVencidos()` no inactive el escenario.

**Verificación:** `php artisan test` → **204 pruebas, 795 aserciones, todas en verde**;
`vendor\bin\pint --test` sin observaciones; `php artisan migrate:fresh --seed` en **MySQL 8** con 16
migraciones y 5 seeders; `npm run build` correcto.

**Decisión documentada, no resuelta:** el criterio 8 de HU-15 («solo se comprueba que no tenga
horarios») contradice el `cascade` de `horario.id_grupo` y el `min:1` de `GuardarGrupoRequest`; el
código comprueba `cupo_ocupado > 0`, que hoy siempre es 0. Se creó
[`decisiones/borrado-de-grupos.md`](decisiones/borrado-de-grupos.md) con las tres opciones y la
recomendación (corregir el criterio). **No se tocó** `Grupo::tieneInscripciones()` ni
`GrupoController::destroy`, y no se escribió la prueba del borrado de un grupo con horarios para no
fijar una postura.

**Otros cambios:** `/skills-lock.json` se agregó a `.gitignore` (artefacto de herramientas de IA).

**Documentación actualizada:** `README.md`, `contexto.md`, `requisitos.md` (RF-07 y RF-08 a
*Cumplido*; RNF-03 y RNF-05 al día), `convenciones.md`, `AGENTS.md`, `CLAUDE.md`,
`sprint-03-plan.md` y el documento del sprint.

---

## 2026-10-18 · documentación

**Resumen:** se cerraron los pendientes que dejó el Sprint 2 y se corrigieron los conteos y estados
de sprint que estaban desactualizados. **No hubo cambios de código**: las dos decisiones pendientes
se resolvieron confirmando que el comportamiento actual ya era el correcto.

**Decisiones confirmadas por el equipo:**

1. **Horas de inscripción.** Las inscripciones abren a la hora del inicio y cierran a la hora del
   fin del periodo. Es exactamente lo que ya hace `Semestre::inscripcionesAbiertas()`, así que no
   se modificó código y las 139 pruebas siguen en verde. Queda pendiente solo confirmarlo con el
   residente antes del Sprint 4, donde la regla empieza a bloquear de verdad.
2. **Máximo de 5 meses del semestre.** Se mantiene la regla del proyecto anterior (meses completos,
   hasta 5 meses y 29 días) en lugar de hacerla estricta. Sin cambios en
   `GuardarSemestreRequest::validarDuracion()`.

**Pendientes que ya se habían resuelto y aquí quedan confirmados:**

3. **Zona horaria.** `APP_TIMEZONE=America/Mexico_City` ya está en el `.env` local y en las variables
   del servicio de Railway, así que el cierre automático de las 00:05 ocurre a la medianoche de
   Oaxaca y no seis horas antes.
4. **Columna `año` (con ñ).** Verificada en MySQL 8 con `utf8mb4` tanto en el contenedor local como
   en Railway: la columna resuelve como identificador, el round-trip por PDO devuelve los bytes
   `61 C3B1 6F` y «Ingeniería Eléctrica y Electrónica» se guarda con `C3AD` y `C3B3`. No hace falta
   renombrarla a `anio`.
5. **Revisión visual.** Se abrió el proyecto en un navegador y todo se ve en orden.

**Correcciones de documentación:**

| Archivo | Antes | Ahora |
|---|---|---|
| `AGENTS.md` y `CLAUDE.md` | Sprint actual 1 · Usuarios y Roles | Sprint actual 2 · Catálogos base y semestre; sigue el 3 |
| `AGENTS.md`, `CLAUDE.md`, `docs/README.md`, `docs/convenciones.md` | 51 o 122 pruebas | **139 pruebas** |
| `AGENTS.md`, `CLAUDE.md` | Tabla «Lee esto primero» | Se añadió `docs/contenedor.md` |
| `AGENTS.md`, `CLAUDE.md`, `docs/convenciones.md` | Solo comandos con PHP local | Se añadieron los de Docker, que ya es la vía más rápida |
| `docs/README.md` | Índice sin `contenedor.md` ni el documento de tablas | Ambos documentos listados |

No se reescribieron `docs/sprints/sprint-02-catalogos-y-semestres.md` ni `PRODUCT.md`: son registros
de lo que estaba pendiente en el momento de escribirse, y la bitácora ya recoge su resolución.

---

## 2026-10-18 · infra

**Resumen:** el contenedor se publicó en la nube como un **segundo servicio de Railway**, en
`https://actividades-contenedor-production.up.railway.app`, dentro del mismo proyecto
(`victorious-harmony`) y sin tocar el servicio de producción ya existente.

**Cómo se dejó:** variables copiadas del servicio de producción (`APP_KEY`, `APP_NAME`, `APP_ENV`,
`APP_TIMEZONE`, `DB_URL`, `DB_CONNECTION`, `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`,
`LOG_CHANNEL`) y `APP_DEBUG=false`. La conexión a la base se hace por `DB_URL`, que es lo que lee
`config/database.php:49`; no por `DATABASE_URL`. Los secretos se enviaron por entrada estándar para
no dejarlos en la línea de comandos.

**Dos tropiezos con Railway que conviene documentar:**

1. **La primera compilación falló** aunque el `Dockerfile` estaba bien. Railway ignoró el
   `Dockerfile` y usó **Railpack 0.41.0**, que eligió PHP 8.3.35 y se atascó en
   `composer install --optimize-autoloader --no-scripts`. La causa es que el servicio se creó
   apuntando a la rama `main` del repositorio (donde `composer.json` todavía pedía PHP 8.3), no a la
   rama `contenedor`: el parámetro `--branch` de `railway add` no se aplicó.
2. **La rama no se puede fijar por API.** La introspección de la API GraphQL de Railway muestra que
   `ServiceSource` y `ServiceSourceInput` solo tienen `image` y `repo`, sin campo `branch`, y la
   mutación `githubRepoDeploy` no recibe `serviceId`, por lo que desplegaría también al servicio de
   producción. Se resolvió desplegando el directorio local con `railway up --detach`, que sí afecta
   únicamente al servicio enlazado. Además, `railway up` dejó el `Caddyfile` escuchando en el puerto
   que Railway inyecta: `{$PORT:80}` en lugar de `80` fijo, sin lo cual el tráfico no llegaba.

**Consecuencia que hay que tener en cuenta:** como el servicio se despliega desde el código local y
no desde una rama fija, **los `push` a `contenedor` no lo redesplegan automáticamente**. Para volver
a publicar hay que ejecutar `railway up --detach` desde la rama `contenedor`.

**Verificación en la nube:** `/up` 200, `POST /login` redirige a `/panel`, y con sesión iniciada
`/panel`, `/usuarios`, `/roles` y `/usuarios/create` devuelven 200. Los assets sirven con los mismos
hash que la compilación local (`app-PycXxSRq.css`), lo que confirma que la imagen construyó el CSS
igual. `/.env`, `/composer.json` y `/vendor/autoload.php` devuelven 404. Se comprobó además que el
log de compilación ejecutó las líneas del `Dockerfile` (`install-php-extensions`, `COPY Caddyfile`,
`package:discover`), es decir, que esta vez sí se usó la imagen propia. El servicio de producción se
volvió a verificar al final y seguía respondiendo igual.

---

## 2026-10-18 · infra

**Resumen:** el proyecto se empaquetó en una imagen de Docker para poder levantarlo completo (aplicación
y base de datos) sin instalar PHP, Composer ni MySQL en la máquina, y publicarlo como contenedor en
la nube. Vive en la **rama `contenedor`**: `Mejoras_UI` sigue siendo la rama que Railway despliega y
no contiene archivos de contenedor, de modo que su despliegue actual no se altera.

**Archivos nuevos:** `Dockerfile`, `.dockerignore`, `docker-compose.yml`, `Caddyfile`,
`docs/contenedor.md`.

**Cómo está montado la imagen:** tres etapas. `assets` compila Tailwind con Node, porque
`public/build` está en el `.gitignore` y no viaja en el repositorio. `vendor` instala con Composer
solo dependencias de producción. La etapa final usa `dunglas/frankenphp:php8.4-bookworm`, que es
PHP 8.4 con el servidor Caddy en un mismo proceso, e instala `pdo_mysql`, `intl` y `bcmath`, las
únicas extensiones que la imagen base no trae y el proyecto sí necesita. Resultado: PHP 8.4.26 (ZTS),
Zend OPcache activo, imagen de 909 MB. `docker compose` levanta dos servicios: `app` en el puerto
8080 y MySQL 8 en el 3307, este último con `utf8mb4` porque la columna `año` lo necesita.

**Tres problemas que aparecieron y cómo se resolvieron:**

1. **El `package:discover` falló** con `Class "Laravel\Boost\BoostServiceProvider" not found`. No era un
   problema del `Dockerfile`: `bootstrap/cache/packages.php` y `services.php` de la máquina traían el
   manifiesto con Boost, y `COPY . .` los metió a la imagen. Como Boost es dependencia de desarrollo,
   el manifiesto pedía una clase que en producción no existe y la aplicación no arrancaba. Se
   resolvió **excluyendo las cachés generadas** (`bootstrap/cache/*.php` y
   `storage/framework/views/*`) en el `.dockerignore`.
2. **Todas las rutas devolvían 308.** La imagen base de FrankenPHP trae un `Caddyfile` con
   `auto_https` activado, que manda todo el tráfico a HTTPS. Ni el puerto 8080 en local ni Railway
   (donde el TLS lo termina el borde) sirven HTTPS al contenedor, así que se escribió un `Caddyfile`
   propio con `auto_https off` que expone únicamente `public/`.
3. **La contraseña de MySQL nunca entra en la imagen:** el `.env` está en el `.dockerignore` y se
   verificó con `ls /app` dentro del contenedor (0 resultados).

**Verificación:** `/up` 200, `/login` 200, `POST /login` redirige a `/panel` y con sesión iniciada
`/panel`, `/usuarios`, `/roles` y `/usuarios/create` devuelven 200. CSS de 46.2 KB con 529 reglas y
JS cargando. Datos: 21 tablas, 11 migraciones, 4 roles, 21 permisos, 22 asignaciones, 4 usuarios,
5 departamentos, 10 carreras, 5 ubicaciones. La ñ se comprobó con bytes: `año` resuelve como
identificador y el round-trip de PDO devuelve `61 C3B1 6F`, y "Ingeniería Eléctrica y Electrónica"
guarda `C3AD` y `C3B3` correctamente. Además `/.env`, `/composer.json` y `/vendor/autoload.php`
devuelven 404 y `/storage/logs/laravel.log` devuelve 403. Las 139 pruebas siguen pasando en la
máquina, ya que la imagen se construye sin dependencias de desarrollo.

---

## 2026-10-18 · documentación

**Resumen:** se documentó el **Sprint 3 · Actividades y grupos** antes de implementarlo: historias de
usuario, tarjetas CRC, esquema de las cinco tablas nuevas e instrucciones de implementación.

**Archivo nuevo:** `docs/sprints/sprint-03-historias-de-usuario-y-crc.md`, que replica la estructura
de las secciones 4.5 y 4.6 del documento oficial de requerimientos, como **sección 4.7**:

- **4.7.1 Planificación del Sprint** — Sprint Goal y Tabla 11 (Sprint Backlog). 18 puntos y 48 horas
  estimadas, repartidas entre HU-14, HU-15 y HU-16.
- **4.7.2 Historias de usuario** — las tres del backlog oficial: HU-14 Gestionar actividades
  complementarias (5 pts), HU-15 Gestionar grupos y horarios (8 pts) y HU-16 Verificar conflictos de
  horario (5 pts), con criterios de aceptación numerados.
- **4.7.3 Tarjetas CRC** — 11 tarjetas: `DiaSemana`, `Actividad`, `Grupo`, `Horario`,
  `ActividadController`, `GrupoController`, `HorarioController`, `GuardarActividadRequest`,
  `GuardarGrupoRequest`, `GuardarHorarioRequest` y `ServicioConflictos`.
- **Anexo A** — esquema de `dia_semana`, `actividad_complementaria`, `actividad_carrera`, `grupo` y
  `horario`, transcrito del paquete `Sprint0_Base_de_Datos.zip`.
- **Anexo B** — instrucciones de implementación en diez pasos: migraciones, modelos, permisos, Form
  Requests, `ServicioConflictos`, controladores y rutas, vistas, seeders, pruebas y documentación.

**Decisiones tomadas:**

1. El coordinador administra **solo su departamento**, igual que en el Sprint 1.
2. El validador detecta **las cuatro** clases de conflicto: instructor ocupado, ubicación ocupada,
   horarios traslapados del mismo grupo y cupo máximo mayor a la capacidad de la ubicación.
3. El instructor obtiene **pantalla de solo lectura** de los grupos que tiene asignados.
4. Se conservan `actividad_complementaria.id_categoria` y el nombre con acentos en `dia_semana`.

**Hallazgos que cambiaron el diseño:**

- El paquete de Sprint 0 advertía que `inscripcion` (Sprint 4) no funcionaba en SQLite por su columna
  generada. **Esa restricción quedó resuelta** al migrar el proyecto a MySQL 8: la migración se puede
  copiar tal cual en el Sprint 4.
- En el sistema anterior **ningún grupo tiene instructor asignado** (los 5 tienen `id_instructor` en
  `NULL`), así que la asignación es trabajo sin datos de referencia.
- El grupo 4 del sistema anterior tiene horarios `08:30–09:00`, `09:00–09:30`, `14:00–14:30` y
  `14:30–15:00`: dos franjas que **solo se tocan**. Se documentó como criterio de aceptación que el
  traslape se compara en tiempo, porque el validador por número de horas daría falsos positivos.
- `actividad_complementaria.id_categoria` está en `NULL` en las 8 actividades del sistema anterior y no
  existe tabla `categoria`: se conserva la columna por fidelidad al modelo E-R, pero el formulario no
  la pide.

**Archivos:**
- Nuevo: `docs/sprints/sprint-03-historias-de-usuario-y-crc.md`
- Modificados: `docs/README.md` (índice por sprint y enlace a `requisitos.md`), `docs/bitacora.md`,
  `docs/contexto.md`

**Verificación:** sin cambios de código ni de base de datos; enlaces de `docs/` revisados. No aplica
`php artisan test`.

---

## 2026-10-18 · infra

**Resumen:** la base de datos del proyecto migró de **SQLite a MySQL 8.0.46**.

**Motivo:** SQLite era solo el motor de desarrollo. MySQL es el motor objetivo y el que se usará en
el servidor donde se despliegue.

**Qué se hizo:**
1. Se creó la base `ActividadesComplementarias2026` con `utf8mb4` / `utf8mb4_unicode_ci`.
2. Se corrigió `DB_CONNECTION=Mysql` a `DB_CONNECTION=mysql` en el `.env`. **Con mayúscula no
   funcionaba**: Laravel busca la clave literal `mysql` en `config/database.php` y lanzaba
   `Database connection [Mysql] not configured` incluso con `php artisan config:clear`.
3. Se aplicaron las 11 migraciones y los 3 seeders: `php artisan migrate --seed`.
4. Se agregó `APP_TIMEZONE=America/Mexico_City` a `.env` y `.env.example`. Sin esto el cierre
   automático de semestres (Sprint 2) se disparaba 6 horas antes de medianoche en Oaxaca.
5. Se documentó el bloque de MySQL en `.env.example`, dejando SQLite como opción comentada.
6. Respaldo de la base anterior en `database/database.sqlite.bak`.

**⚠️ Base que NO se debe tocar:** `actividades_complementarias` es el **sistema anterior** (Laravel 8,
creado en mayo de 2026) y contiene 109 usuarios, 101 alumnos, 8 actividades, 12 horarios, 6
inscripciones y las tablas `actividad_complementaria`, `grupo`, `horario`, `inscripcion`,
`calificacion` y `dia_semana`.

**Verificación:**
- `php artisan migrate:status` → las 11 migraciones aparecen aplicadas.
- 21 tablas creadas con `utf8mb4_unicode_ci`.
- Datos semilla: 4 roles, 21 permisos, 5 departamentos, 10 carreras, 5 ubicaciones, 4 usuarios.
- La columna con `año` existe: bytes `61 C3B1 6F`, es decir `ñ` en UTF-8 correcto.
- Recorrido real contra MySQL: login, panel, usuarios, roles, departamentos, carreras, ubicaciones y
  semestres responden 200.
- Escritura comprobada: alta de un departamento, rechazo del duplicado por unicidad (quedó una sola
  fila) y borrado del dato de prueba. Las claves foráneas responden correctamente.
- `php artisan test` → 139/139, 550 aserciones. **Ojo:** las pruebas corren en SQLite en memoria
  (`phpunit.xml`), así que no validan MySQL; por eso el recorrido manual anterior.

**Archivos:**
- Modificados: `.env` (no versionado), `.env.example`, `README.md`, `docs/contexto.md`,
  `docs/bitacora.md`
- Nuevo (ignorado por git): `database/database.sqlite.bak`

**Nota pendiente:** los servicios `MySQL80` (parado) y `MySQL802` (corriendo) apuntan al mismo
`datadir`. Si se arranca `MySQL80` fallará por conflicto. No se modificó ninguno.

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