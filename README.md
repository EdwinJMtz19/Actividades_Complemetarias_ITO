# Actividades Complementarias ITO

Sistema web para administrar las **actividades complementarias** del Instituto Tecnológico de
Oaxaca: catálogo de departamentos, carreras, instructores y alumnos; actividades con grupos y
horarios; inscripción de alumnos y calificaciones; y dashboards por rol.

Cada persona entra con un **rol** y opera solo lo que le corresponde: el administrador ve todo, el
coordinador gestiona a los instructores de su departamento, el instructor consulta sus grupos y el
alumno se inscribe a las actividades (a partir del Sprint 4).

## Estado del proyecto

| Sprint | Alcance | Estado |
|---|---|---|
| 1 | Usuarios, roles y permisos + acceso por departamento | **Entregado** (2026-10-17) |
| 2 | Catálogos: departamentos, carreras, ubicaciones y semestres | **Entregado** (2026-10-17) |
| 3 | Actividades, grupos y horarios | **Entregado** (2026-10-20) |
| 4 | Inscripción de alumnos | Pendiente |
| 5 | Calificaciones | Pendiente |
| 6 | Dashboards por rol | Pendiente |

## Documentación

Toda la documentación vive en [`docs/`](docs/):

- [`docs/README.md`](docs/README.md) — índice, stack, comandos y cuentas de prueba
- [`docs/contexto.md`](docs/contexto.md) — modelo de datos, reglas de negocio y decisiones técnicas
- [`docs/convenciones.md`](docs/convenciones.md) — cómo trabajar en el repositorio
- [`docs/bitacora.md`](docs/bitacora.md) — registro de todos los cambios
- [`docs/sprints/`](docs/sprints/) — un documento por sprint
- [`CHANGELOG.md`](CHANGELOG.md) — historial de cambios para quien usa el sistema

Además, en la raíz: [`PRODUCT.md`](PRODUCT.md) (qué es el producto), [`DESIGN.md`](DESIGN.md)
(guía de diseño visual) y [`CONTRIBUTING.md`](CONTRIBUTING.md) (cómo contribuir).

## Requisitos

- PHP 8.4.1 o superior (en local: 8.5 vía [Herd Lite](https://herd.laravel.com))
- Composer 2
- Node.js 20 o superior y npm
- MySQL 8 (motor de la aplicación; las pruebas corren en SQLite en memoria)

## Instalación

```sh
composer install
npm install

cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

php artisan migrate:fresh --seed
npm run build

php artisan serve           # http://localhost:8000
```

El `.env.example` viene configurado con SQLite para arrancar rápido; para trabajar con MySQL 8
(igual que producción) ajusta `DB_*` como indica [`docs/contexto.md`](docs/contexto.md).

**Alternativa sin instalar PHP ni MySQL:** Docker. Ver
[`docs/contenedor.md`](docs/contenedor.md) (`docker compose up -d --build`, http://localhost:8080).

## Cuentas de prueba

Solo fuera de producción. Contraseña: `Actividades2026`

| Correo | Rol | Alcance |
|---|---|---|
| admin@gmail.com | administrador | Todo el sistema |
| coordinador@gmail.com | coordinador | Instructores, actividades y grupos de «Sistemas y Computación» |
| instructor@gmail.com | instructor | Solo consulta sus grupos |
| alumno@gmail.com | alumno | Sin módulos todavía (Sprint 4) |

## Pruebas

```sh
php artisan test             # 205 pruebas
vendor\bin\pint --test       # estilo de código
```

## Stack

Laravel 13 · PHP 8.4.1+ · MySQL 8 (SQLite en pruebas) · `spatie/laravel-permission` 8.3 · Pest 5 ·
Tailwind CSS 4 (Vite)

## Repositorio

`https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO`
