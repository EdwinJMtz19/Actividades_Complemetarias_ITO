<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'instructor', key: 'id_instructor', incrementing: false)]
#[Fillable(['id_instructor', 'id_departamento', 'especialidad'])]
class Instructor extends Model
{
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_instructor');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }
}
