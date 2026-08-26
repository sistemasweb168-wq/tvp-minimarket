<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['clave' => 'puntos_por_moneda', 'valor' => '0.1', 'tipo' => 'string', 'grupo' => 'fidelidad', 'descripcion' => 'Puntos por unidad de moneda'],
            ['clave' => 'dias_aviso_vencimiento', 'valor' => '30', 'tipo' => 'integer', 'grupo' => 'inventario'],
            ['clave' => 'stock_minimo_default', 'valor' => '5', 'tipo' => 'integer', 'grupo' => 'inventario'],
            ['clave' => 'serie_ticket', 'valor' => 'T001', 'tipo' => 'string', 'grupo' => 'facturacion'],
            ['clave' => 'serie_boleta', 'valor' => 'B001', 'tipo' => 'string', 'grupo' => 'facturacion'],
            ['clave' => 'serie_factura', 'valor' => 'F001', 'tipo' => 'string', 'grupo' => 'facturacion'],
            ['clave' => 'ancho_ticket', 'valor' => '80', 'tipo' => 'integer', 'grupo' => 'ticket'],
            ['clave' => 'imprimir_auto', 'valor' => '1', 'tipo' => 'boolean', 'grupo' => 'ticket'],
            ['clave' => 'mostrar_logo_ticket', 'valor' => '1', 'tipo' => 'boolean', 'grupo' => 'ticket'],
        ];

        foreach ($configs as $c) {
            Configuracion::create($c);
        }
    }
}
