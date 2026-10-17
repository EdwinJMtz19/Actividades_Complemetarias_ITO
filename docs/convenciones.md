# Convenciones y reglas de trabajo

> Contrato para cualquier persona o IA que toque este repositorio.

## 1. Flujo obligatorio

1. **Lee [`contexto.md`](contexto.md)** y el documento del sprint que vas a modificar
   (`sprints/`). Ahí está el «por qué» de las decisiones existentes.
2. **Trabaja** siguiendo las reglas de abajo.
3. **Verifica**: `php artisan test` debe quedar en verde y `vendor\bin\pint --test` sin observaciones.
4. **Documenta** (este paso no se salta):
   - Si es un sprint nuevo → crea `docs/sprints/sprint-NN-tema.md`.
   - Si es un cambio o corrección → agrega una **entrada nueva al principio** de
     [`bitacora.md`](bitacora.md) con la fecha de hoy.
   - Si cambió una regla de negocio, el modelo de datos o una decisión técnica →
     actualiza [`contexto.md`](contexto.md).
5. **Commit** solo cuando se pida explícitamente.

## 2. Comandos

```sh
php artisan migrate:fresh --seed   # rehacer la base de datos con datos iniciales
php artisan test                   # 122 pruebas (51 del Sprint 1 + 71 del Sprint 2)
vendor\bin\pint                    # formatear    |    vendor\bin\pint --test  # revisar
npm run build                      # compilar Tailwind y JS
php artisan serve                  # http://localhost:8000
```

En Windows, antepón Herd Lite si `php` no se reconoce:
`$env:Path = "C:\Users\em556\.config\herd-lite\bin;$env:Path"`

## 3. Reglas de código

- **Comentarios y documentación en español.** Es un proyecto escolar y el código se explica al
  profesor y al residente.
- **Estilo Laravel + Pint.** NoUses comillas simples por costumbre, `declare(strict_types=1)`, etc.:
  deja que Pint decida (`vendor\bin\pint`).
- **Nada de lógica en las vistas.** Los Blade solo muestran; las reglas van en controladores,
  Form Requests, políticas o middleware.
- **Vistas con Tailwind 4** usando las clases de la paleta `slate`/`blue` que ya usa
  `components/layouts/app.blade.php`. No introduzcas otra librería de UI (Stisla, Bootstrap, Alpine…).
- **Validación en Form Requests** (`app/Http/Requests/`), nunca dentro del controlador.
- **Control de acceso con permisos y políticas**, no con condicionales sueltos en las vistas:
  - permiso puntual → `->middleware('permission:usuarios.ver')`
  - regla sobre un modelo → `UserPolicy`
  - condición global (p. ej. «coordinador sin departamento») → middleware propio en
    `app/Http/Middleware/` registrado en `bootstrap/app.php`
- **Modelos con atributos de PHP**: `#[Table]`, `#[Fillable]`, `#[Hidden]`, y `casts()`
  protegido. No introduzcas `$table`/`$fillable`/`$hidden` Properties.
- **Relaciones `hasOne` por perfil**: `alumno()`, `instructor()`, `coordinacion()`.

## 4. Base de datos

- **No edites una migración que ya se aplicó** (las de `0001_01_01_*` y las de `2026_*` ya corrieron).
  Crea una nueva con nombre `YYYY_MM_DD_HHMMSS_descripcion.php`.
- **Al cambiar el esquema, agrega la prueba que lo cubre** y ejecuta
  `php artisan migrate:fresh --seed` para verificar desde cero.
- **Seeders**: `firstOrCreate`, nunca `create` a secas, para que sean idempotentes.
- **`DatabaseSeeder`** llama a los seeders en orden; las cuentas de prueba quedan dentro de
  `if (! app()->environment('production'))`.
- **Nuevo permiso** → agrégalo a `RolesYPermisosSeeder` (nunca lo borres si ya se usó).
- **SQLite es la base de desarrollo**: nada de funciones exclusivas de MySQL (columnas generadas,
  `ENUM` real, etc.) hasta que se decida el motor final.

## 5. Pruebas

- Pruebas **Pest** en `tests/Feature/`, en español y con `it('...')`.
- Cada prueba que toque base de datos usa `uses(RefreshDatabase::class)`.
- Los datos de apoyo van en `tests/Support/Escenarios.php` (no los crees dentro de cada prueba).
- Las pruebas usan `$this->withoutVite()` para no depender de assets compilados.
- **Un cambio sin pruebas que lo cubran está incompleto.**

## 6. Git

- Rama: `main`. Commits descriptivos en español, ejemplo:
  `Sprint 2: CRUD de departamentos y carreras`.
- **Nunca** subas `.env`, `vendor/` ni `node_modules/` (ya están en `.gitignore`).
- No uses `git push --force` salvo que el usuario lo pida de forma explícita.
- Antes de un commit: `git status` y `git diff` para revisar qué entra.

## 7. Secrets y datos de prueba

- Las contraseñas de las cuentas de prueba (`Actividades2026`) **solo existen fuera de producción**.
  No las muevas a `.env` ni las incluyas en un despliegue real.
- No subas llaves, tokens ni datos personales de alumnos reales.