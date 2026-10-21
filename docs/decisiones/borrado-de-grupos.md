# Decisión · Borrado de grupos con horarios

**Estado:** **resuelta el 2026-10-20 · opción A** (corregir la documentación; el código no cambia)
**Detectada:** 2026-10-20, al cerrar el Sprint 3
**Afecta:** criterio de aceptación 8 de HU-15

## Resolución

Se aplicó la opción A:

- El criterio 8 de HU-15 se reescribió en
  [`../sprints/sprint-03-historias-de-usuario-y-crc.md`](../sprints/sprint-03-historias-de-usuario-y-crc.md):
  un grupo se elimina junto con sus horarios (`cascade`) y la verificación definitiva contra
  inscripciones se activa en el Sprint 4.
- Se agregó la prueba `elimina un grupo con horarios y se lleva sus horarios en cascada` en
  `tests/Feature/GruposCrudTest.php` (**ejecutada el 2026-10-20** dentro de `php artisan test`:
  205 pruebas, 801 aserciones, en verde).
- `Grupo::tieneInscripciones()` y `GrupoController@destroy` siguen igual; en el Sprint 4 se
  sustituye el cuerpo del método por `$this->inscripciones()->exists()` (ver §7).

El resto del documento se conserva como registro del análisis que llevó a la decisión.

Este documento es autosuficiente: quien lo lea no necesita reconstruir el contexto. Explica qué
dice cada fuente del proyecto, por qué se contradicen, cuál es el estado real del código, qué
implica cada opción y qué archivos habría que tocar.

---

## 1. Resumen

Tres fuentes del proyecto dicen cosas distintas sobre si un grupo **con horarios** se puede
eliminar:

- El **criterio de aceptación** de HU-15 dice que, mientras no exista la tabla de inscripciones,
  se compruebe que el grupo **no tenga horarios** para poder borrarlo.
- La **decisión de esquema** del Anexo A puso `horario.id_grupo` con **cascade** precisamente para
  que borrar un grupo **se lleve** sus horarios.
- El **código** comprueba `cupo_ocupado > 0`, un campo que nadie escribe todavía, por lo que **hoy
  cualquier grupo se puede borrar**.

Hay que decidir cuál manda. El código **no se ha modificado** para no fijar una postura antes de
la decisión.

---

## 2. Las tres fuentes, textuales

### 2.1 Criterio de aceptación 8 de HU-15

En `docs/sprints/sprint-03-historias-de-usuario-y-crc.md:56`:

> 8. No se elimina un grupo que ya tenga inscripciones; **mientras la tabla de inscripciones no
> exista, solo se comprueba que no tenga horarios**.

Interpretación literal: si el grupo tiene al menos un horario, el borrado se rechaza.

### 2.2 Decisión de esquema del Anexo A

En el mismo documento, `:191`:

> `id_grupo` | unsignedBigInteger, FK → `grupo`, **cascade** | **Cambio deliberado** frente al
> esquema anterior: al borrar un grupo sus horarios se van con él

Y en `:199`:

> `horario.id_grupo` con **cascade** | En el sistema anterior el borrado de un grupo con horarios
> fallaba por la llave foránea. Con cascade, borrar un grupo se lleva sus horarios

Interpretación: **se decidió deliberadamente** que borrar un grupo elimine sus horarios. Si se
bloqueara el borrado por tener horarios, el cascade no tendría ningún efecto práctico.

Está implementado en `database/migrations/2026_10_19_100400_create_horario_table.php`:

```php
$table->foreignId('id_grupo')->constrained('grupo', 'id_grupo')->cascadeOnDelete();
```

### 2.3 El código actual

`app/Models/Grupo.php:73-80`:

```php
/**
 * Un grupo con alumnos no se elimina. Mientras no exista la tabla de inscripciones
 * (Sprint 4), el cupo ocupado hace de indicador; entonces se cambia por la relación.
 */
public function tieneInscripciones(): bool
{
    return $this->cupo_ocupado > 0;
}
```

`app/Http/Controllers/GrupoController.php:114-129`:

