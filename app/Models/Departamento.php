<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table(name: 'departamento', key: 'id_departamento')]
#[Fillable(['nombre', 'edificio'])]
class Departamento extends Model
{
    public function coordinacion(): HasOne
    {
        return $this->hasOne(CoordinadorDepartamento::class, 'id_departamento');
    }

    public function instructores(): HasMany
    {
        return $this->hasMany(Instructor::class, 'id_departamento');
    }
}
