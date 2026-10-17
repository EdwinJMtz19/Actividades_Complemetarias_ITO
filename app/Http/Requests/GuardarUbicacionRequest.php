<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta y edición de ubicaciones (administrador). */
class GuardarUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('ubicacion') ? 'ubicaciones.editar' : 'ubicaciones.crear');
    }

    public function rules(): array
    {
        $id = $this->route('ubicacion')?->id_ubicacion;

        return [
            'espacio' => ['required', 'string', 'max:100', Rule::unique('ubicacion', 'espacio')->ignore($id, 'id_ubicacion')],
            'capacidad' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'espacio.required' => 'El nombre del espacio es obligatorio.',
            'espacio.unique' => 'Ya existe una ubicación con ese nombre.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.min' => 'La capacidad debe ser al menos 1.',
        ];
    }
}
