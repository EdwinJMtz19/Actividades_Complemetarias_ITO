<?php

namespace Database\Seeders;

use App\Models\DiaSemana;
use Illuminate\Database\Seeder;

/**
 * Los seis días hábiles. Es un catálogo fijo que el sistema necesita siempre (también en producción);
 * el orden de los ids (1 = lunes … 6 = sábado) es el orden con que se muestran los horarios.
 */
class DiasSemanaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $dia) {
            DiaSemana::firstOrCreate(['nombre_dia' => $dia]);
        }
    }
}
