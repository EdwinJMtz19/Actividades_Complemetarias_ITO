<?php

use App\Models\Actividad;
use App\Models\Semestre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo('2026-09-15 10:00:00');
    Escenarios::base();
    Escenarios::dias();
    $this->admin = Escenarios::usuario('administrador');
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->basicas = Escenarios::departamento('Ciencias Básicas');
    $this->carrera = Escenarios::carrera();
    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
});

/** Datos válidos de una actividad; `$extra` cambia o agrega campos. */
function datosDeActividad(int $departamento, int $carrera, array $extra = []): array
{
    return array_merge([
        'nombre' => 'Ajedrez',
        'descripcion' => 'Torneos internos.',
        'requisitos' => null,
        'nivel_actividad' => 'Básico',
        'creditos' => 1,
        'disponible' => '1',
        'id_departamento' => $departamento,
        'carreras' => [$carrera],
    ], $extra);
}

it('lista las actividades con buscador y filtros', function () {
    Escenarios::actividad(['nombre' => 'Ajedrez', 'id_semestre' => $this->semestre->id_semestre]);
    Escenarios::actividad([
        'nombre' => 'Fútbol',
        'id_departamento' => $this->basicas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);

    $this->actingAs($this->admin)->get('/actividades')
        ->assertOk()->assertSee('Ajedrez')->assertSee('Fútbol');

    $this->get('/actividades?buscar=Ajed')->assertSee('Ajedrez')->assertDontSee('Fútbol');
    $this->get('/actividades?departamento='.$this->basicas->id_departamento)
        ->assertSee('Fútbol')->assertDontSee('Ajedrez');
    $this->get('/actividades?semestre='.$this->semestre->id_semestre)
        ->assertSee('Ajedrez')->assertSee('Fútbol');
});

it('pagina el listado de diez en diez', function () {
    foreach (range(1, 11) as $numero) {
        Escenarios::actividad(['nombre' => "Actividad {$numero}"]);
    }

    $primera = $this->actingAs($this->admin)->get('/actividades')->assertOk();
    expect($primera->viewData('actividades')->count())->toBe(10)
        ->and($primera->viewData('actividades')->total())->toBe(11);

    $segunda = $this->get('/actividades?page=2')->assertOk();
    expect($segunda->viewData('actividades')->count())->toBe(1);
});

it('crea una actividad con sus carreras en el semestre activo', function () {
    $this->actingAs($this->admin)
        ->post('/actividades', datosDeActividad($this->sistemas->id_departamento, $this->carrera->id_carrera))
        ->assertRedirect(route('actividades.index'))->assertSessionHas('exito');

    $actividad = Actividad::where('nombre', 'Ajedrez')->firstOrFail();

    expect($actividad->id_semestre)->toBe($this->semestre->id_semestre)
        ->and($actividad->disponible)->toBeTrue()
        ->and($actividad->creditos)->toBe(1)
        ->and($actividad->carreras->pluck('id_carrera')->all())->toBe([$this->carrera->id_carrera]);
});

it('valida nombre, créditos y al menos una carrera', function () {
    $this->actingAs($this->admin)
        ->post('/actividades', datosDeActividad($this->sistemas->id_departamento, $this->carrera->id_carrera, [
            'nombre' => '',
            'creditos' => 3,
            'carreras' => [],
        ]))
        ->assertSessionHasErrors(['nombre', 'creditos', 'carreras']);

    expect(Actividad::count())->toBe(0);
});

it('no repite el nombre y limita su longitud', function () {
    Escenarios::actividad(['nombre' => 'Ajedrez']);

    $this->actingAs($this->admin)
        ->post('/actividades', datosDeActividad($this->sistemas->id_departamento, $this->carrera->id_carrera))
        ->assertSessionHasErrors(['nombre' => 'Ya existe una actividad con ese nombre.']);

    $this->post('/actividades', datosDeActividad($this->sistemas->id_departamento, $this->carrera->id_carrera, [
        'nombre' => str_repeat('a', 151),
    ]))->assertSessionHasErrors('nombre');

    expect(Actividad::where('nombre', 'Ajedrez')->count())->toBe(1);
});

it('edita una actividad, conserva su nombre y cambia sus carreras', function () {
    $actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
    ]);
    $otraCarrera = Escenarios::carrera('Arquitectura');

    $this->actingAs($this->admin)
        ->put("/actividades/{$actividad->id_actividad}", datosDeActividad(
            $this->sistemas->id_departamento,
            $otraCarrera->id_carrera,
            ['nombre' => 'Ajedrez', 'creditos' => 2],
        ))
        ->assertRedirect(route('actividades.index'))->assertSessionHas('exito');

    expect($actividad->fresh()->creditos)->toBe(2)
        ->and($actividad->fresh()->carreras->pluck('id_carrera')->all())->toBe([$otraCarrera->id_carrera]);

    $otra = Escenarios::actividad(['nombre' => 'Fútbol']);

    $this->put("/actividades/{$otra->id_actividad}", datosDeActividad(
        $this->sistemas->id_departamento,
        $this->carrera->id_carrera,
        ['nombre' => 'Ajedrez'],
    ))->assertSessionHasErrors('nombre');
});

