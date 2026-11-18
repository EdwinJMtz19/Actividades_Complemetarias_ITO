# Sprint 6 — Dashboards por rol y reportes administrativos

**Fecha de entrega:** 2026-11-17
**Estado:** entregado
**Historias:** HU-22 (RF-13) — Consultar dashboard del departamento; HU-23 (RF-13) — Consultar reportes administrativos.

## Alcance

El coordinador consulta, desde `/panel`, un resumen del departamento que coordina:
actividades, grupos, instructores, alumnos inscritos, inscripciones activas, aprobados,
reprobados y grupos recientes. El administrador ve el resumen general de todas las unidades
y, además, accede a reportes administrativos de solo lectura sobre alumnos, inscripciones y
últimos accesos. El acceso se controla con los permisos `dashboard.ver` y `reportes.ver`.

## HU-22 — Consultar dashboard del departamento

- Permiso nuevo `dashboard.ver`, asignado a `administrador` y `coordinador` en
  `RolesYPermisosSeeder`.
- `PanelController::dashboardDepartamento()` computa las métricas con alcance por
  `ambitoDepartamento()`. Si el coordinador no tiene departamento asignado, no hay dashboard.
- `resources/views/panel.blade.php` muestra la sección del dashboard cuando el usuario tiene
  datos: tarjetas de indicadores y tabla de grupos recientes.
- No hay ruta nueva; el dashboard vive en `/panel`, por lo que no modifica el middleware ni el
  enrutado actual.

## HU-23 — Consultar reportes administrativos

- Permiso nuevo `reportes.ver`, asignado únicamente a `administrador`.
- `ReporteController` expone cuatro pantallas de solo lectura:
  - `/reportes`: índice con totales de alumnos, inscripciones, grupos y actividades.
  - `/reportes/alumnos`: padrón con búsqueda por nombre, correo o número de control, filtro por
    carrera e inscripciones activas por alumno.
  - `/reportes/inscripciones`: monitor con filtros por estatus, carrera, actividad, departamento y
    semestre, más totales por estatus.
  - `/reportes/accesos`: últimos accesos por usuario con filtros por rol y búsqueda.
- El reporte de accesos usa `USUARIO.ultimo_acceso`; no se agregó una tabla de bitácora histórica.
- Se agregaron enlaces a reportes en el panel y en la navegación, visibles solo con `reportes.ver`.
- No agrega migraciones ni tablas.

## Permisos

- Total de permisos: 36 (se agregan `dashboard.ver` y `reportes.ver`).
- `administrador`: `dashboard.ver` y `reportes.ver`, con dashboard general y todos los reportes.
- `coordinador`: `dashboard.ver` y solo su departamento; sin acceso a reportes.
- `instructor` y `alumno`: sin acceso al dashboard ni a reportes.

## Rutas

- `GET /panel` (`PanelController@index`) muestra el dashboard según el rol.
- `GET /reportes` (`ReporteController@index`).
- `GET /reportes/alumnos` (`ReporteController@alumnos`).
- `GET /reportes/inscripciones` (`ReporteController@inscripciones`).
- `GET /reportes/accesos` (`ReporteController@accesos`).
- Las rutas de reportes usan `permission:reportes.ver` y el controlador refuerza con
  `Gate::authorize('reportes.ver')`.

## Pruebas

- `tests/Feature/DashboardDepartamentoTest.php` (6 pruebas): dashboard del coordinador,
  alcance por departamento, dashboard general del administrador, sin dashboard para
  instructor/alumno, coordinador sin departamento y asignación del permiso.
- `tests/Feature/ReportesAdministrativosTest.php` (9 pruebas): login obligatorio, índice del
  administrador, 403 para coordinador/instructor/alumno, permiso exclusivo, enlace en el panel,
  padrón de alumnos con filtros, monitor de inscripciones con totales y filtros, y reporte de
  accesos con filtros.
- `tests/Feature/RolesPermisosTest.php` (`TOTAL_PERMISOS` = 36).

**Verificación:** `php artisan test` → **302 pruebas, 1243 aserciones, en verde**;
`vendor\bin\pint --test` sin observaciones.
