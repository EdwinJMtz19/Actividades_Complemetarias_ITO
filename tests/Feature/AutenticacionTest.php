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
        ->assertSessionHasErrors('credenciales')
        ->assertSessionDoesntHaveErrors('email');

    $this->assertGuest();
});

it('muestra el fallo de autenticación en la banda de mensajes y no bajo el campo de correo', function () {
    Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    $respuesta = $this->from('/login')->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'incorrecta']);

    $respuesta->assertRedirect('/login');

    $this->from('/login')->followingRedirects()->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'incorrecta'])
        ->assertOk()
        ->assertSee(trans('auth.failed'))
        ->assertSee('role="alert"', escape: false)
        ->assertDontSee('id="email-error"', escape: false);
});

it('no deja entrar a una cuenta desactivada y avisa solo con la contraseña correcta', function () {
    Escenarios::usuario('alumno', null, ['email' => 'baja@example.com', 'activo' => false]);

    $this->post('/login', ['email' => 'baja@example.com', 'contrasena' => Escenarios::CONTRASENA])
        ->assertSessionHasErrors(['credenciales' => 'Tu cuenta está desactivada. Contacta al administrador.']);
    $this->assertGuest();

    $this->post('/login', ['email' => 'baja@example.com', 'contrasena' => 'otra-cosa'])
        ->assertSessionHasErrors(['credenciales' => trans('auth.failed')]);
});

it('bloquea el acceso tras 5 intentos fallidos', function () {
    Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    foreach (range(1, 5) as $_) {
        $this->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'mala']);
    }

    // Aun con la contraseña correcta, queda bloqueado un rato.
    $this->post('/login', ['email' => 'ana@example.com', 'contrasena' => Escenarios::CONTRASENA])
        ->assertSessionHasErrors('credenciales');
    $this->assertGuest();
});

it('avisa del bloqueo en la banda de mensajes, no bajo el campo de correo', function () {
    Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    foreach (range(1, 5) as $_) {
        $this->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'mala']);
    }

    $this->from('/login')->followingRedirects()->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'mala'])
        ->assertOk()
        ->assertSee('Demasiados intentos de acceso');
});

it('deja sin errores de campo los fallos de autenticación y conserva el correo escrito', function () {
    Escenarios::usuario('alumno', null, ['email' => 'ana@example.com']);

    $this->from('/login')->followingRedirects()->post('/login', ['email' => 'ana@example.com', 'contrasena' => 'mala'])
        ->assertOk()
        ->assertSee('value="ana@example.com"', escape: false)
        ->assertDontSee('id="email-error"', escape: false);
});

it('ofrece una salida al que no puede entrar, con el correo de soporte cuando está configurado', function () {
    config(['contacto.soporte_email' => 'soporte@ito.mx']);

    $this->get('/login')
        ->assertOk()
        ->assertSee('¿No puedo entrar?')
        ->assertSee('mailto:soporte@ito.mx', escape: false);

    config(['contacto.soporte_email' => null]);

    $this->get('/login')
        ->assertOk()
        ->assertSee('¿No puedo entrar?')
        ->assertSee('administrador del sistema')
        ->assertDontSee('mailto:', escape: false);
});

it('desactiva la validación nativa del navegador para que los errores sean los del servidor', function () {
    $this->get('/login')->assertOk()->assertSee('novalidate', escape: false);
    $this->get('/registro')->assertOk()->assertSee('novalidate', escape: false);
});

it('ofrece mostrar contraseña y aviso de Bloq Mayús en las pantallas de acceso', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('data-ver-contrasena="contrasena"', escape: false)
        ->assertSee('aria-pressed="false"', escape: false)
        ->assertSee('id="contrasena-bloq-mayus"', escape: false)
        ->assertSee('El Bloq Mayús está activado.');

    $this->get('/registro')
        ->assertOk()
        ->assertSee('data-ver-contrasena="contrasena"', escape: false)
        ->assertSee('id="contrasena-bloq-mayus"', escape: false);
});

it('deja el campo de contraseña oculto por defecto y separado del botón Entrar', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('type="password"', escape: false)
        ->assertSee('name="remember"', escape: false)
        ->assertSee('mt-6 flex items-center gap-2', escape: false);
});

it('conecta el error del campo con su etiqueta y marca el campo como inválido', function () {
    $this->from('/registro')->followingRedirects()->post('/registro', ['nombre' => 'X', 'apellido_paterno' => 'Y'])
        ->assertOk()
        ->assertSee('for="email"', escape: false)
        ->assertSee('aria-invalid="true"', escape: false)
        ->assertSee('aria-describedby="email-error"', escape: false)
        ->assertSee('id="email-error"', escape: false)
        ->assertSee('aria-describedby="num_control-ayuda num_control-error"', escape: false);
});

it('conecta también el error de la selección de carrera con su etiqueta', function () {
    $this->from('/registro')->followingRedirects()->post('/registro', ['nombre' => 'X', 'apellido_paterno' => 'Y'])
        ->assertOk()
        ->assertSee('for="id_carrera"', escape: false)
        ->assertSee('aria-describedby="id_carrera-error"', escape: false);
});

it('muestra un aviso de revisión en el registro cuando hay errores de campo', function () {
    $this->from('/registro')->followingRedirects()->post('/registro', ['nombre' => 'X'])
        ->assertOk()
        ->assertSee('Revisa los campos marcados');
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
