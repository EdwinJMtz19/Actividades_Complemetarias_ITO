<?php

use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->admin = Escenarios::usuario('administrador');
});

it('lista las ubicaciones con su capacidad y busca por nombre', function () {
    Ubicacion::create(['espacio' => 'Auditorio', 'capacidad' => 200]);
    Ubicacion::create(['espacio' => 'Cancha', 'capacidad' => 60]);

    $this->actingAs($this->admin)->get('/ubicaciones')
        ->assertOk()->assertSee('Auditorio')->assertSee('200 personas')->assertSee('Cancha');

    $this->get('/ubicaciones?buscar=Cancha')->assertSee('Cancha')->assertDontSee('Auditorio');
});

it('crea una ubicación', function () {
    $this->actingAs($this->admin)->post('/ubicaciones', ['espacio' => 'Aula A-101', 'capacidad' => 30])
        ->assertRedirect(route('ubicaciones.index'))->assertSessionHas('exito');

    expect(Ubicacion::where('espacio', 'Aula A-101')->value('capacidad'))->toBe(30);
});

it('valida el nombre y la capacidad', function (array $datos, array|string $errores) {
    Ubicacion::create(['espacio' => 'Auditorio', 'capacidad' => 200]);

    $this->actingAs($this->admin)->post('/ubicaciones', $datos)->assertSessionHasErrors($errores);

    expect(Ubicacion::count())->toBe(1);
})->with([
    'sin datos' => [[], ['espacio', 'capacidad']],
    'nombre repetido' => [['espacio' => 'Auditorio', 'capacidad' => 10], 'espacio'],
    'nombre muy largo' => [['espacio' => str_repeat('a', 101), 'capacidad' => 10], 'espacio'],
    'capacidad cero' => [['espacio' => 'Aula', 'capacidad' => 0], 'capacidad'],
    'capacidad enorme' => [['espacio' => 'Aula', 'capacidad' => 10000], 'capacidad'],
    'capacidad con decimales' => [['espacio' => 'Aula', 'capacidad' => '12.5'], 'capacidad'],
    'capacidad no numérica' => [['espacio' => 'Aula', 'capacidad' => 'muchas'], 'capacidad'],
]);

it('edita una ubicación y deja conservar su propio nombre', function () {
    $ubicacion = Ubicacion::create(['espacio' => 'Auditorio', 'capacidad' => 200]);

    $this->actingAs($this->admin)->put("/ubicaciones/{$ubicacion->id_ubicacion}", ['espacio' => 'Auditorio', 'capacidad' => 250])
        ->assertRedirect(route('ubicaciones.index'));

    expect($ubicacion->fresh()->capacidad)->toBe(250);

    $otra = Ubicacion::create(['espacio' => 'Cancha', 'capacidad' => 60]);
    $this->put("/ubicaciones/{$otra->id_ubicacion}", ['espacio' => 'Auditorio', 'capacidad' => 60])->assertSessionHasErrors('espacio');
});

it('muestra los formularios de alta y edición', function () {
    $ubicacion = Ubicacion::create(['espacio' => 'Auditorio', 'capacidad' => 200]);

    $this->actingAs($this->admin)->get('/ubicaciones/create')->assertOk();
    $this->get("/ubicaciones/{$ubicacion->id_ubicacion}/edit")->assertOk()->assertSee('Auditorio');
});

it('elimina una ubicación', function () {
    $ubicacion = Ubicacion::create(['espacio' => 'Auditorio', 'capacidad' => 200]);

    $this->actingAs($this->admin)->delete("/ubicaciones/{$ubicacion->id_ubicacion}")
        ->assertRedirect(route('ubicaciones.index'))->assertSessionHas('exito');

    expect(Ubicacion::find($ubicacion->id_ubicacion))->toBeNull();
});