```php
if ($grupo->tieneInscripciones()) {
    return back()->with('error', "No se puede eliminar: ... ya tiene alumnos inscritos ...");
}

try {
    $grupo->delete(); // sus horarios se van con él (llave foránea en cascada)
} catch (QueryException) {
    return back()->with('error', 'No se puede eliminar: el grupo tiene registros relacionados.');
}
```

Interpretación: **hoy todos los grupos se pueden borrar**, porque `tieneInscripciones()` siempre
devuelve `false` (ver §3).

---

## 3. El dato que cambia el análisis

`cupo_ocupado` **no lo escribe nadie en el Sprint 3**. Búsqueda completa en el proyecto:

| Archivo | Uso |
|---|---|
| `database/migrations/2026_10_19_100300_create_grupo_table.php:25` | `default(0)` |
| `app/Models/Grupo.php:36` | cast a `integer` |
| `app/Models/Grupo.php:79` | lectura en `tieneInscripciones()` |
| `resources/views/grupos/index.blade.php:67` | solo lo muestra |
| `resources/views/grupos/show.blade.php:48` | solo lo muestra |

No aparece en `GuardarGrupoRequest`, ni en el modelo (`cupo_ocupado` **no** está en `#[Fillable]`),
ni en los seeders. Solo lo actualizará el Sprint 4 al inscribir. Por eso la guarda **nunca se
activa**.

### El problema de aplicar el criterio al pie de la letra

El formulario de grupos **exige al menos un horario**:

- `app/Http/Requests/GuardarGrupoRequest.php:77`:
  `'horarios' => ['required', 'array', 'min:1']`
- Al editar, `GrupoController@update` **borra todos** y vuelve a crearlos
  (`GrupoController.php:107-108`).

Consecuencia: **todo grupo creado o editado desde la interfaz tiene horarios siempre**. No existe
ninguna pantalla para eliminar horarios por separado (se administran dentro del formulario del
grupo, que vuelve a exigir uno). Por lo tanto, cumplir el criterio tal como está redactado haría
que **ningún grupo se pudiera borrar nunca** desde la aplicación.

---

## 4. Estado real del borrado hoy

1. Se entra a `DELETE /grupos/{grupo}` con el permiso `grupos.eliminar` y la política
   `GrupoPolicy@delete` (administrador o coordinador del departamento).
2. `tieneInscripciones()` devuelve `false` siempre.
3. `$grupo->delete()` borra el grupo.
4. MySQL elimina en cascada sus filas de `horario`.
5. La respuesta es «Grupo eliminado correctamente».

No hay prueba automatizada de este flujo: se omitió a propósito para no fijar una postura antes
de decidir.

---

## 5. Opciones

### A. Corregir la documentación (recomendada)

Se mantiene el código y se reescribe el criterio 8 para que refleje la decisión de esquema:

> 8. No se elimina un grupo que ya tenga inscripciones. Mientras la tabla de inscripciones no
> exista (Sprint 3), el grupo se puede eliminar junto con sus horarios; la verificación definitiva
> se activa en el Sprint 4 con la relación `inscripciones`.

- **Código:** sin cambios.
- **Pruebas:** se agrega «elimina un grupo sin inscripciones (y sus horarios se van en cascada)».
- **Sprint 4:** sustituir el cuerpo de `tieneInscripciones()` por
  `return $this->inscripciones()->exists();`.
- **Ventajas:** coherente con la migración, con la UX y con el plan; no amplía el alcance.
- **Desventaja:** durante el Sprint 3 un coordinador puede borrar un grupo abierto con horarios.
  Mitigación: el diálogo de confirmación ya advierte «¿Eliminar este grupo y sus horarios?».

### B. Cumplir el criterio al pie de la letra

`GrupoController@destroy` rechaza el borrado si `$grupo->horarios()->exists()`.

- **Código:** cambio en `destroy`; probablemente también un mensaje nuevo.
- **Consecuencia inevitable:** como todo grupo tiene horarios, **ningún grupo se podrá borrar**.
  Para que la opción sea usable habría que:
  1. Permitir grupos sin horarios al crearlos (relajar `min:1`), o
  2. Crear una pantalla/flujo para vaciar horarios antes de borrar.
  Ambas cosas **amplían el alcance del Sprint 3** y contradicen el cascade.
