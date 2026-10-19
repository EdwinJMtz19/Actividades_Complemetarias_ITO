//
// Comportamiento mínimo de las pantallas de acceso, sin librerías externas.
//  - Mostrar / ocultar contraseña.
//  - Avisar cuando el Bloq Mayús está activado.
//  - Acusar recibo del envío y evitar el doble envío.
//

document.addEventListener('click', (evento) => {
    const boton = evento.target.closest('[data-ver-contrasena]');

    if (!boton) {
        return;
    }

    const campo = document.getElementById(boton.dataset.verContrasena);

    if (!campo) {
        return;
    }

    const visible = campo.type === 'text';

    campo.type = visible ? 'password' : 'text';
    boton.setAttribute('aria-pressed', String(!visible));
    boton.querySelector('[data-ver-texto]').textContent = visible ? 'Ocultar contraseña' : 'Mostrar contraseña';
    // Los iconos se apilan: se ocultan por opacidad para que el cambio sea un fundido.
    boton.querySelector('[data-ver-icono="mostrar"]').classList.toggle('opacity-0', !visible);
    boton.querySelector('[data-ver-icono="ocultar"]').classList.toggle('opacity-0', visible);
    campo.focus();
});

document.addEventListener('submit', (evento) => {
    const boton = evento.target.querySelector('[data-enviar]');

    if (!boton) {
        return;
    }

    // El botón se marca ocupado y deja de aceptar clics: el texto no cambia, solo su estado.
    boton.setAttribute('aria-busy', 'true');
    boton.disabled = true;
});

const avisarBloqMayus = (evento) => {
    const aviso = document.getElementById(`${evento.target.id}-bloq-mayus`);

    if (aviso) {
        aviso.hidden = !evento.getModifierState('CapsLock');
    }
};

document.addEventListener('keyup', avisarBloqMayus);
document.addEventListener('keydown', avisarBloqMayus);
