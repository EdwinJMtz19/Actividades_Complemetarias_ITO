<?php

use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa y descuenta el cupo del grupo. */
function crearInscripcionParaCsv(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
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
    $this->travelTo('2026-11-10 10:00:00');
    Escenarios::base();
    Escenarios::dias();

    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->otro = Escenarios::departamento('Administración');

    $this->admin = Escenarios::usuario('administrador');
    $this->coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    $this->coordinadorOtro = Escenarios::usuario('coordinador', $this->otro);
    $this->instructor = Escenarios::usuario('instructor', $this->sistemas);
    $this->otroInstructor = Escenarios::usuario('instructor', $this->sistemas);

    $this->alumnoA = Escenarios::usuario('alumno', null, [
        'nombre' => 'Ana',
        'apellido_paterno' => 'López',
        'email' => 'ana@ito.test',
    ]);
    $this->alumnoB = Escenarios::usuario('alumno', null, [
        'nombre' => 'Beto',
        'apellido_paterno' => 'Pérez',
        'email' => 'beto@ito.test',
    ]);
    $this->alumnoC = Escenarios::usuario('alumno', null, [
        'nombre' => 'Carla',
        'apellido_paterno' => 'Ruiz',
        'email' => 'carla@ito.test',
    ]);
    $this->alumnoA->alumno->update(['num_control' => '20230001']);
    $this->alumnoB->alumno->update(['num_control' => '20230002']);
    $this->alumnoC->alumno->update(['num_control' => '20230003']);

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

    $this->otraActividad = Escenarios::actividad([
        'nombre' => 'Fútbol',
        'id_departamento' => $this->otro->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupoAjeno = Escenarios::grupo([
        'id_actividad' => $this->otraActividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->otroInstructor->id,
    ], [[2, '10:00', '12:00']]);

    $this->inscripcionA = crearInscripcionParaCsv($this->alumnoA, $this->grupo, 'inscrito');
    $this->inscripcionB = crearInscripcionParaCsv($this->alumnoB, $this->grupo, 'aprobado');
    $this->inscripcionC = crearInscripcionParaCsv($this->alumnoC, $this->grupo, 'dado_de_baja');
    $this->inscripcionB->calificacion()->create([
        'desempenio' => 'excelente',
        'observaciones' => 'Participó en todas las sesiones.',
    ]);

    $this->alumnoAjeno = Escenarios::usuario('alumno', null, ['nombre' => 'Diego']);
    crearInscripcionParaCsv($this->alumnoAjeno, $this->grupoAjeno);
});

it('manda al login a los invitados', function () {
    $this->get(route('grupos.alumnos.csv', $this->grupo))->assertRedirect('/login');
});

it('descarga la lista de alumnos del grupo en CSV', function () {
    $respuesta = $this->actingAs($this->instructor)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertOk()
        ->assertDownload('alumnos-ajedrez-a.csv');

    expect($respuesta->headers->get('content-type'))->toContain('text/csv')
        ->and($respuesta->headers->get('content-type'))->toContain('UTF-8');

    $csv = $respuesta->streamedContent();

    expect(str_starts_with($csv, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($csv)->toContain('No. control')
        ->and($csv)->toContain('Nombre')
        ->and($csv)->toContain('20230001')
        ->and($csv)->toContain('Ana López')
        ->and($csv)->toContain('ana@ito.test')
        ->and($csv)->toContain('20230002')
        ->and($csv)->toContain('Beto Pérez')
        ->and($csv)->toContain('Excelente')
        ->and($csv)->toContain('Participó en todas las sesiones.')
        ->and($csv)->toContain('20230003')
        ->and($csv)->toContain('Carla Ruiz')
        ->and($csv)->toContain('Sin calificar')
        ->and($csv)->toContain('Dado de baja')
        ->and($csv)->toContain('10/11/2026 10:00')
        ->and($csv)->not->toContain('Diego');
});

it('permite la descarga al administrador y al coordinador del departamento', function () {
    $this->actingAs($this->admin)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertOk();

    $this->actingAs($this->coordinador)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertOk();
});

it('bloquea la descarga a alumnos, instructores ajenos y otros departamentos', function () {
    $this->actingAs($this->alumnoA)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertForbidden();

    $this->actingAs($this->otroInstructor)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertForbidden();

    $this->actingAs($this->coordinadorOtro)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertForbidden();
});

it('genera solo el encabezado si el grupo no tiene alumnos', function () {
    $grupoVacio = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
        'grupo' => 'B',
    ], [[1, '14:00', '16:00']]);

    $csv = $this->actingAs($this->instructor)
        ->get(route('grupos.alumnos.csv', $grupoVacio))
        ->assertOk()
        ->streamedContent();

    expect($csv)->toContain('No. control')
        ->and($csv)->not->toContain('Ana López')
        ->and($csv)->not->toContain('Beto Pérez');
});

it('neutraliza fórmulas en los campos de texto', function () {
    $formula = Escenarios::usuario('alumno', null, [
        'nombre' => '=SUM(1,1)',
        'apellido_paterno' => 'Prueba',
    ]);
    crearInscripcionParaCsv($formula, $this->grupo);

    $csv = $this->actingAs($this->instructor)
        ->get(route('grupos.alumnos.csv', $this->grupo))
        ->assertOk()
        ->streamedContent();

    expect($csv)->toContain("'=SUM(1,1) Prueba");
});

it('muestra el botón de exportar solo a quien puede ver la lista', function () {
    $this->actingAs($this->instructor)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertSee('Exportar CSV')
        ->assertSee(route('grupos.alumnos.csv', $this->grupo), false);

    $this->actingAs($this->alumnoA)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertDontSee('Exportar CSV');
});
