<?php

use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\Semestre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

/** Crea una inscripción directa y descuenta el cupo del grupo. */
function crearInscripcionReporte(User $alumno, Grupo $grupo, string $estatus): Inscripcion
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
    $this->basicas = Escenarios::departamento('Ciencias Básicas');
    $this->carreraSistemas = Escenarios::carrera();
    $this->carreraOtra = Escenarios::carrera('Administración');

    $this->admin = Escenarios::usuario('administrador', null, [
        'nombre' => 'Admin',
        'apellido_paterno' => 'Root',
        'email' => 'admin@ito.test',
        'ultimo_acceso' => now()->subHour(),
    ]);
    $this->coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    $this->instructor = Escenarios::usuario('instructor', $this->sistemas, [
        'nombre' => 'Inés',
        'apellido_paterno' => 'Docente',
        'ultimo_acceso' => now()->subDays(2),
    ]);

    $this->alumnoA = Escenarios::usuario('alumno', null, [
        'nombre' => 'Ana',
        'apellido_paterno' => 'López',
        'email' => 'ana@ito.test',
    ]);
    $this->alumnoB = Escenarios::usuario('alumno', null, [
        'nombre' => 'Beto',
        'apellido_paterno' => 'Pérez',
        'email' => 'beto@ito.test',
        'ultimo_acceso' => now()->subDays(10),
    ]);
    $this->alumnoC = Escenarios::usuario('alumno', null, [
        'nombre' => 'Carla',
        'apellido_paterno' => 'Ruiz',
        'email' => 'carla@ito.test',
    ]);
    $this->alumnoB->alumno->update(['id_carrera' => $this->carreraOtra->id_carrera]);

    $this->actividad = Escenarios::actividad([
        'nombre' => 'Ajedrez',
        'id_departamento' => $this->sistemas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->actividadAjena = Escenarios::actividad([
        'nombre' => 'Fútbol',
        'id_departamento' => $this->basicas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);

    $this->grupo = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'id_instructor' => $this->instructor->id,
        'grupo' => 'A',
    ], [[1, '10:00', '12:00']]);
    $this->grupoAjeno = Escenarios::grupo([
        'id_actividad' => $this->actividadAjena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'grupo' => 'B',
    ], [[2, '10:00', '12:00']]);

    $this->inscripcionA = crearInscripcionReporte($this->alumnoA, $this->grupo, 'inscrito');
    $this->inscripcionB = crearInscripcionReporte($this->alumnoB, $this->grupo, 'aprobado');
    $this->inscripcionC = crearInscripcionReporte($this->alumnoC, $this->grupoAjeno, 'reprobado');
});

it('manda al login a los invitados', function () {
    foreach (['/reportes', '/reportes/alumnos', '/reportes/inscripciones', '/reportes/accesos'] as $ruta) {
        $this->get($ruta)->assertRedirect('/login');
    }
});

it('muestra el índice de reportes al administrador', function () {
    $respuesta = $this->actingAs($this->admin)
        ->get(route('reportes.index'))
        ->assertOk()
        ->assertSee('Reportes administrativos')
        ->assertSee(route('reportes.alumnos'), false)
        ->assertSee(route('reportes.inscripciones'), false)
        ->assertSee(route('reportes.accesos'), false);

    expect($respuesta->viewData('totales'))->toMatchArray([
        'alumnos' => 3,
        'inscripciones' => 3,
        'grupos' => 2,
        'actividades' => 2,
    ]);
});

it('bloquea los reportes a coordinador, instructor y alumno', function () {
    $rutas = [
        route('reportes.index'),
        route('reportes.alumnos'),
        route('reportes.inscripciones'),
        route('reportes.accesos'),
    ];

    foreach ([$this->coordinador, $this->instructor, $this->alumnoA] as $usuario) {
        foreach ($rutas as $ruta) {
            $this->actingAs($usuario)->get($ruta)->assertForbidden();
        }
    }
});

it('da el permiso de reportes solo al administrador', function () {
    expect(Role::findByName('administrador')->hasPermissionTo('reportes.ver'))->toBeTrue()
        ->and(Role::findByName('coordinador')->hasPermissionTo('reportes.ver'))->toBeFalse()
        ->and(Role::findByName('instructor')->hasPermissionTo('reportes.ver'))->toBeFalse()
        ->and(Role::findByName('alumno')->hasPermissionTo('reportes.ver'))->toBeFalse();
});

it('muestra el enlace de reportes solo al administrador en el panel', function () {
    $this->actingAs($this->admin)->get('/panel')
        ->assertOk()
        ->assertSee(route('reportes.index'), false);

    $this->actingAs($this->coordinador)->get('/panel')
        ->assertOk()
        ->assertDontSee(route('reportes.index'), false);
});

