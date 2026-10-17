<?php

use App\Models\Alumno;
use App\Models\CoordinadorDepartamento;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
    $this->admin = Escenarios::usuario('administrador');
    $this->sistemas = Escenarios::departamento('Sistemas');
});

function datosBase(array $extra = []): array
{
    return $extra + [
        'nombre' => 'Marta', 'apellido_paterno' => 'Lopez', 'apellido_materno' => 'Diaz',
        'email' => 'marta@example.com', 'telefono' => '9511112222',
        'contrasena' => 'Segura2026', 'contrasena_confirmation' => 'Segura2026', 'activo' => '1',
    ];
}

it('lista los usuarios para el administrador', function () {
    Escenarios::usuario('instructor', $this->sistemas, ['nombre' => 'Zoila']);

    $this->actingAs($this->admin)->get('/usuarios')->assertOk()->assertSee('Zoila');
});

it('crea un instructor con su departamento y sincroniza tipo_usuario', function () {
    $this->actingAs($this->admin)->post('/usuarios', datosBase([
        'rol' => 'instructor', 'id_departamento' => $this->sistemas->id_departamento, 'especialidad' => 'Redes',
    ]))->assertRedirect();

    $usuario = User::where('email', 'marta@example.com')->firstOrFail();
    expect($usuario->hasRole('instructor'))->toBeTrue()
        ->and($usuario->tipo_usuario)->toBe('instructor')
        ->and(Instructor::find($usuario->id)->id_departamento)->toBe($this->sistemas->id_departamento);
});

it('crea un alumno con su carrera', function () {
    $carrera = Escenarios::carrera();

    $this->actingAs($this->admin)->post('/usuarios', datosBase([
        'rol' => 'alumno', 'num_control' => '20231111', 'id_carrera' => $carrera->id_carrera, 'semestre_cursando' => 4,
    ]))->assertRedirect();

    $usuario = User::where('email', 'marta@example.com')->firstOrFail();
    expect(Alumno::find($usuario->id)->num_control)->toBe('20231111')->and($usuario->hasRole('alumno'))->toBeTrue();
});

it('crea un coordinador y le asigna un departamento', function () {
    $this->actingAs($this->admin)->post('/usuarios', datosBase([
        'rol' => 'coordinador', 'id_departamento' => $this->sistemas->id_departamento,
    ]))->assertRedirect();

    $usuario = User::where('email', 'marta@example.com')->firstOrFail();
    expect($usuario->departamentoId())->toBe($this->sistemas->id_departamento);
});

it('no permite dos coordinadores en el mismo departamento', function () {
    Escenarios::usuario('coordinador', $this->sistemas);

    $this->actingAs($this->admin)->post('/usuarios', datosBase([
        'rol' => 'coordinador', 'id_departamento' => $this->sistemas->id_departamento,
    ]))->assertSessionHasErrors('id_departamento');

    expect(CoordinadorDepartamento::count())->toBe(1)
        ->and(User::where('email', 'marta@example.com')->exists())->toBeFalse();
});

it('rechaza correo duplicado, contraseña débil y rol inválido', function () {
    Escenarios::usuario('alumno', null, ['email' => 'marta@example.com']);

    $this->actingAs($this->admin)->post('/usuarios', datosBase([
        'rol' => 'superusuario', 'contrasena' => 'abc', 'contrasena_confirmation' => 'abc',
    ]))->assertSessionHasErrors(['email', 'contrasena', 'rol']);
});

it('exige los datos propios de cada rol', function () {
    $this->actingAs($this->admin)->post('/usuarios', datosBase(['rol' => 'alumno']))
        ->assertSessionHasErrors(['num_control', 'id_carrera', 'semestre_cursando']);

    $this->actingAs($this->admin)->post('/usuarios', datosBase(['rol' => 'instructor']))
        ->assertSessionHasErrors('id_departamento');
});

it('no crea nada si falla una parte del alta (transacción)', function () {
    $this->actingAs($this->admin)->post('/usuarios', datosBase([
        'rol' => 'instructor', 'id_departamento' => 9999,
    ]))->assertSessionHasErrors('id_departamento');

    expect(User::where('email', 'marta@example.com')->exists())->toBeFalse();
});

