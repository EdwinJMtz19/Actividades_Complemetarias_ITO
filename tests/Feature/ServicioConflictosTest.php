<?php

use App\Models\Semestre;
use App\Models\Ubicacion;
use App\Services\ServicioConflictos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    Escenarios::base();
    Escenarios::dias(); // 1 lunes · 2 martes · … · 6 sábado
    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->instructor = Escenarios::usuario('instructor')->id;
    $this->aula = Ubicacion::create(['espacio' => 'Aula A-101', 'capacidad' => 30]);
});

/** Atajo: revisa un grupo hipotético (en el semestre activo) con los horarios dados. */
function revisar(array $grupo, array $horarios, ?int $excluir = null): array
{
    return (new ServicioConflictos)->verificar(
        $grupo + ['id_semestre' => Semestre::activo()->id_semestre, 'modalidad' => 'presencial', 'estatus' => 'abierta'],
        array_map(fn ($h) => ['id_dia' => $h[0], 'hora_inicio' => $h[1], 'hora_fin' => $h[2]], $horarios),
        $excluir,
    );
}

it('decide el traslape con el borde excluido', function (int $inicioA, int $finA, int $inicioB, int $finB, bool $esperado) {
    expect(ServicioConflictos::traslapan($inicioA, $finA, $inicioB, $finB))->toBe($esperado)
        ->and(ServicioConflictos::traslapan($inicioB, $finB, $inicioA, $finA))->toBe($esperado);
})->with([
    'se tocan en el borde' => [540, 600, 600, 660, false],
    'se cruzan' => [510, 570, 540, 600, true],
    'uno contiene al otro' => [480, 720, 540, 600, true],
    'idénticos' => [540, 600, 540, 600, true],
    'separados' => [480, 540, 600, 660, false],
]);

it('convierte horas a minutos con o sin segundos', function () {
    expect(ServicioConflictos::minutos('08:30'))->toBe(510)
        ->and(ServicioConflictos::minutos('08:30:00'))->toBe(510)
        ->and(ServicioConflictos::minutos('00:00'))->toBe(0);
});

it('avisa cuando el instructor ya tiene otro grupo a esa hora', function () {
    $actividad = Escenarios::actividad(['nombre' => 'Programación Web']);
    Escenarios::grupo(['id_actividad' => $actividad->id_actividad, 'grupo' => 'B', 'id_instructor' => $this->instructor], [[1, '10:00', '12:00']]);

    $conflictos = revisar(['id_instructor' => $this->instructor], [[1, '11:00', '13:00']]);

    expect($conflictos)->toHaveCount(1)
        ->and($conflictos[0]['tipo'])->toBe('instructor')
        ->and($conflictos[0]['campo'])->toBe('id_instructor')
        ->and($conflictos[0]['mensaje'])->toBe('El instructor ya tiene el grupo B de Programación Web el lunes de 10:00 a 12:00.');
});

it('no hay conflicto si los horarios solo se tocan o son de otro día', function (int $dia, string $inicio, string $fin) {
    Escenarios::grupo(['id_instructor' => $this->instructor], [[1, '10:00', '12:00']]);

    expect(revisar(['id_instructor' => $this->instructor], [[$dia, $inicio, $fin]]))->toBe([]);
})->with([
    'justo después' => [1, '12:00', '14:00'],
    'justo antes' => [1, '08:00', '10:00'],
    'otro día a la misma hora' => [2, '10:00', '12:00'],
]);

it('no choca con grupos cancelados, finalizados o de otro semestre', function (string $estatus) {
    Escenarios::grupo(['id_instructor' => $this->instructor, 'estatus' => $estatus], [[1, '10:00', '12:00']]);

    expect(revisar(['id_instructor' => $this->instructor], [[1, '10:00', '12:00']]))->toBe([]);
})->with(['cancelada', 'finalizada']);

it('no choca con un grupo de un semestre pasado', function () {
    $pasado = Escenarios::semestre(['año' => 2025, 'periodo' => 2, 'status' => Semestre::INACTIVO]);
    Escenarios::grupo(['id_semestre' => $pasado->id_semestre, 'id_instructor' => $this->instructor], [[1, '10:00', '12:00']]);

    expect(revisar(['id_instructor' => $this->instructor], [[1, '10:00', '12:00']]))->toBe([]);
});

it('sí choca con un grupo cerrado', function () {
    Escenarios::grupo(['id_instructor' => $this->instructor, 'estatus' => 'cerrada'], [[1, '10:00', '12:00']]);

    expect(revisar(['id_instructor' => $this->instructor], [[1, '10:00', '12:00']]))->toHaveCount(1);
});

