# Cómo contribuir

Guía corta para trabajar en **Actividades Complementarias ITO**. La referencia completa está en
[`docs/convenciones.md`](docs/convenciones.md); este archivo solo resume el flujo.

## Antes de empezar

1. Lee [`docs/contexto.md`](docs/contexto.md) (modelo de datos, reglas de negocio y decisiones
   técnicas) y el documento del sprint que vayas a tocar en [`docs/sprints/`](docs/sprints/).
2. Levanta el entorno según [`docs/README.md`](docs/README.md) (PHP local o
   [`docs/contenedor.md`](docs/contenedor.md) con Docker).

## Flujo de trabajo

1. Haz tu cambio con su prueba Pest en `tests/Feature/`.
2. Verifica que quede en verde:

   ```sh
   php artisan test        # 204 pruebas
   vendor\bin\pint --test  # sin observaciones
   ```

3. Documenta:
   - Cambio o corrección → entrada **al principio** de [`docs/bitacora.md`](docs/bitacora.md) con la
     fecha de hoy.
   - Cambio visible para el usuario → refleja en [`CHANGELOG.md`](CHANGELOG.md).
   - Regla de negocio, modelo de datos o decisión técnica nueva → actualiza
     [`docs/contexto.md`](docs/contexto.md).

## Reglas que no se rompen

- Comentarios y documentación en español; sin lógica en Blade.
- No edites migraciones ya aplicadas: crea una nueva.
- No toques `.env` ni subas `.env`, `vendor/` o `node_modules/`.
- Spatie es la única fuente de rol; el acceso se controla con permisos y políticas.
- No hagas `git push --force` ni commitees sin que te lo pidan.

El detalle de estas reglas está en [`AGENTS.md`](AGENTS.md) y
[`docs/convenciones.md`](docs/convenciones.md).
