# Documentación · Actividades Complementarias ITO

Documentación viva del proyecto. **Actualízala al cerrar cada sprint o cada cambio** que te solicite
el equipo: es lo que leen las otras IAs (y el residente) para entender qué se está construyendo y por qué.

## Índice

| Documento | Para qué sirve |
|---|---|
| [`contexto.md`](contexto.md) | Qué es el sistema, cronograma de sprints, modelo de datos, reglas de negocio y decisiones técnicas. **Léelo primero.** |
| [`convenciones.md`](convenciones.md) | Cómo se trabaja en este repo: comandos, estilo, pruebas y reglas obligatorias. |
| [`bitacora.md`](bitacora.md) | Registro cronológico de cada sprint y cambio (solo se agrega, nunca se reescribe). |
| [`requisitos.md`](requisitos.md) | Los 15 requisitos funcionales y los 6 no funcionales, con su estado real y la evidencia en el código. |
| [`contenedor.md`](contenedor.md) | Cómo levantar el proyecto y su base en Docker, y cómo publicar la imagen en la nube. |
| [`decisiones/`](decisiones/) | Decisiones que necesitaron respuesta del equipo y se resolvieron (hoy: borrado de grupos). |
| [`sprints/`](sprints/) | Un documento por sprint con el detalle de lo entregado. |

En la raíz del repositorio: [`README.md`](../README.md) (entrada pública),
[`CHANGELOG.md`](../CHANGELOG.md) (historial de cambios para quien usa el sistema) y
[`CONTRIBUTING.md`](../CONTRIBUTING.md) (cómo contribuir).

### Documentos por sprint

| Sprint | Documentos |
|---|---|
| 1 · Usuarios y roles | [Técnico](sprints/sprint-01-usuarios-y-roles.md) |
| 2 · Catálogos base y semestres | [Técnico](sprints/sprint-02-catalogos-y-semestres.md) · [Historias de usuario y CRC](sprints/sprint-02-historias-de-usuario-y-crc.md) · [Tablas del Sprint 0](sprints/sprint-02-historias-de-usuario-y-crc-tablas.md) · [Resumen y mejoras de UI](sprints/resumen-sprint-2-y-mejoras.md) |
| 3 · Actividades y grupos | [Técnico](sprints/sprint-03-actividades-y-grupos.md) · [Historias de usuario y CRC, esquema e instrucciones](sprints/sprint-03-historias-de-usuario-y-crc.md) · [Plan](sprints/sprint-03-plan.md) · [Decisión resuelta: borrado de grupos](decisiones/borrado-de-grupos.md) |

## Estado actual

**Sprint 3 · Actividades, grupos y horarios — ENTREGADO (2026-10-20)**
CRUD de actividades con carreras y semestre; CRUD de grupos con horarios, instructor, ubicación y
cupos; verificación de conflictos en el servidor y aviso en vivo. Detalle en
[`sprints/sprint-03-actividades-y-grupos.md`](sprints/sprint-03-actividades-y-grupos.md).
Siguiente en el cronograma: **Sprint 4 · Inscripción de alumnos**.

**Sprint 2 · Catálogos base y semestre — ENTREGADO (2026-10-17)**
CRUD de departamentos, carreras y ubicaciones; semestres con cierre automático y validaciones de
duración (3 a 5 meses) e inscripción (5 a 10 días). Detalle en
[`sprints/sprint-02-catalogos-y-semestres.md`](sprints/sprint-02-catalogos-y-semestres.md).

Sprint 1 · Usuarios y Roles — entregado (2026-10-17): autenticación, CRUD de usuarios, roles y permisos
con Spatie, y control de acceso por rol y departamento.

## Stack

| Componente | Versión |
|---|---|
| Laravel | 13.37.0 (`composer.lock`) |
| PHP | 8.4.1 o superior (local: 8.5.0 vía Herd Lite) |
| Base de datos | MySQL 8 en local; SQLite en memoria para las pruebas |
| Autenticación y permisos | `spatie/laravel-permission` 8.3.0 |
| Pruebas | Pest 5 + PHPUnit |
| Vistas | Blade + Tailwind CSS 4 (Vite) |

## Comandos

```sh
# Base de datos (recrea todo y siembra los datos iniciales)
php artisan migrate:fresh --seed

# Servidor local -> http://localhost:8000
php artisan serve

# Pruebas (deben pasar 204)
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
| coordinador@gmail.com | coordinador | Instructores, actividades y grupos de «Sistemas y Computación» |
| instructor@gmail.com | instructor | Solo consulta sus grupos (Sprint 3) |
| alumno@gmail.com | alumno | Sin módulos todavía (Sprint 4) |

## Mapa de carpetas

```
app/
  Console/Commands/     CerrarSemestresVencidos
  Http/Controllers/     Controladores (Auth\, Panel, Usuario, Rol, Departamento, Carrera, Ubicacion, Semestre, Actividad, Grupo, Horario)
  Http/Middleware/      usuario.activo, departamento.asignado
  Http/Requests/        Validación de formularios
  Models/               User, Alumno, Instructor, Departamento, Carrera, CoordinadorDepartamento, Ubicacion, Semestre, DiaSemana, Actividad, Grupo, Horario
  Policies/             UserPolicy, ActividadPolicy, GrupoPolicy
  Services/             ServicioConflictos (reglas de horario)
database/
  migrations/           Esquema (16 migraciones)
  seeders/              RolesYPermisos, CatalogosBase, DiasSemana, ActividadesBase, UsuariosPrueba
resources/views/        Blade: auth\, usuarios\, roles\, departamentos\, carreras\, ubicaciones\, semestres\, actividades\, grupos\, components\
resources/js/           app.js, grupo-horarios.js (aviso en vivo)
routes/web.php          Rutas web
tests/Feature/          Pruebas Pest
docs/                   Esta documentación
```

## Repositorio

`https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO`