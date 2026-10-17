<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// La llave es el id del usuario (no se autoincrementa).
#[Table(name: 'alumno', key: 'id_alumno', incrementing: false)]
#[Fillable(['id_alumno', 'num_control', 'id_carrera', 'semestre_cursando', 'creditos_acumulados'])]
class Alumno extends Model
{
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_alumno');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }
}
