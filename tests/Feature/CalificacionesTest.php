<?php

use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa (sin pasar por la ventana) y descuenta el cupo. */
function crearInscripcionParaCalificacion(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
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
    $this->instructor = Escenarios::usuario('instructor', $this->sistemas);
    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
    ], [[1, '10:00', '12:00']]);
    $this->inscripcion = crearInscripcionParaCalificacion($this->alumno, $this->grupo);
});

it('muestra al instructor los alumnos inscritos en su grupo', function () {
    $this->actingAs($this->instructor)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertSee('Alumnos inscritos')
        ->assertSee($this->alumno->nombre_completo)
        ->assertSee($this->alumno->alumno->num_control)
        ->assertSee('Calificar');
});

it('no muestra la lista de inscritos al alumno', function () {
    $this->actingAs($this->alumno)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertDontSee('Alumnos inscritos');
});

it('registra una calificación excelente y deja la inscripción aprobada', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), [
            'desempenio' => 'excelente',
            'observaciones' => 'Participó en todas las sesiones.',
        ])
        ->assertRedirect(route('grupos.show', $this->grupo))
        ->assertSessionHas('exito');

    expect($this->inscripcion->fresh()->estatus)->toBe('aprobado');

    $this->assertDatabaseHas('calificacion', [
        'id_inscripcion' => $this->inscripcion->id_inscripcion,
        'desempenio' => 'excelente',
        'observaciones' => 'Participó en todas las sesiones.',
    ]);
});

it('registra una calificación mala y deja la inscripción reprobada', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'malo'])
        ->assertSessionHas('exito');

    expect($this->inscripcion->fresh()->estatus)->toBe('reprobado')
        ->and($this->inscripcion->fresh()->calificacion->desempenio)->toBe('malo');
});

it('modifica una calificación existente sin duplicar la fila', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente']);

    $this->actingAs($this->instructor)
        ->get(route('calificaciones.edit', $this->inscripcion))
        ->assertOk()
        ->assertSee('Editar calificación')
        ->assertSee('Actualizar calificación');

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), [
            'desempenio' => 'malo',
            'observaciones' => 'No cumplió.',
        ])
        ->assertSessionHas('exito');

    expect(Calificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->count())->toBe(1)
        ->and($this->inscripcion->fresh()->estatus)->toBe('reprobado')
        ->and($this->inscripcion->fresh()->calificacion->observaciones)->toBe('No cumplió.');
});

it('impide calificar a un instructor de otro grupo', function () {
    $otroInstructor = Escenarios::usuario('instructor', $this->sistemas);

    $this->actingAs($otroInstructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente'])
        ->assertForbidden();

    expect($this->inscripcion->fresh()->estatus)->toBe('inscrito')
        ->and(Calificacion::count())->toBe(0);
});

it('impide al alumno y al coordinador calificar', function () {
    $this->actingAs($this->alumno)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente'])
        ->assertForbidden();

    $this->actingAs($this->coordinador)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'excelente'])
        ->assertForbidden();

    expect($this->inscripcion->fresh()->estatus)->toBe('inscrito')
        ->and(Calificacion::count())->toBe(0);
});

it('no califica una inscripción dada de baja', function () {
    $baja = crearInscripcionParaCalificacion(Escenarios::usuario('alumno'), $this->grupo, 'dado_de_baja');

    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $baja), ['desempenio' => 'bueno'])
        ->assertForbidden();

    expect($baja->fresh()->estatus)->toBe('dado_de_baja')
        ->and($baja->fresh()->calificacion)->toBeNull();
});

it('no permite al administrador calificar una inscripción dada de baja', function () {
    $baja = crearInscripcionParaCalificacion(Escenarios::usuario('alumno'), $this->grupo, 'dado_de_baja');

    $this->actingAs($this->admin)
        ->put(route('calificaciones.update', $baja), ['desempenio' => 'bueno'])
        ->assertForbidden();

    expect($baja->fresh()->estatus)->toBe('dado_de_baja')
        ->and($baja->fresh()->calificacion)->toBeNull();
});

it('oculta el formulario de calificación al administrador si la inscripción está dada de baja', function () {
    $baja = crearInscripcionParaCalificacion(Escenarios::usuario('alumno'), $this->grupo, 'dado_de_baja');

    $this->actingAs($this->admin)
        ->get(route('calificaciones.edit', $baja))
        ->assertOk()
        ->assertSee('La inscripción está dada de baja; no se puede calificar.')
        ->assertDontSee('Guardar calificación');
});

it('rechaza un desempeño que no está en el catálogo', function () {
    $this->from(route('calificaciones.edit', $this->inscripcion))
        ->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'regular'])
        ->assertSessionHasErrors('desempenio');

    expect($this->inscripcion->fresh()->estatus)->toBe('inscrito')
        ->and(Calificacion::count())->toBe(0);
});

it('muestra al alumno su calificación y observaciones en el historial', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), [
            'desempenio' => 'excelente',
            'observaciones' => 'Excelente desempeño.',
        ]);

    $this->actingAs($this->alumno)
        ->get('/inscripciones')
        ->assertOk()
        ->assertSee('Excelente')
        ->assertSee('Excelente desempeño.');
});

it('permite al coordinador consultar las calificaciones de su departamento sin editarlas', function () {
    $this->actingAs($this->instructor)
        ->put(route('calificaciones.update', $this->inscripcion), ['desempenio' => 'bueno']);

    $this->actingAs($this->coordinador)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertSee('Alumnos inscritos')
        ->assertSee('Bueno')
        ->assertDontSee('Calificar');

    $this->actingAs($this->coordinador)
        ->get(route('calificaciones.edit', $this->inscripcion))
        ->assertOk()
        ->assertSee('Solo el instructor del grupo puede modificar esta calificación.')
        ->assertDontSee('Guardar calificación');
});

it('permite al administrador calificar en cualquier departamento', function () {
    $otroInstructor = Escenarios::usuario('instructor', $this->otro);
    $ajena = Escenarios::actividad([
        'nombre' => 'Basquetbol',
        'id_departamento' => $this->otro->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $grupoAjeno = Escenarios::grupo([
        'id_actividad' => $ajena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $otroInstructor->id,
    ]);
    $inscripcionAjena = crearInscripcionParaCalificacion(Escenarios::usuario('alumno'), $grupoAjeno);

    $this->actingAs($this->admin)
        ->put(route('calificaciones.update', $inscripcionAjena), ['desempenio' => 'excelente'])
        ->assertSessionHas('exito');

    expect($inscripcionAjena->fresh()->estatus)->toBe('aprobado')
        ->and($inscripcionAjena->fresh()->calificacion->desempenio)->toBe('excelente');
});
