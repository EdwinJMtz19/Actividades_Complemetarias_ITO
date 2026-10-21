<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesYPermisosSeeder::class,
            CatalogosBaseSeeder::class,
            DiasSemanaSeeder::class,
        ]);

        // Datos de ejemplo y cuentas de prueba: nunca en producción.
        if (! app()->environment('production')) {
            $this->call([
                ActividadesBaseSeeder::class,
                UsuariosPruebaSeeder::class,
            ]);
        }
    }
}
