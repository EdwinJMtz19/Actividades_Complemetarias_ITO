<?php

namespace App\Console\Commands;

use App\Models\Semestre;
use Illuminate\Console\Command;

class CerrarSemestresVencidos extends Command
{
    protected $signature = 'semestres:cerrar-vencidos';

    protected $description = 'Marca como inactivos los semestres activos cuya fecha de fin ya pasó';

    public function handle(): int
    {
        $cerrados = Semestre::cerrarVencidos();

        $this->info($cerrados === 0
            ? 'No hay semestres vencidos.'
            : "Semestres cerrados: {$cerrados}.");

        return self::SUCCESS;
    }
}
