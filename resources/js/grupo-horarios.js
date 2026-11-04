//
// Formulario de grupos (HU-15 y HU-16), sin librerías externas.
//  - Rejilla semanal de bloques de una hora: marcar y desmarcar sin recargar la página.
//  - Mostrar solo los instructores del departamento de la actividad elegida.
//  - Modalidad virtual: la ubicación no aplica.
//  - Avisar en vivo de conflictos de horario. Es una ayuda: la decisión final la toma el servidor
//    al guardar, con el mismo servicio (ServicioConflictos).
//

const ESPERA_MS = 400;

const iniciar = (contenedor) => {
    const formulario = contenedor.closest('form');
    const aviso = contenedor.querySelector('[data-conflictos]');
    const campo = (selector) => formulario.querySelector(selector);

    const actividad = campo('[data-actividad]');
    const instructor = campo('[data-instructor]');
    const modalidad = campo('[data-modalidad]');
    const ubicacion = campo('[data-ubicacion]');
    const cupo = campo('[data-cupo]');
    const estatus = campo('[data-estatus]');
    const token = campo('input[name="_token"]')?.value ?? '';

    let temporizador = null;
    let controlador = null;
    let secuencia = 0;

    // ── Rejilla semanal ─────────────────────────────────────────────────

    const rejilla = contenedor.querySelector('[data-rejilla]');
    const celdas = rejilla ? [...rejilla.querySelectorAll('[data-slot]')] : [];
    const horas = [...new Set(celdas.map((celda) => celda.dataset.hora))];
    const dias = [...new Set(celdas.map((celda) => celda.dataset.dia))];
    const ocultos = contenedor.querySelector('[data-horarios]');
    const avisoRejilla = contenedor.querySelector('[data-rejilla-aviso]');
    const iniciales = JSON.parse(contenedor.querySelector('[data-horarios-iniciales]')?.textContent || '[]');

    const estado = new Map();
    const clave = (dia, hora) => `${dia}|${hora}`;
    const sumarHora = (hora) => `${String(Number(hora.slice(0, 2)) + 1).padStart(2, '0')}:00`;
    const alineada = (hora) => /^\d{2}:00$/.test(hora);

    let ajustados = 0;

    for (const intervalo of iniciales) {
        const inicio = String(intervalo.hora_inicio ?? '').slice(0, 5);
        const fin = String(intervalo.hora_fin ?? '').slice(0, 5);
        // Un bloque entra si se traslapa con el intervalo, aunque no empiece en punto.
        const cubiertos = horas.filter((hora) => hora < fin && sumarHora(hora) > inicio);

        if (cubiertos.length === 0) {
            ajustados++;

            continue;
        }

        // Un intervalo es exacto si empieza y termina en punto y cabe en la rejilla.
        const exacto = alineada(inicio) && alineada(fin)
            && horas.includes(inicio) && (horas.includes(fin) || fin === '20:00');

        if (!exacto) {
            ajustados++;
        }

        for (const hora of cubiertos) {
            estado.set(clave(intervalo.id_dia, hora), true);
        }
    }

    if (ajustados > 0) {
        avisoRejilla.textContent = 'Algunos horarios no coinciden con bloques exactos de una hora: al guardar se ajustarán a los bloques marcados.';
        avisoRejilla.classList.remove('hidden');
    }

    for (const celda of celdas) {
        celda.setAttribute('aria-pressed', String(estado.has(clave(celda.dataset.dia, celda.dataset.hora))));
    }

    const actualizarOcultos = () => {
        ocultos.replaceChildren();

        let indice = 0;

        for (const hora of horas) {
            for (const dia of dias) {
                if (!estado.get(clave(dia, hora))) {
                    continue;
                }

                const campos = {
                    id_dia: dia,
                    hora_inicio: hora,
                    hora_fin: sumarHora(hora),
                };

                for (const [nombre, valor] of Object.entries(campos)) {
                    const entrada = document.createElement('input');

                    entrada.type = 'hidden';
                    entrada.name = `horarios[${indice}][${nombre}]`;
                    entrada.value = valor;
                    ocultos.append(entrada);
                }

                indice++;
            }
        }
    };

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

    const datosActuales = () => {
        const horarios = [];

        for (const entrada of ocultos.querySelectorAll('input[name$="[id_dia]"]')) {
            const base = entrada.name.slice(0, -'[id_dia]'.length);

            horarios.push({
                id_dia: entrada.value || null,
                hora_inicio: ocultos.querySelector(`input[name="${base}[hora_inicio]"]`)?.value || null,
                hora_fin: ocultos.querySelector(`input[name="${base}[hora_fin]"]`)?.value || null,
            });
        }

        return {
            id_grupo: contenedor.dataset.grupo || null,
            id_instructor: instructor.value || null,
            id_ubicacion: ubicacion.disabled ? null : ubicacion.value || null,
            modalidad: modalidad.value || null,
            cupo_maximo: cupo.value || null,
            estatus: estatus.value || null,
            horarios,
        };
    };

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

    // ── Interacción con la rejilla ──────────────────────────────────────

    contenedor.addEventListener('click', (evento) => {
        const celda = evento.target.closest('[data-slot]');

        if (celda) {
            const llave = clave(celda.dataset.dia, celda.dataset.hora);
            const activa = !estado.get(llave);

            if (activa) {
                estado.set(llave, true);
            } else {
                estado.delete(llave);
            }

            celda.setAttribute('aria-pressed', String(activa));
            actualizarOcultos();
            programar();

            return;
        }

        if (evento.target.closest('[data-limpiar-horario]')) {
            estado.clear();
            celdas.forEach((celda) => celda.setAttribute('aria-pressed', 'false'));
            actualizarOcultos();
            programar();
        }
    });

    const relevantes = '[data-instructor], [data-ubicacion], [data-modalidad], [data-cupo], [data-estatus]';

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
        if (evento.target.matches('[data-cupo]')) {
            programar();
        }
    });

    filtrarInstructores();
    ajustarUbicacion();
    actualizarOcultos();
    verificar();
};

document.querySelectorAll('[data-grupo-form]').forEach(iniciar);
