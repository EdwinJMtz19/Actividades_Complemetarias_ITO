<?php

use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->admin = Escenarios::usuario('administrador');
});

it('lista los departamentos y busca por nombre o edificio', function () {
    Departamento::create(['nombre' => 'Metal Mecánica', 'edificio' => 'Edificio Z']);
    Departamento::create(['nombre' => 'Gestión', 'edificio' => 'Torre Norte']);

    $this->actingAs($this->admin)->get('/departamentos')
        ->assertOk()->assertSee('Metal Mecánica')->assertSee('Gestión');

    $this->get('/departamentos?buscar=Metal')->assertSee('Metal Mecánica')->assertDontSee('Gestión');
    $this->get('/departamentos?buscar=Torre')->assertSee('Gestión')->assertDontSee('Metal Mecánica');
});

it('crea un departamento con o sin edificio', function () {
    $this->actingAs($this->admin)->post('/departamentos', ['nombre' => 'Química', 'edificio' => 'Edificio Q'])
        ->assertRedirect(route('departamentos.index'))->assertSessionHas('exito');

    $this->post('/departamentos', ['nombre' => 'Bioquímica', 'edificio' => ''])->assertRedirect();

    expect(Departamento::where('nombre', 'Química')->value('edificio'))->toBe('Edificio Q')
        ->and(Departamento::where('nombre', 'Bioquímica')->value('edificio'))->toBeNull();
});

it('rechaza un nombre vacío, demasiado largo o repetido', function () {
    Departamento::create(['nombre' => 'Química']);

    $this->actingAs($this->admin)->post('/departamentos', ['nombre' => ''])->assertSessionHasErrors('nombre');
    $this->post('/departamentos', ['nombre' => str_repeat('a', 101)])->assertSessionHasErrors('nombre');
    $this->post('/departamentos', ['nombre' => 'Química'])
        ->assertSessionHasErrors(['nombre' => 'Ya existe un departamento con ese nombre.']);

    expect(Departamento::where('nombre', 'Química')->count())->toBe(1);
});

it('edita un departamento y deja conservar su propio nombre', function () {
    $departamento = Departamento::create(['nombre' => 'Química', 'edificio' => 'A']);

    $this->actingAs($this->admin)->put("/departamentos/{$departamento->id_departamento}", ['nombre' => 'Química', 'edificio' => 'B'])
        ->assertRedirect(route('departamentos.index'));

    expect($departamento->fresh()->edificio)->toBe('B');

    $otro = Departamento::create(['nombre' => 'Física']);
    $this->put("/departamentos/{$otro->id_departamento}", ['nombre' => 'Química'])->assertSessionHasErrors('nombre');
});

it('muestra los formularios de alta y edición', function () {
    $departamento = Departamento::create(['nombre' => 'Química']);

    $this->actingAs($this->admin)->get('/departamentos/create')->assertOk();
    $this->get("/departamentos/{$departamento->id_departamento}/edit")->assertOk()->assertSee('Química');
});

it('elimina un departamento sin registros relacionados', function () {
    $departamento = Departamento::create(['nombre' => 'Química']);

    $this->actingAs($this->admin)->delete("/departamentos/{$departamento->id_departamento}")
        ->assertRedirect(route('departamentos.index'))->assertSessionHas('exito');

    expect(Departamento::find($departamento->id_departamento))->toBeNull();
});

it('no elimina un departamento con instructores', function () {
    $departamento = Escenarios::departamento('Sistemas');
    Escenarios::usuario('instructor', $departamento);

    $this->actingAs($this->admin)->delete("/departamentos/{$departamento->id_departamento}")
        ->assertRedirect()->assertSessionHas('error', fn ($m) => str_contains($m, '1 instructor(es)'));

    expect(Departamento::find($departamento->id_departamento))->not->toBeNull();
});

it('no elimina un departamento con coordinador asignado', function () {
    $departamento = Escenarios::departamento('Sistemas');
    Escenarios::usuario('coordinador', $departamento);

    $this->actingAs($this->admin)->delete("/departamentos/{$departamento->id_departamento}")
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'coordinador'));

    expect(Departamento::find($departamento->id_departamento))->not->toBeNull();
});
