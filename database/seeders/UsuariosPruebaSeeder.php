<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\CoordinadorDepartamento;
use App\Models\Departamento;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cuentas de prueba (solo fuera de producción). Contraseña: Actividades2026
 */
class UsuariosPruebaSeeder extends Seeder
{
    private const CONTRASENA = 'Actividades2026';

    public function run(): void
    {
        $sistemas = Departamento::where('nombre', 'Sistemas y Computación')->firstOrFail();
        $carrera = Carrera::where('nombre', 'Ingeniería en Sistemas Computacionales')->firstOrFail();

        $admin = $this->usuario('admin@gmail.com', 'administrador', 'Admin', 'Sistema', 'ITO');

        $coord = $this->usuario('coordinador@gmail.com', 'coordinador', 'Carlos', 'Hernández', 'Gómez');
        CoordinadorDepartamento::firstOrCreate(['id_usuario' => $coord->id], ['id_departamento' => $sistemas->id_departamento]);

        $instr = $this->usuario('instructor@gmail.com', 'instructor', 'Laura', 'Pérez', 'Ramírez');
        Instructor::firstOrCreate(['id_instructor' => $instr->id], [
            'id_departamento' => $sistemas->id_departamento,
            'especialidad' => 'Programación',
        ]);

        $alum = $this->usuario('alumno@gmail.com', 'alumno', 'Ana', 'Martínez', 'Ruiz');
        Alumno::firstOrCreate(['id_alumno' => $alum->id], [
            'num_control' => '20230001',
            'id_carrera' => $carrera->id_carrera,
            'semestre_cursando' => 5,
            'creditos_acumulados' => 0,
        ]);
    }

    private function usuario(string $email, string $rol, string $nombre, string $paterno, string $materno): User
    {
        $usuario = User::firstOrCreate(['email' => $email], [
            'nombre' => $nombre,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'contrasena' => self::CONTRASENA,
            'tipo_usuario' => User::TIPO_POR_ROL[$rol],
            'activo' => true,
        ]);

        $usuario->syncRoles([$rol]);

        return $usuario;
    }
}
