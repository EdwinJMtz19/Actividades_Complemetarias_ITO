//
// Formulario de grupos (HU-15 y HU-16), sin librerías externas.
//  - Agregar y quitar bloques de horario sin recargar la página.
//  - Mostrar solo los instructores del departamento de la actividad elegida.
//  - Modalidad virtual: la ubicación no aplica.
//  - Avisar en vivo de conflictos de horario. Es una ayuda: la decisión final la toma el servidor
//    al guardar, con el mismo servicio (ServicioConflictos).
//

const ESPERA_MS = 400;

const iniciar = (contenedor) => {
    const formulario = contenedor.closest('form');
    const lista = contenedor.querySelector('[data-horarios]');
    const plantilla = contenedor.querySelector('[data-plantilla-horario]');
    const aviso = contenedor.querySelector('[data-conflictos]');
    const campo = (selector) => formulario.querySelector(selector);

    const actividad = campo('[data-actividad]');
    const instructor = campo('[data-instructor]');
    const modalidad = campo('[data-modalidad]');
    const ubicacion = campo('[data-ubicacion]');
    const cupo = campo('[data-cupo]');
    const estatus = campo('[data-estatus]');
    const token = campo('input[name="_token"]')?.value ?? '';

    let siguiente = lista.querySelectorAll('[data-horario]').length;
    let temporizador = null;
    let controlador = null;
    let secuencia = 0;

    // ── Bloques de horario ──────────────────────────────────────────────

    const agregarBloque = () => {
        lista.insertAdjacentHTML('beforeend', plantilla.innerHTML.replaceAll('__INDEX__', String(siguiente++)));
    };

    contenedor.addEventListener('click', (evento) => {
        if (evento.target.closest('[data-agregar-horario]')) {
            agregarBloque();
            programar();

            return;
        }

        const quitar = evento.target.closest('[data-quitar-horario]');

        if (!quitar) {
            return;
        }

        quitar.closest('[data-horario]').remove();

        // Siempre queda al menos un bloque para capturar.
        if (lista.querySelectorAll('[data-horario]').length === 0) {
            agregarBloque();
        }

        programar();
    });

    // ── Campos que dependen de otros ────────────────────────────────────

    const filtrarInstructores = () => {
        const departamento = actividad.selectedOptions[0]?.dataset.departamento;

        for (const opcion of instructor.options) {
            if (!opcion.value) {
                continue;
            }

            const coincide = !departamento || opcion.dataset.departamento === departamento;

            opcion.hidden = !coincide;
            opcion.disabled = !coincide;
        }

        if (instructor.selectedOptions[0]?.disabled) {
            instructor.value = '';
        }
    };

    const ajustarUbicacion = () => {
        const virtual = modalidad.value === 'virtual';

        ubicacion.disabled = virtual;

        if (virtual) {
            ubicacion.value = '';
        }
    };

    // ── Aviso de conflictos en vivo ─────────────────────────────────────

    const datosActuales = () => ({
        id_grupo: contenedor.dataset.grupo || null,
        id_instructor: instructor.value || null,
        id_ubicacion: ubicacion.disabled ? null : ubicacion.value || null,
        modalidad: modalidad.value || null,
        cupo_maximo: cupo.value || null,
        estatus: estatus.value || null,
        horarios: [...lista.querySelectorAll('[data-horario]')].map((bloque) => ({
            id_dia: bloque.querySelector('[data-campo="id_dia"]').value || null,
            hora_inicio: bloque.querySelector('[data-campo="hora_inicio"]').value || null,
            hora_fin: bloque.querySelector('[data-campo="hora_fin"]').value || null,
        })),
    });

    // Con las horas en HH:MM la comparación de texto equivale a la de tiempo.
    const hayHorarioCompleto = (datos) =>
        datos.horarios.some((h) => h.id_dia && h.hora_inicio && h.hora_fin && h.hora_fin > h.hora_inicio);

    const pintar = (resultado) => {
        aviso.replaceChildren();

        if (!resultado) {
            return;
        }

        const mensaje = (clases, texto) => {
            const parrafo = document.createElement('p');

            parrafo.className = clases;
            parrafo.textContent = texto;
            aviso.append(parrafo);
        };

        if (resultado.error) {
            mensaje('text-slate-500', 'No se pudo comprobar ahora; el servidor lo revisará al guardar.');

            return;
        }

        if (resultado.aviso) {
            mensaje('text-slate-500', resultado.aviso);

            return;
        }

        if (!resultado.hayConflicto) {
            mensaje('text-green-700', '✓ Sin conflictos de horario.');

            return;
        }

        const caja = document.createElement('div');
        const titulo = document.createElement('p');
        const items = document.createElement('ul');

        caja.className = 'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800';
        titulo.className = 'font-semibold';
        titulo.textContent = 'Hay conflictos de horario:';
        items.className = 'mt-1 list-disc space-y-1 pl-5';

        for (const conflicto of resultado.conflictos) {
            const item = document.createElement('li');

            item.textContent = conflicto.mensaje;
            items.append(item);
        }

        caja.append(titulo, items);
        aviso.append(caja);
    };

    const verificar = async () => {
        const datos = datosActuales();

        if (!hayHorarioCompleto(datos)) {
            secuencia++;
            controlador?.abort();
            pintar(null);

            return;
        }

        const mia = ++secuencia;

        controlador?.abort();
        controlador = new AbortController();

        try {
            const respuesta = await fetch(contenedor.dataset.url, {
                method: 'POST',
                signal: controlador.signal,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify(datos),
            });

            if (!respuesta.ok) {
                throw new Error(String(respuesta.status));
            }

            const resultado = await respuesta.json();

            // Una respuesta lenta de una captura anterior no debe pisar a la más reciente.
            if (mia === secuencia) {
                pintar(resultado);
            }
        } catch (error) {
            if (error.name !== 'AbortError' && mia === secuencia) {
                pintar({ error: true });
            }
        }
    };

    function programar() {
        clearTimeout(temporizador);
        temporizador = setTimeout(verificar, ESPERA_MS);
    }

    const relevantes = '[data-horario] input, [data-horario] select, [data-instructor], [data-ubicacion], [data-modalidad], [data-cupo], [data-estatus]';

    formulario.addEventListener('change', (evento) => {
        if (evento.target.matches('[data-actividad]')) {
            filtrarInstructores();
        }

        if (evento.target.matches('[data-modalidad]')) {
            ajustarUbicacion();
        }

        if (evento.target.matches(relevantes)) {
            programar();
        }
    });

    formulario.addEventListener('input', (evento) => {
        if (evento.target.matches('[data-cupo], [data-horario] input')) {
            programar();
        }
    });

    filtrarInstructores();
    ajustarUbicacion();
    verificar();
};

document.querySelectorAll('[data-grupo-form]').forEach(iniciar);
