<?php

use App\Models\Actividad;
use App\Models\Inscripcion;
use App\Models\Semestre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo('2026-08-25 10:00:00');
    Escenarios::base();
    Escenarios::dias();
    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->alumno = Escenarios::usuario('alumno');
    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ], [[1, '10:00', '12:00']]);
});

it('muestra el catálogo de grupos disponibles al alumno', function () {
    $respuesta = $this->actingAs($this->alumno)->get('/inscripciones')->assertOk();

    expect($respuesta->viewData('abierta'))->toBeTrue()
        ->and($respuesta->viewData('grupos')->pluck('id_grupo')->all())->toBe([$this->grupo->id_grupo]);
});

it('inscribe al alumno en un grupo abierto con cupo', function () {
    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$this->grupo->id_grupo}")
        ->assertRedirect(route('inscripciones.index'))
        ->assertSessionHas('exito');

    $inscripcion = Inscripcion::firstOrFail();

    expect($inscripcion->id_alumno)->toBe($this->alumno->id)
        ->and($inscripcion->id_grupo)->toBe($this->grupo->id_grupo)
        ->and($inscripcion->estatus)->toBe('inscrito')
        ->and($inscripcion->fecha_inscripcion->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(1);
});

it('muestra la inscripción activa del alumno', function () {
    $this->actingAs($this->alumno)->post("/inscripciones/{$this->grupo->id_grupo}");

    $respuesta = $this->actingAs($this->alumno)->get('/inscripciones')->assertOk();

    expect($respuesta->viewData('inscripcion'))->not->toBeNull()
        ->and($respuesta->viewData('inscripcion')->grupo->id_grupo)->toBe($this->grupo->id_grupo);
});

it('no inscribe fuera de la ventana de inscripción', function () {
    $this->travelTo('2026-09-15 10:00:00');

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$this->grupo->id_grupo}")
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'ventana'));

    expect(Inscripcion::count())->toBe(0)
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(0);
});

it('no inscribe si el grupo ya no tiene cupo', function () {
    $this->grupo->cupo_ocupado = 1;
    $this->grupo->cupo_maximo = 1;
    $this->grupo->save();

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$this->grupo->id_grupo}")
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'cupo'));

    expect(Inscripcion::count())->toBe(0);
});

it('no permite dos inscripciones activas', function () {
    $otro = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'grupo' => 'B',
    ]);

    Inscripcion::create([
        'id_alumno' => $this->alumno->id,
        'id_grupo' => $otro->id_grupo,
        'fecha_inscripcion' => now(),
        'estatus' => 'inscrito',
    ]);

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$this->grupo->id_grupo}")
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'inscripción activa'));

    expect(Inscripcion::count())->toBe(1)
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(0);
});

it('no inscribe en una actividad de otra carrera', function () {
    $otraCarrera = Escenarios::carrera('Licenciatura en Administración');
    $ajena = Actividad::create([
        'nombre' => 'Basquetbol',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
        'creditos' => 1,
        'disponible' => true,
    ]);
    $ajena->carreras()->attach($otraCarrera->id_carrera);

    $grupoAjeno = Escenarios::grupo([
        'id_actividad' => $ajena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ]);

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$grupoAjeno->id_grupo}")
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'carrera'));

    expect(Inscripcion::count())->toBe(0);
});

it('no inscribe en un grupo que no está abierto', function () {
    $this->grupo->update(['estatus' => 'cerrada']);

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$this->grupo->id_grupo}")
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'abierto'));

    expect(Inscripcion::count())->toBe(0);
});

it('no inscribe en un grupo de otro periodo', function () {
    $otroSemestre = Escenarios::semestre(['año' => 2027, 'periodo' => 1]);
    $grupoOtro = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $otroSemestre->id_semestre,
        'grupo' => 'C',
    ]);

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$grupoOtro->id_grupo}")
        ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'periodo'));

    expect(Inscripcion::count())->toBe(0);
});

it('oculta del catálogo los grupos llenos, cerrados o de otra carrera', function () {
    $this->grupo->cupo_ocupado = 20;
    $this->grupo->save();

    $cerrado = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'grupo' => 'B',
        'estatus' => 'cerrada',
    ]);

    $respuesta = $this->actingAs($this->alumno)->get('/inscripciones')->assertOk();

    expect($respuesta->viewData('grupos'))->toBeEmpty()
        ->and($cerrado->id_grupo)->not->toBeNull();
});

it('no permite que un instructor consulte ni se inscriba', function () {
    $instructor = Escenarios::usuario('instructor', $this->sistemas);

    $this->actingAs($instructor)->get('/inscripciones')->assertForbidden();
    $this->actingAs($instructor)->post("/inscripciones/{$this->grupo->id_grupo}")->assertForbidden();
});
