<?php

use App\Models\Semestre;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->admin = Escenarios::usuario('administrador');
    $this->travelTo('2026-09-15 10:00:00');
});

function datosSemestre(array $extra = []): array
{
    return $extra + [
        'año' => 2026, 'periodo' => 2,
        'fecha_inicio' => '2026-08-24', 'fecha_fin' => '2026-12-11',
        'fecha_inicio_inscripciones' => '2026-08-24', 'hora_inicio_inscripciones' => '08:00',
        'fecha_fin_inscripciones' => '2026-08-31', 'hora_fin_inscripciones' => '20:00',
        'status' => 'inactivo',
    ];
}

it('abre un periodo nuevo y lo deja activo', function () {
    $this->actingAs($this->admin)->post('/semestres', datosSemestre(['status' => 'activo']))
        ->assertRedirect(route('semestres.index'))->assertSessionHas('exito');

    $semestre = Semestre::activo();
    expect($semestre->año)->toBe(2026)
        ->and($semestre->etiqueta)->toBe('Agosto–Diciembre 2026')
        ->and($semestre->hora_inicio_corta)->toBe('08:00')
        ->and($semestre->fecha_fin->toDateString())->toBe('2026-12-11');
});

it('exige todos los campos', function () {
    $this->actingAs($this->admin)->post('/semestres', [])->assertSessionHasErrors([
        'año', 'periodo', 'fecha_inicio', 'fecha_fin', 'fecha_inicio_inscripciones',
        'hora_inicio_inscripciones', 'fecha_fin_inscripciones', 'hora_fin_inscripciones', 'status',
    ]);
});

it('valida la duración del semestre: de 3 a 5 meses', function (string $inicio, string $fin, bool $valido) {
    $ventanaInicio = $inicio;
    $ventanaFin = Carbon::parse($inicio)->addDays(7)->toDateString();

    $respuesta = $this->actingAs($this->admin)->post('/semestres', datosSemestre([
        'periodo' => str_starts_with($inicio, '2026-01') ? 1 : 2,
        'fecha_inicio' => $inicio, 'fecha_fin' => $fin,
        'fecha_inicio_inscripciones' => $ventanaInicio, 'fecha_fin_inscripciones' => $ventanaFin,
    ]));

    $valido ? $respuesta->assertSessionDoesntHaveErrors() : $respuesta->assertSessionHasErrors('fecha_fin');
    expect(Semestre::count())->toBe($valido ? 1 : 0);
})->with([
    'menos de 3 meses' => ['2026-08-24', '2026-11-23', false],
    '3 meses exactos' => ['2026-08-24', '2026-11-24', true],
    '4 meses' => ['2026-08-24', '2026-12-24', true],
    '5 meses y 29 días' => ['2026-01-01', '2026-06-30', true],
    '6 meses exactos' => ['2026-01-01', '2026-07-01', false],
]);

it('rechaza un fin de semestre anterior al inicio', function () {
    $this->actingAs($this->admin)->post('/semestres', datosSemestre(['fecha_fin' => '2026-08-01']))
        ->assertSessionHasErrors('fecha_fin');
});

it('valida la ventana de inscripción: de 5 a 10 días', function (int $dias, bool $valido) {
    $fin = Carbon::parse('2026-08-24')->addDays($dias)->toDateString();

    $respuesta = $this->actingAs($this->admin)->post('/semestres', datosSemestre(['fecha_fin_inscripciones' => $fin]));

    $valido
        ? $respuesta->assertSessionDoesntHaveErrors()
        : $respuesta->assertSessionHasErrors('fecha_fin_inscripciones');
    expect(Semestre::count())->toBe($valido ? 1 : 0);
})->with([
    '4 días' => [4, false],
    '5 días' => [5, true],
    '10 días' => [10, true],
    '11 días' => [11, false],
]);

it('exige que las inscripciones queden dentro del semestre', function (string $inicio, string $fin) {
    $this->actingAs($this->admin)->post('/semestres', datosSemestre([
        'fecha_inicio_inscripciones' => $inicio, 'fecha_fin_inscripciones' => $fin,
    ]))->assertSessionHasErrors('fecha_inicio_inscripciones');

    expect(Semestre::count())->toBe(0);
})->with([
    'empiezan antes del semestre' => ['2026-08-18', '2026-08-25'],
    'terminan después del semestre' => ['2026-12-05', '2026-12-14'],
]);

