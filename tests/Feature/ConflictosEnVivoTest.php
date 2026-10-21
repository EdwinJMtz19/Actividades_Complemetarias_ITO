<?php

use App\Models\Semestre;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo('2026-09-15 10:00:00');
    Escenarios::base();
    Escenarios::dias();
    $this->admin = Escenarios::usuario('administrador');
    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->ubicacion = Ubicacion::create(['espacio' => 'Aula A-101', 'capacidad' => 30]);
    $this->instructor = Escenarios::usuario('instructor', $this->sistemas);
});

/** Cuerpo del aviso en vivo; `$extra` cambia o agrega campos. */
function consultaDeConflictos(int $instructor, int $ubicacion, array $extra = []): array
{
    return array_merge([
        'id_instructor' => $instructor,
        'id_ubicacion' => $ubicacion,
        'modalidad' => 'presencial',
        'cupo_maximo' => 20,
        'estatus' => 'abierta',
        'horarios' => [
            ['id_dia' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '10:00'],
        ],
    ], $extra);
}

it('informa que no hay conflictos cuando el hueco está libre', function () {
    $this->actingAs($this->admin)
        ->postJson('/grupos/conflictos', consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion))
        ->assertOk()
        ->assertJson(['hayConflicto' => false])
        ->assertJsonCount(0, 'conflictos');
});

it('detecta que el instructor ya tiene otro grupo en ese horario', function () {
    Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'grupo' => 'B',
        'id_instructor' => $this->instructor->id,
    ], [[1, '10:00', '12:00']]);

    $respuesta = $this->actingAs($this->admin)
        ->postJson('/grupos/conflictos', consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion, [
            'horarios' => [['id_dia' => 1, 'hora_inicio' => '11:00', 'hora_fin' => '13:00']],
        ]))
        ->assertOk()
        ->assertJson(['hayConflicto' => true]);

    expect($respuesta->json('conflictos.0.tipo'))->toBe('instructor');
});

it('detecta el espacio ocupado y el cupo mayor que la capacidad', function () {
    Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'grupo' => 'B',
        'id_ubicacion' => $this->ubicacion->id_ubicacion,
    ], [[2, '10:00', '12:00']]);

    $respuesta = $this->actingAs($this->admin)
        ->postJson('/grupos/conflictos', consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion, [
            'cupo_maximo' => 99,
            'horarios' => [['id_dia' => 2, 'hora_inicio' => '11:00', 'hora_fin' => '13:00']],
        ]))
        ->assertOk()
        ->assertJson(['hayConflicto' => true]);

    expect(collect($respuesta->json('conflictos'))->pluck('tipo')->all())
        ->toContain('ubicacion', 'cupo');
});

it('no se reporta a sí mismo al editar un grupo', function () {
    $grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_instructor' => $this->instructor->id,
        'id_ubicacion' => $this->ubicacion->id_ubicacion,
    ], [[1, '10:00', '12:00']]);

    $this->actingAs($this->admin)
        ->postJson('/grupos/conflictos', consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion, [
            'id_grupo' => $grupo->id_grupo,
            'horarios' => [['id_dia' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '12:00']],
        ]))
        ->assertOk()
        ->assertJson(['hayConflicto' => false]);
});

it('responde con un aviso cuando no hay periodo escolar activo', function () {
    $this->semestre->update(['status' => Semestre::INACTIVO]);

    $respuesta = $this->actingAs($this->admin)
        ->postJson('/grupos/conflictos', consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion))
        ->assertOk();

    expect($respuesta->json('aviso'))->toContain('periodo escolar activo');
});

it('valida el formato de las horas', function () {
    $this->actingAs($this->admin)
        ->postJson('/grupos/conflictos', consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion, [
            'horarios' => [['id_dia' => 1, 'hora_inicio' => '25:00', 'hora_fin' => '26:00']],
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['horarios.0.hora_inicio', 'horarios.0.hora_fin']);
});

it('protege el aviso: invitados, alumnos e instructores no lo usan', function () {
    $cuerpo = consultaDeConflictos($this->instructor->id, $this->ubicacion->id_ubicacion);

    $this->postJson('/grupos/conflictos', $cuerpo)->assertUnauthorized();

    $this->actingAs(Escenarios::usuario('alumno'))
        ->postJson('/grupos/conflictos', $cuerpo)->assertForbidden();

    $this->actingAs(Escenarios::usuario('instructor', $this->sistemas))
        ->postJson('/grupos/conflictos', $cuerpo)->assertForbidden();
});
