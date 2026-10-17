<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'carrera', key: 'id_carrera')]
#[Fillable(['nombre'])]
class Carrera extends Model
{
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'id_carrera');
    }
}
