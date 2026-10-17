<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta y edición de carreras (administrador). */
class GuardarCarreraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('carrera') ? 'carreras.editar' : 'carreras.crear');
    }

    public function rules(): array
    {
        $id = $this->route('carrera')?->id_carrera;

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('carrera', 'nombre')->ignore($id, 'id_carrera')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la carrera es obligatorio.',
            'nombre.unique' => 'Ya existe una carrera con ese nombre.',
        ];
    }
}
