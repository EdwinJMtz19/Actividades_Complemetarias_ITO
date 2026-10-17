<?php

test('la raíz redirige al inicio de sesión', function () {
    $this->get('/')->assertRedirect(route('login'));
});