it('filtra el padrón de alumnos por carrera y búsqueda', function () {
    $respuesta = $this->actingAs($this->admin)
        ->get(route('reportes.alumnos'))
        ->assertOk()
        ->assertSee('Padrón de alumnos')
        ->assertSee('Ana')
        ->assertSee('Beto')
        ->assertSee('Carla');

    $alumnos = $respuesta->viewData('alumnos');
    expect($alumnos->total())->toBe(3)
        ->and($alumnos->getCollection()->firstWhere('id_alumno', $this->alumnoA->id)->inscripciones_activas)->toBe(1)
        ->and($alumnos->getCollection()->firstWhere('id_alumno', $this->alumnoB->id)->inscripciones_activas)->toBe(0);

    $respuesta = $this->get(route('reportes.alumnos', ['carrera' => $this->carreraOtra->id_carrera]))
        ->assertOk();

    expect($respuesta->viewData('alumnos')->getCollection()->pluck('id_alumno')->all())
        ->toBe([$this->alumnoB->id]);

    $respuesta = $this->get(route('reportes.alumnos', ['buscar' => 'Ana']))->assertOk();

    expect($respuesta->viewData('alumnos')->getCollection()->pluck('id_alumno')->all())
        ->toBe([$this->alumnoA->id]);
});

it('muestra el monitor de inscripciones con totales por estatus', function () {
    $respuesta = $this->actingAs($this->admin)
        ->get(route('reportes.inscripciones'))
        ->assertOk()
        ->assertSee('Monitor de inscripciones')
        ->assertSee('Ana')
        ->assertSee('Beto')
        ->assertSee('Carla');

    $totales = $respuesta->viewData('totales');

    expect($totales['inscrito'] ?? 0)->toBe(1)
        ->and($totales['aprobado'] ?? 0)->toBe(1)
        ->and($totales['reprobado'] ?? 0)->toBe(1)
        ->and($totales['cursando'] ?? 0)->toBe(0)
        ->and($totales['dado_de_baja'] ?? 0)->toBe(0);
});

it('filtra las inscripciones por estatus, carrera, departamento y actividad', function () {
    $respuesta = $this->actingAs($this->admin)
        ->get(route('reportes.inscripciones', ['estatus' => 'aprobado']))
        ->assertOk();

    expect($respuesta->viewData('inscripciones')->getCollection()->pluck('id_inscripcion')->all())
        ->toBe([$this->inscripcionB->id_inscripcion]);

    $respuesta = $this->get(route('reportes.inscripciones', ['carrera' => $this->carreraOtra->id_carrera]))
        ->assertOk();

    expect($respuesta->viewData('inscripciones')->getCollection()->pluck('id_inscripcion')->all())
        ->toBe([$this->inscripcionB->id_inscripcion]);

    $respuesta = $this->get(route('reportes.inscripciones', ['departamento' => $this->basicas->id_departamento]))
        ->assertOk();

    expect($respuesta->viewData('inscripciones')->getCollection()->pluck('id_inscripcion')->all())
        ->toBe([$this->inscripcionC->id_inscripcion]);

    $respuesta = $this->get(route('reportes.inscripciones', ['actividad' => $this->actividad->id_actividad]))
        ->assertOk();

    expect($respuesta->viewData('inscripciones')->getCollection()->pluck('id_inscripcion')->all())
        ->toBe([$this->inscripcionB->id_inscripcion, $this->inscripcionA->id_inscripcion]);
});

it('muestra y filtra el reporte de accesos', function () {
    $respuesta = $this->actingAs($this->admin)
        ->get(route('reportes.accesos'))
        ->assertOk()
        ->assertSee('Últimos accesos')
        ->assertSee('Admin Root')
        ->assertSee('Inés Docente')
        ->assertSee('Sin acceso');

    expect($respuesta->viewData('resumen'))->toMatchArray([
        'total' => 6,
        'activos' => 6,
        'sinAcceso' => 3,
        'recientes' => 2,
    ]);

    $respuesta = $this->get(route('reportes.accesos', ['rol' => 'alumno']))->assertOk();
    $ids = $respuesta->viewData('usuarios')->getCollection()->pluck('id')->all();

    expect($ids)->toHaveCount(3)
        ->and($ids)->toContain($this->alumnoA->id)
        ->and($ids)->toContain($this->alumnoB->id)
        ->and($ids)->toContain($this->alumnoC->id);

    $respuesta = $this->get(route('reportes.accesos', ['buscar' => 'Docente']))->assertOk();

    expect($respuesta->viewData('usuarios')->getCollection()->pluck('id')->all())
        ->toBe([$this->instructor->id]);
});
