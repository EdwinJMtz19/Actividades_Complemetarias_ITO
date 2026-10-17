---
name: Actividades Complementarias ITO
description: Sistema de administración del ITO donde el semestre activo manda: azul oficial, grises de expediente, planos por defecto y estados siempre inequívocos.
colors:
  azul-ito: "oklch(48.8% 0.243 264.376)"
  azul-ito-hover: "oklch(42.4% 0.199 265.638)"
  azul-ito-tenue: "oklch(93.2% 0.032 255.585)"
  azul-consulta: "oklch(58.8% 0.158 241.966)"
  ambar-editar: "oklch(82.8% 0.189 84.429)"
  lienzo: "oklch(98.4% 0.003 247.858)"
  superficie: "#ffffff"
  borde-sutil: "oklch(92.9% 0.013 255.508)"
  borde-fuerte: "oklch(86.9% 0.022 252.894)"
  texto-fuerte: "oklch(27.9% 0.041 260.031)"
  texto-medio: "oklch(44.6% 0.043 257.281)"
  texto-tenue: "oklch(55.4% 0.046 257.417)"
  estado-activo: "oklch(44.8% 0.119 151.328)"
  estado-inactivo: "oklch(37.2% 0.044 257.287)"
  estado-peligro: "oklch(57.7% 0.245 27.325)"
  estado-aviso: "oklch(47.3% 0.137 46.201)"
typography:
  headline:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.333
  title:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.556
  body:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    lineHeight: 1.429
  label:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.333
    letterSpacing: "0.025em"
  body-lg:
    fontFamily: "'Instrument Sans', ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    lineHeight: 1.5
rounded:
  control: "0.375rem"
  superficie: "0.5rem"
  insignia: "0.25rem"
  pill: "9999px"
spacing:
  xs: "0.25rem"
  sm: "0.5rem"
  md: "0.75rem"
  control: "1.25rem"
  card: "1.25rem"
  section: "2rem"
components:
  button-primary:
    backgroundColor: "{colors.azul-ito}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.control}"
    padding: "0.5rem 1rem"
    typography: "600 0.875rem/1.429 {typography.body.fontFamily}"
  button-primary-hover:
    backgroundColor: "{colors.azul-ito-hover}"
  button-secondary:
    backgroundColor: "{colors.superficie}"
    textColor: "{colors.texto-medio}"
    rounded: "{rounded.control}"
    padding: "0.5rem 1rem"
  button-secondary-hover:
    backgroundColor: "{colors.lienzo}"
  button-icon:
    backgroundColor: "{colors.azul-consulta}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.control}"
    size: "2rem"
  button-icon-editar:
    backgroundColor: "{colors.ambar-editar}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.control}"
    size: "2rem"
  card:
    backgroundColor: "{colors.superficie}"
    rounded: "{rounded.superficie}"
    padding: "1.25rem"
  input:
    backgroundColor: "{colors.superficie}"
    textColor: "{colors.texto-fuerte}"
    rounded: "{rounded.control}"
    padding: "0.5rem 0.75rem"
    height: "2.375rem"
  chip-activo:
    backgroundColor: "{colors.estado-activo}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.pill}"
    padding: "0.125rem 0.5rem"
  chip-inactivo:
    backgroundColor: "{colors.borde-fuerte}"
    textColor: "{colors.estado-inactivo}"
    rounded: "{rounded.pill}"
    padding: "0.125rem 0.5rem"
---

# Design System: Actividades Complementarias ITO

## Overview

**Creative North Star: "El Orden del Día"**

Este sistema se lee como el orden del día de un periodo académico, no como un tablero de software.
El semestre activo manda: cuando hay un periodo en curso, su banda de azul ocupa el primer plano de la
pantalla de semestres y todo lo demás se ordena por debajo. Esa jerarquía —primero el periodo, luego su
historial, luego el detalle— es la misma que gobierna el resto del producto: la pantalla de usuarios pone
encima la cuenta, abajo los módulos, y el resumen de cuenta antes que el de módulos.

La personalidad es administrativa y sobria, en el sentido bueno de la palabra: el pulso lo da el azul
oficial del ITO y una escala de grises fría, y todo lo demás es estructura. La densidad es alta a
propósito —las tablas llevan sus filas a `0.75rem` de padding vertical y el cuerpo del texto es de
`0.875rem`— porque quien usa esto revisa catálogos durante la jornada, no lee reportes.
La interacción se anuncia en lugar de decorarse: un estado activo es verde sobre verde claro y en
versalitas, un estado inactivo es gris, y no hay ambigüedad entre ambos. No hay gradientes,
no hay sombras como ornamento, no hay microinteracciones: hay bordes de 1 píxel, fondos que se separan
por tono y color reservado exclusivamente para acción y estado.

