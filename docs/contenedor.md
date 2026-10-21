# El proyecto en un contenedor

Guía para levantar **Actividades Complementarias ITO** completo (aplicación + base de datos) en
Docker, sin instalar PHP, Composer ni MySQL en la máquina.

Todo esto vive en la **rama `contenedor`**. La rama `Mejoras_UI` es la que Railway despliega, y no
contiene ningún archivo de contenedor: así Railway nunca ve el `Dockerfile` y su despliegue actual
no se altera.

## Requisitos

Docker Desktop instalado y con el motor encendido (WSL2 es el motor que usa por debajo).

## Levantar el proyecto

```sh
docker compose up -d --build      # la primera vez compila unos minutos
docker compose exec app php artisan migrate --seed
```

Después entra en <http://localhost:8080> con cualquiera de las cuentas de prueba:

| Correo | Contraseña |
|---|---|
| `admin@gmail.com` | `Actividades2026` |
| `coordinador@gmail.com` | `Actividades2026` |
| `instructor@gmail.com` | `Actividades2026` |
| `alumno@gmail.com` | `Actividades2026` |

El puerto **3307** del equipo abre la base MySQL del contenedor, por si quieres revisarla con
Workbench o DBeaver sin chocar con el MySQL instalado en Windows.

## Comandos de uso diario

```sh
docker compose ps                  # ver estado y salud de los contenedores
docker compose logs -f app         # ver la salida de la aplicación
docker compose restart app         # reiniciar solo la aplicación
docker compose exec app php artisan tinker   # consola de Laravel
docker compose down                # apagar, conservando los datos
docker compose down -v             # apagar y BORRAR la base del volumen
docker compose up -d --build       # volver a levantar
```

Si cambias el código, `docker compose exec app php artisan view:clear` y recarga el navegador. Si
cambias dependencias, hay que **reconstruir** la imagen: `docker compose up -d --build`.

## Cómo está montado

| Servicio | Qué es | Puerto |
|---|---|---|
| `app` | Imagen construida con el `Dockerfile` (FrankenPHP: PHP 8.4 + Caddy) | `8080` → 80 |
| `db` | MySQL 8 con `utf8mb4` | `3307` → 3306 |

La aplicación se conecta a la base por el nombre del servicio `db`. Dentro de la red de Compose los
contenedores se ven por su nombre, así que `127.0.0.1` significaría el propio contenedor `app` y la
conexión fallaría.

El `docker-compose.yml` hereda el resto de variables de tu `.env` local y sobreescribe solo las que
apuntan a la base de datos. Tu `.env` no se modifica y su contraseña **nunca entra en la imagen**.

## La imagen en tres etapas

1. **`assets`** — Node compila Tailwind. Necesario porque `public/build` está en el `.gitignore` y
   no viaja en el repositorio.
2. **`vendor`** — Composer instala solo dependencias de producción (`--no-dev`), sin las de
   desarrollo (Pest, Pint, Boost).
3. **`final`** — FrankenPHP, al que se le instalan `pdo_mysql`, `intl` y `bcmath`, las únicas tres
   extensiones que la imagen base no trae y el proyecto necesita.

La imagen pesa unos **900 MB**, casi todos de la base. Para reducirla habría que migrar a
`php:8.4-fpm` con un Nginx aparte, a costa de un contenedor más.

## Tres decisiones que conviene no deshacer

**`auto_https off` en el `Caddyfile`.** La imagen base de FrankenPHP trae una configuración que
redirige todo a HTTPS con un 308. En local y en Railway el contenedor habla HTTP plano porque es un
proxy externo quien termina el TLS, así que sin esta línea la aplicación no responde a nada.

**`bootstrap/cache/*.php` y `storage/framework/views/*` están en el `.dockerignore`.** Son cachés
generadas. Si se copiaran a la imagen, sus manifiestos seguirían registrando paquetes de desarrollo
como Laravel Boost, que no existe en una instalación sin dependencias de desarrollo, y la aplicación
no arrancaría.

**MySQL con `utf8mb4`.** La columna `año` de los ciclos académicos y los nombres de departamentos con
tildes lo necesitan. Con el `utf8mb3` de MySQL 5.7 esos textos se guardaban mal.

## Pruebas

Las pruebas siguen corriendo **en la máquina**, no en el contenedor, porque la imagen se construye
sin dependencias de desarrollo:

```sh
php artisan test
```

## Publicar el contenedor en la nube

Ya está publicado en Railway como el servicio **`actividades-contenedor`**:

> https://actividades-contenedor-production.up.railway.app

Comparte la base de datos MySQL del servicio de producción, así que ve los mismos datos y las mismas
cuentas de prueba.

**Variables que necesita** (las mismas que usa el servicio de producción, con `APP_DEBUG=false`):

```
APP_NAME, APP_ENV=production, APP_DEBUG=false, APP_KEY, APP_TIMEZONE,
DB_URL, DB_CONNECTION=mysql, CACHE_STORE, SESSION_DRIVER, QUEUE_CONNECTION, LOG_CHANNEL
```

La base se conecta con **`DB_URL`**, que es lo que lee `config/database.php` en la línea 49. No con
`DATABASE_URL`, que Laravel no mira.

### Volver a publicarlo

El servicio **no** está atado a la rama `contenedor`, así que un `push` no lo redespliega. Se
republica desde la máquina:

```sh
git checkout contenedor
railway service actividades-contenedor    # apuntar el CLI al servicio del contenedor
railway up --detach                        # sube el código local y compila la imagen
railway service Actividades_Complemetarias_ITO   # volver al servicio de producción
```

Un detalle importante: al crear el servicio, `railway add --branch contenedor` **no aplicó la rama**
y Railway compiló desde `main`, donde el `composer.json` todavía pedía PHP 8.3, así que la
compilación falló. La API GraphQL de Railway tampoco permite fijar la rama (`ServiceSource` solo
tiene `image` y `repo`). Por eso el despliegue se hace subiendo el código local.

**Nunca** hay que desplegar con `githubRepoDeploy` desde la API: no recibe `serviceId`, así que
también reconstruiría el servicio de producción.