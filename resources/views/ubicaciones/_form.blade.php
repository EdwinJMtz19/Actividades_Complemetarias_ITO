<x-campo name="espacio" label="Nombre del Espacio" :value="$ubicacion?->espacio" placeholder="Ej: Aula A-101, Auditorio Central, Cancha de Fútbol" maxlength="100" required />
<x-campo name="capacidad" type="number" label="Capacidad Máxima" :value="$ubicacion?->capacidad" placeholder="Ej: 30" min="1" max="9999"
         ayuda="Número máximo de personas que puede albergar." required />
