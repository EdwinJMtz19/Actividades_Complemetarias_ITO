<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Calificación de una inscripción (Sprint 5, HU-20).
 * `desempenio` es cualitativo; el controlador lo traduce a aprobado/reprobado.
 */
#[Table(name: 'calificacion', key: 'id_calificacion')]
#[Fillable(['id_inscripcion', 'desempenio', 'observaciones'])]
class Calificacion extends Model
{
    public const DESEMPENIOS = [
        'excelente' => 'Excelente',
        'bueno' => 'Bueno',
        'malo' => 'Malo',
    ];

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }
}
