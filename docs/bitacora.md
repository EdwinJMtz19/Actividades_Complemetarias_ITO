# Bitácora de cambios

Registro cronológico del proyecto. **Solo se agrega al principio; nunca se reescribe una entrada.**

Formato de cada entrada: fecha · tipo · resumen · archivos · verificación.
Tipos: `sprint` · `cambio` · `fix` · `infra` · `documentación`.

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