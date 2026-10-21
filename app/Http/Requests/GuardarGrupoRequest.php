<?php

namespace App\Http\Requests;

use App\Models\Actividad;
use App\Models\Grupo;
use App\Models\Instructor;
use App\Models\Semestre;
use App\Services\ServicioConflictos;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta y edición de grupos con sus horarios (HU-15).
 *  - El grupo nace en el semestre activo y conserva ese semestre al editarse.
 *  - Las fechas del grupo caen dentro del semestre.
 *  - El instructor es del departamento de la actividad.
 *  - Los horarios no chocan con nada: lo decide ServicioConflictos (HU-16), que vuelve a revisar aquí
 *    aunque el aviso en vivo ya lo haya hecho.
 */
class GuardarGrupoRequest extends FormRequest
{
    private ?Semestre $semestre = null;

    public function authorize(): bool
    {
        $grupo = $this->route('grupo');

        return $grupo
            ? $this->user()->can('update', $grupo)
            : $this->user()->can('create', Grupo::class);
    }

    protected function prepareForValidation(): void
    {
        // Si el semestre activo ya venció, no debe contar como activo.
        Semestre::cerrarVencidos();

        $horarios = collect($this->input('horarios', []))
            ->filter(fn ($h) => is_array($h))
            ->map(fn ($h) => [
                'id_dia' => $h['id_dia'] ?? null,
                'hora_inicio' => isset($h['hora_inicio']) ? substr($h['hora_inicio'], 0, 5) : null,
                'hora_fin' => isset($h['hora_fin']) ? substr($h['hora_fin'], 0, 5) : null,
            ])
            ->values()
            ->all();

        $this->merge([
            // El nombre del grupo siempre va en mayúsculas: «a» y «A» son el mismo grupo.
            'grupo' => mb_strtoupper(trim((string) $this->input('grupo'))),
            'horarios' => $horarios,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('grupo')?->id_grupo;
        $requiereUbicacion = $this->input('modalidad') !== 'virtual';

        return [
            'id_actividad' => ['required', 'integer', Rule::exists('actividad_complementaria', 'id_actividad')],
            'grupo' => [
                'required', 'string', 'max:10',
                Rule::unique('grupo', 'grupo')->where('id_actividad', $this->input('id_actividad'))->ignore($id, 'id_grupo'),
            ],
            'cupo_minimo' => ['required', 'integer', 'min:1'],
            'cupo_maximo' => ['required', 'integer', 'min:1', 'gte:cupo_minimo'],
            'modalidad' => ['required', Rule::in(array_keys(Grupo::MODALIDADES))],
            'id_ubicacion' => [Rule::requiredIf($requiereUbicacion), 'nullable', 'integer', Rule::exists('ubicacion', 'id_ubicacion')],
            'id_instructor' => ['nullable', 'integer', Rule::exists('instructor', 'id_instructor')],
            'estatus' => ['required', Rule::in(array_keys(Grupo::ESTATUS))],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'materiales_requeridos' => ['nullable', 'string', 'max:2000'],
            'horarios' => ['required', 'array', 'min:1'],
            'horarios.*.id_dia' => ['required', 'integer', Rule::exists('dia_semana', 'id_dia')],
            'horarios.*.hora_inicio' => ['required', 'date_format:H:i'],
            'horarios.*.hora_fin' => ['required', 'date_format:H:i', 'after:horarios.*.hora_inicio'],
        ];
    }

    /** Reglas que dependen de varios campos; solo corren si el formato básico es válido. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $actividad = Actividad::find($this->input('id_actividad'));
            $semestre = $this->semestreDelGrupo();

            $this->validarActividad($validator, $actividad, $semestre);

            if ($semestre === null) {
                return;
            }

            $this->validarFechas($validator, $semestre);
            $this->validarInstructor($validator, $actividad);

            foreach ($this->conflictos($semestre) as $conflicto) {
                $validator->errors()->add($conflicto['campo'], $conflicto['mensaje']);
            }
        }];
    }

    public function messages(): array
    {
        return [
            'id_actividad.required' => 'Selecciona la actividad del grupo.',
            'grupo.required' => 'El nombre del grupo es obligatorio.',
            'grupo.unique' => 'Esa actividad ya tiene un grupo con ese nombre.',
            'cupo_maximo.gte' => 'El cupo máximo no puede ser menor que el cupo mínimo.',
            'id_ubicacion.required' => 'Selecciona la ubicación: los grupos presenciales e híbridos la necesitan.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'horarios.required' => 'Agrega al menos un horario.',
            'horarios.min' => 'Agrega al menos un horario.',
            'horarios.*.id_dia.required' => 'Selecciona el día del horario.',
            'horarios.*.hora_inicio.required' => 'La hora de inicio del horario es obligatoria.',
            'horarios.*.hora_inicio.date_format' => 'La hora de inicio no tiene el formato HH:MM.',
            'horarios.*.hora_fin.required' => 'La hora de fin del horario es obligatoria.',
            'horarios.*.hora_fin.date_format' => 'La hora de fin no tiene el formato HH:MM.',
            'horarios.*.hora_fin.after' => 'La hora de fin debe ser posterior a la de inicio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_actividad' => 'actividad',
            'id_ubicacion' => 'ubicación',
            'id_instructor' => 'instructor',
            'cupo_minimo' => 'cupo mínimo',
            'cupo_maximo' => 'cupo máximo',
            'materiales_requeridos' => 'materiales requeridos',
        ];
    }

    /** El semestre en que vive el grupo: el suyo si ya existe; si no, el activo (null si no hay). */
    public function semestreDelGrupo(): ?Semestre
    {
        return $this->semestre ??= $this->route('grupo')?->semestre ?? Semestre::activo();
    }

    /** Datos del grupo listos para guardar (sin horarios, con el semestre y sin ubicación si es virtual). */
    public function datosGrupo(): array
    {
        $datos = collect($this->validated())->except('horarios')->all();

        if ($datos['modalidad'] === 'virtual') {
            $datos['id_ubicacion'] = null;
        }

        return $datos + ['id_semestre' => $this->semestreDelGrupo()->id_semestre];
    }

    /** Horarios listos para guardar: horas con segundos (HH:MM:SS), igual que en la base. */
    public function horariosParaGuardar(): array
    {
        return array_map(fn ($h) => [
            'id_dia' => (int) $h['id_dia'],
            'hora_inicio' => $h['hora_inicio'].':00',
            'hora_fin' => $h['hora_fin'].':00',
        ], $this->validated('horarios'));
    }

    private function validarActividad(Validator $validator, ?Actividad $actividad, ?Semestre $semestre): void
    {
        $usuario = $this->user();

        if (! $usuario->esAdministrador() && (int) $actividad->id_departamento !== (int) $usuario->ambitoDepartamento()) {
            $validator->errors()->add('id_actividad', 'La actividad no pertenece a tu departamento.');

            return;
        }

        if ($semestre === null) {
            $validator->errors()->add('id_actividad', 'No hay un periodo escolar activo: no se pueden crear ni editar grupos.');

            return;
        }

        // Al crear, la actividad debe ser del semestre vigente (o no tener semestre todavía).
        if ($this->route('grupo') === null && $actividad->id_semestre !== null && (int) $actividad->id_semestre !== $semestre->id_semestre) {
            $validator->errors()->add('id_actividad', 'La actividad pertenece a otro periodo escolar. Asígnala al semestre vigente desde su edición.');
        }
    }

    private function validarFechas(Validator $validator, Semestre $semestre): void
    {
        $rango = $semestre->fecha_inicio->format('d/m/Y').' al '.$semestre->fecha_fin->format('d/m/Y');

        if ($this->date('fecha_inicio')->startOfDay()->lt($semestre->fecha_inicio)) {
            $validator->errors()->add('fecha_inicio', "El grupo no puede empezar antes del semestre (del {$rango}).");
        }

        if ($this->date('fecha_fin')->startOfDay()->gt($semestre->fecha_fin)) {
            $validator->errors()->add('fecha_fin', "El grupo no puede terminar después del semestre (del {$rango}).");
        }
    }

    private function validarInstructor(Validator $validator, Actividad $actividad): void
    {
        if (! $this->filled('id_instructor')) {
            return;
        }

        $instructor = Instructor::find($this->input('id_instructor'));

        if ((int) $instructor->id_departamento !== (int) $actividad->id_departamento) {
            $validator->errors()->add('id_instructor', 'El instructor debe pertenecer al departamento de la actividad.');
        }
    }

    private function conflictos(Semestre $semestre): array
    {
        return app(ServicioConflictos::class)->verificar(
            [
                'id_semestre' => $semestre->id_semestre,
                'id_instructor' => $this->input('id_instructor'),
                'id_ubicacion' => $this->input('id_ubicacion'),
                'modalidad' => $this->input('modalidad'),
                'cupo_maximo' => $this->input('cupo_maximo'),
                'estatus' => $this->input('estatus'),
            ],
            $this->input('horarios', []),
            $this->route('grupo')?->id_grupo,
        );
    }
}