El logotipo del ITO es obligatorio en la interfaz, pero **aún no hay archivo en el repositorio**: el
header actual usa el nombre de la aplicación en `azul-ito`. Cuando llegue el logo, reemplaza ese texto en
el mismo tamaño óptico y con la misma altura de línea; no se rediseña el header.

**Key Characteristics:**
- El azul del ITO significa acción o estado activo; nunca decoración.
- El gris `slate` es el sistema: fondo de página, tarjetas blancas, bordes que separan.
- Superficies planas; sombra solo donde algo se despega del plano.
- Jerarquía primero el periodo, luego el historial, luego el detalle.
- Toda la interfaz en español, con etiquetas del dominio tal como las usa el personal («Periodo»,
  «Inscripciones», «Historial de Periodos Pasados»).

## Colors

Una sola voz cromática —el azul oficial del ITO— sobre una escala de grises fría; el resto del color
existe únicamente para marcar estado.

### Primary
- **Azul ITO** (`oklch(48.8% 0.243 264.376)`, `blue-700`): la acción principal de cada pantalla
  (Guardar, Nuevo usuario, Entrar) y el nombre de la aplicación en el header. También es el fondo de la
  banda «Periodo Actual Activo», donde funciona como cintillo de estado, y el color de los enlaces de
  acción (`Ver`, `Editar`, `Filtrar`). En hover pasa a `azul-ito-hover`.
- **Azul ITO tenue** (`oklch(93.2% 0.032 255.585)`, `blue-100`): fondo de la insignia del rol del usuario
  en el header, con texto `azul-ito`. Sirve para decir «quién eres» sin gastar el azul de acción.

### Secondary
- **Azul Consulta** (`oklch(58.8% 0.158 241.966)`, `sky-600`): reservado para la acción de lectura
  pura —«Ver detalle», «Ver». Un paso más frío que el primario para que en una fila de acciones no se
  confunda con «Editar».

### Tertiary
- **Ámbar Editar** (`oklch(82.8% 0.189 84.429)`, `amber-400`): exclusivo de la acción que cambia el
  estado de un periodo (Editar / Finalizar, Reactivar). Su rareza es el punto: si ves ámbar, el periodo
  está en una transición y la pantalla te lo dice.

### Neutral
- **Lienzo** (`oklch(98.4% 0.003 247.858)`, `slate-50`): fondo del `body` y de las cabeceras de tabla.
- **Superficie** (`#ffffff`): tarjetas, tablas, campos y el header. El contraste blanco sobre gris frío
  es el mecanismo de profundidad principal del sistema.
- **Borde sutil** (`oklch(92.9% 0.013 255.508)`, `slate-200`): separadores internos, borde de tarjeta,
  divisores de fila.
- **Borde fuerte** (`oklch(86.9% 0.022 252.894)`, `slate-300`): borde de campo y de botón secundario, la
  única línea con peso suficiente para marcar un control.
- **Texto fuerte** (`oklch(27.9% 0.041 260.031)`, `slate-800`): color de texto por defecto del `body`.
- **Texto medio** (`oklch(44.6% 0.043 257.281)`, `slate-600`): descripciones, navegación, texto de tabla
  secundario.
- **Texto tenue** (`oklch(55.4% 0.046 257.417)`, `slate-500`): etiquetas de sección, encabezados de
  tabla en `slate-100` sobre `slate-800`, estados vacíos.

### Named Rules
**The One Voice Rule.** El `azul-ito` se usa para acción o estado activo, nunca como adorno. Si una
pantalla tiene más de un `azul-ito` sólido visible a la vez, uno de los dos es un error.

**The Green Means Live Rule.** El verde (`estado-activo` sobre `green-100`, `oklch(44.8% 0.119
151.328)`) significa únicamente «esto está en vigor ahora»: periodo activo, inscripciones abiertas,
usuario activo. Nunca se usa para confirmar un acierto. Un mensaje de éxito de la sesión (`exito`)
también es verde, pero como banda con borde, nunca como insignia.

