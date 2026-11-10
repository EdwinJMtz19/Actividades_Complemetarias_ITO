<?php

use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Inscribe por la ruta real (descuenta cupo) y devuelve la inscripción activa. */
function inscribirParaBaja(User $alumno, Grupo $grupo): Inscripcion
{
    test()->actingAs($alumno)->post("/inscripciones/{$grupo->id_grupo}");

    return Inscripcion::query()
        ->where('id_alumno', $alumno->id)
        ->where('id_grupo', $grupo->id_grupo)
        ->latest('id_inscripcion')
        ->firstOrFail();
}

/** Crea una inscripción directa (sin pasar por la ventana) para probar alcances y filtros. */
function crearInscripcionParaBaja(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
{
    $grupo->increment('cupo_ocupado');

    return Inscripcion::create([
        'id_alumno' => $alumno->id,
        'id_grupo' => $grupo->id_grupo,
        'fecha_inscripcion' => now(),
        'estatus' => $estatus,
    ]);
}

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo('2026-08-25 10:00:00');
    Mail::fake();
    Escenarios::base();
    Escenarios::dias();
    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->otro = Escenarios::departamento('Administración');
    $this->alumno = Escenarios::usuario('alumno');
    $this->coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    $this->admin = Escenarios::usuario('administrador');
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

it('muestra al coordinador solo las inscripciones de su departamento', function () {
    $propia = inscribirParaBaja($this->alumno, $this->grupo);

    $ajena = Escenarios::actividad([
        'nombre' => 'Basquetbol',
        'id_departamento' => $this->otro->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $grupoAjeno = Escenarios::grupo([
        'id_actividad' => $ajena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $otroAlumno = Escenarios::usuario('alumno');
    crearInscripcionParaBaja($otroAlumno, $grupoAjeno);

    $respuesta = $this->actingAs($this->coordinador)->get('/inscripciones')->assertOk();

    expect($respuesta->viewData('inscripciones')->pluck('id_inscripcion')->all())
        ->toBe([$propia->id_inscripcion]);

    $respuesta->assertSee('Ajedrez')->assertDontSee('Basquetbol');
});

it('da de baja, conserva la fila y libera el cupo', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);

    expect($this->grupo->fresh()->cupo_ocupado)->toBe(1);

    $this->from('/inscripciones')
        ->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja")
        ->assertRedirect('/inscripciones')
        ->assertSessionHas('exito');

    expect($inscripcion->fresh()->estatus)->toBe('dado_de_baja')
        ->and(Inscripcion::count())->toBe(1)
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(0);

    $this->assertDatabaseHas('inscripcion', [
        'id_inscripcion' => $inscripcion->id_inscripcion,
        'estatus' => 'dado_de_baja',
    ]);
});

it('permite al alumno volver a inscribirse después de la baja', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);

    $this->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja");

    $this->actingAs($this->alumno)
        ->post("/inscripciones/{$this->grupo->id_grupo}")
        ->assertRedirect(route('inscripciones.index'))
        ->assertSessionHas('exito');

    $activas = Inscripcion::query()
        ->where('id_alumno', $this->alumno->id)
        ->activas()
        ->get();

    expect($activas)->toHaveCount(1)
        ->and($activas->first()->id_grupo)->toBe($this->grupo->id_grupo)
        ->and(Inscripcion::count())->toBe(2)
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(1);
});

it('muestra el historial al alumno y ya no su inscripción activa', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);

    $this->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja");

    $respuesta = $this->actingAs($this->alumno)->get('/inscripciones')->assertOk();

    expect($respuesta->viewData('inscripcion'))->toBeNull()
        ->and($respuesta->viewData('historial'))->toHaveCount(1)
        ->and($respuesta->viewData('historial')->first()->estatus)->toBe('dado_de_baja');

    $respuesta->assertSee('Historial')
        ->assertSee('Dado de baja')
        ->assertSee('Ajedrez');
});

it('impide al coordinador dar de baja inscripciones de otro departamento', function () {
    $ajena = Escenarios::actividad([
        'nombre' => 'Basquetbol',
        'id_departamento' => $this->otro->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $grupoAjeno = Escenarios::grupo([
        'id_actividad' => $ajena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $otroAlumno = Escenarios::usuario('alumno');
    $inscripcionAjena = crearInscripcionParaBaja($otroAlumno, $grupoAjeno);

    $this->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcionAjena->id_inscripcion}/baja")
        ->assertForbidden();

    expect($inscripcionAjena->fresh()->estatus)->toBe('inscrito')
        ->and($grupoAjeno->fresh()->cupo_ocupado)->toBe(1);
});

it('impide al instructor dar de baja', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);
    $instructor = Escenarios::usuario('instructor', $this->sistemas);

    $this->actingAs($instructor)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja")
        ->assertForbidden();

    expect($inscripcion->fresh()->estatus)->toBe('inscrito')
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(1);
});

it('permite al alumno cancelar su propia inscripción', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);

    $this->from('/inscripciones')
        ->actingAs($this->alumno)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja")
        ->assertRedirect('/inscripciones')
        ->assertSessionHas('exito', 'Cancelaste tu inscripción a «Ajedrez».');

    expect($inscripcion->fresh()->estatus)->toBe('dado_de_baja')
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(0);
});

