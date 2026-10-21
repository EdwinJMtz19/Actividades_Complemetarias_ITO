# Actividades Complementarias ITO

Sistema web (Laravel 13 + Blade + Tailwind 4 + MySQL 8) para administrar las actividades
complementarias del ITO: usuarios por rol, actividades, grupos, horarios, inscripciones y
calificaciones. Sprint actual: **3 · Actividades, grupos y horarios (entregado)**; sigue el **4 ·
Inscripción de alumnos**.

## Lee esto primero

| Documento | Para qué |
|---|---|
| [`docs/README.md`](docs/README.md) | Índice, stack, comandos, cuentas de prueba, estado del proyecto |
| [`docs/contexto.md`](docs/contexto.md) | **Obligatorio:** modelo de datos, roles/permisos, reglas de negocio, decisiones técnicas |
| [`docs/convenciones.md`](docs/convenciones.md) | **Obligatorio:** cómo trabajar aquí (comandos, estilo, pruebas, git) |
| [`docs/bitacora.md`](docs/bitacora.md) | Qué se ha hecho y cuándo |
| [`docs/contenedor.md`](docs/contenedor.md) | Cómo levantar el proyecto y la base en Docker, y publicarlo como imagen |
| [`docs/decisiones/`](docs/decisiones/) | Decisiones abiertas que necesitan respuesta del equipo |
| [`docs/sprints/`](docs/sprints/) | Un documento por sprint con lo entregado |

## Reglas que no se rompen

1. **Nada sin pruebas.** Todo cambio va con su prueba Pest en `tests/Feature/`. Debe quedar:
   `php artisan test` en verde (hoy son 205) y `vendor\bin\pint --test` sin observaciones.
2. **No edites migraciones ya aplicadas.** Crea una nueva `YYYY_MM_DD_HHMMSS_*.php`.
3. **El acceso se controla con permisos y políticas**, no con condicionales en las vistas:
   `permission:...` en rutas, `UserPolicy` para reglas sobre modelos, middleware propio para
   condiciones globales. Registra todo middleware nuevo en `bootstrap/app.php`.
4. **Spatie es la única fuente de rol.** La columna `tipo_usuario` se deriva del rol
   (`User::TIPO_POR_ROL`); no se elige a mano. Los permisos nuevos van en `RolesYPermisosSeeder`.
5. **No toques `.env`** (leé los valores de `.env.example`) ni subas `.env`, `vendor/` o `node_modules/`.
6. **Comentarios y documentación en español**; Blade solo muestra, la lógica va en controladores,
   Form Requests, políticas o middleware. Vistas con Tailwind 4, sin otras librerías de UI.
7. **MySQL 8 es el motor de la aplicación y SQLite el de las pruebas**: evita funciones exclusivas
   de MySQL porque la suite corre en SQLite en memoria.
8. **No hagas `git push --force`** salvo petición explícita, y no commitees sin que te lo pidan.

## Después de cada cambio, documenta

- Cambio o corrección → agrega una entrada **al principio** de `docs/bitacora.md` con la fecha de hoy.
- Sprint nuevo → crea `docs/sprints/sprint-NN-tema.md`.
- Cambió una regla de negocio, el modelo de datos o una decisión técnica → actualiza `docs/contexto.md`.
- Lanzaste una versión visible para el usuario → refleja el cambio en `CHANGELOG.md`.

## Comandos, cuentas de prueba y entorno

Todo está en [`docs/README.md`](docs/README.md): comandos de Docker y de PHP local, ruta de Herd Lite
en Windows, cuentas de prueba y mapa de carpetas. Resumen mínimo:

```sh
php artisan test                   # 205 pruebas
vendor\bin\pint                    # formatear
```

---

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