**The Gray Is the Default Rule.** Todo lo que no está activo, no está en error y no está en advertencia es
`slate`. Un periodo cerrado, un usuario inactivo y un botón secundario se ven distintos del azul, pero
no compiten con él.

## Typography

**Display Font:** no hay display; el título más grande de una pantalla es de `1.5rem`.
**Body Font:** Instrument Sans (con `ui-sans-serif, system-ui, sans-serif` como respaldo), declarada
como `--font-sans` en el bloque `@theme` de Tailwind 4.
**Label/Mono Font:** no hay familia mono ni de etiquetas; la etiqueta es Instrument Sans en versalitas
por CSS.

**Character:** una sola sans serif, neutra y de lectura rápida, sin contraste display/body. La
personalidad no viene del tipo sino de la escala: muchos tamaños chicos, peso 600 donde hay jerarquía y
nunca pesos por debajo de 400.

### Hierarchy
- **Headline** (600, `1.5rem`/`2rem`): título de página («Usuarios», «Gestión de Semestres»). Una por
  pantalla, al inicio del flujo.
- **Title** (600, `1.125rem`/`1.25rem`): encabezado de tarjeta o de sección cuando el contenido es
  extenso; `1rem` (`text-base`, 600) cuando el encabezado vive dentro de una tarjeta de formulario o
  de una banda de tabla.
- **Body** (400, `0.875rem`/`1.25rem`): todo el contenido de tablas, formularios y párrafos
  explicativos. Es el tamaño de lectura real del producto.
- **Label** (600, `0.75rem`, `letter-spacing: 0.025em`, MAYÚSCULAS): encabezados de columna y títulos de
  bloque dentro de tarjeta («Módulos disponibles», «Inicio Semestre»).
- **Body grande** (400, `1rem`): nombre de la aplicación en el header del layout de invitado.

### Named Rules
**The Table Is The Body Rule.** Ningún texto de interfaz pasa de `1rem` (solo el nombre de la aplicación
en el header) ni baja de `0.75rem` sin ser una etiqueta en mayúsculas. Las tablas densas son la razón de
que el sistema se sienta rápido.

## Layout

Un solo contenedor para todo el producto: `max-width: 72rem` (`max-w-6xl`) con `1rem` de padding lateral
en móvil y `2rem` de padding vertical (`py-8`) en el `main`. El layout de invitado reduce a `max-width:
28rem` (`max-w-md`) y centra vertical y horizontalmente: es un login, no una página.

La densidad es alta y constante: celdas de tabla a `0.75rem` vertical y `1rem` horizontal, campos a
`0.5rem` vertical y `0.75rem` horizontal, tarjetas a `1.25rem`, separaciones de sección a `1.5rem` y
bandas de encabezado a `0.75rem`. Los formularios de alta y edición se estrechan a `max-width: 36rem`
(`max-w-xl`) y se centran: una tarea, una columna, sin ruido.

El ritmo responsive es Tailwind por defecto —`sm` 40rem, `md` 48rem, `lg` 64rem— y el sistema solo lo usa
en tres formas: la barra superior pasa a dos grupos separados por `justify-between`, las rejillas de
tarjetas y de permisos pasan a 2 columnas en `sm` y 3 en `lg`, y las tablas se envuelven en
`overflow-x-auto` en lugar de reordenarse. **Las tablas no se apilan en móvil**: se desplazan
horizontalmente, y eso es deliberado porque la comparación de columnas es el trabajo.

## Elevation & Depth

Plano por defecto, con sombra funcional. La profundidad del sistema la producen dos cosas: una tarjeta
blanca sobre el `lienzo` gris, y un borde de `slate-200`/`slate-300` de 1 píxel. La sombra aparece
únicamente donde un componente se despega del plano y necesita leerse como elevado —hoy, `shadow-sm`
en los campos de formulario y en la tarjeta del layout de invitado. No hay sombras en botones, tarjetas,
tablas ni insignias, y no hay sombras en hover.

### Shadow Vocabulary
- **Sombra de control** (`box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)`):
  inputs y selects; los saca del plano de la tarjeta que los contiene.
- **Sombra de sesión** (misma receta, `0.1` de opacidad): tarjeta del layout de invitado, la única
  superficie elevada del sistema fuera de un formulario.

### Named Rules
**The Flat-By-Default Rule.** Las superficies están planas en reposo. Si añades sombra a algo, es porque
ese algo flota (un control dentro de una tarjeta) y porque sin ella se pierde el borde; no porque
«resalte».

## Shapes

