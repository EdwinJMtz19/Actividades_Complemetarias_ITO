<?php

namespace Tests\Support;

use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\CoordinadorDepartamento;
use App\Models\Departamento;
use App\Models\Instructor;
use App\Models\User;
use Database\Seeders\RolesYPermisosSeeder;

/** Datos de apoyo para las pruebas del Sprint 1. */
class Escenarios
{
    public const CONTRASENA = 'Actividades2026';

    public static function base(): void
    {
        (new RolesYPermisosSeeder)->run();
    }

    public static function departamento(string $nombre): Departamento
    {
        return Departamento::firstOrCreate(['nombre' => $nombre], ['edificio' => 'Edificio X']);
    }

    public static function carrera(string $nombre = 'Ingeniería en Sistemas Computacionales'): Carrera
    {
        return Carrera::firstOrCreate(['nombre' => $nombre]);
    }

    /** Crea un usuario con su rol y el perfil que le corresponde. */
    public static function usuario(string $rol, ?Departamento $departamento = null, array $atributos = []): User
    {
        $usuario = User::factory()->create($atributos + ['tipo_usuario' => User::TIPO_POR_ROL[$rol]]);
        $usuario->assignRole($rol);

        match ($rol) {
            'alumno' => Alumno::create([
                'id_alumno' => $usuario->id,
                'num_control' => fake()->unique()->numerify('2023####'),
                'id_carrera' => self::carrera()->id_carrera,
                'semestre_cursando' => 5,
            ]),
            'instructor' => Instructor::create([
                'id_instructor' => $usuario->id,
                'id_departamento' => ($departamento ?? self::departamento('Sistemas'))->id_departamento,
            ]),
            'coordinador' => $departamento
                ? CoordinadorDepartamento::create(['id_usuario' => $usuario->id, 'id_departamento' => $departamento->id_departamento])
                : null,
            default => null,
        };

        return $usuario;
    }
}
