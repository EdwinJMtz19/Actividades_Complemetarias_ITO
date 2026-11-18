<?php

use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa y descuenta el cupo del grupo. */
function crearInscripcionParaHorario(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
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

    $this->sinInscripcion = Escenarios::usuario('alumno', null, [
        'nombre' => 'Beto',
        'apellido_paterno' => 'Pérez',
        'email' => 'beto@ito.test',
    ]);
    $this->sinInscripcion->alumno->update(['num_control' => '20230002']);

    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->departamento->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
    ], [[1, '10:00', '12:00']]);

    crearInscripcionParaHorario($this->alumno, $this->grupo);
});

it('manda al login a los invitados', function () {
    $this->get(route('inscripciones.horario.pdf'))->assertRedirect('/login');
});

it('descarga el horario del alumno en PDF', function () {
    $respuesta = $this->actingAs($this->alumno)
        ->get(route('inscripciones.horario.pdf'))
        ->assertOk()
        ->assertDownload('horario-20230001.pdf');

    expect($respuesta->headers->get('content-type'))->toContain('application/pdf')
        ->and(str_starts_with($respuesta->getContent(), '%PDF'))->toBeTrue();
});

it('incluye los datos del alumno y los bloques de horario en el documento', function () {
    $inscripcion = Inscripcion::query()
        ->activas()
        ->with([
            'grupo.actividad.departamento',
            'grupo.semestre',
            'grupo.instructor.usuario',
            'grupo.ubicacion',
            'grupo.horarios.dia',
        ])
        ->first();

    $html = view('pdf.horario-alumno', [
        'alumno' => $this->alumno->alumno->loadMissing(['usuario', 'carrera']),
        'inscripcion' => $inscripcion,
        'generadoEl' => now(),
    ])->render();

    expect($html)->toContain('Horario del alumno')
        ->and($html)->toContain('Ana López')
        ->and($html)->toContain('20230001')
        ->and($html)->toContain('Ajedrez')
        ->and($html)->toContain('Lunes')
        ->and($html)->toContain('10:00 a 12:00')
        ->and($html)->toContain('Bloques de clase: 1');
});

it('redirige con aviso cuando el alumno no tiene inscripción activa', function () {
    $this->actingAs($this->sinInscripcion)
        ->from(route('inscripciones.index'))
        ->get(route('inscripciones.horario.pdf'))
        ->assertRedirect(route('inscripciones.index'))
        ->assertSessionHas('error');
});

it('ignora las inscripciones dadas de baja al buscar el horario', function () {
    $this->alumno->alumno->inscripciones()->update(['estatus' => 'dado_de_baja']);

    $this->actingAs($this->alumno)
        ->from(route('inscripciones.index'))
        ->get(route('inscripciones.horario.pdf'))
        ->assertRedirect(route('inscripciones.index'))
        ->assertSessionHas('error');
});

it('bloquea a quienes no son alumnos', function () {
    foreach ([$this->admin, $this->coordinador, $this->instructor] as $usuario) {
        $this->actingAs($usuario)
            ->get(route('inscripciones.horario.pdf'))
            ->assertForbidden();
    }
});

it('genera el documento aunque el grupo no tenga horarios', function () {
    $grupoVacio = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
        'grupo' => 'B',
    ]);

    $inscripcion = crearInscripcionParaHorario($this->sinInscripcion, $grupoVacio);

    $html = view('pdf.horario-alumno', [
        'alumno' => $this->sinInscripcion->alumno->loadMissing(['usuario', 'carrera']),
        'inscripcion' => $inscripcion->load([
            'grupo.actividad.departamento',
            'grupo.semestre',
            'grupo.instructor.usuario',
            'grupo.ubicacion',
            'grupo.horarios.dia',
        ]),
        'generadoEl' => now(),
    ])->render();

    expect($html)->toContain('Este grupo no tiene horarios registrados.')
        ->and($html)->toContain('Bloques de clase: 0');
});

it('muestra el botón de descarga al alumno con inscripción activa', function () {
    $this->actingAs($this->alumno)
        ->get(route('inscripciones.index'))
        ->assertOk()
        ->assertSee('Descargar horario PDF')
        ->assertSee(route('inscripciones.horario.pdf'), false);

    $this->actingAs($this->sinInscripcion)
        ->get(route('inscripciones.index'))
        ->assertOk()
        ->assertDontSee('Descargar horario PDF');
});
