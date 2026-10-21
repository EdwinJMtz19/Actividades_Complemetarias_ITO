<?php

namespace App\Http\Requests;

use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos parciales de un grupo en captura, para el aviso de conflictos en vivo. Todo es opcional:
 * mientras la persona llena el formulario, los campos aparecen de uno en uno.
 */
class VerificarHorariosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('grupos.crear') || $this->user()->can('grupos.editar');
    }

    public function rules(): array
    {
        return [
            'id_grupo' => ['nullable', 'integer', Rule::exists('grupo', 'id_grupo')],
            'id_instructor' => ['nullable', 'integer'],
            'id_ubicacion' => ['nullable', 'integer'],
            'modalidad' => ['nullable', Rule::in(array_keys(Grupo::MODALIDADES))],
            'cupo_maximo' => ['nullable', 'integer', 'min:0'],
            'estatus' => ['nullable', Rule::in(array_keys(Grupo::ESTATUS))],
            'horarios' => ['nullable', 'array'],
            'horarios.*' => ['array'],
            'horarios.*.id_dia' => ['nullable', 'integer'],
            'horarios.*.hora_inicio' => ['nullable', 'date_format:H:i'],
            'horarios.*.hora_fin' => ['nullable', 'date_format:H:i'],
        ];
    }
}
