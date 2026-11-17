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
function crearInscripcionDashboard(User $alumno, Grupo $grupo, string $estatus = 'inscrito'): Inscripcion
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
    Escenarios::base();
    Escenarios::dias();

    $this->semestre = Escenarios::semestre(['status' => Semestre::ACTIVO]);
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->basicas = Escenarios::departamento('Ciencias Básicas');

    $this->coordinador = Escenarios::usuario('coordinador', $this->sistemas);
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
        'grupo' => 'A',
    ], [[1, '10:00', '12:00']]);
    $this->grupoSinInstructor = Escenarios::grupo([
        'id_actividad' => $this->actividad->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'grupo' => 'B',
    ], [[2, '10:00', '12:00']]);

    $this->actividadAjena = Escenarios::actividad([
        'nombre' => 'Fútbol',
        'id_departamento' => $this->basicas->id_departamento,
        'id_semestre' => $this->semestre->id_semestre,
    ]);
    $this->grupoAjeno = Escenarios::grupo([
        'id_actividad' => $this->actividadAjena->id_actividad,
        'id_semestre' => $this->semestre->id_semestre,
        'grupo' => 'C',
    ], [[3, '10:00', '12:00']]);

    $this->alumnoActivo = Escenarios::usuario('alumno');
    $this->alumnoAprobado = Escenarios::usuario('alumno');
    $this->alumnoAjeno = Escenarios::usuario('alumno');

    crearInscripcionDashboard($this->alumnoActivo, $this->grupo);
    crearInscripcionDashboard($this->alumnoAprobado, $this->grupo, 'aprobado');
    crearInscripcionDashboard($this->alumnoAjeno, $this->grupoAjeno);
});

it('muestra al coordinador el dashboard de su departamento', function () {
    $respuesta = $this->actingAs($this->coordinador)
        ->get('/panel')
        ->assertOk()
        ->assertSee('Dashboard del departamento')
        ->assertSee('Sistemas')
        ->assertSee('Grupos recientes');

    $dashboard = $respuesta->viewData('dashboard');

    expect($dashboard['totalActividades'])->toBe(1)
        ->and($dashboard['totalGrupos'])->toBe(2)
        ->and($dashboard['gruposSinInstructor'])->toBe(1)
        ->and($dashboard['totalInstructores'])->toBe(1)
        ->and($dashboard['totalAlumnos'])->toBe(1)
        ->and($dashboard['totalInscripcionesActivas'])->toBe(1)
        ->and($dashboard['aprobados'])->toBe(1)
        ->and($dashboard['reprobados'])->toBe(0)
        ->and($dashboard['gruposRecientes'])->toHaveCount(2);
});

it('limita el dashboard al departamento del coordinador', function () {
    $dashboard = $this->actingAs($this->coordinador)->get('/panel')->viewData('dashboard');

    expect($dashboard['departamento']->id_departamento)->toBe($this->sistemas->id_departamento)
        ->and($dashboard['gruposRecientes']->pluck('id_grupo')->all())
        ->not->toContain($this->grupoAjeno->id_grupo);
});

it('muestra al administrador el dashboard general de todos los departamentos', function () {
    $admin = Escenarios::usuario('administrador');

    $respuesta = $this->actingAs($admin)->get('/panel')
        ->assertOk()
        ->assertSee('Dashboard general')
        ->assertSee('Todos los departamentos')
        ->assertSee('Grupos recientes');

    $dashboard = $respuesta->viewData('dashboard');

    expect($dashboard['departamento'])->toBeNull()
        ->and($dashboard['totalActividades'])->toBe(2)
        ->and($dashboard['totalGrupos'])->toBe(3)
        ->and($dashboard['gruposSinInstructor'])->toBe(2)
        ->and($dashboard['totalInstructores'])->toBe(1)
        ->and($dashboard['totalAlumnos'])->toBe(2)
        ->and($dashboard['totalInscripcionesActivas'])->toBe(2)
        ->and($dashboard['aprobados'])->toBe(1)
        ->and($dashboard['reprobados'])->toBe(0)
        ->and($dashboard['gruposRecientes'])->toHaveCount(3);
});

it('no muestra el dashboard al instructor ni al alumno', function () {
    $usuarios = [
        Escenarios::usuario('instructor', $this->sistemas),
        Escenarios::usuario('alumno'),
    ];

    foreach ($usuarios as $usuario) {
        $respuesta = $this->actingAs($usuario)->get('/panel')->assertOk()
            ->assertDontSee('Dashboard del departamento');

        expect($respuesta->viewData('dashboard'))->toBeNull();
    }
});

it('no muestra el dashboard al coordinador sin departamento', function () {
    $coordinador = Escenarios::usuario('coordinador');

    $this->actingAs($coordinador)
        ->get('/panel')
        ->assertOk()
        ->assertSee('Aún no tienes un departamento asignado')
        ->assertDontSee('Dashboard del departamento')
        ->assertViewHas('dashboard', null);
});

it('asigna el permiso de dashboard al administrador y al coordinador', function () {
    expect(Role::findByName('coordinador')->hasPermissionTo('dashboard.ver'))->toBeTrue()
        ->and(Role::findByName('administrador')->hasPermissionTo('dashboard.ver'))->toBeTrue()
        ->and(Role::findByName('instructor')->hasPermissionTo('dashboard.ver'))->toBeFalse()
        ->and(Role::findByName('alumno')->hasPermissionTo('dashboard.ver'))->toBeFalse();
});
