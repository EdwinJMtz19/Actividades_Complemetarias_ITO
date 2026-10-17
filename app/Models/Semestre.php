<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Periodo escolar. Solo uno puede estar activo a la vez y se cierra solo
 * cuando pasa su fecha de fin (ver cerrarVencidos()).
 */
#[Table(name: 'semestre', key: 'id_semestre')]
#[Fillable([
    'año', 'periodo', 'fecha_inicio', 'fecha_fin',
    'fecha_inicio_inscripciones', 'hora_inicio_inscripciones',
    'fecha_fin_inscripciones', 'hora_fin_inscripciones', 'status',
])]
class Semestre extends Model
{
    public const ACTIVO = 'activo';

    public const INACTIVO = 'inactivo';

    protected function casts(): array
    {
        return [
            'año' => 'integer',
            'periodo' => 'integer',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
            'fecha_inicio_inscripciones' => 'date:Y-m-d',
            'fecha_fin_inscripciones' => 'date:Y-m-d',
        ];
    }

    // ── Consultas ───────────────────────────────────────────────────────

    /** El periodo activo, o null si no hay ninguno. */
    public static function activo(): ?self
    {
        return static::where('status', self::ACTIVO)->first();
    }

    /** El periodo inactivo más reciente (el único que se puede reactivar). */
    public static function masRecienteInactivo(): ?self
    {
        return static::where('status', self::INACTIVO)
            ->orderByDesc('año')->orderByDesc('periodo')
            ->first();
    }

    /**
     * Cierra los periodos activos cuya fecha de fin ya pasó.
     * Lo ejecuta el comando programado «semestres:cerrar-vencidos» y, por si el
     * programador no está corriendo, también las pantallas de semestres.
     */
    public static function cerrarVencidos(): int
    {
        return static::where('status', self::ACTIVO)
            ->where('fecha_fin', '<', now()->toDateString())
            ->update(['status' => self::INACTIVO]);
    }

    // ── Reglas de negocio ───────────────────────────────────────────────

    public function haVencido(): bool
    {
        return now()->startOfDay()->gt($this->fecha_fin);
    }

    /**
     * La ventana de inscripción abre el día de inicio a la hora de inicio y cierra
     * el día de fin a la hora de fin. Solo cuenta si el periodo está activo.
     */
    public function inscripcionesAbiertas(?CarbonInterface $ahora = null): bool
    {
        if ($this->status !== self::ACTIVO) {
            return false;
        }

        $ahora ??= now();
        $abre = $this->fecha_inicio_inscripciones->copy()->setTimeFromTimeString($this->hora_inicio_inscripciones);
        $cierra = $this->fecha_fin_inscripciones->copy()->setTimeFromTimeString($this->hora_fin_inscripciones);

        return $ahora->betweenIncluded($abre, $cierra);
    }

    /**
     * Por qué no se puede editar este periodo, o null si sí se puede.
     * Se edita el activo; sin ninguno activo, solo el inactivo más reciente (para reactivarlo).
     */
    public function motivoNoEditable(): ?string
    {
        if ($this->status === self::ACTIVO) {
            return null;
        }

        if (self::activo() !== null) {
            return 'Los periodos inactivos no pueden editarse mientras haya uno activo.';
        }

        return self::masRecienteInactivo()?->is($this)
            ? null
            : 'Solo el periodo más reciente puede reactivarse cuando no hay ninguno activo.';
    }

    // ── Presentación ────────────────────────────────────────────────────

    /** «Enero–Junio 2026» */
    public function getEtiquetaAttribute(): string
    {
        return ($this->periodo === 1 ? 'Enero–Junio' : 'Agosto–Diciembre').' '.$this->año;
    }

    /** «Ene–Jun 2026» */
    public function getEtiquetaCortaAttribute(): string
    {
        return ($this->periodo === 1 ? 'Ene–Jun' : 'Ago–Dic').' '.$this->año;
    }

    /** Hora de apertura en formato HH:MM. */
    public function getHoraInicioCortaAttribute(): string
    {
        return substr($this->hora_inicio_inscripciones, 0, 5);
    }

    /** Hora de cierre en formato HH:MM. */
    public function getHoraFinCortaAttribute(): string
    {
        return substr($this->hora_fin_inscripciones, 0, 5);
    }
}