it('impide al alumno cancelar la inscripción de otro', function () {
    $inscripcionAjena = crearInscripcionParaBaja(Escenarios::usuario('alumno'), $this->grupo);

    $this->actingAs($this->alumno)
        ->patch("/inscripciones/{$inscripcionAjena->id_inscripcion}/baja")
        ->assertForbidden();

    expect($inscripcionAjena->fresh()->estatus)->toBe('inscrito')
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(1);
});

it('muestra al alumno el botón para cancelar su inscripción', function () {
    inscribirParaBaja($this->alumno, $this->grupo);

    $this->actingAs($this->alumno)->get('/inscripciones')
        ->assertOk()
        ->assertSee('Cancelar mi inscripción')
        ->assertDontSee('Dar de baja');
});

it('no da de baja dos veces ni descuenta dos veces el cupo', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);

    $this->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja")
        ->assertSessionHas('exito');

    $this->from('/inscripciones')
        ->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja")
        ->assertSessionHas('error');

    expect($inscripcion->fresh()->estatus)->toBe('dado_de_baja')
        ->and($this->grupo->fresh()->cupo_ocupado)->toBe(0);
});

it('permite al administrador dar de baja en cualquier departamento', function () {
    $ajena = Escenarios::actividad([
        'nombre' => 'Basquetbol',
        'id_departamento' => $this->otro->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $grupoAjeno = Escenarios::grupo([
        'id_actividad' => $ajena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $otroAlumno = Escenarios::usuario('alumno');
    $inscripcionAjena = crearInscripcionParaBaja($otroAlumno, $grupoAjeno);

    $this->actingAs($this->admin)
        ->patch("/inscripciones/{$inscripcionAjena->id_inscripcion}/baja")
        ->assertSessionHas('exito');

    expect($inscripcionAjena->fresh()->estatus)->toBe('dado_de_baja')
        ->and($grupoAjeno->fresh()->cupo_ocupado)->toBe(0);
});

it('filtra el listado por estatus, actividad y alumno', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);

    $this->actingAs($this->coordinador)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja");

    $otraActividad = Escenarios::actividad([
        'nombre' => 'Robótica',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $otroGrupo = Escenarios::grupo([
        'id_actividad' => $otraActividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $otroAlumno = Escenarios::usuario('alumno');
    $activa = crearInscripcionParaBaja($otroAlumno, $otroGrupo);

    $porEstatus = $this->actingAs($this->coordinador)
        ->get('/inscripciones?estatus=dado_de_baja')
        ->assertOk();

    expect($porEstatus->viewData('inscripciones')->pluck('id_inscripcion')->all())
        ->toBe([$inscripcion->id_inscripcion]);

    $porActividad = $this->actingAs($this->coordinador)
        ->get('/inscripciones?actividad='.$otraActividad->id_actividad)
        ->assertOk();

    expect($porActividad->viewData('inscripciones')->pluck('id_inscripcion')->all())
        ->toBe([$activa->id_inscripcion]);

    $porAlumno = $this->actingAs($this->coordinador)
        ->get('/inscripciones?buscar='.$otroAlumno->alumno->num_control)
        ->assertOk();

    expect($porAlumno->viewData('inscripciones')->pluck('id_inscripcion')->all())
        ->toBe([$activa->id_inscripcion]);
});

it('no muestra la gestión al alumno', function () {
    $this->actingAs($this->alumno)
        ->get('/inscripciones')
        ->assertOk()
        ->assertDontSee('Dar de baja')
        ->assertDontSee('Buscar alumno');
});

it('bloquea al coordinador sin departamento', function () {
    $inscripcion = inscribirParaBaja($this->alumno, $this->grupo);
    $coordinadorSin = Escenarios::usuario('coordinador');

    $this->actingAs($coordinadorSin)->get('/inscripciones')->assertForbidden();
    $this->actingAs($coordinadorSin)
        ->patch("/inscripciones/{$inscripcion->id_inscripcion}/baja")
        ->assertForbidden();

    expect($inscripcion->fresh()->estatus)->toBe('inscrito');
});
