<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\Semestre;
use Illuminate\Database\Seeder;

/**
 * Actividades de ejemplo para desarrollo. Cada una cuelga de un departamento del catálogo base y se
 * dirige a todas las carreras. Se asocian al semestre activo; si no hay ninguno quedan sin semestre
 * (la pantalla de edición permite asignarlas después). Es idempotente.
 */
class ActividadesBaseSeeder extends Seeder
{
    public function run(): void
    {
        $semestre = Semestre::activo();
        $carreras = Carrera::pluck('id_carrera');

        $actividades = [
            ['Fútbol soccer', 'Cultura Física', 2, 'Entrenamiento y partidos amistosos.'],
            ['Básquetbol', 'Cultura Física', 2, 'Fundamentos, técnica y juego en equipo.'],
            ['Ajedrez', 'Ciencias Básicas', 1, 'Aperturas, táctica y torneos internos.'],
            ['Programación competitiva', 'Sistemas y Computación', 1, 'Resolución de problemas algorítmicos en equipo.'],
            ['Emprendimiento', 'Ciencias Económico Administrativas', 1, 'De la idea al plan de negocio.'],
        ];

        foreach ($actividades as [$nombre, $departamento, $creditos, $descripcion]) {
            $dep = Departamento::where('nombre', $departamento)->first();

            if ($dep === null) {
                continue;
            }

            $actividad = Actividad::firstOrCreate(['nombre' => $nombre], [
                'descripcion' => $descripcion,
                'id_departamento' => $dep->id_departamento,
                'id_semestre' => $semestre?->id_semestre,
                'creditos' => $creditos,
                'disponible' => true,
            ]);

            $actividad->carreras()->syncWithoutDetaching($carreras);
        }
    }
}
