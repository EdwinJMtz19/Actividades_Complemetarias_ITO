<?php

namespace App\Services;

use App\Models\DiaSemana;
use App\Models\Grupo;
use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Builder;

/**
 * Revisa si los horarios de un grupo chocan con algo (HU-16). Es la única fuente de estas reglas:
 * la usan el formulario del grupo al guardar y el aviso en vivo.
 *
 *  1. Dos horarios del propio grupo no pueden traslaparse.
 *  2. El instructor no puede estar en dos grupos a la vez.
 *  3. La ubicación no puede tener dos grupos a la vez (la modalidad virtual no usa espacio).
 *  4. El cupo máximo no puede superar la capacidad de la ubicación.
 *
 * Solo cuentan los grupos del MISMO semestre con estatus abierta o cerrada: un grupo cancelado,
 * finalizado o de un semestre pasado no ocupa a nadie. Dos horarios que solo se tocan en el borde
 * (09:00–10:00 y 10:00–11:00) no se traslapan.
 */
class ServicioConflictos
{
    public const INSTRUCTOR = 'instructor';

    public const UBICACION = 'ubicacion';

    public const GRUPO = 'grupo';

    public const CUPO = 'cupo';

    /**
     * @param  array{id_semestre: int, id_instructor?: int|string|null, id_ubicacion?: int|string|null, modalidad?: string|null, cupo_maximo?: int|string|null, estatus?: string|null}  $grupo
     * @param  array<int, array{id_dia?: int|string|null, hora_inicio?: string|null, hora_fin?: string|null}>  $horarios
     * @param  int|null  $excluirGrupo  Grupo que se está editando: no choca consigo mismo.
     * @return list<array{tipo: string, campo: string, mensaje: string}>
     */
    public function verificar(array $grupo, array $horarios, ?int $excluirGrupo = null): array
    {
        $solicitados = $this->normalizar($horarios);
        $conflictos = [];

        $this->horariosInternos($solicitados, $conflictos);

        $modalidad = $grupo['modalidad'] ?? null;
        $vigente = in_array($grupo['estatus'] ?? 'abierta', Grupo::ESTATUS_VIGENTES, true);
        $dias = array_values(array_unique(array_column($solicitados, 'dia')));
        $semestre = (int) ($grupo['id_semestre'] ?? 0);

        if ($vigente && $dias !== [] && ! empty($grupo['id_instructor'])) {
            $this->conflictosContra(
                $solicitados, $dias, $semestre, $excluirGrupo, $conflictos,
                self::INSTRUCTOR, 'id_instructor',
                fn (Builder $consulta) => $consulta->where('id_instructor', $grupo['id_instructor']),
                fn (Grupo $g, string $horario) => "El instructor ya tiene el grupo {$g->grupo} de {$g->actividad->nombre} {$horario}.",
            );
        }

        if ($modalidad !== 'virtual' && ! empty($grupo['id_ubicacion'])) {
            $ubicacion = Ubicacion::find($grupo['id_ubicacion']);

            if ($vigente && $dias !== [] && $ubicacion !== null) {
                $this->conflictosContra(
                    $solicitados, $dias, $semestre, $excluirGrupo, $conflictos,
                    self::UBICACION, 'id_ubicacion',
                    fn (Builder $consulta) => $consulta->where('id_ubicacion', $ubicacion->id_ubicacion)->where('modalidad', '!=', 'virtual'),
                    fn (Grupo $g, string $horario) => "El espacio «{$ubicacion->espacio}» ya está ocupado por el grupo {$g->grupo} de {$g->actividad->nombre} {$horario}.",
                );
            }

            $cupo = (int) ($grupo['cupo_maximo'] ?? 0);

            if ($ubicacion !== null && $cupo > $ubicacion->capacidad) {
                $conflictos[] = [
                    'tipo' => self::CUPO,
                    'campo' => 'cupo_maximo',
                    'mensaje' => "El cupo máximo ({$cupo}) supera la capacidad de «{$ubicacion->espacio}» ({$ubicacion->capacidad} personas).",
                ];
            }
        }

        return $conflictos;
    }

