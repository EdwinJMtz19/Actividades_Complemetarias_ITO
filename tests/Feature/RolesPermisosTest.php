<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->admin = Escenarios::usuario('administrador');
});

it('crea los cuatro roles del cronograma', function () {
    expect(Role::orderBy('id')->pluck('name')->all())
        ->toBe(['administrador', 'coordinador', 'instructor', 'alumno']);
});

it('es idempotente y no pisa permisos agregados desde la pantalla de roles', function () {
    Role::findByName('instructor')->givePermissionTo('usuarios.ver');

    Escenarios::base();

    expect(Role::findByName('instructor')->hasPermissionTo('usuarios.ver'))->toBeTrue()
        ->and(Permission::count())->toBe(6);
});

it('mantiene tipo_usuario alineado con el rol', function () {
    foreach (User::TIPO_POR_ROL as $rol => $tipo) {
        expect(Escenarios::usuario($rol)->tipo_usuario)->toBe($tipo);
    }
});

it('muestra los roles y permisos al administrador', function () {
    $this->actingAs($this->admin)->get('/roles')
        ->assertOk()->assertSee('usuarios.ver')->assertSee('coordinador')->assertSee('Acceso total');
});

it('permite al administrador cambiar los permisos de un rol', function () {
    $coordinador = Role::findByName('coordinador');

    $this->actingAs($this->admin)->put("/roles/{$coordinador->id}", ['permisos' => ['usuarios.ver', 'roles.ver']])
        ->assertRedirect();

    expect($coordinador->fresh()->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['roles.ver', 'usuarios.ver']);

    $this->put("/roles/{$coordinador->id}", [])->assertRedirect();
    expect($coordinador->fresh()->permissions)->toHaveCount(0);
});

it('no deja modificar los permisos del administrador', function () {
    $rolAdmin = Role::findByName('administrador');

    $this->actingAs($this->admin)->put("/roles/{$rolAdmin->id}", ['permisos' => []])->assertForbidden();

    expect($rolAdmin->fresh()->permissions)->toHaveCount(6);
});

it('rechaza permisos que no existen', function () {
    $rol = Role::findByName('alumno');

    $this->actingAs($this->admin)->put("/roles/{$rol->id}", ['permisos' => ['inventado.todo']])
        ->assertSessionHasErrors('permisos.0');
});

it('impide que un coordinador edite roles aunque conozca la ruta', function () {
    $coordinador = Escenarios::usuario('coordinador', Escenarios::departamento('Sistemas'));
    $rol = Role::findByName('alumno');

    $this->actingAs($coordinador)->put("/roles/{$rol->id}", ['permisos' => ['usuarios.eliminar']])->assertForbidden();

    expect($rol->fresh()->permissions)->toHaveCount(0);
});