it('edita los datos y deja la contraseña igual si se envía en blanco', function () {
    $instructor = Escenarios::usuario('instructor', $this->sistemas, ['email' => 'viejo@example.com']);
    $otro = Escenarios::departamento('Ciencias Básicas');
    $hashAntes = $instructor->contrasena;

    $this->actingAs($this->admin)->put("/usuarios/{$instructor->id}", datosBase([
        'email' => 'nuevo@example.com', 'contrasena' => '', 'contrasena_confirmation' => '',
        'id_departamento' => $otro->id_departamento, 'especialidad' => 'Física',
    ]))->assertRedirect();

    $instructor->refresh();
    expect($instructor->email)->toBe('nuevo@example.com')
        ->and($instructor->contrasena)->toBe($hashAntes)
        ->and($instructor->instructor->id_departamento)->toBe($otro->id_departamento);
});

it('cambia la contraseña cuando se envía una nueva', function () {
    $alumno = Escenarios::usuario('alumno');
    $hashAntes = $alumno->contrasena;
    $datos = Alumno::find($alumno->id);

    $this->actingAs($this->admin)->put("/usuarios/{$alumno->id}", datosBase([
        'email' => $alumno->email, 'num_control' => $datos->num_control, 'id_carrera' => $datos->id_carrera,
        'semestre_cursando' => 6, 'contrasena' => 'Nueva2026ok', 'contrasena_confirmation' => 'Nueva2026ok',
    ]))->assertRedirect();

    expect($alumno->fresh()->contrasena)->not->toBe($hashAntes);
});

it('no permite cambiar el rol al editar', function () {
    $instructor = Escenarios::usuario('instructor', $this->sistemas);

    $this->actingAs($this->admin)->put("/usuarios/{$instructor->id}", datosBase([
        'email' => $instructor->email, 'rol' => 'administrador', 'id_departamento' => $this->sistemas->id_departamento,
    ]))->assertRedirect();

    expect($instructor->fresh()->getRoleNames()->all())->toBe(['instructor']);
});

it('activa y desactiva cuentas, pero no la propia', function () {
    $alumno = Escenarios::usuario('alumno');

    $this->actingAs($this->admin)->patch("/usuarios/{$alumno->id}/estado")->assertRedirect();
    expect($alumno->fresh()->activo)->toBeFalse();

    $this->patch("/usuarios/{$alumno->id}/estado");
    expect($alumno->fresh()->activo)->toBeTrue();

    $this->patch("/usuarios/{$this->admin->id}/estado")->assertForbidden();
    expect($this->admin->fresh()->activo)->toBeTrue();
});

it('no deja que el administrador se desactive a sí mismo desde la edición', function () {
    $this->actingAs($this->admin)->put("/usuarios/{$this->admin->id}", datosBase([
        'email' => $this->admin->email, 'activo' => '0', 'contrasena' => '', 'contrasena_confirmation' => '',
    ]))->assertRedirect();

    expect($this->admin->fresh()->activo)->toBeTrue();
});

it('elimina a un instructor junto con su perfil, y no a sí mismo', function () {
    $instructor = Escenarios::usuario('instructor', $this->sistemas);

    $this->actingAs($this->admin)->delete("/usuarios/{$instructor->id}")->assertRedirect(route('usuarios.index'));
    expect(User::find($instructor->id))->toBeNull()->and(Instructor::find($instructor->id))->toBeNull();

    $this->delete("/usuarios/{$this->admin->id}")->assertForbidden();
    expect(User::find($this->admin->id))->not->toBeNull();
});

it('elimina a un coordinador y libera su departamento', function () {
    $coordinador = Escenarios::usuario('coordinador', $this->sistemas);

    $this->actingAs($this->admin)->delete("/usuarios/{$coordinador->id}")->assertRedirect();

    expect(CoordinadorDepartamento::count())->toBe(0);
});

it('filtra por texto y por rol', function () {
    Escenarios::usuario('instructor', $this->sistemas, ['nombre' => 'Quetzal']);
    Escenarios::usuario('alumno', null, ['nombre' => 'Xochitl']);

    $this->actingAs($this->admin)->get('/usuarios?buscar=Quetzal')->assertSee('Quetzal')->assertDontSee('Xochitl');
    $this->get('/usuarios?rol=alumno')->assertSee('Xochitl')->assertDontSee('Quetzal');
});

it('muestra el detalle y los formularios', function () {
    $alumno = Escenarios::usuario('alumno');

    $this->actingAs($this->admin)->get("/usuarios/{$alumno->id}")->assertOk()->assertSee($alumno->email);
    $this->get("/usuarios/{$alumno->id}/edit")->assertOk();
    $this->get('/usuarios/create')->assertOk();
});
