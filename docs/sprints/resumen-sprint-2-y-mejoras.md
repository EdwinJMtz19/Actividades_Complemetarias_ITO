# Resumen: Sprint 2 y mejora de vistas

**Fechas:** 2026-10-17 (Sprint 2) y 2026-10-18 (mejora visual) · **Proyecto:** Actividades Complementarias ITO

Este documento resume todo lo hecho. El detalle vive en:
[`sprint-02-catalogos-y-semestres.md`](sprint-02-catalogos-y-semestres.md),
[`bitacora.md`](../bitacora.md), [`contexto.md`](../contexto.md) y [`DESIGN.md`](../../DESIGN.md).

---

## 1. Sprint 2 · Catálogos base y semestre

Según el cronograma: CRUD de departamentos, carreras y ubicaciones; semestres con apertura y cierre
automático; validación de la duración (3 a 5 meses) y de la ventana de inscripción (5 a 10 días).

### Catálogos

| Catálogo | Campos | Regla al eliminar |
|---|---|---|
| Departamento | `nombre` (único, máx. 100), `edificio` (opcional) | No se elimina si tiene instructores o coordinador |
| Carrera | `nombre` (único, máx. 100) | No se elimina si tiene alumnos |
| Ubicación | `espacio` (único), `capacidad` (1 a 9999) | La base lo impedirá cuando haya horarios (Sprint 3) |

Todos con listado paginado (10 por página), buscador y permisos por acción
(`departamentos.ver/crear/editar/eliminar`, y lo mismo para `carreras` y `ubicaciones`).

### Semestres

- Periodo activo e historial en una sola pantalla (`/semestres`) y detalle de cada periodo.
- Un solo periodo activo a la vez; un solo semestre por año y periodo.
- Se edita el periodo activo; sin ninguno activo, solo se reactiva el inactivo más reciente.
- Los semestres **no se eliminan** (no existe el permiso `semestres.eliminar`).
- **Cierre automático:** el comando `semestres:cerrar-vencidos` corre todos los días a las 00:05. Como
  respaldo también se ejecuta al abrir las pantallas de semestres y al validar el formulario.
- **Inscripciones abiertas/cerradas:** `Semestre::inscripcionesAbiertas()` decide por fecha y hora
  (abre el día de inicio a su hora y cierra el día de fin a su hora). Lo usará el Sprint 4.

### Validaciones (`GuardarSemestreRequest`)

| Regla | Detalle |
|---|---|
| Duración del semestre | De 3 a 5 meses completos: `fin ≥ inicio + 3 meses` y `fin < inicio + 6 meses` |
| Ventana de inscripción | De 5 a 10 días, y dentro del rango del semestre |
| Un solo periodo activo | No se activa otro mientras haya uno activo |
| Periodo único | No se repite año + periodo |
| No activar un periodo vencido | Un periodo con `fecha_fin` pasada se cerraría de inmediato |

El formulario avisa en vivo («✓ Duración válida», «⚠ Mínimo 5 días») y limita los calendarios al rango del
periodo elegido (enero–junio o agosto–diciembre; solo en la interfaz).

### Archivos principales

```
app/Console/Commands/CerrarSemestresVencidos.php
app/Http/Controllers/{Departamento,Carrera,Ubicacion,Semestre}Controller.php
app/Http/Requests/Guardar{Departamento,Carrera,Ubicacion,Semestre}Request.php
app/Models/{Ubicacion,Semestre}.php
database/migrations/2026_10_17_100000_create_ubicacion_table.php
database/migrations/2026_10_17_100100_create_semestre_table.php
tests/Feature/{DepartamentosCrud,CarrerasCrud,UbicacionesCrud,Semestres,AccesoCatalogos}Test.php
```

Modificados: `routes/web.php`, `routes/console.php`, `RolesYPermisosSeeder` (+15 permisos, total 21),
`CatalogosBaseSeeder` (+5 ubicaciones), `lang/es/validation.php`, `Escenarios`, `RolesPermisosTest`.

### Vistas copiadas del proyecto anterior

Las originales eran Stisla/Bootstrap 4. Se reprodujeron con los mismos campos, columnas, botones, textos y
flujos, pero en **Blade + Tailwind 4**, porque las convenciones del proyecto prohíben otras librerías de UI.
Diferencias deliberadas:

- Rutas sin el prefijo `admin.` (`carreras.index`, no `admin.carreras.index`), como `usuarios` y `roles`.
- Crear y editar comparten `_form`.
- Iconos de FontAwesome → SVG en línea (`<x-icono>`).
- En semestres se quitó la columna **Grupos** y las secciones de grupos, alumnos y calificaciones del
  detalle, porque dependen de tablas de los Sprints 3 a 5. Se restauran cuando exista `grupo`.

---

## 2. Mejora de vistas, animaciones y logo

### Componentes nuevos

| Componente | Para qué sirve |
|---|---|
| `x-boton` | Botón o enlace de texto (primario, secundario, consulta, periodo, sobre-azul), con icono y gesto |
| `x-accion` | Botón de icono de 2 rem para las filas: ver, editar, editar-periodo, eliminar |
| `x-barra-catalogo` | Encabezado de listado: título con total, buscador, «Limpiar» y botón de alta |
| `x-tarjeta-formulario` | Tarjeta de alta/edición con «Cancelar» y «Guardar» |
| `x-fecha-hora` | Fecha dd/mm/aaaa con la hora como insignia |

