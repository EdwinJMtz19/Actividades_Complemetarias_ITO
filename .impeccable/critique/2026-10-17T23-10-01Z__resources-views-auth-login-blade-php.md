---
target: el login
total_score: 26
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 2
p2_count: 3
target_identity: "file:C:\\Users\\em556\\Desktop\\Actividades_complementarias\\asdsada\\Actividades\\resources\\views\\auth\\login.blade.php"
target_fingerprint: "sha256:47052d4898a65c6e6d3a480f1058e796d15bdb1b35febd5b93265b738a610dd0"
target_path: "C:\\Users\\em556\\Desktop\\Actividades_complementarias\\asdsada\\Actividades\\resources\\views\\auth\\login.blade.php"
timestamp: 2026-10-17T23-10-01Z
slug: resources-views-auth-login-blade-php
---
⚠️ DEGRADED: single-context (no sub-agent/Task tool exposed; no browser automation — live evidence via server-rendered HTML + `php artisan serve`, no screenshots or in-page overlays)

Target: `resources/views/auth/login.blade.php` + `components/layouts/guest.blade.php` + `components/campo.blade.php`. Mode: **Operate**. Snapshot: `resources-views-auth-login-blade-php`.

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 2 | Fallo y bloqueo se pintan como una línea roja bajo *Correo electrónico*, sin `role="alert"` ni resumen |
| 2 | Match System / Real World | 3 | Español correcto en todo (`auth.failed`, `auth.throttle`, `validation.php`), pero el mensaje de bloqueo se cuelga del campo equivocado |
| 3 | User Control and Freedom | 2 | Sin recuperación de contraseña: bloqueado = sin salida |
| 4 | Consistency and Standards | 3 | Reusa `x-campo` y el primario `blue-700` del sistema, pero rompe el techo tipográfico de DESIGN.md |
| 5 | Error Prevention | 4 | `required`, `type="email"`, `autocomplete` correctos, rate limit de 5 intentos, cuenta desactivada con mensaje propio |
| 6 | Recognition Rather Than Recall | 4 | Todo tiene etiqueta; el enlace de registro dice a quién aplica («¿Eres alumno…?»); cero controles solo-icono |
| 7 | Flexibility and Efficiency | 2 | Camino único, sin mostrar contraseña ni aviso de Bloq Mayús |
| 8 | Aesthetic and Minimalist Design | 3 | Una tarjeta limpia y nada decorativo, pero el bloque de marca ocupa más peso visual que la tarea |
| 9 | Error Recovery | 2 | Nombra el problema pero no ofrece salida, ni conserva contexto, ni anuncia el error |
| 10 | Help and Documentation | 1 | Ninguna ayuda en pantalla, ni enlace de recuperación, ni contacto con administración |
| **Total** | | **26/40** | **Aceptable — antes de que el usuario esté contento hay trabajo real** |

Carga cognitiva: 2 de 8 fallos (jerarquía visual, foco único). El problema no es que piense demasiado, es que no le dicen qué pasó.

Recorrido emocional: sin valle. Pico negativo en el quinto intento fallido: misma línea roja, ahora con «Demasiados intentos…», y ninguna puerta de salida.

## Veredicto de especificidad

LLM: el login es correcto pero intercambiable. `login.blade.php:14` es una línea que se podría pegar en cualquier SaaS. Lo único que ancla el producto es el marco de `guest.blade.php:12-13` y el asterisco rojo de `x-campo`. Faltan las tres cosas que el resto del producto sí tiene: el periodo activo como contexto, el monograma del ITO, y un pie que diga a quién llamar cuando la cuenta no es de alumno.

Detector: `impeccable detect --json` sobre los tres archivos → `[]`, exit 0, cero hallazgos. Sin falsos positivos; el detector pasa el asterisco de obligatorio y los `autocomplete` correctos, que son aciertos reales. Los problemas son editoriales y de comportamiento, no de marcado.

Overlays: no disponibles (sin automatización de navegador). Señal de respaldo: HTML renderizado con la app real en `php artisan serve --port=8123` más `trans('auth.failed')` resuelto por tinker.

## Impresión general

Pantalla de login bien resuelta en plumbing y pobre en contenido. La validación del servidor está impecable y traducida; el problema es que todo el feedback pasa por un único canal: una línea roja bajo el primer campo. Eso funciona para «te falta el correo» y no para tres de los cuatro estados reales (credenciales incorrectas, cuenta desactivada, cuenta bloqueada), que no son errores de ese campo. Y el estado que más duele —el bloqueo tras 5 intentos— no tiene salida.

## Qué funciona bien

- Prevención de errores de verdad: `LoginRequest` filtra `activo => true` en `Auth::attempt`, distingue cuenta desactivada de contraseña incorrecta, y el limitador es por correo+IP (`LoginRequest.php:57-73`).
- Etiquetado y semántica: `for`/`id` correctos, `autofocus`, `autocomplete="username"` y `"current-password"`.
- Copia honesta: `guest.blade.php:13` baja el nombre de la app a `text-sm slate-500`; el enlace de registro delimita la audiencia.

## Issues prioritarios

