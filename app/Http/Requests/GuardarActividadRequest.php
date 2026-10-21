<?php

namespace App\Http\Requests;

use App\Models\Actividad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta y edición de actividades complementarias.
 * Un coordinador solo trabaja en su departamento: el valor del formulario se ignora y se
 * reemplaza por el suyo, así que no puede registrar ni mover una actividad a otro departamento.
 */
class GuardarActividadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actividad = $this->route('actividad');

        return $actividad
            ? $this->user()->can('update', $actividad)
            : $this->user()->can('create', Actividad::class);
    }

    protected function prepareForValidation(): void
    {
        if (! $this->user()->esAdministrador()) {
            $this->merge(['id_departamento' => $this->user()->ambitoDepartamento()]);
        }

        $this->merge(['disponible' => $this->boolean('disponible')]);
    }

    public function rules(): array
    {
        $id = $this->route('actividad')?->id_actividad;

        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('actividad_complementaria', 'nombre')->ignore($id, 'id_actividad')],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'requisitos' => ['nullable', 'string', 'max:2000'],
            'nivel_actividad' => ['nullable', 'string', 'max:50'],
            'creditos' => ['required', 'integer', Rule::in(Actividad::CREDITOS_PERMITIDOS)],
            'disponible' => ['boolean'],
            'id_departamento' => ['required', 'integer', Rule::exists('departamento', 'id_departamento')],
            'carreras' => ['required', 'array', 'min:1'],
            'carreras.*' => ['integer', 'distinct', Rule::exists('carrera', 'id_carrera')],
            'asignar_semestre_vigente' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la actividad es obligatorio.',
            'nombre.unique' => 'Ya existe una actividad con ese nombre.',
            'creditos.required' => 'Indica cuántos créditos vale la actividad.',
            'creditos.in' => 'Los créditos deben ser 1 o 2.',
            'id_departamento.required' => 'Selecciona el departamento de la actividad.',
            'carreras.required' => 'Selecciona al menos una carrera.',
            'carreras.min' => 'Selecciona al menos una carrera.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nivel_actividad' => 'nivel',
            'id_departamento' => 'departamento',
            'creditos' => 'créditos',
        ];
    }
}
