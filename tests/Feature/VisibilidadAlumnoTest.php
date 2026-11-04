<?php

use App\Models\Semestre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo('2026-09-15 10:00:00');
    Escenarios::base();
    Escenarios::dias();
    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->alumno = Escenarios::usuario('alumno');
    $this->otraCarrera = Escenarios::carrera('Ingeniería Civil');
});

it('lista al alumno solo las actividades disponibles de su carrera', function () {
    Escenarios::actividad(['nombre' => 'Ajedrez', 'id_semestre' => $this->semestre->id_semestre]);
    Escenarios::actividad(['nombre' => 'Fútbol', 'id_semestre' => $this->semestre->id_semestre]);
    $ajena = Escenarios::actividad(['nombre' => 'Voleibol', 'id_semestre' => $this->semestre->id_semestre]);
    $ajena->carreras()->sync([$this->otraCarrera->id_carrera]);
    Escenarios::actividad(['nombre' => 'Danza', 'disponible' => false]);

    $this->actingAs($this->alumno)->get('/actividades')
        ->assertOk()
        ->assertSee('Ajedrez')
        ->assertSee('Fútbol')
        ->assertDontSee('Voleibol')
        ->assertDontSee('Danza');
});

it('lista al alumno los grupos de las actividades de su carrera', function () {
    $propia = Escenarios::actividad(['nombre' => 'Ajedrez']);
    Escenarios::grupo(['id_actividad' => $propia->id_actividad, 'grupo' => 'A']);
    $ajena = Escenarios::actividad(['nombre' => 'Voleibol']);
    $ajena->carreras()->sync([$this->otraCarrera->id_carrera]);
    Escenarios::grupo(['id_actividad' => $ajena->id_actividad, 'grupo' => 'B']);

    $this->actingAs($this->alumno)->get('/grupos')
        ->assertOk()
        ->assertSee('Ajedrez')
        ->assertDontSee('Voleibol');
});

it('deja al alumno ver el detalle de un grupo de su carrera', function () {
    $actividad = Escenarios::actividad(['nombre' => 'Ajedrez']);
    $grupo = Escenarios::grupo(['id_actividad' => $actividad->id_actividad]);

    $this->actingAs($this->alumno)->get('/grupos/'.$grupo->id_grupo)
        ->assertOk()->assertSee('Ajedrez');
});

it('no deja al alumno ver el detalle de un grupo de otra carrera', function () {
    $ajena = Escenarios::actividad(['nombre' => 'Voleibol']);
    $ajena->carreras()->sync([$this->otraCarrera->id_carrera]);
    $grupo = Escenarios::grupo(['id_actividad' => $ajena->id_actividad]);

    $this->actingAs($this->alumno)->get('/grupos/'.$grupo->id_grupo)->assertForbidden();
});

it('muestra al alumno el catálogo de inscripción con los grupos de su carrera', function () {
    $actividad = Escenarios::actividad(['nombre' => 'Ajedrez']);
    Escenarios::grupo(['id_actividad' => $actividad->id_actividad, 'grupo' => 'A']);

    $this->actingAs($this->alumno)->get('/inscripciones')
        ->assertOk()->assertSee('Ajedrez')->assertSee('A');
});

it('no deja al alumno administrar actividades ni grupos', function () {
    $actividad = Escenarios::actividad();
    $grupo = Escenarios::grupo(['id_actividad' => $actividad->id_actividad]);

    $this->actingAs($this->alumno)->get('/actividades/create')->assertForbidden();
    $this->get('/actividades/'.$actividad->id_actividad.'/edit')->assertForbidden();
    $this->get('/grupos/create')->assertForbidden();
    $this->get('/grupos/'.$grupo->id_grupo.'/edit')->assertForbidden();
});
