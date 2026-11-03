<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inscripción de un alumno a un grupo. Solo puede haber una activa por alumno
 * (`inscrito` o `cursando`), regla garantizada por la base de datos.
 */
#[Table(name: 'inscripcion', key: 'id_inscripcion')]
#[Fillable(['id_alumno', 'id_grupo', 'fecha_inscripcion', 'estatus'])]
class Inscripcion extends Model
{
    public const ESTATUS = [
        'inscrito' => 'Inscrito',
        'cursando' => 'Cursando',
        'aprobado' => 'Aprobado',
        'reprobado' => 'Reprobado',
        'dado_de_baja' => 'Dado de baja',
    ];

    /** Estatus que cuentan como inscripción vigente. */
    public const ACTIVOS = ['inscrito', 'cursando'];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'datetime',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    public function scopeActivas(Builder $consulta): Builder
    {
        return $consulta->whereIn('estatus', self::ACTIVOS);
    }

    public function estaActiva(): bool
    {
        return in_array($this->estatus, self::ACTIVOS, true);
    }
}
