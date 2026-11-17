# Sprint 6 · Dashboards por rol

**Fecha:** 2026-11-10  
**Estado:** en curso  
**Historias:** HU-22 (RF-13) · Consultar dashboard del departamento.

## Alcance

El coordinador consulta, desde `/panel`, un resumen del departamento que coordina:
actividades, grupos, instructores, alumnos inscritos, inscripciones activas, aprobados,
reprobados y grupos recientes. El administrador ve el resumen general de todas las unidades
y el acceso se controla con el permiso `dashboard.ver`.

## HU-22 · Consultar dashboard del departamento

- Permiso nuevo `dashboard.ver`, asignado a `administrador` y `coordinador` en
  `RolesYPermisosSeeder`.
- `PanelController::dashboardDepartamento()` computa las métricas con alcance por
  `ambitoDepartamento()`. Si el coordinador no tiene departamento asignado, no hay dashboard.
- `resources/views/panel.blade.php` muestra la sección del dashboard cuando el usuario tiene
  datos: tarjetas de indicadores y tabla de grupos recientes.
- No hay ruta nueva; el dashboard vive en `/panel`, por lo que no modifica el middleware ni el enrutado actual.

## Permisos

- Total de permisos: 35 (se agrega `dashboard.ver`).
- `administrador`: `dashboard.ver` y dashboard general de todos los departamentos.
- `coordinador`: `dashboard.ver` y solo su departamento.
- `instructor` y `alumno`: sin acceso al dashboard.

## Modelo de datos

No agrega migraciones ni tablas.

## Rutas

No agrega rutas; expone información en `GET /panel` (`PanelController`).

## Pruebas

- `tests/Feature/DashboardDepartamentoTest.php` (6 pruebas): dashboard del coordinador,
  alcance por departamento, dashboard general del administrador, sin dashboard para
  instructor/alumno, coordinador sin departamento y asignación del permiso.
- `tests/Feature/RolesPermisosTest.php` (`TOTAL_PERMISOS` = 35).

**Verificación:** `php artisan test` → **271 pruebas, 1054 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones.

Pendiente: HU-23 · Consultar reportes administrativos.
