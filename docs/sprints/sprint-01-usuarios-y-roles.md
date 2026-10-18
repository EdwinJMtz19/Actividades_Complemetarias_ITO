# Sprint 1 · Usuarios y Roles

**Estado:** entregado · **Fecha:** 2026-10-17 · **Alcance:** autenticación, CRUD de usuarios,
roles y permisos con Spatie, y control de acceso por rol y por departamento asignado.

> **Ver también:** la trazabilidad de este sprint contra los requisitos del sistema está en
> [`../requisitos.md`](../requisitos.md). Ahí se registra que **RF-04 («permitir a cada usuario
> actualizar su perfil») sigue sin implementarse**, aunque el documento oficial de requerimientos
> lo declara como parte de este sprint.

## Qué se entregó

### 1. Autenticación

- Login, logout y «mantener sesión» (recordar).
- **Límite de 5 intentos por combinación correo + IP** (`LoginRequest`).
- Registro público **solo para alumnos**.
- Registro del `ultimo_acceso` en cada entrada.
- Cierre automático de la sesión si la cuenta se desactiva mientras está conectada
  (middleware `usuario.activo`).

### 2. CRUD de usuarios (administrador)

Crear, ver, editar, activar/desactivar y eliminar. Cada rol guarda sus datos propios:

| Rol | Datos propios |
|---|---|
| alumno | número de control, carrera, semestre, créditos |
| instructor | departamento, especialidad |
| coordinador | departamento que coordina |

### 3. Roles y permisos (Spatie)

4 roles (`administrador`, `coordinador`, `instructor`, `alumno`) y 6 permisos (`usuarios.*`,
`roles.*`), con pantalla `/roles` para asignar permisos por rol.

### 4. Acceso por rol y departamento

`UserPolicy` + middleware `departamento.asignado`: un coordinador solo ve a los instructores de su
departamento; si no tiene departamento asignado, no puede operar (403 con mensaje explicativo).

## Archivos

### Nuevos

```
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/Auth/RegistroController.php
app/Http/Controllers/PanelController.php
app/Http/Controllers/RolController.php
app/Http/Controllers/UsuarioController.php
app/Http/Middleware/EnsureDepartamentoAsignado.php
app/Http/Middleware/EnsureUsuarioActivo.php
app/Http/Requests/Auth/LoginRequest.php
app/Http/Requests/Auth/RegistroRequest.php
app/Http/Requests/GuardarUsuarioRequest.php
app/Models/Alumno.php          app/Models/Instructor.php
app/Models/Carrera.php         app/Models/Departamento.php
app/Models/CoordinadorDepartamento.php
app/Policies/UserPolicy.php
database/seeders/RolesYPermisosSeeder.php
database/seeders/CatalogosBaseSeeder.php
database/seeders/UsuariosPruebaSeeder.php
resources/views/auth/{login,registro}.blade.php
resources/views/usuarios/{index,create,edit,show,_form}.blade.php
resources/views/roles/index.blade.php
resources/views/panel.blade.php
resources/views/components/{campo,seleccion}.blade.php
resources/views/components/layouts/{app,guest}.blade.php
config/permission.php
lang/es/{auth,validation}.php
tests/Support/Escenarios.php
tests/Feature/{AutenticacionTest,UsuariosCrudTest,RolesPermisosTest,AccesoPorRolYDepartamentoTest}.php
```

### Archivos del esqueleto que fueron reemplazados

| Archivo | Cambio |
|---|---|
| `bootstrap/app.php` | Alias de middleware (Spatie + propios) y redirección de invitados/usuarios |
| `routes/web.php` | Login, registro, panel, `usuarios` (resource) y `roles` |
| `app/Models/User.php` | Tabla `USUARIO`, `HasRoles`, `contrasena` como contraseña, relaciones de perfil y ámbito de departamento |
| `app/Providers/AppServiceProvider.php` | `Gate::before`: el administrador pasa todo, excepto eliminarse/desactivarse a sí mismo |
| `database/factories/UserFactory.php` | Fabrica con los campos de `USUARIO` y estado `inactivo()` |
| `database/seeders/DatabaseSeeder.php` | Roles → catálogos → cuentas de prueba (fuera de producción) |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Tabla `USUARIO` + `password_reset_tokens` + `sessions` |

Sin tocar: `.env`, `composer.json`/`composer.lock`, `vite.config.js`, `tests/Pest.php`.

### Migraciones

| Migración | Crea |
|---|---|
| `0001_01_01_000000_create_users_table` | `USUARIO`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` (de fábrica) |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` (de fábrica) |
| `0001_01_01_000003_create_permission_tables` | Tablas de Spatie |
| `2026_03_18_084652_create_carrera_table` | `carrera` |
| `2026_03_18_084701_create_departamento_table` | `departamento` |
| `2026_03_18_084740_create_alumno_table` | `alumno` (FK a `USUARIO` y `carrera`) |
| `2026_03_18_084747_create_instructor_table` | `instructor` (FK a `USUARIO` y `departamento`) |
| `2026_04_20_000001_create_coordinador_departamento_table` | `coordinador_departamento` (FK a `USUARIO` y `departamento`, ambas únicas) |

## Rutas

| Ruta | Nombre | Acceso |
|---|---|---|
| `/` | — | Redirige a `/login` |
| `GET\|POST /login` | `login` | Invitado (POST con `throttle:20,1`) |
| `GET\|POST /registro` | `registro` | Invitado (POST con `throttle:10,1`) |
| `POST /logout` | `logout` | Autenticado |
| `GET /panel` | `panel` | Autenticado + activo |
| `GET /usuarios` … | `usuarios.*` | `usuarios.*` + departamento asignado |
| `PATCH /usuarios/{usuario}/estado` | `usuarios.toggle` | `usuarios.editar` |
| `GET /roles` | `roles.index` | `roles.ver` |
| `PUT /roles/{role}` | `roles.update` | `roles.editar` |

## Verificación

- `php artisan migrate:fresh --seed`: 9 migraciones + 3 seeders sin errores.
  Resultado: 4 usuarios, 4 roles, 6 permisos, 5 departamentos, 10 carreras, 1 alumno, 1 instructor,
  1 coordinación.
- `php artisan test`: **51 pruebas, 172 aserciones, todas en verde** (49 del Sprint 1 más 2 de ejemplo).
- `vendor\bin\pint --test`: sin observaciones.
- Recorrido HTTP real: `GET /login` (200) → `POST /login` con `admin@gmail.com` → panel con la lista
  de usuarios (200).

## Fuera de alcance (para otros sprints)

- Recuperación de contraseña (necesita SMTP) → Sprint 4.
- CRUD de departamentos y carreras → Sprint 2.
- Módulos de instructor y alumno → Sprints 3, 4 y 5.
- Dashboards por rol → Sprint 6.

## Deuda técnica y pendientes

1. `tests/Feature/ExampleTest.php` venía de fábrica esperando `200` en `/`; como ahora `/` redirige a
   `/login`, se actualizó a `assertRedirect(route('login'))`.
2. Faltan las 9 tablas del Sprint 0 (`ubicacion`, `dia`, `semestre`, `actividad`, `grupo`, `horario`,
   `inscripcion`, `calificacion`, `log_acceso`): vienen del paquete `Sprint0_Base_de_Datos.zip`.
3. La futura migración de `inscripcion` usa una **columna generada de MySQL** que habrá que adaptar
   para SQLite.
4. El cronograma oficial dice **Laravel 8** y el proyecto es **Laravel 13**; hay que corregir ese
   dato en la documentación que entrega el residente.
5. El motor de base de datos final (MySQL/SQL Server) sigue por definirse.