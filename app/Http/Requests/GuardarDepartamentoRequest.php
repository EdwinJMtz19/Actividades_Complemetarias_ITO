<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta y edición de departamentos (administrador). */
class GuardarDepartamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('departamento') ? 'departamentos.editar' : 'departamentos.crear');
    }

    public function rules(): array
    {
        $id = $this->route('departamento')?->id_departamento;

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('departamento', 'nombre')->ignore($id, 'id_departamento')],
            'edificio' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del departamento es obligatorio.',
            'nombre.unique' => 'Ya existe un departamento con ese nombre.',
        ];
    }
}
