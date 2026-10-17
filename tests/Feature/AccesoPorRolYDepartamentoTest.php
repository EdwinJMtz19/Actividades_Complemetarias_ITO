<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->sistemas = Escenarios::departamento('Sistemas');
    $this->basicas = Escenarios::departamento('Ciencias Básicas');
});

it('niega el módulo de usuarios y de roles a alumnos e instructores', function (string $rol) {
    $usuario = Escenarios::usuario($rol, $this->sistemas);

    $this->actingAs($usuario)->get('/usuarios')->assertForbidden();
    $this->get('/usuarios/create')->assertForbidden();
    $this->get('/roles')->assertForbidden();
})->with(['alumno', 'instructor']);

it('permite al administrador entrar a todo', function () {
    $admin = Escenarios::usuario('administrador');

    $this->actingAs($admin)->get('/usuarios')->assertOk();
    $this->get('/usuarios/create')->assertOk();
    $this->get('/roles')->assertOk();
});

it('muestra al coordinador solo a los instructores de su departamento', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    Escenarios::usuario('instructor', $this->sistemas, ['nombre' => 'Propio']);
    Escenarios::usuario('instructor', $this->basicas, ['nombre' => 'Ajeno']);
    Escenarios::usuario('alumno', null, ['nombre' => 'Estudiante']);

    $this->actingAs($coordinador)->get('/usuarios')
        ->assertOk()->assertSee('Propio')->assertDontSee('Ajeno')->assertDontSee('Estudiante');
});

it('no deja al coordinador abrir el detalle de usuarios fuera de su alcance', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    $propio = Escenarios::usuario('instructor', $this->sistemas);
    $ajeno = Escenarios::usuario('instructor', $this->basicas);
    $alumno = Escenarios::usuario('alumno');

    $this->actingAs($coordinador)->get("/usuarios/{$propio->id}")->assertOk();
    $this->get("/usuarios/{$ajeno->id}")->assertForbidden();
    $this->get("/usuarios/{$alumno->id}")->assertForbidden();
    $this->get("/usuarios/{$coordinador->id}")->assertForbidden();
});

it('no deja al coordinador crear, editar, eliminar ni desactivar usuarios', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    $propio = Escenarios::usuario('instructor', $this->sistemas);

    $this->actingAs($coordinador)->get('/usuarios/create')->assertForbidden();
    $this->post('/usuarios', ['rol' => 'alumno'])->assertForbidden();
    $this->get("/usuarios/{$propio->id}/edit")->assertForbidden();
    $this->put("/usuarios/{$propio->id}", ['nombre' => 'X'])->assertForbidden();
    $this->delete("/usuarios/{$propio->id}")->assertForbidden();
    $this->patch("/usuarios/{$propio->id}/estado")->assertForbidden();

    expect(User::find($propio->id))->not->toBeNull()->and($propio->fresh()->activo)->toBeTrue();
});

it('bloquea el módulo a un coordinador sin departamento, pero deja abrir el panel', function () {
    $sinDepartamento = Escenarios::usuario('coordinador');

    $this->actingAs($sinDepartamento)->get('/usuarios')->assertForbidden();
    $this->get('/panel')->assertOk()->assertSee('Aún no tienes un departamento asignado');
    $this->post('/logout')->assertRedirect('/login');
});

it('respeta los permisos configurados: sin usuarios.ver el coordinador pierde el acceso', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);
    Role::findByName('coordinador')->revokePermissionTo('usuarios.ver');

    $this->actingAs($coordinador)->get('/usuarios')->assertForbidden();
});

it('aplica el middleware de Spatie a la pantalla de roles según el permiso', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);

    $this->actingAs($coordinador)->get('/roles')->assertForbidden();

    Role::findByName('coordinador')->givePermissionTo('roles.ver');
    $this->actingAs($coordinador->fresh())->get('/roles')->assertOk();
});

it('el panel muestra el enlace a Usuarios solo a quien puede verlos', function () {
    $this->actingAs(Escenarios::usuario('alumno'))->get('/panel')->assertOk()->assertDontSee('Roles y permisos');
    $this->actingAs(Escenarios::usuario('administrador'))->get('/panel')->assertOk()->assertSee('Roles y permisos');
});
