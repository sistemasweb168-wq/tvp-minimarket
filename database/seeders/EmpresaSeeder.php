<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'razon_social' => 'TPV Minimarket Demo S.A.C.',
            'nombre_comercial' => 'Mi Minimarket',
            'ruc_nit' => '20100100100',
            'direccion' => 'Av. Principal 123',
            'ciudad' => 'Lima',
            'telefono' => '01-555-1234',
            'email' => 'contacto@minimarket.com',
            'sitio_web' => 'www.minimarket.com',
            'moneda' => 'S/',
            'codigo_moneda' => 'PEN',
            'impuesto' => 18.00,
            'impuesto_incluido' => true,
            'mensaje_ticket' => '¡Gracias por su preferencia! Vuelva pronto.',
            'terminos_condiciones' => 'No se aceptan devoluciones después de 24 horas.',
        ]);
    }
}
