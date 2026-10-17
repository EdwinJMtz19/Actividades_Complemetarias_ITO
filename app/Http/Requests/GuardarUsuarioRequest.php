<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Alta y edición de usuarios (la usa el administrador).
 * El rol se elige solo al crear: los datos de perfil (alumno, instructor,
 * coordinador) quedan ligados a él y a futuros registros (inscripciones, grupos).
 */
class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->route('usuario');

        return $usuario
            ? $this->user()->can('update', $usuario)
            : $this->user()->can('create', User::class);
    }

    /** Rol efectivo: el elegido al crear, o el que ya tiene al editar. */
    public function rolEfectivo(): ?string
    {
        $usuario = $this->route('usuario');

        return $usuario ? $usuario->getRoleNames()->first() : $this->input('rol');
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $id = $usuario?->id;

        $reglas = [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('USUARIO', 'email')->ignore($id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'contrasena' => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'activo' => ['nullable', 'boolean'],
        ];

        if (! $usuario) {
            $reglas['rol'] = ['required', Rule::in(array_keys(User::TIPO_POR_ROL))];
        }

        switch ($this->rolEfectivo()) {
            case 'alumno':
                $reglas += [
                    'num_control' => ['required', 'regex:/^[A-Za-z0-9]{8,9}$/', Rule::unique('alumno', 'num_control')->ignore($id, 'id_alumno')],
                    'id_carrera' => ['required', 'integer', Rule::exists('carrera', 'id_carrera')],
                    'semestre_cursando' => ['required', 'integer', 'between:1,12'],
                ];
                break;

            case 'instructor':
                $reglas += [
                    'id_departamento' => ['required', 'integer', Rule::exists('departamento', 'id_departamento')],
                    'especialidad' => ['nullable', 'string', 'max:100'],
                ];
                break;

            case 'coordinador':
                // Cada departamento tiene un solo coordinador.
                $reglas += [
                    'id_departamento' => [
                        'required', 'integer', Rule::exists('departamento', 'id_departamento'),
                        Rule::unique('coordinador_departamento', 'id_departamento')->ignore($usuario?->coordinacion?->id),
                    ],
                ];
                break;
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'id_departamento.unique' => 'Ese departamento ya tiene un coordinador asignado.',
            'num_control.regex' => 'El número de control debe tener 8 o 9 caracteres alfanuméricos.',
        ];
    }
}
