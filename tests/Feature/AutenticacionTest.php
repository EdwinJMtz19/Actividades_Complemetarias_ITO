<?php

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\Escenarios;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
});

it('muestra el formulario de inicio de sesión', function () {
    $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
});

it('redirige la raíz al login', function () {
    $this->get('/')->assertRedirect('/login');
});

it('inicia sesión con credenciales válidas y registra el último acceso', function () {
    $usuario = Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    $this->post('/login', ['email' => 'ana@example.com', 'contrasena' => Escenarios::CONTRASENA])
        ->assertRedirect(route('panel'));

    $this->assertAuthenticatedAs($usuario);
    expect($usuario->fresh()->ultimo_acceso)->not->toBeNull();
});

it('guarda la contraseña cifrada en la columna contrasena', function () {
    $usuario = Escenarios::usuario('alumno');

    expect($usuario->contrasena)->not->toBe(Escenarios::CONTRASENA)
        ->and(Hash::check(Escenarios::CONTRASENA, $usuario->contrasena))->toBeTrue();
});

it('rechaza una contraseña incorrecta', function () {
    Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    $this->from('/login')->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'incorrecta'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('no deja entrar a una cuenta desactivada y avisa solo con la contraseña correcta', function () {
    Escenarios::usuario('alumno', null, ['email' => 'baja@example.com', 'activo' => false]);

    $this->post('/login', ['email' => 'baja@example.com', 'contrasena' => Escenarios::CONTRASENA])
        ->assertSessionHasErrors(['email' => 'Tu cuenta está desactivada. Contacta al administrador.']);
    $this->assertGuest();

    $this->post('/login', ['email' => 'baja@example.com', 'contrasena' => 'otra-cosa'])
        ->assertSessionHasErrors(['email' => trans('auth.failed')]);
});

it('bloquea el acceso tras 5 intentos fallidos', function () {
    Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    foreach (range(1, 5) as $_) {
        $this->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'mala']);
    }

    // Aun con la contraseña correcta, queda bloqueado un rato.
    $this->post('/login', ['email' => 'ana@example.com', 'contrasena' => Escenarios::CONTRASENA])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('cierra la sesión', function () {
    $usuario = Escenarios::usuario('alumno');

    $this->actingAs($usuario)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});

it('envía a los invitados al login cuando piden una página protegida', function () {
    $this->get('/panel')->assertRedirect(route('login'));
    $this->get('/usuarios')->assertRedirect(route('login'));
});

it('envía al panel a quien ya inició sesión y abre el login', function () {
    $this->actingAs(Escenarios::usuario('alumno'))->get('/login')->assertRedirect(route('panel'));
});

it('cierra la sesión de una cuenta que fue desactivada mientras estaba conectada', function () {
    $usuario = Escenarios::usuario('alumno');
    $this->actingAs($usuario)->get('/panel')->assertOk();

    $usuario->update(['activo' => false]);

    $this->get('/panel')->assertRedirect(route('login'));
    $this->assertGuest();
});

it('registra a un alumno nuevo con el rol alumno e inicia su sesión', function () {
    $carrera = Escenarios::carrera();

    $this->post('/registro', [
        'nombre' => 'Luis', 'apellido_paterno' => 'Ortiz', 'apellido_materno' => 'Cruz',
        'email' => 'luis@example.com', 'telefono' => '9511234567',
        'num_control' => '20240001', 'id_carrera' => $carrera->id_carrera, 'semestre_cursando' => 3,
        'contrasena' => 'Segura2026', 'contrasena_confirmation' => 'Segura2026',
    ])->assertRedirect(route('panel'));

    $usuario = User::where('email', 'luis@example.com')->firstOrFail();
    expect($usuario->hasRole('alumno'))->toBeTrue()
        ->and($usuario->tipo_usuario)->toBe('alumno')
        ->and(Alumno::find($usuario->id)->num_control)->toBe('20240001');
    $this->assertAuthenticatedAs($usuario);
});

it('no permite elegir el rol al registrarse', function () {
    $carrera = Escenarios::carrera();

    $this->post('/registro', [
        'nombre' => 'Mal', 'apellido_paterno' => 'Intento', 'email' => 'mal@example.com',
        'num_control' => '20240002', 'id_carrera' => $carrera->id_carrera, 'semestre_cursando' => 1,
        'contrasena' => 'Segura2026', 'contrasena_confirmation' => 'Segura2026',
        'rol' => 'administrador', 'tipo_usuario' => 'admin',
    ]);

    $usuario = User::where('email', 'mal@example.com')->firstOrFail();
    expect($usuario->getRoleNames()->all())->toBe(['alumno'])->and($usuario->tipo_usuario)->toBe('alumno');
});

it('valida el registro: correo y número de control únicos, contraseña segura', function () {
    $carrera = Escenarios::carrera();
    $existente = Escenarios::usuario('alumno', null, ['email' => 'repetido@example.com']);

    $this->post('/registro', [
        'nombre' => 'X', 'apellido_paterno' => 'Y', 'email' => 'repetido@example.com',
        'num_control' => Alumno::find($existente->id)->num_control, 'id_carrera' => $carrera->id_carrera,
        'semestre_cursando' => 1, 'contrasena' => 'corta', 'contrasena_confirmation' => 'corta',
    ])->assertSessionHasErrors(['email', 'num_control', 'contrasena']);
});