El radio viene de la escala de Tailwind y se usa con disciplina: `0.375rem` (`rounded-md`) para todo control —
botón, input, select, botón de filtro—; `0.5rem` (`rounded-lg`) para contenedores (tarjetas, secciones,
tablas); `0.25rem` (`rounded-sm`) para la insignia mínima de hora dentro de `x-fecha-hora`; y
`rounded-full` para todo lo que es etiqueta de estado o insignia, sin excepción.

Las esquinas suaves son el gesto carácter del sistema, pero siempre moderadas: nada pasa de `0.5rem`.
Cuando un contenedor lleva un encabezado dentro, el borde se une sin separación —el encabezado tiene
`border-b` y conserva su propio radio superior (`rounded-t-lg`), como en «Periodo Actual Activo»— y
`overflow-hidden` recorta el borde inferior de la tabla contra el radio de la tarjeta.

## Components

Directos y sin adornos: cada control dice qué hace y en qué estado está, sin adorno visual.

### Buttons
- **Shape:** esquinas suaves (`0.375rem`), sin borde en el primario.
- **Primary:** fondo `azul-ito`, texto blanco, peso 600, `0.5rem 1rem` de padding, `0.875rem`. Hover
  `azul-ito-hover`. Sin sombra, sin transición de posición.
- **Hover / Focus:** el hover cambia el tono del fondo (o el fondo a `lienzo` en el secundario, en cuyo
  caso el borde `borde-fuerte` se queda). En tablas, las acciones no son botones sino enlaces de texto
  en `azul-ito` con `hover:underline`, y las destructivas en `estado-peligro`.
- **Secondary:** fondo blanco, texto `texto-medio`, borde de 1 píxel `borde-fuerte`; hover a `lienzo`.
  Es el «Cancelar» y el «Volver al listado».
- **Icon:** `inline-flex` con un SVG de 1rem (`x-icono`, trazo 1.8, `currentColor`). En tablas de historial
  son cuadrados de `2rem` (`h-8 w-8`) con icono: `azul-consulta` para ver, `ámbar-editar` para editar. El
  candado de solo lectura no es botón: es el mismo SVG en `slate-400`, sin fondo.

### Chips (Insignias de estado)
- **Style:** `rounded-full`, `0.125rem 0.5rem` de padding, `0.75rem` en peso 600. Fondo `green-100` con
  texto `estado-activo` para «Activo», «ACTIVO», «Abiertas»; fondo `slate-200` con texto `estado-inactivo`
  para «Inactivo», «INACTIVO», «Cerradas»; fondo `azul-ito-tenue` con texto `azul-ito` para el rol del
  usuario en el header. Las de estado del historial llevan `uppercase`; las de la tabla de usuarios no.
- **State:** son de lectura, nunca seleccionables.

### Cards / Containers
- **Corner Style:** `0.5rem`.
- **Background:** superficie blanca sobre el `lienzo` gris.
- **Shadow Strategy:** ninguna; ver Elevation & Depth.
- **Border:** 1 píxel `borde-sutil`; los que llevan encabezado usan `border-b` en ese mismo tono para el
  divisor.
- **Internal Padding:** `1.25rem` (`p-5`), o `1rem` horizontal (`px-4`) en las bandas de encabezado
  de tabla.
- **Firma del producto:** la tarjeta de formulario (`x-tarjeta-formulario`) cierra con una fila
  `justify-between` que pone «Cancelar» a la izquierda (secundario) y «Guardar» a la derecha
  (primario). Esa fila es la que ordena cada pantalla de alta y edición.

### Inputs / Fields
- **Style:** borde de 1 píxel `borde-fuerte`, fondo blanco, radio `0.375rem`, `0.5rem 0.75rem` de
  padding, `0.875rem`, ancho completo del contenedor.
- **Focus:** el borde pasa a `blue-500` (`focus:border-blue-500 focus:outline-none focus:ring-1
  focus:ring-blue-500`) — un anillo de 1 píxel, no un halo grueso.
- **Error / Disabled:** el error no vive en el campo, sino debajo en `red-600` a `0.875rem`; el campo no
  cambia de color. La ayuda opcional va debajo en `0.75rem` `slate-500`. El asterisco de obligatorio es un
  `span` `red-600` junto a la etiqueta, nunca un placeholder. Los checkboxes de permisos usan
  `rounded` con el mismo borde `borde-fuerte` y se deshabilitan en grey cuando el rol está bloqueado.