it('permite retirar una actividad del catálogo sin borrarla', function () {
    $this->actingAs($this->admin)
        ->post('/actividades', datosDeActividad($this->sistemas->id_departamento, $this->carrera->id_carrera, [
            'disponible' => '0',
        ]))
        ->assertRedirect(route('actividades.index'));

    expect(Actividad::where('nombre', 'Ajedrez')->firstOrFail()->disponible)->toBeFalse();
});

it('mueve una actividad al semestre vigente al editarla', function () {
    $anterior = Escenarios::semestre(['año' => 2025, 'periodo' => 2, 'status' => Semestre::INACTIVO]);
    $actividad = Escenarios::actividad(['nombre' => 'Ajedrez', 'id_semestre' => $anterior->id_semestre]);

    $this->actingAs($this->admin)
        ->put("/actividades/{$actividad->id_actividad}", datosDeActividad(
            $this->sistemas->id_departamento,
            $this->carrera->id_carrera,
            ['nombre' => 'Ajedrez', 'asignar_semestre_vigente' => '1'],
        ))
        ->assertRedirect(route('actividades.index'))->assertSessionHas('exito');

    expect($actividad->fresh()->id_semestre)->toBe($this->semestre->id_semestre);
});

it('no elimina una actividad que tiene grupos', function () {
    $actividad = Escenarios::actividad(['nombre' => 'Ajedrez']);
    Escenarios::grupo([
        'id_actividad' => $actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ], [[1, '10:00', '12:00']]);

    $this->actingAs($this->admin)->delete("/actividades/{$actividad->id_actividad}")
        ->assertRedirect()->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, '1 grupo'));

    expect(Actividad::find($actividad->id_actividad))->not->toBeNull();
});

it('elimina una actividad sin grupos', function () {
    $actividad = Escenarios::actividad(['nombre' => 'Ajedrez']);

    $this->actingAs($this->admin)->delete("/actividades/{$actividad->id_actividad}")
        ->assertRedirect(route('actividades.index'))->assertSessionHas('exito');

    expect(Actividad::find($actividad->id_actividad))->toBeNull();
});

it('limita al coordinador a las actividades de su departamento', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    $propia = Escenarios::actividad(['nombre' => 'Propia', 'id_departamento' => $this->sistemas->id_departamento]);
    $ajena = Escenarios::actividad(['nombre' => 'Ajena', 'id_departamento' => $this->basicas->id_departamento]);

    $this->actingAs($coordinador)->get('/actividades')
        ->assertOk()->assertSee('Propia')->assertDontSee('Ajena');

    $this->get("/actividades/{$ajena->id_actividad}/edit")->assertForbidden();
    $this->put("/actividades/{$ajena->id_actividad}", datosDeActividad(
        $this->basicas->id_departamento,
        $this->carrera->id_carrera,
        ['nombre' => 'Robada'],
    ))->assertForbidden();
    $this->delete("/actividades/{$ajena->id_actividad}")->assertForbidden();

    expect($ajena->fresh()->nombre)->toBe('Ajena')
        ->and(Actividad::find($propia->id_actividad))->not->toBeNull();
});

it('ignora el departamento que un coordinador intente imponer al crear', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);

    $this->actingAs($coordinador)
        ->post('/actividades', datosDeActividad(
            $this->basicas->id_departamento,
            $this->carrera->id_carrera,
            ['nombre' => 'Intrusa'],
        ))
        ->assertRedirect(route('actividades.index'));

    expect(Actividad::where('nombre', 'Intrusa')->firstOrFail()->id_departamento)
        ->toBe($this->sistemas->id_departamento);
});

it('muestra los formularios de alta y edición', function () {
    $actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
    ]);

    $this->actingAs($this->admin)->get('/actividades/create')->assertOk()->assertSee('Nueva Actividad');
    $this->get("/actividades/{$actividad->id_actividad}/edit")->assertOk()->assertSee('Ajedrez');
});