    /** Dos bloques se traslapan si cada uno empieza antes de que termine el otro (el borde no cuenta). */
    public static function traslapan(int $inicioA, int $finA, int $inicioB, int $finB): bool
    {
        return $inicioA < $finB && $inicioB < $finA;
    }

    /** «08:30» o «08:30:00» → minutos desde la medianoche. */
    public static function minutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora) + [1 => 0]);

        return $h * 60 + $m;
    }

    /**
     * Descarta los bloques incompletos o al revés (fin antes de inicio): de eso se queja la
     * validación del formulario, no este servicio. Conserva la posición original para señalar el campo.
     *
     * @return array<int, array{dia: int, inicio: int, fin: int}>
     */
    private function normalizar(array $horarios): array
    {
        $normalizados = [];

        foreach ($horarios as $posicion => $horario) {
            if (empty($horario['id_dia']) || empty($horario['hora_inicio']) || empty($horario['hora_fin'])) {
                continue;
            }

            $inicio = self::minutos($horario['hora_inicio']);
            $fin = self::minutos($horario['hora_fin']);

            if ($fin <= $inicio) {
                continue;
            }

            $normalizados[$posicion] = ['dia' => (int) $horario['id_dia'], 'inicio' => $inicio, 'fin' => $fin];
        }

        return $normalizados;
    }

    private function horariosInternos(array $solicitados, array &$conflictos): void
    {
        $posiciones = array_keys($solicitados);

        foreach ($posiciones as $i => $a) {
            foreach (array_slice($posiciones, $i + 1) as $b) {
                $x = $solicitados[$a];
                $y = $solicitados[$b];

                if ($x['dia'] === $y['dia'] && self::traslapan($x['inicio'], $x['fin'], $y['inicio'], $y['fin'])) {
                    $conflictos[] = [
                        'tipo' => self::GRUPO,
                        'campo' => "horarios.{$b}.hora_inicio",
                        'mensaje' => 'Dos horarios del grupo se traslapan: '.$this->describir($this->nombreDia($x['dia']), $x['inicio'], $x['fin'])
                            .' y '.$this->describir($this->nombreDia($y['dia']), $y['inicio'], $y['fin']).'.',
                    ];
                }
            }
        }
    }

    /**
     * @param  callable(Builder): mixed  $filtro
     * @param  callable(Grupo, string): string  $mensaje
     */
    private function conflictosContra(array $solicitados, array $dias, int $semestre, ?int $excluirGrupo, array &$conflictos, string $tipo, string $campo, callable $filtro, callable $mensaje): void
    {
        $existentes = Grupo::query()
            ->with(['horarios.dia', 'actividad'])
            ->where('id_semestre', $semestre)
            ->whereIn('estatus', Grupo::ESTATUS_VIGENTES)
            ->when($excluirGrupo, fn ($q) => $q->where('id_grupo', '!=', $excluirGrupo))
            ->whereHas('horarios', fn ($q) => $q->whereIn('id_dia', $dias))
            ->tap($filtro)
            ->get();

        $vistos = [];

        foreach ($existentes as $grupoExistente) {
            foreach ($grupoExistente->horarios as $horario) {
                $inicio = self::minutos($horario->hora_inicio);
                $fin = self::minutos($horario->hora_fin);

                foreach ($solicitados as $pedido) {
                    if ($pedido['dia'] !== (int) $horario->id_dia || ! self::traslapan($pedido['inicio'], $pedido['fin'], $inicio, $fin)) {
                        continue;
                    }

                    $texto = $mensaje($grupoExistente, 'el '.$this->describir(mb_strtolower($horario->dia->nombre_dia), $inicio, $fin));

                    if (! isset($vistos[$texto])) {
                        $vistos[$texto] = true;
                        $conflictos[] = ['tipo' => $tipo, 'campo' => $campo, 'mensaje' => $texto];
                    }
                }
            }
        }
    }

    private function describir(string $dia, int $inicio, int $fin): string
    {
        return sprintf('%s de %02d:%02d a %02d:%02d', $dia, intdiv($inicio, 60), $inicio % 60, intdiv($fin, 60), $fin % 60);
    }

    private function nombreDia(int $idDia): string
    {
        return mb_strtolower(DiaSemana::find($idDia)?->nombre_dia ?? 'un día');
    }
}
