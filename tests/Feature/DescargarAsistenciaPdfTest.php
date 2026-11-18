<?php

use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa y descuenta el cupo del grupo. */
function crearInscripcionParaPdf(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
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
    $this->alumnoD = Escenarios::usuario('alumno', null, [
        'nombre' => 'Diana',
        'apellido_paterno' => 'Santos',
        'email' => 'diana@ito.test',
    ]);
    $this->alumnoA->alumno->update(['num_control' => '20230001']);
    $this->alumnoB->alumno->update(['num_control' => '20230002']);
    $this->alumnoC->alumno->update(['num_control' => '20230003']);
    $this->alumnoD->alumno->update(['num_control' => '20230004']);

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

    crearInscripcionParaPdf($this->alumnoA, $this->grupo, 'inscrito');
    crearInscripcionParaPdf($this->alumnoB, $this->grupo, 'cursando');
    crearInscripcionParaPdf($this->alumnoC, $this->grupo, 'dado_de_baja');
    crearInscripcionParaPdf($this->alumnoD, $this->grupo, 'aprobado');

    $this->alumnoAjeno = Escenarios::usuario('alumno', null, ['nombre' => 'Diego']);
    crearInscripcionParaPdf($this->alumnoAjeno, $this->grupoAjeno);
});

it('manda al login a los invitados', function () {
    $this->get(route('grupos.asistencia.pdf', $this->grupo))->assertRedirect('/login');
});

it('descarga la lista de asistencia del grupo en PDF', function () {
    $respuesta = $this->actingAs($this->instructor)
        ->get(route('grupos.asistencia.pdf', $this->grupo))
        ->assertOk()
        ->assertDownload('asistencia-ajedrez-a.pdf');

    $pdf = $respuesta->getContent();

    expect($respuesta->headers->get('content-type'))->toContain('application/pdf')
        ->and(str_starts_with($pdf, '%PDF'))->toBeTrue();
});

it('incluye a los alumnos activos y los datos del grupo en el documento', function () {
    $grupo = $this->grupo->load([
        'actividad.departamento',
        'semestre',
        'instructor.usuario',
        'ubicacion',
        'horarios.dia',
    ]);

    $inscripciones = $grupo->inscripciones()
        ->with(['alumno.usuario', 'alumno.carrera'])
        ->whereIn('estatus', Inscripcion::ACTIVOS)
        ->orderBy('id_inscripcion')
        ->get();

    $html = view('pdf.asistencia-grupo', [
        'grupo' => $grupo,
        'inscripciones' => $inscripciones,
        'generadoEl' => now(),
    ])->render();

    expect($html)->toContain('Lista de asistencia')
        ->and($html)->toContain('Ajedrez')
        ->and($html)->toContain('Lunes de 10:00 a 12:00')
        ->and($html)->toContain('Ana López')
        ->and($html)->toContain('20230001')
        ->and($html)->toContain('Beto Pérez')
        ->and($html)->toContain('20230002')
        ->and($html)->toContain('Total de alumnos activos: 2')
        ->and($html)->not->toContain('Carla Ruiz')
        ->and($html)->not->toContain('Diana Santos')
        ->and($html)->not->toContain('Diego');
});

it('permite la descarga al administrador y al coordinador del departamento', function () {
    $this->actingAs($this->admin)
        ->get(route('grupos.asistencia.pdf', $this->grupo))
        ->assertOk();

    $this->actingAs($this->coordinador)
        ->get(route('grupos.asistencia.pdf', $this->grupo))
        ->assertOk();
});

it('bloquea la descarga a alumnos, instructores ajenos y otros departamentos', function () {
    $this->actingAs($this->alumnoA)
        ->get(route('grupos.asistencia.pdf', $this->grupo))
        ->assertForbidden();

    $this->actingAs($this->otroInstructor)
        ->get(route('grupos.asistencia.pdf', $this->grupo))
        ->assertForbidden();

    $this->actingAs($this->coordinadorOtro)
        ->get(route('grupos.asistencia.pdf', $this->grupo))
        ->assertForbidden();
});

it('genera el PDF aunque el grupo no tenga alumnos activos', function () {
    $grupoVacio = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
        'grupo' => 'B',
    ], [[1, '14:00', '16:00']]);

    $this->actingAs($this->instructor)
        ->get(route('grupos.asistencia.pdf', $grupoVacio))
        ->assertOk()
        ->assertDownload('asistencia-ajedrez-b.pdf');

    $html = view('pdf.asistencia-grupo', [
        'grupo' => $grupoVacio->load(['actividad.departamento', 'semestre', 'instructor.usuario', 'ubicacion', 'horarios.dia']),
        'inscripciones' => collect(),
        'generadoEl' => now(),
    ])->render();

    expect($html)->toContain('Este grupo no tiene alumnos activos.')
        ->and($html)->toContain('Total de alumnos activos: 0');
});

it('muestra el botón de asistencia solo a quien puede ver la lista', function () {
    $this->actingAs($this->instructor)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertSee('Lista de asistencia PDF')
        ->assertSee(route('grupos.asistencia.pdf', $this->grupo), false);

    $this->actingAs($this->alumnoA)
        ->get(route('grupos.show', $this->grupo))
        ->assertOk()
        ->assertDontSee('Lista de asistencia PDF');
});