**1. [P1] Fallo y bloqueo se reportan en el campo que no es**
`LoginRequest.php:69` lanza el `ValidationException` con clave `'email'` para los tres fallos, incluido `auth.throttle`; `campo.blade.php:12` lo pinta bajo *Correo electrónico*.
- Por qué importa: en el bloqueo el usuario no necesita corregir el campo, necesita saber qué esperar; el mensaje lo dirige a revisar lo que está bien.
- Arreglo: componente `x-mensaje` (banda con borde, `role="alert"`, variantes de DESIGN.md *Alerts*); volcar los errores de autenticación a `errors` en vez de a un campo; resumen antes del formulario.
- Comando: `/impeccable harden`

**2. [P1] El bloqueo no tiene salida y no hay recuperación de contraseña**
No existe ruta `password.request`; tras 5 intentos el usuario solo espera o escribe al administrador. PRODUCT.md sitúa SMTP en el Sprint 4, pero la ausencia se vive aquí.
- Por qué importa: es el único estado que bloquea la tarea por completo; el usuario escribe al soporte.
- Arreglo: enlace «No puedo entrar» con texto de contacto a administración; dejar hueco para el enlace de recuperación del Sprint 4.
- Comando: `/impeccable onboard`

**3. [P2] La marca grita más que la tarea y viola el techo del sistema**
`guest.blade.php:12` usa `text-2xl` para el nombre de la app —el texto más grande del producto en la pantalla con menos contenido útil— mientras el `h2` de la tarea (`login.blade.php:2`) es `text-lg`. DESIGN.md fija `1rem` para ese nombre y `1.5rem` como techo absoluto. El nombre aparece dos veces: `h1` y `title`.
- Por qué importa: el ojo aterriza en la app, no en el formulario; en móvil empuja los campos hacia abajo.
- Arreglo: `text-xl` para el nombre, `text-lg` para la tarea, nombre solo en el `title`. Afecta también a `registro.blade.php`.
- Comando: `/impeccable typeset`

**4. [P2] Dos sistemas de validación compitiendo, uno en inglés**
`x-campo` emite `@required` y `login.blade.php:6` usa `type="email"` sin `novalidate`. El navegador lanza su burbuja en el idioma del navegador, no en español; el servidor responde en español.
- Por qué importa: contradice el compromiso de idioma; en móvil el mensaje nativo es el primero que se ve.
- Arreglo: `novalidate` en login y registro; Laravel como única fuente de error.
- Comando: `/impeccable harden`

**5. [P2] El layout de invitado no tiene región de mensaje**
`guest.blade.php:14-16` renderiza solo `{{ $slot }}`: no hay banda de sesión ni de error posible. Bloquea el arreglo de la prioridad 1.
- Arreglo: bloque de alertas de DESIGN.md sobre la tarjeta, con las mismas clases del layout autenticado.
- Comando: `/impeccable layout`

## Red flags por persona

**Jordan (primera vez)** — La primera pantalla es su primer juicio sobre la seriedad del sistema. Al fallar, la línea roja no dice qué hacer y no hay «¿no recuerdas tu contraseña?». No distingue entre «escribí mal el correo» y «esa cuenta no existe».

**Sam (lector de pantalla, teclado)** — Flujo completable con teclado y `autofocus` correcto, pero el mensaje de `campo.blade.php:12` es un `<p>` suelto: sin `role="alert"`, sin `aria-invalid`, sin `aria-describedby`. `guest.blade.php` no declara `main` ni otros landmarks.

**Casey (móvil, una mano)** — Usuario mayoritario según PRODUCT.md. Escribe la contraseña a ciegas: no hay botón de mostrar contraseña, y el bubbling nativo del issue 4 salta sobre el teclado. «Entrar» está bien situado; el `h1` de 24px lo empuja en pantallas cortas.

**Alma (alumna de primer semestre, celular propio, fuera de horario)** — Único camino de alta: `/registro`, en el mismo layout. Su bloqueo real no es técnico: correo que no recuerda, número de control mal escrito, carrera que no encuentra. Y no hay a quién llamar: la pantalla no explica que para personal el camino es hablar con administración.

## Observaciones menores

- `login.blade.php:9-12`: el checkbox «Mantener la sesión iniciada» queda a 16px del botón azul de ancho completo, sin separación; se lee como parte del conjunto obligatorio.
- No hay aviso de Bloq Mayús en el campo de contraseña.
- `x-campo` admite `ayuda` y el login no la usa: «Mínimo 8 caracteres» daría la regla sin instrucción extra.
- `title` y `h1` repiten el nombre de la app.
- Logotipo del ITO: compromiso de marca sin archivo en el repo (documentado en DESIGN.md y PRODUCT.md). Bloqueado, no es defecto; cuando llegue sustituye el `h1` en el mismo tamaño óptico sin rediseñar el header.
- `shadow-sm` en los inputs dentro de la tarjeta de invitado anula el efecto de «sacar del plano» que ese shadow que justifica; contradicción interna menor del documento.

## Preguntas para considerar

- Si el semestre activo gobierna el producto, ¿por qué la pantalla de acceso —la primera que ve el 100% de los usuarios— es la única que no dice en qué periodo se entra?
- ¿Qué pasaría si el login dijera, en una línea, «Acceso para personal del ITO y alumnos»?
- Una versión más segura no añade campos: saca el error del campo equivocado y le da al bloqueo una salida. ¿Cuál falta más?
