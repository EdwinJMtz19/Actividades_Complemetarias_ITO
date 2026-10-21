<?php

use App\Models\Grupo;
use App\Models\Horario;
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
    $this->basicas = Escenarios::departamento('Ciencias Básicas');
    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->ubicacion = Ubicacion::create(['espacio' => 'Aula A-101', 'capacidad' => 30]);
    $this->instructor = Escenarios::usuario('instructor', $this->sistemas);
});

/** Datos válidos de un grupo; `$extra` cambia o agrega campos. */
function datosDeGrupo(int $actividad, int $ubicacion, ?int $instructor = null, array $extra = []): array
{
    return array_merge([
        'id_actividad' => $actividad,
        'grupo' => 'A',
        'cupo_minimo' => 5,
        'cupo_maximo' => 20,
        'modalidad' => 'presencial',
        'id_ubicacion' => $ubicacion,
        'id_instructor' => $instructor,
        'estatus' => 'abierta',
        'fecha_inicio' => '2026-09-01',
        'fecha_fin' => '2026-12-01',
        'materiales_requeridos' => 'Balones',
        'horarios' => [
            ['id_dia' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '12:00'],
        ],
    ], $extra);
}

it('crea un grupo con sus horarios en el semestre activo', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, $this->instructor->id))
        ->assertRedirect()->assertSessionHas('exito');

    $grupo = Grupo::firstOrFail();

    expect($grupo->grupo)->toBe('A')
        ->and($grupo->id_semestre)->toBe($this->semestre->id_semestre)
        ->and($grupo->id_instructor)->toBe($this->instructor->id)
        ->and($grupo->id_ubicacion)->toBe($this->ubicacion->id_ubicacion)
        ->and($grupo->horarios)->toHaveCount(1)
        ->and($grupo->horarios->first()->inicio_corto)->toBe('10:00')
        ->and($grupo->horarios->first()->fin_corto)->toBe('12:00');
});

it('guarda el nombre del grupo en mayúsculas', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, ['grupo' => ' b ']))
        ->assertRedirect()->assertSessionHas('exito');

    expect(Grupo::firstOrFail()->grupo)->toBe('B');
});

it('exige que el cupo máximo no sea menor que el mínimo', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
            'cupo_minimo' => 10,
            'cupo_maximo' => 5,
        ]))
        ->assertSessionHasErrors('cupo_maximo');

    expect(Grupo::count())->toBe(0)
        ->and(Horario::count())->toBe(0);
});

it('mantiene las fechas del grupo dentro del semestre', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
            'fecha_inicio' => '2026-08-01',
        ]))
        ->assertSessionHasErrors('fecha_inicio');

    $this->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
        'fecha_fin' => '2026-12-20',
    ]))->assertSessionHasErrors('fecha_fin');

    expect(Grupo::count())->toBe(0);
});

it('pide ubicación a los grupos presenciales pero no a los virtuales', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
            'id_ubicacion' => null,
        ]))
        ->assertSessionHasErrors('id_ubicacion');

    $this->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
        'modalidad' => 'virtual',
        'id_ubicacion' => null,
    ]))->assertRedirect()->assertSessionHas('exito');

    expect(Grupo::firstOrFail()->id_ubicacion)->toBeNull();
});

it('no acepta un instructor de otro departamento', function () {
    $ajeno = Escenarios::usuario('instructor', $this->basicas);

    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, $ajeno->id))
        ->assertSessionHasErrors('id_instructor');

    expect(Grupo::count())->toBe(0);
});

it('no repite el nombre del grupo dentro de la misma actividad', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion))
        ->assertRedirect();

    $this->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
        'grupo' => 'a',
    ]))->assertSessionHasErrors(['grupo' => 'Esa actividad ya tiene un grupo con ese nombre.']);

    $otra = Escenarios::actividad(['nombre' => 'Fútbol', 'id_semestre' => $this->semestre->id_semestre]);

    $this->post('/grupos', datosDeGrupo($otra->id_actividad, $this->ubicacion->id_ubicacion, null, [
        'grupo' => 'A',
        'horarios' => [['id_dia' => 2, 'hora_inicio' => '10:00', 'hora_fin' => '12:00']],
    ]))->assertRedirect()->assertSessionHas('exito');

    expect(Grupo::count())->toBe(2);
});

it('no crea grupos si no hay un periodo escolar activo', function () {
    $this->semestre->update(['status' => Semestre::INACTIVO]);

    $this->actingAs($this->admin)->get('/grupos/create')
        ->assertRedirect(route('grupos.index'))
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'periodo escolar activo'));

    $this->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion))
        ->assertSessionHasErrors('id_actividad');

    expect(Grupo::count())->toBe(0);
});

