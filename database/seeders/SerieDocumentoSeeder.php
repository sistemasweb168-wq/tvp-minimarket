<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SerieDocumento;

class SerieDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $series = [
            ['tipo_documento' => '01', 'serie' => 'F001', 'descripcion' => 'Factura electrónica principal'],
            ['tipo_documento' => '03', 'serie' => 'B001', 'descripcion' => 'Boleta de venta electrónica'],
            ['tipo_documento' => '07', 'serie' => 'FC01', 'descripcion' => 'Nota de crédito de facturas'],
            ['tipo_documento' => '07', 'serie' => 'BC01', 'descripcion' => 'Nota de crédito de boletas'],
            ['tipo_documento' => '08', 'serie' => 'FD01', 'descripcion' => 'Nota de débito de facturas'],
            ['tipo_documento' => '08', 'serie' => 'BD01', 'descripcion' => 'Nota de débito de boletas'],
        ];

        foreach ($series as $s) {
            SerieDocumento::firstOrCreate(
                ['tipo_documento' => $s['tipo_documento'], 'serie' => $s['serie']],
                array_merge($s, ['correlativo_actual' => 0, 'activo' => true])
            );
        }
    }
}
