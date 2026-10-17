{{-- Formulario compartido de alta y edición de semestres. $semestre es null al crear. --}}
<div class="grid gap-4 sm:grid-cols-3">
    <x-campo name="año" id="anio" type="number" label="Año" :value="$semestre?->año ?? now()->year" min="2000" max="2100" required />
    <x-seleccion name="periodo" label="Periodo" :value="$semestre?->periodo" vacio="-- Selecciona --" required
                 :opciones="[1 => '1 – Enero / Junio', 2 => '2 – Agosto / Diciembre']" />
    <x-seleccion name="status" label="Estatus" :value="$semestre?->status ?? 'activo'" :vacio="null" required
                 :opciones="['activo' => 'Activo', 'inactivo' => $semestre ? 'Inactivo (Finalizar)' : 'Inactivo']"
                 :ayuda="$semestre ? 'Cambia a «Inactivo» para cerrar el periodo.' : null" />
</div>

<hr class="border-slate-200">

{{-- Fechas del semestre --}}
<div>
    <h3 class="text-sm font-bold uppercase tracking-wide text-blue-700">Fechas del Semestre</h3>
    <p class="mt-1 text-xs text-slate-500">La duración debe ser entre 3 y 5 meses.</p>
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <x-campo name="fecha_inicio" type="date" label="Fecha de Inicio" :value="$semestre?->fecha_inicio?->format('Y-m-d')" data-limite-periodo required />
    <x-campo name="fecha_fin" type="date" label="Fecha de Fin" :value="$semestre?->fecha_fin?->format('Y-m-d')" data-limite-periodo required />
</div>
<p id="hint-semestre" class="-mt-2 text-xs"></p>

<hr class="border-slate-200">

{{-- Período de inscripciones --}}
<div>
    <h3 class="text-sm font-bold uppercase tracking-wide text-green-700">Período de Inscripciones</h3>
    <p class="mt-1 text-xs text-slate-500">Duración entre 5 y 10 días. Las fechas deben caer dentro del rango del semestre.</p>
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <x-campo name="fecha_inicio_inscripciones" type="date" label="Fecha de Inicio de Inscripciones" :value="$semestre?->fecha_inicio_inscripciones?->format('Y-m-d')" data-limite-periodo required />
    <x-campo name="fecha_fin_inscripciones" type="date" label="Fecha de Fin de Inscripciones" :value="$semestre?->fecha_fin_inscripciones?->format('Y-m-d')" data-limite-periodo required />
</div>
<p id="hint-inscripcion" class="-mt-2 text-xs"></p>

<div class="grid gap-4 sm:grid-cols-2">
    <x-campo name="hora_inicio_inscripciones" type="time" label="Hora de Inicio de Inscripciones" :value="$semestre?->hora_inicio_corta ?? '08:00'"
             ayuda="Hora en que abren las inscripciones el día de inicio." required />
    <x-campo name="hora_fin_inscripciones" type="time" label="Hora de Fin de Inscripciones" :value="$semestre?->hora_fin_corta ?? '20:00'"
             ayuda="Hora en que cierran las inscripciones el día de fin." required />
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const porId = (id) => document.getElementById(id);
    const anio = porId('anio'), periodo = porId('periodo');
    const inicio = porId('fecha_inicio'), fin = porId('fecha_fin');
    const iniInsc = porId('fecha_inicio_inscripciones'), finInsc = porId('fecha_fin_inscripciones');
    const hintSemestre = porId('hint-semestre'), hintInscripcion = porId('hint-inscripcion');

    const fecha = (valor) => new Date(valor + 'T00:00:00Z');

    // Suma meses sin desbordar el día (31 de enero + 1 mes = 28 o 29 de febrero), igual que el servidor.
    function sumarMeses(base, n) {
        const mes = new Date(Date.UTC(base.getUTCFullYear(), base.getUTCMonth() + n, 1));
        const diasDelMes = new Date(Date.UTC(mes.getUTCFullYear(), mes.getUTCMonth() + 1, 0)).getUTCDate();
        mes.setUTCDate(Math.min(base.getUTCDate(), diasDelMes));
        return mes;
    }

    function mostrar(elemento, ok, texto) {
        elemento.textContent = texto;
        elemento.className = '-mt-2 text-xs ' + (ok ? 'text-green-700' : 'text-red-700');
    }

    // El periodo 1 va de enero a junio y el 2 de agosto a diciembre.
    function actualizarLimites() {
        if (!anio.value || anio.value.length !== 4 || !periodo.value) return;

        const min = periodo.value === '1' ? `${anio.value}-01-01` : `${anio.value}-08-01`;
        const max = periodo.value === '1' ? `${anio.value}-06-30` : `${anio.value}-12-31`;

        document.querySelectorAll('[data-limite-periodo]').forEach(function (campo) {
            campo.min = min;
            campo.max = max;
            if (campo.value && (campo.value < min || campo.value > max)) campo.value = '';
        });
        validarSemestre();
        validarInscripciones();
    }

    // Duración del semestre: de 3 a 5 meses completos.
    function validarSemestre() {
        if (!inicio.value || !fin.value) { hintSemestre.textContent = ''; return; }
        const d1 = fecha(inicio.value), d2 = fecha(fin.value);

        if (d2 < sumarMeses(d1, 3)) mostrar(hintSemestre, false, '⚠ La duración es menor a 3 meses.');
        else if (d2 >= sumarMeses(d1, 6)) mostrar(hintSemestre, false, '⚠ La duración excede los 5 meses.');
        else mostrar(hintSemestre, true, '✓ Duración válida.');
    }

    // Ventana de inscripciones: de 5 a 10 días.
    function validarInscripciones() {
        if (!iniInsc.value || !finInsc.value) { hintInscripcion.textContent = ''; return; }
        const dias = Math.round((fecha(finInsc.value) - fecha(iniInsc.value)) / 86400000);

        if (dias < 5) mostrar(hintInscripcion, false, `⚠ ${dias} día(s). Mínimo 5 días.`);
        else if (dias > 10) mostrar(hintInscripcion, false, `⚠ ${dias} días. Máximo 10 días.`);
        else mostrar(hintInscripcion, true, `✓ ${dias} días. Válido.`);
    }

    anio.addEventListener('input', actualizarLimites);
    periodo.addEventListener('change', actualizarLimites);
    inicio.addEventListener('change', validarSemestre);
    fin.addEventListener('change', validarSemestre);
    iniInsc.addEventListener('change', validarInscripciones);
    finInsc.addEventListener('change', validarInscripciones);

    actualizarLimites();
    validarSemestre();
    validarInscripciones();
});
</script>
@endpush
