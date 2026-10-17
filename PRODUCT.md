# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Cuatro grupos con el mismo peso en el producto; ninguna pantalla es la «principal».

| Rol | Situación | Trabajo por hacer |
|---|---|---|
| `administrador` | Dirección del ITO, en escritorio | Administrar cuentas, roles, catálogos (departamentos, carreras, ubicaciones) y semestres. Único con acceso total. |
| `coordinador` | Jefatura de un departamento, en escritorio | Ver y administrar solo a los instructores de su departamento. |
| `instructor` | Docente, en escritorio o tablet | Aún sin módulo: sus grupos y actividades (Sprint 3 y 5). |
| `alumno` | Estudiante, mayormente móvil y fuera del horario de oficina | Aún sin módulo: consulta e inscripción en actividades (Sprint 4). |

Los cuatro acceden con correo y contraseña; el sistema no tiene invitaciones ni alta por parte de
terceros salvo el registro público de alumnos.

## Product Purpose

Administrar de punta a punta las actividades complementarias del ITO: quién imparte qué, en qué
lugar y horario, quién se inscribió y cómo se califica.

Existió un sistema anterior, con Laravel 8 + Stisla/Bootstrap 4. Este lo reemplaza. La razón del
reemplazo, según el equipo, es la combinación de: control de permisos y alcance por rol y departamento que el
anterior no tenía, trazabilidad y consultas a la base de datos más confiables, y **mejoras
visibles en las vistas**.

Éxito, en términos del equipo: el personal del ITO administra su trabajo diario en el sistema sin
volver al anterior, y las consultas (reportes, listados, dashboards) responden con datos correctos.

## Positioning

Un sistema institucional de alcance controlado: cada rol ve exactamente el tramo del Instituto que le
corresponde (el coordinador no sale de su departamento, el alumno no ve administration), y esa
restricción es el producto, no un accidente. El alcance se declara con datos —`tipo_usuario` derivado
del rol, filtro por departamento— en lugar de dejarse a cada pantalla.

## Operating Context

- **Cronograma por sprints**, no entrega continua. Sprints 1 y 2 entregados (usuarios y roles;
  catálogos base y semestre); 3 a 6 pendientes (actividades/grupos/horarios, inscripción de alumnos
  con recuperación de contraseña por SMTP, calificaciones, dashboards por rol).
- **Semestres como eje del calendario.** Todo lo demás cuelga del semestre activo: las inscripciones
  solo abren dentro de su ventana de 5 a 10 días, y los periodos vencidos se cierran solos.
- **Documentación en español y obligatoria.** `AGENTS.md`, `docs/README.md`, `docs/contexto.md`,
  `docs/convenciones.md` y `docs/bitacora.md` son parte del flujo de trabajo: cada cambio se documenta
  ahí y hay que leerlos antes de tocar código.
- **Reglas firmes del repo:** nada sin pruebas Pest en verde y `vendor\bin\pint --test` limpio; no se
  editan migraciones aplicadas; el acceso se controla con permisos y políticas, no con condicionales
  en las vistas; Spatie es la única fuente de rol; no se sube `.env`, `vendor/` ni `node_modules/`;
  no se force-pushea.
- **Ambiente local:** PHP 8.5 y Composer por Herd Lite (fuera del PATH en Windows), Node/npm
  disponibles, SQLite en desarrollo con destino futuro MySQL/SQL Server — nada de funciones
  exclusivas de MySQL.
- **Sprint 0 aporta el modelo de datos** en el paquete `Sprint0_Base_de_Datos.zip`; tablas como
  `dia`, `actividad`, `grupo`, `horario`, `inscripcion` y `calificacion` llegan desde ahí.
- **No hay equipo de diseño.** Las vistas se hacen directamente en Blade; no hay proceso de
  handoff visual.

## Capabilities and Constraints

Confirmado:

- Autenticación con correo y contraseña, cierre de sesión automático para cuentas inactivas,
  límite de 5 intentos por correo + IP.
- Cuatro roles y permisos Spatie; permisos granulares del tipo `recurso.accion`.
- Alcance por departamento para el coordinador, con middleware propio y política `UserPolicy`.
- CRUD de departamentos, carreras y ubicaciones, y semestres con validaciones de duración y de
  ventana de inscripción.
- Registros que no se pueden eliminar si tienen relaciones (departamentos, carreras, cuentas); los
  semestres nunca se eliminan.
- SQLite en desarrollo, con el esquema pensado para migrar a MySQL/SQL Server.
- Vistas Blade + Tailwind CSS 4, sin librerías de UI de terceros.

Abierto / sin decidir:

- Los módulos de instructor y alumno todavía no existen; sus necesidades de pantalla no están
  confirmadas.
- Requisito de estándar de accesibilidad: no se ha establecido ninguno específico.
- El modelo de `inscripcion` del paquete Sprint 0 usa una columna generada de MySQL y habrá que
  adaptarla a SQLite.

## Brand Commitments

- **Nombre:** «Actividades Complementarias ITO». **Idioma de todo el producto: español** (incluidos
  los textos de las vistas).
- **El logotipo del ITO es obligatorio** en la interfaz.
- La paleta puede seguir la del sistema anterior, y el aspecto puede ser parecido al anterior
  **con mejoras**. Continuar la línea visual previa está permitido y es la opción por defecto; no es
  obligación conservarla si una mejora se justifies.
- Las vistas del Sprint 2 ya reprodujeron la del sistema anterior (mismos campos, columnas,
  botones y textos) con componentes Blade propios, `x-icono` con SVG en línea y `x-fecha-hora`.

## Evidence on Hand

- Cuentas de prueba con datos reales de muestra (solo fuera de producción), contraseña
  `Actividades2026`: `admin@gmail.com`, `coordinador@gmail.com`, `instructor@gmail.com`,
  `alumno@gmail.com`. Sirven para verificar cualquier pantalla por rol.
- Seeders con catálogos base: departamentos, carreras y ubicaciones.
- Pruebas Pest de los Sprints 1 y 2 (122 al momento de escribir esto) que describen el comportamiento
  esperado de usuarios, roles, catálogos y semestres.
- Repositorio: `https://github.com/EdwinJMtz19/Actividades_Complemetarias_ITO`.

Ausencias que no se deben inventar:

- **No hay archivo de logotipo en el repositorio** (`public/` solo tiene `favicon.ico`). Hay que
  pedirlo antes de tratar la identidad como algo verificable.
- No existen testimonios, cifras de uso, estudios de caso, precios ni logos de terceros.
- No hay material del sistema anterior en este repo; solo se conoce por descripción.

## Product Principles

1. **Cada rol ve su tramo, y solo su tramo.** El alcance por departamento y por rol es la promesa del
   sistema; se resuelve con permisos y políticas, nunca escondiendo botones.
2. **El semestre manda.** Toda decisión de calendario —inscripciones, actividades, calificaciones—
   se lee del semestre activo, nunca de un supuesto en la pantalla.
3. **Primero los datos, luego la pantalla.** Consultas correctas y consistentes van antes del
   empaque visual; las mejoras de vistas son el motivo del reemplazo, no un lujo posterior.
4. **Cambios probados y documentados.** Sin pruebas Pest en verde, sin `pint --test` limpio y sin
   entrada en la bitácora, el cambio no está terminado.
5. **Conservar lo que funciona.** El personal conoce las pantallas del sistema anterior; mejorar no
   significa replantear de cero lo que ya se usa bien.

## Stack

Definido por el repositorio: Laravel 13 + Blade + Tailwind CSS 4 + SQLite, `spatie/laravel-permission`,
Pest para pruebas. Un proyecto existente, así que no hay decisión de stack abierta.