# Imagen de produccion de Actividades Complementarias ITO.
#
# Se construye en tres etapas para que la imagen final solo lleve lo necesario:
#   1) assets -> compila Tailwind con Vite (public/build no viaja en el repositorio)
#   2) vendor -> dependencias PHP de produccion
#   3) final  -> FrankenPHP, que es PHP 8.4 con el servidor web Caddy en un
#                solo proceso
#
# No se declara CMD a proposito: se hereda el de la imagen base, que sirve
# unicamente la carpeta public/ en el puerto 80.

# --- Etapa 1: assets (Tailwind 4 + Vite) ---
FROM node:20-bookworm AS assets

WORKDIR /app

# Se copian primero los manifiestos para que npm aproveche la cache de Docker.
COPY package.json package-lock.json .npmrc ./
RUN npm ci

COPY resources/ ./resources/
COPY vite.config.js ./
RUN npm run build

# --- Etapa 2: dependencias PHP de produccion ---
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# --no-scripts es obligatorio: el script de Composer invoca a artisan, que en
# esta etapa todavia no existe porque el codigo de la aplicacion se copia en la
# etapa final. El descubrimiento de paquetes se hace al final.
RUN composer install --no-dev --no-scripts --prefer-dist \
    --optimize-autoloader --no-interaction --no-progress

# --- Etapa 3: imagen final ---
FROM dunglas/frankenphp:php8.4-bookworm

# La imagen base no trae pdo_mysql, intl ni bcmath. Las que Laravel exige y si
# vienen (mbstring, dom, xml, ctype, tokenizer, fileinfo, openssl, curl,
# opcache) no se instalan porque install-php-extensions ya las detecta.
RUN install-php-extensions pdo_mysql intl bcmath

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# La configuracion de Caddy que trae la imagen base redirige todo el trafico a
# HTTPS, asi que se usa la del proyecto, que sirve HTTP en el puerto 80 y deja
# public/ como unica carpeta expuesta.
COPY Caddyfile /etc/caddy/Caddyfile

# El servidor web corre como www-data, por eso storage y bootstrap/cache
# deben quedar de su propiedad.
RUN php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

WORKDIR /app

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]