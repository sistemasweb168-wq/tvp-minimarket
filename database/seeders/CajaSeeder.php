<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Caja;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        Caja::create(['nombre' => 'Caja Principal', 'descripcion' => 'Caja principal del minimarket', 'activo' => true]);
        Caja::create(['nombre' => 'Caja 2', 'descripcion' => 'Segunda caja', 'activo' => true]);
    }
}
