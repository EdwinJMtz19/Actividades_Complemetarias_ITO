# Actividades Complementarias ITO

Sistema web para administrar las **actividades complementarias** del Instituto Tecnológico de
Oaxaca: catálogo de departamentos, carreras, instructores y alumnos; inscripción de alumnos a
actividades; horarios, inscripciones y calificaciones; y dashboards por rol.

Cada persona entra con un **rol** y opera solo lo que le corresponde: el administrador ve todo, el
coordinador solo a los instructores de su departamento, y los roles de instructor y alumno se
activan en los siguientes sprints.

## Estado del proyecto

| Sprint | Alcance | Estado |
|---|---|---|
| 1 | Usuarios, roles y permisos + acceso por departamento | **Entregado** (2026-10-17) |
| 2 | CRUD de departamentos y carreras | Pendiente |
| 3 | Actividades, grupos y horarios | Pendiente |
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

## Requisitos

- PHP 8.3 o superior (en local: 8.5 vía [Herd Lite](https://herd.laravel.com))
- Composer 2
- Node.js 20 o superior y npm

## Instalación

```sh
composer install
npm install

cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

composer require spatie/laravel-permission
php artisan migrate:fresh --seed
npm run build

php artisan serve           # http://localhost:8000
```

## Cuentas de prueba

Solo fuera de producción. Contraseña: `Actividades2026`

| Correo | Rol | Alcance |
|---|---|---|
| admin@gmail.com | administrador | Todo el sistema |
| coordinador@gmail.com | coordinador | Instructores de «Sistemas y Computación» |
| instructor@gmail.com | instructor | Sin módulos todavía |
| alumno@gmail.com | alumno | Sin módulos todavía |

## Pruebas

```sh
php artisan test             # 51 pruebas
vendor\bin\pint --test       # estilo de código
```

## Stack

Laravel 13 · PHP 8.3+ · SQLite · `spatie/laravel-permission` 8.3 · Pest 5 · Tailwind CSS 4 (Vite)

## Repositorio

`https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO`