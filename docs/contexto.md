# Contexto del proyecto

> Lee este documento antes de modificar código. Aquí está el «qué» y el «por qué».
> El «cómo se trabaja» está en [`convenciones.md`](convenciones.md).

## 1. Qué es

Sistema web para administrar las **actividades complementarias** del ITO: catálogo de
departamentos, carreras, Instructors y alumnos; inscripción de alumnos a actividades; horarios,
inscripciones y calificaciones; y dashboards por rol.

Cada persona entra con un **rol** y opera solo lo que le corresponde:

| Rol | Qué puede hacer |
|---|---|
| `administrador` | Todo, sin restricciones de departamento. Único que administra usuarios, roles y catálogos. |
| `coordinador` | Solo ve y administra a los **instructores de su departamento**. |
| `instructor` | Pendiente: sus grupos y actividades (Sprint 3 y 5). |
| `alumno` | Pendiente: consulta e inscripción en actividades (Sprint 4). |

## 2. Cronograma de sprints

| Sprint | Alcance | Estado |
|---|---|---|
| 0 | Modelo de datos base (paquete `Sprint0_Base_de_Datos.zip`) | Parcial: solo las tablas del Sprint 1 |
| 1 | Usuarios, roles y permisos + acceso por departamento | **Entregado** ([detalle](sprints/sprint-01-usuarios-y-roles.md)) |
| 2 | Catálogos base y semestre: CRUD de departamentos, carreras y ubicaciones; semestres con cierre automático y validaciones | **Entregado** ([detalle](sprints/sprint-02-catalogos-y-semestres.md)) |
| 3 | Actividades, grupos y horarios (instructor) | Pendiente |
| 4 | Inscripción de alumnos + recuperación de contraseña (SMTP) | Pendiente |
| 5 | Calificaciones | Pendiente |
| 6 | Dashboards por rol | Pendiente |

## 3. Modelo de datos

### Tablas que ya existen

| Tabla | Qué guarda | Notas |
|---|---|---|
| `USUARIO` | Cuenta de acceso de toda persona | **Nombre y columna `contrasena` en mayúsculas**, fiel al modelo E-R. `password_reset_tokens` y `sessions` se crean en la misma migración. |
| `alumno` | Perfil de alumno | PK `id_alumno` = `USUARIO.id`. `num_control`, `id_carrera`, `semestre_cursando`, `creditos_acumulados`. |
| `instructor` | Perfil de instructor | PK `id_instructor` = `USUARIO.id`. `id_departamento`, `especialidad`. |
| `coordinador_departamento` | Qué coordinadores da qué departamento | `id_usuario` único y `id_departamento` único (un coordinador por departamento). |
| `departamento` | Catálogo de departamentos | `nombre`, `edificio`. CRUD en el Sprint 2. |
| `carrera` | Catálogo de carreras | `nombre`. CRUD en el Sprint 2. |
| `ubicacion` | Catálogo de espacios físicos | `espacio` (único) y `capacidad`. Sprint 2. |
| `semestre` | Periodos escolares | Columna `año` (con ñ), `periodo` (1 = ene–jun, 2 = ago–dic), fechas y horas de inscripción, `status` (`activo`/`inactivo`). Único `(año, periodo)`. Sprint 2. |
| `roles`, `permissions`, `model_has_*`, `role_has_*` | Spatie | Permisos y roles. |
| `cache`, `jobs`, `sessions` | Infraestructura Laravel | Necesarias porque `.env` usa drivers `database`. |

**Renombre:** los perfiles (`alumno`, `instructor`, `coordinador_departamento`, `departamento`,
`carrera`) son tablas **separadas** que comparten la llave con `USUARIO`. Un usuario solo tiene
perfil según su rol, y `User` lo expone con las relaciones `alumno()`, `instructor()` y `coordinacion()`.

### Tablas que faltan (Sprint 0, sprints 2-6)

`dia`, `actividad`, `grupo`, `horario`, `inscripcion`, `calificacion`, `log_acceso`.
Vienen del paquete `Sprint0_Base_de_Datos.zip`.

> ⚠️ La migración de `inscripcion` (Sprint 4) usa una **columna generada de MySQL**; habrá que
> adaptarla mientras el proyecto use SQLite.

## 4. Roles y permisos

Spatie es la **única fuente de verdad**: la columna `tipo_usuario` de `USUARIO` se deriva del rol
al crear la cuenta (`User::TIPO_POR_ROL`), no se elige a mano.

Permisos actuales (`database/seeders/RolesYPermisosSeeder.php`):

```
usuarios.ver    usuarios.crear    usuarios.editar    usuarios.eliminar
roles.ver       roles.editar
departamentos.ver  .crear  .editar  .eliminar      (Sprint 2)
carreras.ver       .crear  .editar  .eliminar      (Sprint 2)
ubicaciones.ver    .crear  .editar  .eliminar      (Sprint 2)
semestres.ver      .crear  .editar                 (Sprint 2; no existe .eliminar)
```

Asignación: `administrador` → todos (21) · `coordinador` → `usuarios.ver` · `instructor` y `alumno` → ninguno.
Los permisos del Sprint 2 son solo del administrador; se pueden dar a otro rol desde `/roles`.

Hay una pantalla (`/roles`) para modificar permisos por rol. Por eso el seeder usa
`Permission::firstOrCreate` y es **idempotente**: se puede volver a ejecutar sin borrar lo que se
ajustó a mano. **Cada sprint nuevo agrega aquí sus permisos.**

## 5. Reglas de negocio vigentes