### Navigation
- **Style:** cabecera blanca con `border-b` `borde-sutil`, altura de contenido `0.75rem` vertical. A la
  izquierda el nombre de la aplicación en `azul-ito` peso 600 (`1rem`), luego los enlaces de sección en
  `0.875rem` peso 500 `texto-medio`, separados por `gap-4`. A la derecha el nombre del usuario
  (`hidden` en móvil), la insignia de rol y el botón «Salir» como secundario compacto
  (`0.375rem 0.75rem`).
- **Estados:** el enlace activo no tiene tratamiento distinto —solo hover a `azul-ito`— porque con
  cuatro roles y navegación de un nivel el título de la página ya dice dónde estás. Los enlaces que el
  permiso no autoriza no se renderizan: la restricción se resuelve con `@can`, nunca ocultando por CSS.
- **Móvil:** la barra superior envuelve con `flex-wrap` y `gap-4`; no hay menú hamburguesa.

### Tablas
- **Style:** contenedor blanco con `border` `borde-sutil`, radio `0.5rem` y `overflow-x-auto`.
- **Cabecera:** `bg-slate-50` con texto `0.75rem` 600 en mayúsculas y `tracking-wide` `slate-500`, o
  `bg-slate-800` con `slate-100` cuando la tabla es historial (más densa, más archivada).
- **Filas:** `divide-y` `slate-100`, `0.75rem` vertical. Donde hay alternancia, `odd:bg-white
  even:bg-slate-50 hover:bg-blue-50` —el hover es un azul de 5%, no un cambio de color de texto.
- **Acciones:** alineadas a la derecha, `gap-3`, como enlaces de texto; en tablas densas, botones de
  icono de `2rem`.
- **Vacío:** una fila con `colspan` completo, `py-8`, centrado, en `slate-500`: «No hay usuarios que
  mostrar.»

### Alerts (Mensajes de sesión)
- **Style:** banda con borde de 1 píxel, `1rem 0.75rem`, `0.875rem`, radio `0.375rem`, `mb-6`.
- **Variantes:** éxito = `green-200`/`green-50`/`green-800` con `role="status"`; error =
  `red-200`/`red-50`/`red-800` con `role="alert"`; advertencia (coordinador sin departamento) =
  `amber-200`/`amber-50`/`amber-800`. El aviso de advertencia es el mismo lenguaje que el ámbar de la
  acción de periodo.

## Do's and Don'ts

### Do:
- **Do** mantener el contenedor `max-w-6xl` y la densidad actual: el producto se usa para revisar
  catálogos, no para contemplarlos.
- **Do** usar el `azul-ito` solo para la acción principal de la pantalla y para el cintillo del periodo
  activo, y obscurecer un tono en hover.
- **Do** declarar el estado con una insignia (`rounded-full`) y con su palabra, no solo con color.
- **Do** envolver las tablas en `overflow-x-auto` y dejar que se desplacen en móvil en vez de
  reordenarlas.
- **Do** resolver la visibilidad con `@can` y con la política del modelo; la interfaz no reimplementa el
  alcance por rol.
- **Do** componer los formularios con `x-campo`, `x-seleccion` y `x-tarjeta-formulario` para que hereden
  foco, radio, error y la fila «Cancelar / Guardar» sin reescribirlos.
- **Do** mantener todo el texto de la interfaz en español y en el vocabulario del ITO («Periodo»,
  «Inscripciones», «Historial de Periodos Pasados»).

### Don't:
- **Don't** añadir sombras a botones, tarjetas o tablas para hacerlas «resaltar»; la sombra es solo para
  controles dentro de una tarjeta.
- **Don't** inventar un segundo azul para una acción secundaria: `azul-consulta` es para leer y
  `ámbar-editar` es para cambiar el estado de un periodo. Cualquier otra acción usa el secundario de
  borde.
- **Don't** usar verde como confirmación de una acción guardada (por ejemplo, tras enviar un formulario):
  el verde del sistema es exclusivamente «en vigor ahora».
- **Don't** apilar las tablas en tarjetas ni ocultar columnas por breakpoint; se desplazan.
- **Don't** subir pesos tipográficos ni añadir una familia display: la escala va de `0.75rem` a `1rem`.
- **Don't** iniciar ninguna transacción destructiva sin confirmación explícita (`onsubmit` con
  `confirm`) ni quitar la palabra «Eliminar»/«Desactivar» de las acciones de tabla.
