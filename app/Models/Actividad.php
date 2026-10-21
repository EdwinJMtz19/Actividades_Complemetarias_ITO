<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Actividad complementaria que ofrece un departamento (fútbol, ajedrez, programación…).
 * Está dirigida a una o varias carreras y se asocia al semestre en que se publicó.
 */
#[Table(name: 'actividad_complementaria', key: 'id_actividad')]
#[Fillable([
    'nombre', 'descripcion', 'id_categoria', 'id_departamento', 'id_semestre',
    'requisitos', 'nivel_actividad', 'disponible', 'creditos',
])]
class Actividad extends Model
{
    /** Créditos que puede valer una actividad (igual que en el sistema anterior). */
    public const CREDITOS_PERMITIDOS = [1, 2];

    protected function casts(): array
    {
        return [
            'disponible' => 'boolean',
            'creditos' => 'integer',
        ];
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre');
    }

    public function carreras(): BelongsToMany
    {
        return $this->belongsToMany(Carrera::class, 'actividad_carrera', 'id_actividad', 'id_carrera');
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'id_actividad');
    }

    /**
     * Actividades que el usuario puede ver: todas si es administrador, las de su
     * departamento si coordina uno, y ninguna en cualquier otro caso.
     */
    public function scopeVisiblesPara(Builder $consulta, User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return $consulta;
        }

        $departamento = $usuario->ambitoDepartamento();

        return $departamento === null
            ? $consulta->whereRaw('1 = 0')
            : $consulta->where('id_departamento', $departamento);
    }
}
