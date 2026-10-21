<?php

namespace Tests\Support;

use App\Models\Actividad;
use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\CoordinadorDepartamento;
use App\Models\Departamento;
use App\Models\Grupo;
use App\Models\Instructor;
use App\Models\Semestre;
use App\Models\User;
use Database\Seeders\DiasSemanaSeeder;
use Database\Seeders\RolesYPermisosSeeder;

/** Datos de apoyo para las pruebas (Sprints 1 a 3). */
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

    /** Los seis días de la semana (1 = lunes … 6 = sábado), igual que en producción. */
    public static function dias(): void
    {
        (new DiasSemanaSeeder)->run();
    }

    /** Actividad del departamento de Sistemas, dirigida a una carrera, con los datos que se quieran cambiar. */
    public static function actividad(array $atributos = []): Actividad
    {
        $actividad = Actividad::create($atributos + [
            'nombre' => 'Actividad '.fake()->unique()->numberBetween(1, 99999),
            'id_departamento' => self::departamento('Sistemas')->id_departamento,
            'creditos' => 1,
            'disponible' => true,
        ]);

        $actividad->carreras()->attach(self::carrera()->id_carrera);

        return $actividad;
    }

    /**
     * Grupo abierto, presencial y sin instructor, en el semestre activo (que crea si falta).
     * `$horarios` son tuplas [id_dia, 'HH:MM', 'HH:MM'], p. ej. [[1, '10:00', '12:00']].
     */
    public static function grupo(array $atributos = [], array $horarios = []): Grupo
    {
        $atributos['id_actividad'] ??= self::actividad()->id_actividad;
        $atributos['id_semestre'] ??= (Semestre::activo() ?? self::semestre(['status' => Semestre::ACTIVO]))->id_semestre;

        $grupo = Grupo::create($atributos + [
            'grupo' => 'A',
            'cupo_minimo' => 1,
            'cupo_maximo' => 20,
            'modalidad' => 'presencial',
            'estatus' => 'abierta',
            'fecha_inicio' => '2026-08-31',
            'fecha_fin' => '2026-12-04',
        ]);

        foreach ($horarios as [$dia, $inicio, $fin]) {
            $grupo->horarios()->create(['id_dia' => $dia, 'hora_inicio' => $inicio.':00', 'hora_fin' => $fin.':00']);
        }

        return $grupo;
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
