<?php

namespace Tests\Support;

use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\CoordinadorDepartamento;
use App\Models\Departamento;
use App\Models\Instructor;
use App\Models\Semestre;
use App\Models\User;
use Database\Seeders\RolesYPermisosSeeder;

/** Datos de apoyo para las pruebas (Sprints 1 y 2). */
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

    /** Semestre válido (agosto–diciembre 2026, inactivo) con los datos que se quieran cambiar. */
    public static function semestre(array $atributos = []): Semestre
    {
        return Semestre::create($atributos + [
            'año' => 2026,
            'periodo' => 2,
            'fecha_inicio' => '2026-08-24',
            'fecha_fin' => '2026-12-11',
            'fecha_inicio_inscripciones' => '2026-08-24',
            'hora_inicio_inscripciones' => '08:00',
            'fecha_fin_inscripciones' => '2026-08-31',
            'hora_fin_inscripciones' => '20:00',
            'status' => Semestre::INACTIVO,
        ]);
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