- **Pruebas:** «no elimina un grupo con horarios» y «elimina solo después de quitarlos».
- **Ventajas:** cumple la letra del criterio.
- **Desventajas:** el cascade queda inútil; la operación se vuelve incómoda; se sale del alcance.

### C. Documentación + dejar el método listo

Igual que A, pero además se ajusta `tieneInscripciones()` ahora para que consulte la relación en
cuanto exista, por ejemplo:

```php
public function tieneInscripciones(): bool
{
    return $this->cupo_ocupado > 0; // Sprint 4: $this->inscripciones()->exists()
}
```

- **Código:** un cambio mínimo y sin efecto funcional.
- **Ventaja:** el Sprint 4 solo reemplaza una línea.
- **Desventaja:** toca un modelo que hoy funciona; el comentario no cambia el comportamiento.

---

## 6. Recomendación (adoptada)

**Opción A.** Razones:

1. El cascade fue una **decisión explícita y documentada** para corregir un defecto del sistema
   anterior.
2. El criterio 8, aplicado literalmente, es **incompatible con el propio formulario** del sprint.
3. La regla que de verdad importa —no borrar con alumnos inscritos— **llega en el Sprint 4** y el
   método ya está preparado para sustituirse.
4. No amplía el alcance ni obliga a rehacer pruebas.

Lo único que falta para cerrarla es **reescribir el criterio 8** en
`docs/sprints/sprint-03-historias-de-usuario-y-crc.md` y dejarlo citado en el documento del sprint.

---

## 7. Qué necesita el Sprint 4 en cualquiera de las opciones

1. Crear la tabla `inscripcion` (columna generada de MySQL 5.7+; ya es viable en MySQL 8).
2. Declarar en `Grupo`:

   ```php
   public function inscripciones(): HasMany
   {
       return $this->hasMany(Inscripcion::class, 'id_grupo');
   }
   ```

3. Sustituir `tieneInscripciones()` por `return $this->inscripciones()->exists();`.
4. Agregar las pruebas:
   - «no elimina un grupo con inscripciones»,
   - «sí elimina un grupo sin inscripciones».
5. Si se eligió **B**, agregar además «no elimina un grupo con horarios».

---

## 8. Archivos que toca cada opción

| Archivo | A | B | C |
|---|---|---|---|
| `docs/sprints/sprint-03-historias-de-usuario-y-crc.md` | Reescribir criterio 8 | — | Reescribir criterio 8 |
| `app/Http/Controllers/GrupoController.php` | — | Bloquear si hay horarios | — |
| `app/Models/Grupo.php` | — | — | Comentario/ajuste |
| `tests/Feature/GruposCrudTest.php` | Prueba de borrado con horarios | Pruebas de bloqueo y de vaciado | Prueba de borrado con horarios |
| `docs/sprints/sprint-03-actividades-y-grupos.md` | Enlazar esta decisión | Igual | Igual |
| `docs/bitacora.md` | Anotar la decisión tomada | Igual | Igual |

---

## 9. Qué NO se hizo al cerrar el Sprint 3 (estado previo a la resolución)

- No se modificó `Grupo::tieneInscripciones()`.
- No se modificó `GrupoController@destroy`.
- No se había reescrito el criterio 8 ni escrito la prueba del borrado de un grupo **con
  horarios**, para no fijar una postura. Ambas cosas se hicieron al resolver (ver «Resolución»).
  El borrado de un grupo **sin horarios** ya se probaba y es válido en las tres opciones.

---

## 10. Cómo retomar el trabajo

```sh
# Ver las tres piezas del conflicto
rg -n "no tenga horarios" docs/
rg -n "cascadeOnDelete" database/migrations/2026_10_19_100400_create_horario_table.php
rg -n "tieneInscripciones|cupo_ocupado" app/ database/ resources/

# Estado actual de la suite
php artisan test
```

Pendiente solo al hacer commit: enlazar aquí el commit con estos cambios.