1. **El rol se fija al crear la cuenta** y no se cambia después: los datos de.profile quedan ligados
   a inscripciones y grupos en sprints futuros.
2. **Alcance por departamento:** el coordinador solo opera sobre instructores de su departamento.
   Sin departamento asignado el middleware `departamento.asignado` le responde 403.
3. **Eliminar vs. desactivar:** si la cuenta ya tiene registros relacionados (inscripciones, grupos)
   la base de datos impedirá borrarla y el sistema sugiere desactivarla. **Desde el Sprint 3 en
   adelante, desactivar es lo recomendable.**
4. **Nadie puede eliminarse ni desactivarse a sí mismo**, ni el administrador (para no dejar el
   sistema sin admins). Se aplica en `UserPolicy` y en un `Gate::before` en `AppServiceProvider`.
5. **Cuentas inactivas:** la sesión se cierra automáticamente y no pueden entrar (`usuario.activo`).
6. **Registro público solo para alumnos.**
7. **Límite de intentos:** 5 por combinación correo + IP en el login.
8. **Duración del semestre:** de 3 a 5 meses **completos**. Mínimo: `fecha_fin ≥ inicio + 3 meses`;
   máximo: `fecha_fin < inicio + 6 meses` (es decir, se acepta hasta 5 meses y 29 días, igual que el
   proyecto anterior). Se valida en `GuardarSemestreRequest` y se avisa en vivo en el formulario.
9. **Ventana de inscripción:** de 5 a 10 días entre inicio y fin, y dentro del rango del semestre.
   Abre el día de inicio a la `hora_inicio_inscripciones` y cierra el día de fin a la
   `hora_fin_inscripciones` (`Semestre::inscripcionesAbiertas()`, que usará el Sprint 4).
10. **Un solo periodo activo a la vez**, y un solo semestre por año y periodo. No se puede activar un
    periodo cuya fecha de fin ya pasó.
11. **Cierre automático:** un periodo activo pasa a inactivo cuando su `fecha_fin` es anterior a hoy.
    Lo hace el comando `semestres:cerrar-vencidos` (programado a las 00:05) y, por si el programador
    no corre, también al entrar a las pantallas de semestres y al validar el formulario.
12. **Edición de semestres:** se edita el periodo activo; sin ninguno activo, solo el inactivo más
    reciente (para reactivarlo). Los semestres **no se eliminan**.
13. **Catálogos con registros relacionados:** un departamento con instructores o coordinador, o una
    carrera con alumnos, no se puede eliminar (mensaje explicativo).

## 6. Decisiones técnicas

- **Laravel 13, no 8.** El cronograma dice Laravel 8, pero el proyecto es 13.37.0. Por eso las
  vistas son Blade + Tailwind 4 (**no Stisla**, que es de Laravel 8) y los middleware se registran
  en `bootstrap/app.php`. *Conviene corregir ese dato en la documentación del residente.*
- **Atributos en los modelos:** el proyecto usa los atributos nativos de PHP
  (`#[Table]`, `#[Fillable]`, `#[Hidden]`) en vez de `$table`/`$fillable`/`$hidden`.
- **Vistas del Sprint 2 copiadas del proyecto anterior:** las originales eran Stisla/Bootstrap 4
  con FontAwesome. Se reprodujeron con los mismos campos, columnas, botones, textos y flujos, pero en
  Tailwind 4 y componentes Blade (`x-tarjeta-formulario`, `x-fecha-hora`, `x-icono` con SVG en línea).
- **Zona horaria:** el cierre automático compara contra la fecha de la aplicación. Con
  `APP_TIMEZONE=UTC` (valor por defecto) un periodo se cierra a las 18:00 hora de Oaxaca del día
  anterior; conviene fijar `APP_TIMEZONE=America/Mexico_City` en `.env`.
- **Programador de tareas:** para que el cierre corra solo en producción hace falta un cron
  `* * * * * php artisan schedule:run`; en local, `php artisan schedule:work`.
- **Contraseña en columna `contrasena`:** el modelo sobrescribe `getAuthPassword()` y
  `getAuthPasswordName()`; el cast `hashed` se encarga de cifrar.
- **Sesiones en base de datos:** `SESSION_DRIVER=database`, por eso `sessions` está en la migración
  de usuarios.
- **SQLite en desarrollo:** el proyecto migra a MySQL/SQL Server más adelante. Evita funciones
  específicas de MySQL mientras tanto (columnas generadas, por ejemplo).

## 7. Cuentas de prueba

Están en `database/seeders/UsuariosPruebaSeeder.php` y **solo se siembran fuera de producción**
(la condición está en `DatabaseSeeder`). Contraseña: `Actividades2026`.

| Correo | Rol | Perfil |
|---|---|---|
| admin@gmail.com | administrador | — |
| coordinador@gmail.com | coordinador | Sistemas y Computación |
| instructor@gmail.com | instructor | Sistemas y Computación, especialidad Programación |
| alumno@gmail.com | alumno | ISC, semestre 5, núm. de control 20230001 |

## 8. Entorno local

- PHP 8.5.0 y Composer 2.8.12 vía **Herd Lite**, en `C:\Users\em556\.config\herd-lite\bin`
  (no están en el PATH de la terminal).
- Node 24.11.1 y npm 11.21.0 disponibles.
- Base de datos: SQLite en `database/database.sqlite`, se reconstruye con
  `php artisan migrate:fresh --seed`.
- No está instalado el CLI de GitHub (`gh`); la autenticación de Git es con Git Credential Manager.