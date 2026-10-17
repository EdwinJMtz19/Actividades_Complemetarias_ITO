<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Departamento;
use Illuminate\Database\Seeder;

/**
 * Departamentos y carreras iniciales. El CRUD completo llega en el Sprint 2;
 * aquí se siembran porque el Sprint 1 los necesita para asignar coordinadores,
 * instructores y alumnos.
 */
class CatalogosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            ['nombre' => 'Ciencias Básicas',                   'edificio' => 'Edificio A'],
            ['nombre' => 'Sistemas y Computación',             'edificio' => 'Edificio B'],
            ['nombre' => 'Ciencias Económico Administrativas', 'edificio' => 'Edificio C'],
            ['nombre' => 'Cultura Física',                     'edificio' => 'Gimnasio'],
            ['nombre' => 'Ingeniería Eléctrica y Electrónica', 'edificio' => 'Edificio D'],
        ];

        foreach ($departamentos as $d) {
            Departamento::firstOrCreate(['nombre' => $d['nombre']], ['edificio' => $d['edificio']]);
        }

        $carreras = [
            'Ingeniería en Sistemas Computacionales',
            'Ingeniería en Gestión Empresarial',
            'Ingeniería Civil',
            'Ingeniería Industrial',
            'Ingeniería Mecánica',
            'Ingeniería Electrónica',
            'Ingeniería Eléctrica',
            'Ingeniería Química',
            'Licenciatura en Administración',
            'Licenciatura en Contaduría',
        ];

        foreach ($carreras as $nombre) {
            Carrera::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
