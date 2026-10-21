<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un bloque de clase de un grupo: un día y un rango de horas. No lleva marcas de tiempo. */
#[Table(name: 'horario', key: 'id_horario', timestamps: false)]
#[Fillable(['id_grupo', 'id_dia', 'hora_inicio', 'hora_fin'])]
class Horario extends Model
{
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function dia(): BelongsTo
    {
        return $this->belongsTo(DiaSemana::class, 'id_dia');
    }

    /** Hora de inicio en formato HH:MM. */
    public function getInicioCortoAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5);
    }

    /** Hora de fin en formato HH:MM. */
    public function getFinCortoAttribute(): string
    {
        return substr($this->hora_fin, 0, 5);
    }

    /** «Lunes de 10:00 a 12:00» */
    public function getDescripcionAttribute(): string
    {
        return "{$this->dia->nombre_dia} de {$this->inicio_corto} a {$this->fin_corto}";
    }

    /** «Lun 10:00–12:00», para listados angostos. */
    public function getDescripcionCortaAttribute(): string
    {
        return mb_substr($this->dia->nombre_dia, 0, 3)." {$this->inicio_corto}–{$this->fin_corto}";
    }
}