it('solo permite un periodo activo a la vez, pero sí uno inactivo', function () {
    Escenarios::semestre(['status' => 'activo']);

    $this->actingAs($this->admin)->post('/semestres', datosSemestre(['año' => 2027, 'periodo' => 1, 'fecha_inicio' => '2027-02-01', 'fecha_fin' => '2027-06-10',
        'fecha_inicio_inscripciones' => '2027-02-01', 'fecha_fin_inscripciones' => '2027-02-08', 'status' => 'activo']))
        ->assertSessionHasErrors('status');

    $this->post('/semestres', datosSemestre(['año' => 2027, 'periodo' => 1, 'fecha_inicio' => '2027-02-01', 'fecha_fin' => '2027-06-10',
        'fecha_inicio_inscripciones' => '2027-02-01', 'fecha_fin_inscripciones' => '2027-02-08', 'status' => 'inactivo']))
        ->assertSessionDoesntHaveErrors();

    expect(Semestre::count())->toBe(2);
});

it('no repite el mismo año y periodo', function () {
    Escenarios::semestre();

    $this->actingAs($this->admin)->post('/semestres', datosSemestre())
        ->assertSessionHasErrors(['periodo' => 'Ya existe un semestre registrado para ese año y periodo.']);

    expect(Semestre::count())->toBe(1);
});

it('no activa un periodo cuya fecha de fin ya pasó', function () {
    $datos = datosSemestre([
        'periodo' => 1, 'fecha_inicio' => '2026-02-02', 'fecha_fin' => '2026-06-12',
        'fecha_inicio_inscripciones' => '2026-02-02', 'fecha_fin_inscripciones' => '2026-02-09',
    ]);

    $this->actingAs($this->admin)->post('/semestres', ['status' => 'activo'] + $datos)->assertSessionHasErrors('status');
    $this->post('/semestres', ['status' => 'inactivo'] + $datos)->assertSessionDoesntHaveErrors();
});

it('cierra automáticamente los periodos vencidos al abrir el listado', function () {
    $vencido = Escenarios::semestre(['status' => 'activo', 'fecha_fin' => '2026-09-14']);

    $this->actingAs($this->admin)->get('/semestres')->assertOk()->assertSee('No hay ningún periodo activo');

    expect($vencido->fresh()->status)->toBe('inactivo');
});

it('mantiene activo el periodo que termina hoy', function () {
    $hoy = Escenarios::semestre(['status' => 'activo', 'fecha_fin' => '2026-09-15']);

    $this->actingAs($this->admin)->get('/semestres')->assertOk()->assertSee('Periodo Actual Activo');

    expect($hoy->fresh()->status)->toBe('activo');
});

it('el comando programado cierra solo los semestres vencidos', function () {
    $vencido = Escenarios::semestre(['status' => 'activo', 'fecha_fin' => '2026-09-14']);

    $this->artisan('semestres:cerrar-vencidos')->expectsOutputToContain('Semestres cerrados: 1.')->assertSuccessful();
    expect($vencido->fresh()->status)->toBe('inactivo');

    $this->artisan('semestres:cerrar-vencidos')->expectsOutputToContain('No hay semestres vencidos.')->assertSuccessful();
});

it('programa el cierre automático todos los días', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('semestres:cerrar-vencidos')->assertSuccessful();
});

it('un periodo vencido no impide abrir uno nuevo', function () {
    Escenarios::semestre(['periodo' => 1, 'año' => 2026, 'fecha_inicio' => '2026-02-02', 'fecha_fin' => '2026-06-12',
        'fecha_inicio_inscripciones' => '2026-02-02', 'fecha_fin_inscripciones' => '2026-02-09', 'status' => 'activo']);

    $this->actingAs($this->admin)->get('/semestres/create')->assertOk();
    $this->post('/semestres', datosSemestre(['status' => 'activo']))->assertSessionDoesntHaveErrors();

    expect(Semestre::activo()->periodo)->toBe(2);
});

it('no abre el formulario de alta mientras haya un periodo activo', function () {
    Escenarios::semestre(['status' => 'activo']);

    $this->actingAs($this->admin)->get('/semestres/create')
        ->assertRedirect(route('semestres.index'))->assertSessionHas('error');
});

it('edita el periodo activo y permite finalizarlo', function () {
    $semestre = Escenarios::semestre(['status' => 'activo']);

    $this->actingAs($this->admin)->get("/semestres/{$semestre->id_semestre}/edit")->assertOk()->assertSee('Actualizar Semestre');

    $this->put("/semestres/{$semestre->id_semestre}", datosSemestre(['fecha_fin' => '2026-12-18', 'status' => 'inactivo']))
        ->assertRedirect(route('semestres.index'))->assertSessionHas('exito');

    expect($semestre->fresh())->status->toBe('inactivo')
        ->and($semestre->fresh()->fecha_fin->toDateString())->toBe('2026-12-18');
});

