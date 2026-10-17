<?php

namespace App\Http\Requests;

use App\Models\Semestre;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta y edición de semestres. Concentra las reglas del Sprint 2:
 *  - duración del semestre: de 3 a 5 meses completos;
 *  - ventana de inscripción: de 5 a 10 días, dentro del semestre;
 *  - un solo periodo activo a la vez y un solo semestre por año y periodo.
 */
class GuardarSemestreRequest extends FormRequest
{
    public const MESES_MINIMOS = 3;

    public const MESES_MAXIMOS = 5;

    public const DIAS_MINIMOS_INSCRIPCION = 5;

    public const DIAS_MAXIMOS_INSCRIPCION = 10;

    public function authorize(): bool
    {
        return $this->user()->can($this->route('semestre') ? 'semestres.editar' : 'semestres.crear');
    }

    /** Primero se cierran los periodos vencidos: uno ya vencido no debe contar como «activo». */
    protected function prepareForValidation(): void
    {
        Semestre::cerrarVencidos();
    }

    public function rules(): array
    {
        $id = $this->route('semestre')?->id_semestre;

        return [
            'año' => ['required', 'integer', 'between:2000,2100'],
            'periodo' => [
                'required', 'in:1,2',
                Rule::unique('semestre', 'periodo')->where('año', $this->input('año'))->ignore($id, 'id_semestre'),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'fecha_inicio_inscripciones' => ['required', 'date'],
            'hora_inicio_inscripciones' => ['required', 'date_format:H:i'],
            'fecha_fin_inscripciones' => ['required', 'date', 'after:fecha_inicio_inscripciones'],
            'hora_fin_inscripciones' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in([Semestre::ACTIVO, Semestre::INACTIVO])],
        ];
    }

    /** Reglas que dependen de varios campos; solo corren si el formato básico es válido. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validarDuracion($validator);
            $this->validarVentanaDeInscripcion($validator);
            $this->validarEstatus($validator);
        }];
    }

    public function messages(): array
    {
        return [
            'periodo.unique' => 'Ya existe un semestre registrado para ese año y periodo.',
            'hora_inicio_inscripciones.required' => 'La hora de inicio de inscripciones es obligatoria.',
            'hora_fin_inscripciones.required' => 'La hora de fin de inscripciones es obligatoria.',
            'hora_inicio_inscripciones.date_format' => 'Formato de hora inválido (HH:MM).',
            'hora_fin_inscripciones.date_format' => 'Formato de hora inválido (HH:MM).',
        ];
    }

    private function validarDuracion(Validator $validator): void
    {
        $inicio = Carbon::parse($this->input('fecha_inicio'))->startOfDay();
        $fin = Carbon::parse($this->input('fecha_fin'))->startOfDay();

        // Meses completos: se acepta de 3 meses exactos hasta antes de cumplir 6.
        if ($fin->lt($inicio->copy()->addMonthsNoOverflow(self::MESES_MINIMOS))) {
            $validator->errors()->add('fecha_fin', 'La duración del semestre debe ser de al menos '.self::MESES_MINIMOS.' meses.');
        } elseif ($fin->gte($inicio->copy()->addMonthsNoOverflow(self::MESES_MAXIMOS + 1))) {
            $validator->errors()->add('fecha_fin', 'La duración del semestre no puede exceder los '.self::MESES_MAXIMOS.' meses.');
        }
    }

    private function validarVentanaDeInscripcion(Validator $validator): void
    {
        $inicio = Carbon::parse($this->input('fecha_inicio'))->startOfDay();
        $fin = Carbon::parse($this->input('fecha_fin'))->startOfDay();
        $iniInsc = Carbon::parse($this->input('fecha_inicio_inscripciones'))->startOfDay();
        $finInsc = Carbon::parse($this->input('fecha_fin_inscripciones'))->startOfDay();

        $dias = (int) $iniInsc->diffInDays($finInsc);

        if ($dias < self::DIAS_MINIMOS_INSCRIPCION) {
            $validator->errors()->add('fecha_fin_inscripciones', 'El período de inscripciones debe durar al menos '.self::DIAS_MINIMOS_INSCRIPCION.' días.');
        } elseif ($dias > self::DIAS_MAXIMOS_INSCRIPCION) {
            $validator->errors()->add('fecha_fin_inscripciones', 'El período de inscripciones no puede exceder los '.self::DIAS_MAXIMOS_INSCRIPCION.' días.');
        }

        if ($iniInsc->lt($inicio) || $finInsc->gt($fin)) {
            $validator->errors()->add('fecha_inicio_inscripciones', 'Las fechas de inscripción deben estar dentro del rango del semestre.');
        }
    }

    private function validarEstatus(Validator $validator): void
    {
        if ($this->input('status') !== Semestre::ACTIVO) {
            return;
        }

        $actual = $this->route('semestre');

        $otroActivo = Semestre::where('status', Semestre::ACTIVO)
            ->when($actual, fn ($q) => $q->where('id_semestre', '!=', $actual->id_semestre))
            ->exists();

        if ($otroActivo) {
            $validator->errors()->add('status', $actual
                ? 'Ya existe otro periodo activo.'
                : 'Ya existe un periodo activo. Ponlo como inactivo antes de crear uno nuevo.');

            return;
        }

        if (Carbon::parse($this->input('fecha_fin'))->lt(now()->startOfDay())) {
            $validator->errors()->add('status', 'No se puede activar un periodo cuya fecha de fin ya pasó.');
        }
    }
}
