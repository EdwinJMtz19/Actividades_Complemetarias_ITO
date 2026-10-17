<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Cuenta de acceso (tabla USUARIO). El rol vive en Spatie; la columna
 * tipo_usuario se mantiene sincronizada con él al crear el usuario.
 */
#[Table(name: 'USUARIO')]
#[Fillable([
    'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'contrasena',
    'tipo_usuario', 'telefono', 'activo', 'ultimo_acceso',
])]
#[Hidden(['contrasena', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /** Rol (Spatie) => valor del enum tipo_usuario. */
    public const TIPO_POR_ROL = [
        'administrador' => 'admin',
        'coordinador' => 'coordinador',
        'instructor' => 'instructor',
        'alumno' => 'alumno',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
            'activo' => 'boolean',
            'contrasena' => 'hashed',
        ];
    }

    // La contraseña se guarda en la columna "contrasena", no en "password".
    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumno::class, 'id_alumno');
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class, 'id_instructor');
    }

    public function coordinacion(): HasOne
    {
        return $this->hasOne(CoordinadorDepartamento::class, 'id_usuario');
    }

    public function esAdministrador(): bool
    {
        return $this->hasRole('administrador');
    }

    public function esCoordinador(): bool
    {
        return $this->hasRole('coordinador');
    }

    /**
     * Departamento que delimita el acceso del usuario:
     * el que coordina (coordinador) o al que pertenece (instructor).
     * Null si no tiene uno asignado.
     */
    public function departamentoId(): ?int
    {
        if ($this->esCoordinador()) {
            return $this->coordinacion?->id_departamento;
        }

        return $this->instructor?->id_departamento;
    }

    /**
     * Si el usuario solo puede ver datos de su departamento, devuelve su id;
     * el administrador no tiene restricción (null).
     */
    public function ambitoDepartamento(): ?int
    {
        return $this->esAdministrador() ? null : $this->departamentoId();
    }
}
