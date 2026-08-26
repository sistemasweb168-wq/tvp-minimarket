<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            ['codigo' => 'PR00001', 'razon_social' => 'Distribuidora Alimentos S.A.', 'ruc_nit' => '20100200300', 'contacto' => 'Juan Pérez', 'telefono' => '987-654-321', 'email' => 'ventas@dasa.com', 'ciudad' => 'Lima'],
            ['codigo' => 'PR00002', 'razon_social' => 'Bebidas y Más SAC', 'ruc_nit' => '20200300400', 'contacto' => 'María Gómez', 'telefono' => '976-543-210', 'email' => 'pedidos@bebidasymas.com', 'ciudad' => 'Lima'],
            ['codigo' => 'PR00003', 'razon_social' => 'Lácteos del Norte', 'ruc_nit' => '20300400500', 'contacto' => 'Carlos Ruiz', 'telefono' => '965-432-109', 'email' => 'lacteos@delnorte.com', 'ciudad' => 'Trujillo'],
            ['codigo' => 'PR00004', 'razon_social' => 'Panificadora La Espiga', 'ruc_nit' => '20400500600', 'contacto' => 'Ana Torres', 'telefono' => '954-321-098', 'email' => 'pedidos@laespiga.com', 'ciudad' => 'Lima'],
        ];

        foreach ($proveedores as $p) {
            Proveedor::create(array_merge($p, ['activo' => true]));
        }
    }
}
