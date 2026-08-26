<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            EmpresaSeeder::class,
            ConfiguracionSeeder::class,
            CategoriaSeeder::class,
            CajaSeeder::class,
            ProveedorSeeder::class,
            ProductoSeeder::class,
            SerieDocumentoSeeder::class,
            MetodoPagoSeeder::class,
        ]);
    }
}
