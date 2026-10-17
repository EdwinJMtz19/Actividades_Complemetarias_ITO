<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Departamento;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

/**
 * Departamentos, carreras y ubicaciones iniciales. Se siembran porque el Sprint 1
 * los necesita para asignar coordinadores, instructores y alumnos; el CRUD para
 * mantenerlos es del Sprint 2.
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

        $ubicaciones = [
            'Aula A-101' => 30,
            'Aula B-201' => 35,
            'Auditorio Central' => 200,
            'Cancha de Usos Múltiples' => 60,
            'Gimnasio' => 80,
        ];

        foreach ($ubicaciones as $espacio => $capacidad) {
            Ubicacion::firstOrCreate(['espacio' => $espacio], ['capacidad' => $capacidad]);
        }
    }
}
