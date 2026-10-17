# Sprint 2 · Catálogos base y semestre

**Estado:** entregado · **Fecha:** 2026-10-17 · **Alcance:** CRUD de departamentos, carreras y
ubicaciones; control de semestres con apertura y cierre automático; validación de la duración del
semestre (3 a 5 meses) y de la ventana de inscripción (5 a 10 días).

## Qué se entregó

### 1. Catálogos: departamentos, carreras y ubicaciones

Cada uno con listado paginado (10 por página) y buscador, alta, edición y eliminación, solo para quien
tenga el permiso correspondiente (por defecto el administrador).

| Catálogo | Campos | Reglas |
|---|---|---|
| Departamento | `nombre` (obligatorio, único, máx. 100), `edificio` (opcional) | No se elimina si tiene instructores o coordinador asignado. |
| Carrera | `nombre` (obligatorio, único, máx. 100) | No se elimina si tiene alumnos. El listado muestra cuántos tiene. |
| Ubicación | `espacio` (obligatorio, único, máx. 100), `capacidad` (entero de 1 a 9999) | Cuando existan horarios (Sprint 3) la base impedirá borrar una ubicación en uso. |

### 2. Semestres

- **Periodo activo** y **historial** de periodos pasados en una sola pantalla (`/semestres`).
- **Apertura:** «Iniciar Nuevo Periodo» (solo si no hay uno activo) con año, periodo (1 = ene–jun,
  2 = ago–dic), fechas del semestre, ventana de inscripciones y horas de apertura y cierre.
- **Cierre automático:** un periodo activo cuya `fecha_fin` ya pasó se marca como inactivo. Lo hace el
  comando `php artisan semestres:cerrar-vencidos`, programado todos los días a las 00:05.
  Como respaldo, también se ejecuta al abrir las pantallas de semestres y al validar el formulario.
- **Inscripciones abiertas/cerradas:** `Semestre::inscripcionesAbiertas()` decide por fecha y hora
  (abre el día de inicio a su hora, cierra el día de fin a su hora). La pantalla ya lo muestra; el
  Sprint 4 lo usará para permitir o bloquear inscripciones.
- **Edición:** se edita el periodo activo (incluido «Finalizar» cambiándolo a inactivo). Sin periodo
  activo, solo se puede **reactivar el inactivo más reciente**. Los demás quedan en solo lectura (candado).
- **Detalle** de cada periodo (`/semestres/{id}`).
- Los semestres **no se eliminan**.

### 3. Validaciones (`GuardarSemestreRequest`)

| Regla | Detalle | Error en |
|---|---|---|
| Duración del semestre | De 3 a 5 meses completos: `fin ≥ inicio + 3 meses` y `fin < inicio + 6 meses` | `fecha_fin` |
| Ventana de inscripción | De 5 a 10 días entre inicio y fin | `fecha_fin_inscripciones` |
| Inscripción dentro del semestre | Ambas fechas dentro del rango del semestre | `fecha_inicio_inscripciones` |
| Fechas coherentes | `fecha_fin` posterior a `fecha_inicio`; igual para inscripciones | cada campo |
| Un solo periodo activo | No se activa otro mientras haya uno activo | `status` |
| Periodo único | No se repite el par año + periodo | `periodo` |
| No activar un periodo vencido | No se activa uno cuya `fecha_fin` ya pasó (se cerraría de inmediato) | `status` |

El formulario muestra además un aviso en vivo («✓ Duración válida», «⚠ Mínimo 5 días») y limita los
calendarios al rango del periodo elegido (enero–junio o agosto–diciembre). Ese límite de meses es
solo de interfaz; el servidor no lo exige (igual que el proyecto anterior).

## Vistas copiadas del proyecto anterior

Se tomaron de `Actividades_Complementarias_ITO` (rama `main`) y se reprodujeron con los mismos
campos, columnas, botones, textos y flujos. **Cambió la tecnología, no el contenido:** las originales
eran Stisla/Bootstrap 4 + FontAwesome + jQuery y las nuevas son Blade + Tailwind 4 (regla del
proyecto: no se usan otras librerías de UI).

| Antes (`resources/views/admin/…`) | Ahora (`resources/views/…`) | Rutas |
|---|---|---|
| `departamentos/{index,crear,editar}` | `departamentos/{index,create,edit,_form}` | `departamentos.*` |
| `carreras/{index,crear,editar}` | `carreras/{index,create,edit,_form}` | `carreras.*` |
| `ubicaciones/{index,crear,editar}` | `ubicaciones/{index,create,edit,_form}` | `ubicaciones.*` |
| `semestres/{index,crear,editar,show}` | `semestres/{index,create,edit,show,_form}` | `semestres.*` |

Diferencias deliberadas respecto al original:

- Los nombres de rutas ya no llevan el prefijo `admin.` (`admin.carreras.index` → `carreras.index`),
  igual que `usuarios` y `roles` del Sprint 1; el acceso lo controlan los permisos.
- Crear y editar comparten `_form`, como en `usuarios`. Los avisos son `exito` y `error`.
- Iconos de FontAwesome → SVG en línea (`<x-icono>`).
- En semestres se quitó la columna **Grupos** y, en el detalle, las secciones de grupos, alumnos y
  calificaciones, porque dependen de tablas de los Sprints 3 a 5. En su lugar el índice muestra el
  estado de las inscripciones (Abiertas/Cerradas) y el detalle deja una nota. **Se restauran cuando
  exista `grupo`** (hay un comentario en `semestres/show.blade.php`).
- Los errores de validación del semestre ahora salen junto a cada campo, en vez de un solo aviso arriba.

