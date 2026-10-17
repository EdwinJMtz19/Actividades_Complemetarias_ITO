<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->rutas = [
        '/departamentos', '/departamentos/create', '/carreras', '/carreras/create',
        '/ubicaciones', '/ubicaciones/create', '/semestres', '/semestres/create',
    ];
});

it('manda al login a los invitados', function () {
    foreach ($this->rutas as $ruta) {
        $this->get($ruta)->assertRedirect('/login');
    }
});

it('niega los catálogos y semestres a alumnos, instructores y coordinadores', function (string $rol) {
    $usuario = Escenarios::usuario($rol, $this->sistemas);

    $this->actingAs($usuario);
    foreach ($this->rutas as $ruta) {
        $this->get($ruta)->assertForbidden();
    }

    $this->post('/departamentos', ['nombre' => 'X'])->assertForbidden();
    $this->post('/carreras', ['nombre' => 'X'])->assertForbidden();
    $this->post('/ubicaciones', ['espacio' => 'X', 'capacidad' => 5])->assertForbidden();
    $this->post('/semestres', [])->assertForbidden();
    $this->delete("/departamentos/{$this->sistemas->id_departamento}")->assertForbidden();
})->with(['alumno', 'instructor', 'coordinador']);

it('permite al administrador entrar a todos los módulos', function () {
    $this->actingAs(Escenarios::usuario('administrador'));

    foreach ($this->rutas as $ruta) {
        $this->get($ruta)->assertOk();
    }
});

it('respeta los permisos que se asignan desde la pantalla de roles', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    Role::findByName('coordinador')->givePermissionTo('carreras.ver');

    $this->actingAs($coordinador->fresh())->get('/carreras')->assertOk();
    $this->get('/carreras/create')->assertForbidden();
    $this->post('/carreras', ['nombre' => 'X'])->assertForbidden();
    $this->get('/departamentos')->assertForbidden();
});

it('da los permisos nuevos solo al administrador', function () {
    $permisos = ['departamentos.eliminar', 'carreras.crear', 'ubicaciones.editar', 'semestres.ver', 'semestres.editar'];

    expect(Role::findByName('administrador')->hasAllPermissions($permisos))->toBeTrue()
        ->and(Role::findByName('coordinador')->hasAnyPermission($permisos))->toBeFalse()
        ->and(Role::findByName('alumno')->hasAnyPermission($permisos))->toBeFalse()
        ->and(Permission::where('name', 'semestres.eliminar')->exists())->toBeFalse();
});

it('muestra los enlaces del menú solo a quien tiene el permiso', function () {
    $this->actingAs(Escenarios::usuario('administrador'))->get('/panel')
        ->assertSee(route('semestres.index'), false)->assertSee(route('departamentos.index'), false)
        ->assertSee(route('carreras.index'), false)->assertSee(route('ubicaciones.index'), false);

    $this->actingAs(Escenarios::usuario('alumno'))->get('/panel')
        ->assertDontSee(route('semestres.index'), false)->assertDontSee(route('carreras.index'), false);
});
