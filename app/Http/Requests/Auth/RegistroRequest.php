<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Autorregistro: únicamente para alumnos. */
class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('USUARIO', 'email')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'num_control' => ['required', 'regex:/^[A-Za-z0-9]{8,9}$/', Rule::unique('alumno', 'num_control')],
            'id_carrera' => ['required', 'integer', Rule::exists('carrera', 'id_carrera')],
            'semestre_cursando' => ['required', 'integer', 'between:1,12'],
            'contrasena' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
