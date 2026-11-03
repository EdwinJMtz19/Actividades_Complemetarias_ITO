<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Grupo de una actividad en un semestre: con instructor, ubicación, cupos y horarios.
 * `cupo_ocupado` no es capturable: lo actualizará la inscripción (Sprint 4).
 */
#[Table(name: 'grupo', key: 'id_grupo')]
#[Fillable([
    'id_actividad', 'id_semestre', 'id_instructor', 'id_ubicacion', 'grupo',
    'cupo_minimo', 'cupo_maximo', 'modalidad', 'materiales_requeridos',
    'estatus', 'fecha_inicio', 'fecha_fin',
])]
class Grupo extends Model
{
    public const MODALIDADES = ['presencial' => 'Presencial', 'virtual' => 'Virtual', 'hibrida' => 'Híbrida'];

    public const ESTATUS = ['abierta' => 'Abierta', 'cerrada' => 'Cerrada', 'cancelada' => 'Cancelada', 'finalizada' => 'Finalizada'];

    /** Solo estos grupos ocupan al instructor y al espacio: los cancelados y finalizados no estorban. */
    public const ESTATUS_VIGENTES = ['abierta', 'cerrada'];

    protected function casts(): array
    {
        return [
            'cupo_minimo' => 'integer',
            'cupo_maximo' => 'integer',
            'cupo_ocupado' => 'integer',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad');
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class, 'id_semestre');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'id_instructor');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_grupo')->orderBy('id_dia')->orderBy('hora_inicio');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'id_grupo');
    }

    /** La modalidad virtual no usa espacio físico. */
    public function requiereUbicacion(): bool
    {
        return $this->modalidad !== 'virtual';
    }

    /** Un grupo con alumnos inscritos no se elimina. */
    public function tieneInscripciones(): bool
    {
        return $this->inscripciones()->exists();
    }

    /**
     * Grupos a los que un alumno puede inscribirse: abiertos, del periodo vigente, de
     * actividades disponibles y dirigidas a su carrera, y todavía con cupo.
     */
    public function scopeInscribiblesPara(Builder $consulta, Alumno $alumno, Semestre $semestre): Builder
    {
        return $consulta
            ->where('id_semestre', $semestre->id_semestre)
            ->where('estatus', 'abierta')
            ->whereColumn('cupo_ocupado', '<', 'cupo_maximo')
            ->whereHas('actividad', fn ($q) => $q
                ->where('disponible', true)
                ->whereHas('carreras', fn ($c) => $c->where('carrera.id_carrera', $alumno->id_carrera)));
    }

    /**
     * Grupos que el usuario puede ver: todos si es administrador, los de su departamento
     * si es coordinador, solo los suyos si es instructor y ninguno en otro caso.
     */
    public function scopeVisiblesPara(Builder $consulta, User $usuario): Builder
    {
        if ($usuario->esAdministrador()) {
            return $consulta;
        }

        if ($usuario->esCoordinador()) {
            $departamento = $usuario->ambitoDepartamento();

            return $departamento === null
                ? $consulta->whereRaw('1 = 0')
                : $consulta->whereHas('actividad', fn ($q) => $q->where('id_departamento', $departamento));
        }

        if ($usuario->hasRole('instructor')) {
            return $consulta->where('id_instructor', $usuario->getKey());
        }

        return $consulta->whereRaw('1 = 0');
    }
}
