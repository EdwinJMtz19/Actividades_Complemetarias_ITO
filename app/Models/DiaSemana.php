<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Día de la semana en que puede haber clase (de lunes a sábado). */
#[Table(name: 'dia_semana', key: 'id_dia')]
#[Fillable(['nombre_dia'])]
class DiaSemana extends Model
{
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_dia');
    }
}
