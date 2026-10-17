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
        ]);

        // Cuentas de prueba: nunca en producción.
        if (! app()->environment('production')) {
            $this->call(UsuariosPruebaSeeder::class);
        }
    }
}
