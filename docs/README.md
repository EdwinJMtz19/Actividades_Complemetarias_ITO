# Documentación · Actividades Complementarias ITO

Documentación viva del proyecto. **Actualízala al cerrar cada sprint o cada cambio** que te solicite
el equipo: es lo que leen las otras IAs (y el residente) para entender qué se está construyendo y por qué.

## Índice

| Documento | Para qué sirve |
|---|---|
| [`contexto.md`](contexto.md) | Qué es el sistema, cronograma de sprints, modelo de datos, reglas de negocio y decisiones técnicas. **Léelo primero.** |
| [`convenciones.md`](convenciones.md) | Cómo se trabaja en este repo: comandos, estilo, pruebas y reglas obligatorias. |
| [`bitacora.md`](bitacora.md) | Registro cronológico de cada sprint y cambio (solo se agrega, nunca se reescribe). |
| [`sprints/`](sprints/) | Un documento por sprint con el detalle de lo entregado. |

## Estado actual

**Sprint 2 · Catálogos base y semestre — ENTREGADO (2026-10-17)**
CRUD de departamentos, carreras y ubicaciones; semestres con cierre automático y validaciones de
duración (3 a 5 meses) e inscripción (5 a 10 días). Detalle en
[`sprints/sprint-02-catalogos-y-semestres.md`](sprints/sprint-02-catalogos-y-semestres.md).
Siguiente en el cronograma: **Sprint 3 · Actividades, grupos y horarios**.

Sprint 1 · Usuarios y Roles — entregado (2026-10-17): autenticación, CRUD de usuarios, roles y permisos
con Spatie, y control de acceso por rol y departamento.

## Stack

| Componente | Versión |
|---|---|
| Laravel | 13.37.0 (`composer.lock`) |
| PHP | 8.3 o superior (local: 8.5.0 vía Herd Lite) |
| Base de datos | SQLite en desarrollo (`database/database.sqlite`) |
| Autenticación y permisos | `spatie/laravel-permission` 8.3.0 |
| Pruebas | Pest 5 + PHPUnit |
| Vistas | Blade + Tailwind CSS 4 (Vite) |

## Comandos

```sh
# Base de datos (recrea todo y siembra los datos iniciales)
php artisan migrate:fresh --seed

# Servidor local -> http://localhost:8000
php artisan serve

# Pruebas (deben pasar 122: 51 del Sprint 1 + 71 del Sprint 2)
php artisan test

# Cierre automático de semestres vencidos (manual) y programador en local
php artisan semestres:cerrar-vencidos
php artisan schedule:work

# Estilos y JS
npm run build      # compila
npm run dev        # recarga en caliente

# Estilo del código (Pint)
vendor\bin\pint
```

> **Windows:** PHP y Composer no están en el PATH de la terminal. Están en Herd Lite:
> `C:\Users\em556\.config\herd-lite\bin`. Si `php` no se reconoce, antepón esa carpeta:
> `$env:Path = "C:\Users\em556\.config\herd-lite\bin;$env:Path"`.

## Cuentas de prueba

Solo fuera de producción. Contraseña para las cuatro: `Actividades2026`

| Correo | Rol | Alcance |
|---|---|---|
| admin@gmail.com | administrador | Todo el sistema |
| coordinador@gmail.com | coordinador | Instructores de «Sistemas y Computación» |
| instructor@gmail.com | instructor | Sin módulos todavía (Sprint 3 y 5) |
| alumno@gmail.com | alumno | Sin módulos todavía (Sprint 4) |

## Mapa de carpetas

```
app/
  Console/Commands/     CerrarSemestresVencidos
  Http/Controllers/     Controladores (Auth\, Panel, Usuario, Rol, Departamento, Carrera, Ubicacion, Semestre)
  Http/Middleware/      usuario.activo, departamento.asignado
  Http/Requests/        Validación de formularios
  Models/               User, Alumno, Instructor, Departamento, Carrera, CoordinadorDepartamento, Ubicacion, Semestre
  Policies/             UserPolicy (reglas de acceso)
database/
  migrations/           Esquema (SQLite)
  seeders/              RolesYPermisos, CatalogosBase (departamentos, carreras, ubicaciones), UsuariosPrueba
resources/views/        Blade: auth\, usuarios\, roles\, departamentos\, carreras\, ubicaciones\, semestres\, components\
routes/web.php          Rutas web
tests/Feature/          Pruebas Pest
docs/                   Esta documentación
```

## Repositorio

`https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO`