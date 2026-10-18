<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Escenarios;

/*
 | El movimiento de las pantallas de acceso vive en el marcado (clases `mov-*`, el
 | gancho `data-acceso`) y en la hoja de estilos. Estas pruebas fijan ese contrato:
 | sin ellas, quitar un gancho deja la pantalla igual de válida y más muda.
 */

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Escenarios::base();
});

it('cuelga el gancho de movimiento y la tarjeta que se asienta del layout de invitado', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('data-acceso', false)
        ->assertSee('mov-tarjeta', false);
});

it('marca el envío y el desglose de soporte en el login', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('data-enviar', false)
        ->assertSee('mov-enviar', false)
        ->assertSee('mov-desglose', false);
});

it('marca el envío y la banda de revisión en el registro', function () {
    $this->get('/registro')
        ->assertOk()
        ->assertSee('data-enviar', false)
        ->assertSee('mov-enviar', false);

    $this->post('/registro', [])->assertSessionHasErrors();

    $this->from('/registro')->followingRedirects()->post('/registro', [])
        ->assertOk()
        ->assertSee('mov-banda', false);
});

it('baña la banda de credenciales con el movimiento al rechazar el inicio de sesión', function () {
    $admin = Escenarios::usuario('administrador');

    $this->post('/login', ['email' => $admin->email, 'contrasena' => 'incorrecta'])
        ->assertSessionHasErrors('credenciales');

    $this->from('/login')->followingRedirects()->post('/login', ['email' => $admin->email, 'contrasena' => 'incorrecta'])
        ->assertOk()
        ->assertSee('mov-banda', false);
});

it('marca los motivos de error de campo y el aviso de Bloq Mayús', function () {
    $this->post('/registro', [])->assertSessionHasErrors();

    $this->from('/registro')->followingRedirects()->post('/registro', [])
        ->assertOk()
        ->assertSee('data-error-de-campo', false)
        ->assertSee('data-bloq-mayus', false);
});

it('expone los dos iconos de la contraseña apilados para poder fundirse', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('data-ver-icono="mostrar"', false)
        ->assertSee('data-ver-icono="ocultar"', false)
        ->assertDontSee('data-ver-icono="ocultar" class="hidden"', false);
});

it('declara la ruta de movimiento reducido y no anima el desplazamiento cuando se pide', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('prefers-reduced-motion: reduce')
        ->toContain('@starting-style')
        ->toContain('aria-busy');

    $reducido = Str::after($css, '@media (prefers-reduced-motion: reduce)');

    expect($reducido)
        ->toContain('animation: none')
        ->not->toContain('translateY');
});
