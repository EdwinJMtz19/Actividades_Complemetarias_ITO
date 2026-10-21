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
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->basicas = Escenarios::departamento('Ciencias Básicas');

    $this->actividadPropia = Escenarios::actividad([
        'nombre' => 'Propia',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->actividadAjena = Escenarios::actividad([
        'nombre' => 'Ajena',
        'id_departamento' => $this->basicas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);

    $this->grupoPropio = Escenarios::grupo([
        'id_actividad' => $this->actividadPropia->id_actividad,
        'grupo' => 'A',
    ], [[1, '10:00', '12:00']]);

    $this->grupoAjeno = Escenarios::grupo([
        'id_actividad' => $this->actividadAjena->id_actividad,
        'grupo' => 'B',
    ], [[2, '10:00', '12:00']]);
});

it('manda al login a los invitados', function () {
    foreach (['/actividades', '/grupos', "/grupos/{$this->grupoPropio->id_grupo}"] as $ruta) {
        $this->get($ruta)->assertRedirect('/login');
    }
});

it('niega el acceso al alumno', function () {
    $alumno = Escenarios::usuario('alumno');

    $this->actingAs($alumno)->get('/actividades')->assertForbidden();
    $this->get('/grupos')->assertForbidden();
    $this->get("/grupos/{$this->grupoPropio->id_grupo}")->assertForbidden();
});

it('deja al administrador ver todo', function () {
    $admin = Escenarios::usuario('administrador');

    $this->actingAs($admin)->get('/actividades')->assertOk()->assertSee('Propia')->assertSee('Ajena');
    $this->get('/grupos')->assertOk()->assertSee('Propia')->assertSee('Ajena');
    $this->get("/grupos/{$this->grupoAjeno->id_grupo}")->assertOk();
});

it('limita al coordinador a los grupos de su departamento', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);

    $this->actingAs($coordinador)->get('/grupos')
        ->assertOk()->assertSee('Propia')->assertDontSee('Ajena');

    $this->get("/grupos/{$this->grupoPropio->id_grupo}")->assertOk();
    $this->get("/grupos/{$this->grupoAjeno->id_grupo}")->assertForbidden();
    $this->get("/grupos/{$this->grupoAjeno->id_grupo}/edit")->assertForbidden();
    $this->delete("/grupos/{$this->grupoAjeno->id_grupo}")->assertForbidden();
});

it('deja al instructor ver solo los grupos que imparte', function () {
    $instructor = Escenarios::usuario('instructor', $this->sistemas);
    $this->grupoPropio->update(['id_instructor' => $instructor->id]);

    Escenarios::grupo([
        'id_actividad' => $this->actividadPropia->id_actividad,
        'grupo' => 'C',
    ], [[3, '10:00', '12:00']]);

    $this->actingAs($instructor)->get('/grupos')->assertOk();

    $listado = $this->get('/grupos');
    expect($listado->viewData('grupos')->pluck('id_grupo')->all())->toBe([$this->grupoPropio->id_grupo]);

    $this->get("/grupos/{$this->grupoPropio->id_grupo}")->assertOk();
    $this->get('/grupos/create')->assertForbidden();
    $this->get("/grupos/{$this->grupoPropio->id_grupo}/edit")->assertForbidden();
    $this->delete("/grupos/{$this->grupoPropio->id_grupo}")->assertForbidden();
    $this->get('/actividades')->assertForbidden();
});

it('muestra en el panel solo los módulos que el rol puede usar', function () {
    $this->actingAs(Escenarios::usuario('administrador'))->get('/panel')
        ->assertOk()
        ->assertSee(route('actividades.index'), false)
        ->assertSee(route('grupos.index'), false);

    $this->actingAs(Escenarios::usuario('alumno'))->get('/panel')
        ->assertOk()
        ->assertDontSee(route('actividades.index'), false)
        ->assertDontSee(route('grupos.index'), false);

    $this->actingAs(Escenarios::usuario('instructor', $this->sistemas))->get('/panel')
        ->assertOk()
        ->assertSee(route('grupos.index'), false)
        ->assertDontSee(route('actividades.index'), false);
});