it('solo permite reactivar el periodo inactivo más reciente', function () {
    $anterior = Escenarios::semestre(['año' => 2025, 'periodo' => 2, 'fecha_inicio' => '2025-08-25', 'fecha_fin' => '2025-12-12',
        'fecha_inicio_inscripciones' => '2025-08-25', 'fecha_fin_inscripciones' => '2025-09-01']);
    $reciente = Escenarios::semestre(['año' => 2026, 'periodo' => 1, 'fecha_inicio' => '2026-02-02', 'fecha_fin' => '2026-06-12',
        'fecha_inicio_inscripciones' => '2026-02-02', 'fecha_fin_inscripciones' => '2026-02-09']);

    $this->actingAs($this->admin)->get("/semestres/{$anterior->id_semestre}/edit")
        ->assertRedirect(route('semestres.index'))->assertSessionHas('error');
    $this->put("/semestres/{$anterior->id_semestre}", datosSemestre(['año' => 2025, 'status' => 'activo']))
        ->assertRedirect(route('semestres.index'))->assertSessionHas('error');
    expect($anterior->fresh()->status)->toBe('inactivo');

    $this->get("/semestres/{$reciente->id_semestre}/edit")->assertOk()->assertSee('Reactivación del período más reciente');
    $this->put("/semestres/{$reciente->id_semestre}", datosSemestre(['periodo' => 1, 'status' => 'activo']))
        ->assertSessionDoesntHaveErrors();

    expect($reciente->fresh()->status)->toBe('activo');
});

it('no deja editar periodos inactivos mientras haya uno activo', function () {
    Escenarios::semestre(['status' => 'activo']);
    $inactivo = Escenarios::semestre(['año' => 2026, 'periodo' => 1, 'fecha_inicio' => '2026-02-02', 'fecha_fin' => '2026-06-12',
        'fecha_inicio_inscripciones' => '2026-02-02', 'fecha_fin_inscripciones' => '2026-02-09']);

    $this->actingAs($this->admin)->get("/semestres/{$inactivo->id_semestre}/edit")->assertSessionHas('error');
    $this->put("/semestres/{$inactivo->id_semestre}", datosSemestre([
        'periodo' => 1, 'status' => 'inactivo', 'fecha_inicio' => '2026-02-02', 'fecha_fin' => '2026-06-30',
        'fecha_inicio_inscripciones' => '2026-02-02', 'fecha_fin_inscripciones' => '2026-02-09',
    ]))
        ->assertSessionHas('error');

    expect($inactivo->fresh()->fecha_fin->toDateString())->toBe('2026-06-12');
});

it('al editar permite conservar su propio año y periodo', function () {
    $semestre = Escenarios::semestre(['status' => 'activo']);

    $this->actingAs($this->admin)->put("/semestres/{$semestre->id_semestre}", datosSemestre(['status' => 'activo']))
        ->assertSessionDoesntHaveErrors()->assertRedirect(route('semestres.index'));
});

it('muestra el detalle del periodo', function () {
    $semestre = Escenarios::semestre();

    $this->actingAs($this->admin)->get("/semestres/{$semestre->id_semestre}")
        ->assertOk()->assertSee('Agosto–Diciembre 2026')->assertSee('24/08/2026')->assertSee('11/12/2026');
});

it('muestra el estado de las inscripciones del periodo activo', function () {
    Escenarios::semestre(['status' => 'activo']);

    $this->travelTo('2026-08-25 10:00:00');
    $this->actingAs($this->admin)->get('/semestres')->assertSee('Abiertas');

    $this->travelTo('2026-09-01 10:00:00');
    $this->get('/semestres')->assertSee('Cerradas');
});

it('abre y cierra la ventana de inscripción por fecha y hora', function (string $momento, bool $abierta) {
    $semestre = Escenarios::semestre(['status' => 'activo']);

    expect($semestre->inscripcionesAbiertas(Carbon::parse($momento)))->toBe($abierta);
})->with([
    'un minuto antes de abrir' => ['2026-08-24 07:59:00', false],
    'a la hora de apertura' => ['2026-08-24 08:00:00', true],
    'a mitad de la ventana' => ['2026-08-28 03:00:00', true],
    'a la hora de cierre' => ['2026-08-31 20:00:00', true],
    'un minuto después de cerrar' => ['2026-08-31 20:01:00', false],
]);

it('no abre inscripciones en un periodo inactivo', function () {
    $semestre = Escenarios::semestre();

    expect($semestre->inscripcionesAbiertas(Carbon::parse('2026-08-28 10:00:00')))->toBeFalse();
});

it('no permite eliminar semestres', function () {
    $semestre = Escenarios::semestre();

    $this->actingAs($this->admin)->delete("/semestres/{$semestre->id_semestre}")->assertStatus(405);

    expect(Semestre::count())->toBe(1);
});