## Archivos

### Nuevos

```
app/Console/Commands/CerrarSemestresVencidos.php
app/Http/Controllers/{Departamento,Carrera,Ubicacion,Semestre}Controller.php
app/Http/Requests/Guardar{Departamento,Carrera,Ubicacion,Semestre}Request.php
app/Models/Ubicacion.php        app/Models/Semestre.php
database/migrations/2026_10_17_100000_create_ubicacion_table.php
database/migrations/2026_10_17_100100_create_semestre_table.php
resources/views/{departamentos,carreras,ubicaciones}/{index,create,edit,_form}.blade.php
resources/views/semestres/{index,create,edit,show,_form}.blade.php
resources/views/components/{icono,tarjeta-formulario,fecha-hora}.blade.php
tests/Feature/{DepartamentosCrudTest,CarrerasCrudTest,UbicacionesCrudTest,SemestresTest,AccesoCatalogosTest}.php
```

### Modificados

| Archivo | Cambio |
|---|---|
| `routes/web.php` | 4 recursos con un permiso por acción (`middlewareFor`); `semestres` sin `destroy` |
| `routes/console.php` | Programa `semestres:cerrar-vencidos` diario a las 00:05 |
| `database/seeders/RolesYPermisosSeeder.php` | +15 permisos (total 21) |
| `database/seeders/CatalogosBaseSeeder.php` | +5 ubicaciones iniciales |
| `lang/es/validation.php` | Mensajes `after`, `date`, `date_format` y nombres de los campos nuevos |
| `resources/views/components/layouts/app.blade.php` | Enlaces del menú (según permiso) |
| `resources/views/panel.blade.php` | Enlaces en «Módulos disponibles» |
| `resources/views/components/seleccion.blade.php` | Parámetro opcional `ayuda` |
| `tests/Support/Escenarios.php` | `Escenarios::semestre()` |
| `tests/Feature/RolesPermisosTest.php` | Totales de permisos 6 → 21 |

### Migraciones

| Migración | Crea |
|---|---|
| `2026_10_17_100000_create_ubicacion_table` | `ubicacion` (`espacio` único, `capacidad`) |
| `2026_10_17_100100_create_semestre_table` | `semestre`, esquema final, único `(año, periodo)` |

## Rutas

| Ruta | Nombre | Permiso |
|---|---|---|
| `GET /departamentos` · `/carreras` · `/ubicaciones` · `/semestres` | `*.index` | `*.ver` |
| `GET /…/create` · `POST /…` | `*.create` · `*.store` | `*.crear` |
| `GET /…/{id}/edit` · `PUT /…/{id}` | `*.edit` · `*.update` | `*.editar` |
| `DELETE /departamentos/{id}` · `/carreras/{id}` · `/ubicaciones/{id}` | `*.destroy` | `*.eliminar` |
| `GET /semestres/{id}` | `semestres.show` | `semestres.ver` |

Todas dentro de `auth` + `usuario.activo` + `departamento.asignado`.

## Cómo ponerlo a funcionar en local

```sh
php artisan migrate        # crea ubicacion y semestre (conserva tus datos)
php artisan db:seed --class=RolesYPermisosSeeder     # agrega los 15 permisos nuevos
php artisan db:seed --class=CatalogosBaseSeeder      # agrega las 5 ubicaciones (idempotente)
# o, desde cero: php artisan migrate:fresh --seed
npm run build              # las vistas usan clases Tailwind nuevas
php artisan schedule:work  # (opcional) deja corriendo el cierre automático
```

## Verificación

- `php artisan migrate:fresh --seed`: 11 migraciones y 3 seeders sin errores.
- `php artisan test`: **122 pruebas, 474 aserciones, todas en verde** (51 previas + 71 nuevas).
- `vendor/bin/pint --test`: sin observaciones.
- Corrido con PHP 8.4 en un entorno aislado a partir del repositorio del Sprint 1.
  **No se probó en navegador** (no se compiló Tailwind ni se recorrieron las pantallas a mano): conviene
  revisar la apariencia con `npm run dev`.

## Fuera de alcance (para otros sprints)

- Grupos, actividades y horarios (usarán `ubicacion` y `semestre`) → Sprint 3.
- Middleware que bloquea al alumno cuando no hay periodo activo o inscripciones abiertas → Sprint 4.
- Dashboards → Sprint 6.

## Deuda técnica y pendientes

1. **Zona horaria:** con `APP_TIMEZONE=UTC` el cierre automático ocurre 6 horas antes de medianoche
   en Oaxaca. Conviene `APP_TIMEZONE=America/Mexico_City` en `.env` (no se tocó `.env`).
2. **Semántica de las horas de inscripción:** se interpretó «abre el día de inicio a su hora y cierra
   el día de fin a su hora». El proyecto anterior guardaba las horas pero nunca las usaba; confirmar
   con el residente antes del Sprint 4.
3. **Regla de «máximo 5 meses»:** se conserva la del proyecto anterior (meses completos, hasta 5
   meses y 29 días). Si debe ser estricta (`fin ≤ inicio + 5 meses`), cambiar una línea en
   `GuardarSemestreRequest::validarDuracion()` y el aviso en `semestres/_form.blade.php`.
4. La columna se llama `año` (con ñ) para ser fiel al modelo E-R. Funciona en SQLite y MySQL; si el
   motor final da problemas con la codificación, se puede renombrar a `anio`.
5. Quedan por crear las tablas `dia`, `actividad`, `grupo`, `horario`, `inscripcion`, `calificacion`,
   `log_acceso` (Sprint 0, paquete `Sprint0_Base_de_Datos.zip`).