it('no crea grupos para una actividad de otro semestre', function () {
    $anterior = Escenarios::semestre(['año' => 2025, 'periodo' => 2, 'status' => Semestre::INACTIVO]);
    $vieja = Escenarios::actividad([
        'nombre' => 'Ajedrez 2025',
        'id_semestre' => $anterior->id_semestre,
    ]);

    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($vieja->id_actividad, $this->ubicacion->id_ubicacion))
        ->assertSessionHasErrors('id_actividad');

    expect(Grupo::count())->toBe(0);
});

it('rechaza dos horarios que se traslapan y no deja nada a medias', function () {
    $this->actingAs($this->admin)
        ->post('/grupos', datosDeGrupo($this->actividad->id_actividad, $this->ubicacion->id_ubicacion, null, [
            'horarios' => [
                ['id_dia' => 1, 'hora_inicio' => '10:00', 'hora_fin' => '12:00'],
                ['id_dia' => 1, 'hora_inicio' => '11:00', 'hora_fin' => '13:00'],
            ],
        ]))
        ->assertSessionHasErrors('horarios.1.hora_inicio');

    expect(Grupo::count())->toBe(0)
        ->and(Horario::count())->toBe(0);
});

it('muestra el detalle del grupo con su lugar y sus horarios', function () {
    $grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_ubicacion' => $this->ubicacion->id_ubicacion,
        'grupo' => 'A',
    ], [[1, '10:00', '12:00']]);

    $this->actingAs($this->admin)->get("/grupos/{$grupo->id_grupo}")
        ->assertOk()
        ->assertSee('Ajedrez')
        ->assertSee('Aula A-101')
        ->assertSee('10:00');
});

it('edita un grupo sin chocar consigo mismo y reemplaza sus horarios', function () {
    $grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_ubicacion' => $this->ubicacion->id_ubicacion,
    ], [[1, '10:00', '12:00']]);

    $this->actingAs($this->admin)
        ->put("/grupos/{$grupo->id_grupo}", datosDeGrupo(
            $this->actividad->id_actividad,
            $this->ubicacion->id_ubicacion,
            $this->instructor->id,
            ['grupo' => 'A', 'cupo_maximo' => 25],
        ))
        ->assertRedirect(route('grupos.show', $grupo))->assertSessionHas('exito');

    expect($grupo->fresh()->cupo_maximo)->toBe(25)
        ->and($grupo->fresh()->id_instructor)->toBe($this->instructor->id);

    $this->put("/grupos/{$grupo->id_grupo}", datosDeGrupo(
        $this->actividad->id_actividad,
        $this->ubicacion->id_ubicacion,
        $this->instructor->id,
        ['grupo' => 'A', 'horarios' => [['id_dia' => 3, 'hora_inicio' => '08:00', 'hora_fin' => '09:30']]],
    ))->assertRedirect();

    $horarios = $grupo->fresh()->horarios;

    expect($horarios)->toHaveCount(1)
        ->and($horarios->first()->id_dia)->toBe(3)
        ->and($horarios->first()->inicio_corto)->toBe('08:00');
});

it('lista y filtra los grupos por actividad y estatus', function () {
    Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'grupo' => 'A',
    ], [[1, '10:00', '12:00']]);

    Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'grupo' => 'B',
        'estatus' => 'cancelada',
    ], [[2, '10:00', '12:00']]);

    $this->actingAs($this->admin)->get('/grupos')
        ->assertOk()->assertSee('Ajedrez');

    $this->get('/grupos?buscar=Ajed')->assertSee('Ajedrez');

    $cancelados = $this->get('/grupos?estatus=cancelada')->assertOk();
    expect($cancelados->viewData('grupos')->pluck('grupo')->all())->toBe(['B']);
});

it('no elimina un grupo que ya tiene alumnos inscritos', function () {
    $grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
    ], [[1, '10:00', '12:00']]);

    $grupo->cupo_ocupado = 3;
    $grupo->save();

    $this->actingAs($this->admin)->delete("/grupos/{$grupo->id_grupo}")
        ->assertRedirect()->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'inscritos'));

    expect(Grupo::find($grupo->id_grupo))->not->toBeNull();
});

it('elimina un grupo sin alumnos inscritos', function () {
    $grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
    ]);

    $this->actingAs($this->admin)->delete("/grupos/{$grupo->id_grupo}")
        ->assertRedirect(route('grupos.index'))->assertSessionHas('exito');

    expect(Grupo::find($grupo->id_grupo))->toBeNull();
});

it('elimina un grupo con horarios y se lleva sus horarios en cascada', function () {
    $grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
    ], [[1, '10:00', '12:00'], [3, '14:00', '16:00']]);

    expect($grupo->horarios)->toHaveCount(2);

    $this->actingAs($this->admin)->delete("/grupos/{$grupo->id_grupo}")
        ->assertRedirect(route('grupos.index'))->assertSessionHas('exito');

    expect(Grupo::find($grupo->id_grupo))->toBeNull()
        ->and(Horario::where('id_grupo', $grupo->id_grupo)->count())->toBe(0);
});
