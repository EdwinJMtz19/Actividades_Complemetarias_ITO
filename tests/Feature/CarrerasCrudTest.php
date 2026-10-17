<?php

use App\Models\Carrera;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->admin = Escenarios::usuario('administrador');
});

it('lista las carreras con su número de alumnos y busca por nombre', function () {
    Escenarios::usuario('alumno'); // crea «Ingeniería en Sistemas Computacionales» con 1 alumno
    Carrera::create(['nombre' => 'Arquitectura']);

    $respuesta = $this->actingAs($this->admin)->get('/carreras')->assertOk()->assertSee('Arquitectura');

    expect($respuesta->viewData('carreras')->firstWhere('nombre', 'Ingeniería en Sistemas Computacionales')->alumnos_count)->toBe(1);

    $this->get('/carreras?buscar=Arquit')->assertSee('Arquitectura')->assertDontSee('Sistemas Computacionales');
});

it('crea una carrera', function () {
    $this->actingAs($this->admin)->post('/carreras', ['nombre' => 'Arquitectura'])
        ->assertRedirect(route('carreras.index'))->assertSessionHas('exito');

    expect(Carrera::where('nombre', 'Arquitectura')->exists())->toBeTrue();
});

it('rechaza un nombre vacío, demasiado largo o repetido', function () {
    Carrera::create(['nombre' => 'Arquitectura']);

    $this->actingAs($this->admin)->post('/carreras', ['nombre' => ''])->assertSessionHasErrors('nombre');
    $this->post('/carreras', ['nombre' => str_repeat('a', 101)])->assertSessionHasErrors('nombre');
    $this->post('/carreras', ['nombre' => 'Arquitectura'])
        ->assertSessionHasErrors(['nombre' => 'Ya existe una carrera con ese nombre.']);
});

it('edita una carrera y deja conservar su propio nombre', function () {
    $carrera = Carrera::create(['nombre' => 'Arquitectura']);

    $this->actingAs($this->admin)->put("/carreras/{$carrera->id_carrera}", ['nombre' => 'Arquitectura'])->assertRedirect();
    $this->put("/carreras/{$carrera->id_carrera}", ['nombre' => 'Diseño'])->assertRedirect();

    expect($carrera->fresh()->nombre)->toBe('Diseño');

    $otra = Carrera::create(['nombre' => 'Biología']);
    $this->put("/carreras/{$otra->id_carrera}", ['nombre' => 'Diseño'])->assertSessionHasErrors('nombre');
});

it('muestra los formularios de alta y edición', function () {
    $carrera = Carrera::create(['nombre' => 'Arquitectura']);

    $this->actingAs($this->admin)->get('/carreras/create')->assertOk();
    $this->get("/carreras/{$carrera->id_carrera}/edit")->assertOk()->assertSee('Arquitectura');
});

it('elimina una carrera sin alumnos', function () {
    $carrera = Carrera::create(['nombre' => 'Arquitectura']);

    $this->actingAs($this->admin)->delete("/carreras/{$carrera->id_carrera}")
        ->assertRedirect(route('carreras.index'))->assertSessionHas('exito');

    expect(Carrera::find($carrera->id_carrera))->toBeNull();
});

it('no elimina una carrera con alumnos registrados', function () {
    $alumno = Escenarios::usuario('alumno')->alumno;

    $this->actingAs($this->admin)->delete("/carreras/{$alumno->id_carrera}")
        ->assertRedirect()->assertSessionHas('error', fn ($m) => str_contains($m, '1 alumno(s)'));

    expect(Carrera::find($alumno->id_carrera))->not->toBeNull();
});