it('un grupo que se edita no choca consigo mismo', function () {
    $grupo = Escenarios::grupo(['id_instructor' => $this->instructor], [[1, '10:00', '12:00']]);

    expect(revisar(['id_instructor' => $this->instructor], [[1, '10:00', '12:00']], $grupo->id_grupo))->toBe([]);
});

it('un grupo cancelado que se guarda no reclama instructor ni espacio', function () {
    Escenarios::grupo(['id_instructor' => $this->instructor, 'id_ubicacion' => $this->aula->id_ubicacion], [[1, '10:00', '12:00']]);

    expect(revisar(['id_instructor' => $this->instructor, 'id_ubicacion' => $this->aula->id_ubicacion, 'estatus' => 'cancelada'], [[1, '10:00', '12:00']]))->toBe([]);
});

it('avisa cuando la ubicación ya está ocupada', function () {
    $actividad = Escenarios::actividad(['nombre' => 'Ajedrez']);
    Escenarios::grupo(['id_actividad' => $actividad->id_actividad, 'id_ubicacion' => $this->aula->id_ubicacion], [[3, '16:00', '18:00']]);

    $conflictos = revisar(['id_ubicacion' => $this->aula->id_ubicacion], [[3, '17:00', '19:00']]);

    expect($conflictos)->toHaveCount(1)
        ->and($conflictos[0]['tipo'])->toBe('ubicacion')
        ->and($conflictos[0]['mensaje'])->toBe('El espacio «Aula A-101» ya está ocupado por el grupo A de Ajedrez el miércoles de 16:00 a 18:00.');
});

it('la modalidad virtual no usa espacio ni compara aforo', function () {
    Escenarios::grupo(['id_ubicacion' => $this->aula->id_ubicacion], [[1, '10:00', '12:00']]);

    expect(revisar(['id_ubicacion' => $this->aula->id_ubicacion, 'modalidad' => 'virtual', 'cupo_maximo' => 500], [[1, '10:00', '12:00']]))->toBe([]);
});

it('un grupo virtual ya registrado no ocupa el espacio', function () {
    Escenarios::grupo(['id_ubicacion' => $this->aula->id_ubicacion, 'modalidad' => 'virtual'], [[1, '10:00', '12:00']]);

    expect(revisar(['id_ubicacion' => $this->aula->id_ubicacion], [[1, '10:00', '12:00']]))->toBe([]);
});

it('avisa cuando el cupo máximo supera la capacidad', function () {
    $conflictos = revisar(['id_ubicacion' => $this->aula->id_ubicacion, 'cupo_maximo' => 31], [[1, '10:00', '12:00']]);

    expect($conflictos)->toHaveCount(1)
        ->and($conflictos[0]['tipo'])->toBe('cupo')
        ->and($conflictos[0]['campo'])->toBe('cupo_maximo')
        ->and($conflictos[0]['mensaje'])->toContain('31')->toContain('30 personas');

    expect(revisar(['id_ubicacion' => $this->aula->id_ubicacion, 'cupo_maximo' => 30], [[1, '10:00', '12:00']]))->toBe([]);
});

it('detecta dos horarios del mismo grupo que se traslapan', function () {
    $conflictos = revisar([], [[1, '10:00', '12:00'], [1, '11:00', '13:00']]);

    expect($conflictos)->toHaveCount(1)
        ->and($conflictos[0]['tipo'])->toBe('grupo')
        ->and($conflictos[0]['campo'])->toBe('horarios.1.hora_inicio')
        ->and($conflictos[0]['mensaje'])->toContain('lunes de 10:00 a 12:00')->toContain('lunes de 11:00 a 13:00');
});

it('permite dos horarios del mismo grupo en días distintos o consecutivos', function () {
    expect(revisar([], [[1, '10:00', '12:00'], [2, '10:00', '12:00'], [1, '12:00', '13:00']]))->toBe([]);
});

it('ignora horarios incompletos o al revés en vez de fallar', function () {
    expect(revisar(['id_instructor' => $this->instructor], [[1, '', '12:00'], [null, '10:00', '11:00'], [1, '12:00', '10:00']]))->toBe([]);
});

it('junta todos los conflictos y no repite el mismo aviso', function () {
    Escenarios::grupo(['id_instructor' => $this->instructor, 'id_ubicacion' => $this->aula->id_ubicacion], [[1, '10:00', '12:00']]);

    $conflictos = revisar(
        ['id_instructor' => $this->instructor, 'id_ubicacion' => $this->aula->id_ubicacion, 'cupo_maximo' => 99],
        [[1, '10:00', '11:00'], [1, '10:30', '11:30']],
    );

    expect(collect($conflictos)->pluck('tipo')->sort()->values()->all())->toBe(['cupo', 'grupo', 'instructor', 'ubicacion']);
});
