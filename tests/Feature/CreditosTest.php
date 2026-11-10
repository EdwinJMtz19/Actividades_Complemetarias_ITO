<?php

use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa (sin pasar por la ventana) y descuenta el cupo. */
function crearInscripcionParaCreditos(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
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
    $this->admin = Escenarios::usuario('administrador');
    $this->instructor = Escenarios::usuario('instructor', $this->sistemas);
    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'creditos' => 2,
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
    ], [[1, '10:00', '12:00']]);
    $this->inscripcion = crearInscripcionParaCreditos($this->alumno, $this->grupo);
});

it('suma los créditos de la actividad al aprobar con excelente', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente'])
        ->assertSessionHas('exito');

    expect($this->inscripcion->fresh()->estatus)->toBe('aprobado')
        ->and($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(2);
});

it('suma los créditos de la actividad al aprobar con bueno', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'bueno'])
        ->assertSessionHas('exito');

    expect($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(2);
});

it('no suma créditos cuando el desempeño es malo', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'malo'])
        ->assertSessionHas('exito');

    expect($this->inscripcion->fresh()->estatus)->toBe('reprobado')
        ->and($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(0);
});

it('no duplica los créditos al cambiar de excelente a bueno', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente']);

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'bueno'])
        ->assertSessionHas('exito');

    expect($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(2);
});

it('descuenta los créditos al cambiar de aprobado a reprobado', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente']);

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'malo'])
        ->assertSessionHas('exito');

    expect($this->inscripcion->fresh()->estatus)->toBe('reprobado')
        ->and($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(0);
});

it('vuelve a sumar los créditos al reaprobar después de reprobar', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente']);
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'malo']);
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'bueno']);

    expect($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(2);
});

it('no suma créditos si la inscripción ya estaba aprobada', function () {
    $alumno = Escenarios::usuario('alumno');
    $alumno->alumno->update(['creditos_acumulados' => 2]);
    $inscripcion = crearInscripcionParaCreditos($alumno, $this->grupo, 'aprobado');

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $inscripcion), ['desempenio' => 'excelente'])
        ->assertSessionHas('exito');

    expect($alumno->alumno->fresh()->creditos_acumulados)->toBe(2);
});

it('no deja los créditos en negativo si el acumulado no alcanza', function () {
    $alumno = Escenarios::usuario('alumno');
    $inscripcion = crearInscripcionParaCreditos($alumno, $this->grupo, 'aprobado');

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $inscripcion), ['desempenio' => 'malo'])
        ->assertSessionHas('exito');

    expect($alumno->alumno->fresh()->creditos_acumulados)->toBe(0);
});

it('no modifica créditos si el administrador intenta calificar una inscripción dada de baja', function () {
    $alumno = Escenarios::usuario('alumno');
    $inscripcion = crearInscripcionParaCreditos($alumno, $this->grupo, 'dado_de_baja');

    $this->actingAs($this->admin)
        ->put(route('calificaciones.update', $inscripcion), ['desempenio' => 'excelente'])
        ->assertForbidden();

    expect($inscripcion->fresh()->estatus)->toBe('dado_de_baja')
        ->and($inscripcion->fresh()->calificacion)->toBeNull()
        ->and($alumno->alumno->fresh()->creditos_acumulados)->toBe(0);
});

it('suma la cantidad de créditos configurada en la actividad', function () {
    $this->actividad->update(['creditos' => 1]);

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente'])
        ->assertSessionHas('exito');

    expect($this->alumno->alumno->fresh()->creditos_acumulados)->toBe(1);
});

it('permite al administrador calificar y actualizar créditos en otro departamento', function () {
    $otroInstructor = Escenarios::usuario('instructor', $this->otro);
    $ajena = Escenarios::actividad([
        'nombre' => 'Basquetbol',
        'creditos' => 2,
        'id_departamento' => $this->otro->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $grupoAjeno = Escenarios::grupo([
        'id_actividad' => $ajena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $otroInstructor->id,
    ]);
    $alumnoAjeno = Escenarios::usuario('alumno');
    $inscripcionAjena = crearInscripcionParaCreditos($alumnoAjeno, $grupoAjeno);

    $this->actingAs($this->admin)
        ->put(route('calificaciones.update', $inscripcionAjena), ['desempenio' => 'excelente'])
        ->assertSessionHas('exito');

    expect($inscripcionAjena->fresh()->estatus)->toBe('aprobado')
        ->and($alumnoAjeno->alumno->fresh()->creditos_acumulados)->toBe(2);
});
