<?php

/**
 * Mensajes en español para las reglas que usa el sistema.
 * Cualquier regla no listada aquí cae al inglés (idioma de respaldo).
 */
return [
    'between' => [
        'numeric' => ':Attribute debe estar entre :min y :max.',
        'string' => ':Attribute debe tener entre :min y :max caracteres.',
    ],
    'after' => ':Attribute debe ser una fecha posterior a :date.',
    'boolean' => ':Attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => ':Attribute no es una fecha válida.',
    'date_format' => ':Attribute no tiene el formato :format.',
    'email' => ':Attribute debe ser un correo electrónico válido.',
    'exists' => 'El valor seleccionado para :attribute no es válido.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'integer' => ':Attribute debe ser un número entero.',
    'array' => ':Attribute debe ser una lista.',
    'max' => [
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => ':Attribute debe ser al menos :min.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'string' => ':Attribute debe ser texto.',
    'unique' => ':Attribute ya está registrado.',
    'password' => [
        'letters' => ':Attribute debe contener al menos una letra.',
        'numbers' => ':Attribute debe contener al menos un número.',
        'mixed' => ':Attribute debe contener mayúsculas y minúsculas.',
        'symbols' => ':Attribute debe contener al menos un símbolo.',
    ],

    'attributes' => [
        'nombre' => 'nombre',
        'apellido_paterno' => 'apellido paterno',
        'apellido_materno' => 'apellido materno',
        'email' => 'correo electrónico',
        'telefono' => 'teléfono',
        'contrasena' => 'contraseña',
        'num_control' => 'número de control',
        'id_carrera' => 'carrera',
        'semestre_cursando' => 'semestre',
        'id_departamento' => 'departamento',
        'especialidad' => 'especialidad',
        'rol' => 'rol',
        'permisos' => 'permisos',
        'edificio' => 'edificio',
        'espacio' => 'nombre del espacio',
        'capacidad' => 'capacidad',
        'año' => 'año',
        'periodo' => 'periodo',
        'status' => 'estatus',
        'fecha_inicio' => 'fecha de inicio',
        'fecha_fin' => 'fecha de fin',
        'fecha_inicio_inscripciones' => 'fecha de inicio de inscripciones',
        'fecha_fin_inscripciones' => 'fecha de fin de inscripciones',
        'hora_inicio_inscripciones' => 'hora de inicio de inscripciones',
        'hora_fin_inscripciones' => 'hora de fin de inscripciones',
    ],
];
