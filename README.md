# Actividades Complementarias ITO

Sistema web para administrar las **actividades complementarias** del Instituto Tecnológico de
Oaxaca (ITO): los cursos o talleres que el/alumno debe cursar durante su carrera, quién los imparte,
en qué horario, cuántos lugares hay, quién se inscribe y cómo se califica.

Resuelve tres problemas concretos del proceso del ITO:

1. **El padrón desactualizado.** Departamentos, carreras e instructores se consultan directo de la base
   de datos, sin depender de una hoja de cálculo que se desincroniza.
2. **El proceso manual.** La inscripción de alumnos a actividades se hace hoy por medio de una plataforma que solamente cubre la inscripcion a las actividades complementarias y la generacion de horarios, pero no cubre todo el proceso que sigue el TecNM y el ITO para el control, acreditacion y liberacion de creditos a los alumnos, ya que estos procesos se hacen de forma manual y presencial en oficinas
3. ** .** 

## Módulos que funcionan hoy

| Módulo | Qué hace |
|---|---|
| **Acceso** | Inicio y cierre de sesión, registro público solo para alumnos, límite de intentos, cierre automático de la sesión si la cuenta se desactiva y registro del último acceso. |
| **Usuarios** | Alta, consulta, edición, activación y baja de cuentas. Cada rol guarda sus datos propios: alumno (número de control, carrera, semestre), instructor (departamento, especialidad) o coordinador (departamento que coordina). |
| **Roles y permisos** | Cuatro roles (administrador, coordinador, instructor, alumno) y 21 permisos que se administran desde una pantalla. |
| **Departamentos, carreras y ubicaciones** | Catálogos con buscador, paginación, alta, edición y baja protegida cuando ya hay registros relacionados. |
| **Semestres** | Apertura de un periodo escolar con su ventana de inscripción, historial de periodos, y **cierre automático** cuando termina la fecha de fin. Valida que el semestre dure de 3 a 5 meses y que la ventana de inscripción de 5 a 10 días. |
| **Alcance por departamento** | Un coordinador solo ve y administra a los instructores de su departamento; sin departamento asignado no puede operar. |

## Roles

| Rol | Qué puede hacer |
|---|---|
| **Administrador** | Todo el sistema, sin restricciones de departamento. Único que administra usuarios, roles y catálogos. |
| **Coordinador** | Solo ve y administra a los instructores de su departamento. |
| **Instructor** | Módulo propio en el Sprint 3. |
| **Alumno** | Inscripción a actividades en el Sprint 4. |



## Stack

| Componente | Tecnología |
|---|---|
| Framework | Laravel 13 |
| Lenguaje | PHP 8.3 o superior |
| Base de datos | MySQL 8 (`utf8mb4`) · las pruebas corren en SQLite en memoria |
| Autenticación y permisos | `spatie/laravel-permission` 8.3 |
| Vistas | Blade + Tailwind CSS 4 (Vite) |
| Pruebas | Pest 5 |



## Requisitos

- PHP 8.3 o superior
- Composer 2
- Node.js 20 o superior y npm
- SQLite 

## Instalación

```sh
composer install
npm install

cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

composer require spatie/laravel-permission
php artisan migrate --seed    # sobre la base MySQL del .env
npm run build

php artisan serve             # http://localhost:8000
```

> **Motor de base de datos.** El proyecto usa **MySQL 8**. Crear la base una sola vez antes de la
> primera migración:
>
> ```sql
> CREATE DATABASE ActividadesComplementarias2026
>   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> ```
>
>

## Cuentas de prueba

Solo existen fuera de producción.

| Correo | Rol | Alcance |
|---|---|---|
| admin@gmail.com | administrador | Todo el sistema |
| coordinador@gmail.com | coordinador | Instructores de su propio departamento|
| instructor@gmail.com | instructor | Módulo propio  |
| alumno@gmail.com | alumno | Inscripciones y calificaciones |

## Pruebas y estilo de código

```sh
php artisan test             # 139 pruebas
vendor\bin\pint              # formatea el código
vendor\bin\pint --test       # solo revisa
```

## Estructura

```
app/
  Http/Controllers/   Controladores (Auth, Panel, Usuario, Rol, y los catálogos)
  Http/Middleware/    usuario.activo, departamento.asignado
  Http/Requests/      Validación de formularios
  Models/             User, Alumno, Instructor, Departamento, Carrera, Ubicacion, Semestre
  Policies/           UserPolicy
database/
  migrations/         Esquema de la base (11 migraciones)
  seeders/            Roles y permisos, catálogos base y cuentas de prueba
resources/views/      Blade: auth, usuarios, roles, departamentos, carreras, ubicaciones, semestres
routes/web.php        Rutas web
tests/Feature/        Pruebas Pest
```

## Notas

- **La base de datos se reconstruye** con `php artisan migrate --seed`. En una base ya migrada usa
  `php artisan migrate:seed` para volver a sembrar los catálogos y las cuentas de prueba.
- **El cierre automático de semestres** también se ejecuta al abrir las pantallas de semestres, como
  respaldo del comando programado `semestres:cerrar-vencidos` (diario a las 00:05).


## Repositorio

`https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO`
