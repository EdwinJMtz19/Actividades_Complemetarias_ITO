<?php

use App\Models\Calificacion;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa para probar la constancia. */
function crearInscripcionParaConstancia(User $alumno, int $idGrupo, string $estatus): Inscripcion
{
    return Inscripcion::create([
        'id_alumno' => $alumno->id,
        'id_grupo' => $idGrupo,
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
    $this->departamento = Escenarios::departamento('Sistemas');

    $this->admin = Escenarios::usuario('administrador');
    $this->coordinador = Escenarios::usuario('coordinador', $this->departamento);
    $this->instructor = Escenarios::usuario('instructor', $this->departamento);

    $this->alumno = Escenarios::usuario('alumno', null, [
        'nombre' => 'Ana',
        'apellido_paterno' => 'López',
        'email' => 'ana@ito.test',
    ]);
    $this->alumno->alumno->update(['num_control' => '20230001']);

    $this->otroAlumno = Escenarios::usuario('alumno', null, [
        'nombre' => 'Beto',
        'apellido_paterno' => 'Pérez',
        'email' => 'beto@ito.test',
    ]);
    $this->otroAlumno->alumno->update(['num_control' => '20230002']);

    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'creditos' => 2,
        'id_departamento' => $this->departamento->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
    ], [[1, '10:00', '12:00']]);

    $this->aprobada = crearInscripcionParaConstancia($this->alumno, $this->grupo->id_grupo, 'aprobado');
    Calificacion::create([
        'id_inscripcion' => $this->aprobada->id_inscripcion,
        'desempenio' => 'excelente',
        'observaciones' => 'Cumplió con todas las sesiones.',
    ]);

    $this->reprobada = crearInscripcionParaConstancia($this->alumno, $this->grupo->id_grupo, 'reprobado');
    Calificacion::create([
        'id_inscripcion' => $this->reprobada->id_inscripcion,
        'desempenio' => 'malo',
    ]);

    $this->delOtro = crearInscripcionParaConstancia($this->otroAlumno, $this->grupo->id_grupo, 'aprobado');
    Calificacion::create([
        'id_inscripcion' => $this->delOtro->id_inscripcion,
        'desempenio' => 'bueno',
    ]);
});

it('manda al login a los invitados', function () {
    $this->get(route('inscripciones.constancia.pdf', $this->aprobada))->assertRedirect('/login');
});

it('descarga la constancia de acreditación en PDF', function () {
    $respuesta = $this->actingAs($this->alumno)
        ->get(route('inscripciones.constancia.pdf', $this->aprobada))
        ->assertOk()
        ->assertDownload('constancia-20230001-ajedrez.pdf');

    expect($respuesta->headers->get('content-type'))->toContain('application/pdf')
        ->and(str_starts_with($respuesta->getContent(), '%PDF'))->toBeTrue();
});

it('incluye los datos del alumno y de la actividad en el documento', function () {
    $inscripcion = $this->aprobada->load([
        'alumno.usuario',
        'alumno.carrera',
        'grupo.actividad.departamento',
        'grupo.semestre',
        'grupo.instructor.usuario',
        'calificacion',
    ]);

    $html = view('pdf.constancia-acreditacion', [
        'inscripcion' => $inscripcion,
        'generadoEl' => now(),
    ])->render();

    expect($html)->toContain('Constancia de acreditación')
        ->and($html)->toContain('Ana López')
        ->and($html)->toContain('20230001')
        ->and($html)->toContain('Ajedrez')
        ->and($html)->toContain('2 crédito(s)')
        ->and($html)->toContain('Excelente')
        ->and($html)->toContain('Cumplió con todas las sesiones.');
});

it('bloquea la constancia de otro alumno', function () {
    $this->actingAs($this->otroAlumno)
        ->get(route('inscripciones.constancia.pdf', $this->aprobada))
        ->assertForbidden();
});

it('bloquea a instructores y coordinadores', function () {
    $this->actingAs($this->instructor)
        ->get(route('inscripciones.constancia.pdf', $this->aprobada))
        ->assertForbidden();

    $this->actingAs($this->coordinador)
        ->get(route('inscripciones.constancia.pdf', $this->aprobada))
        ->assertForbidden();
});

it('redirige con aviso cuando la actividad no está aprobada', function () {
    $this->actingAs($this->alumno)
        ->from(route('inscripciones.index'))
        ->get(route('inscripciones.constancia.pdf', $this->reprobada))
        ->assertRedirect(route('inscripciones.index'))
        ->assertSessionHas('error');
});

it('muestra el botón de constancia solo en las actividades aprobadas', function () {
    $this->actingAs($this->alumno)
        ->get(route('inscripciones.index'))
        ->assertOk()
        ->assertSee(route('inscripciones.constancia.pdf', $this->aprobada), false)
        ->assertDontSee(route('inscripciones.constancia.pdf', $this->reprobada), false);
});
