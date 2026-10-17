<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'ubicacion', key: 'id_ubicacion')]
#[Fillable(['espacio', 'capacidad'])]
class Ubicacion extends Model
{
    protected function casts(): array
    {
        return ['capacidad' => 'integer'];
    }
}