### Animaciones (`resources/css/app.css`)

Opt-in por clase `mov-*`, con los tokens de tiempo que ya existían (120 / 200 / 360 ms).

- **Botones:** color en 120 ms y hundimiento de 2 % al pulsar.
- **Gestos del icono al pasar el cursor:** el «+» gira, la flecha de volver se adelanta, lápiz y papelera se
  inclinan, ojo y lupa crecen.
- **Enlaces:** el subrayado de la barra y de las acciones de tabla se dibuja de izquierda a derecha.
- **Buscador:** el foco se dibuja en 120 ms.
- **Avisos:** el mensaje de «guardado» o de error se abre desde su borde superior.
- **Envío:** todos los «Guardar» usan la barra de espera del login y bloquean el doble clic.
- **Movimiento reducido:** todo tiene su ruta en `prefers-reduced-motion`.
- Se mantiene **prohibido**: elevación o sombra en hover, rebotes y revelado por scroll.

### Cambios de vista

- Listados de catálogos con cabecera gris, total de registros y mensaje de vacío que dice qué se buscó.
- **Editar** pasa a botón secundario con borde; el **ámbar** queda solo para editar o reactivar periodos;
  **eliminar** usa el rojo de peligro (reglas de `DESIGN.md`).
- Botones de usuarios, roles, semestres, login y registro usan los mismos componentes.

### Logo del ITO

`resources/logos/instituto-tecnologico-de-oaxaca-ito-logo-png_seeklogo-500948.png` es ahora una marca de
agua fija en login y registro (`.fondo-logo`): centrada, tamaño `min(110vmin, 56rem)`, `opacity: 0.08` y
`mix-blend-mode: multiply` para que el blanco del archivo desaparezca. Queda detrás de la tarjeta de acceso.
Vite procesa la ruta; no se copia nada a `public/`.

### Documentación actualizada

`DESIGN.md` (sección Motion y botones), `PRODUCT.md` (ya hay logo), `docs/README.md`, `docs/contexto.md`,
`docs/convenciones.md`, `docs/bitacora.md` y el documento del Sprint 2.

---

## 3. Verificación

| Comprobación | Resultado |
|---|---|
| `php artisan migrate:fresh --seed` (sandbox, PHP 8.4) | Sin errores |
| `php artisan test` tras el Sprint 2 | **122 pruebas, 474 aserciones, en verde** (51 previas + 71 nuevas) |
| `vendor/bin/pint --test` | Limpio |
| Tras la mejora visual: pruebas del Sprint 2 + render de `/login` y `/registro` | En verde |
| `vite build` del CSS | Compila; el logo queda con huella y existen las clases nuevas |

**No verificado:** nada se probó en un navegador real, así que la apariencia, las animaciones y la opacidad
del logo están sin revisar a ojo. Tampoco se corrió la suite completa con tus pruebas de login más recientes.

---

## 4. Cómo ponerlo a funcionar

```powershell
php artisan migrate
php artisan db:seed --class=RolesYPermisosSeeder     # agrega los 15 permisos nuevos
php artisan db:seed --class=CatalogosBaseSeeder      # agrega las 5 ubicaciones (idempotente)
# o desde cero: php artisan migrate:fresh --seed
npm run build                                        # o npm run dev
php artisan test
vendor\bin\pint --test
php artisan schedule:work                            # opcional: cierre automático en local
```

---

## 5. Decisiones y pendientes

1. **Zona horaria:** con `APP_TIMEZONE=UTC` el cierre automático ocurre unas 6 horas antes de medianoche en
   Oaxaca. Conviene `APP_TIMEZONE=America/Mexico_City` en `.env` (no se tocó).
2. **Horas de inscripción:** se interpretó «abre el día de inicio a su hora y cierra el día de fin a su hora».
   El proyecto anterior las guardaba pero no las usaba; confirmarlo antes del Sprint 4.
3. **«Máximo 5 meses»:** se conservó la regla del proyecto anterior (meses completos, hasta 5 meses y 29 días).
   Si debe ser estricta, es un cambio de una línea en `GuardarSemestreRequest::validarDuracion()`.
4. **Columna `año` (con ñ):** fiel al modelo E-R; funciona en SQLite y MySQL. Se puede renombrar a `anio`.
5. **Extras no pedidos:** no se activa un periodo ya vencido y `ubicacion.espacio` es único en la base de datos.
6. **Logo:** si se quiere más visible, ajustar `opacity` y tamaño en `.fondo-logo`; si se quiere en el
   encabezado de la aplicación, reemplazar el nombre en `azul-ito` (ver `DESIGN.md`).
7. **Tablas aún sin crear:** `dia_semana`, `actividad_complementaria`, `actividad_carrera`, `grupo`,
   `horario` (Sprint 3), `inscripcion` (4), `calificacion` (5) y `log_acceso` (6).
8. **Sin commit:** los cambios no se subieron a git.

## 6. Incidente corregido

Durante la mejora visual un reemplazo borró por accidente la columna «Nombre» de `usuarios/index`. Se
restauró de inmediato y el diff lo confirmó, pero conviene mirar esa tabla.
