<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso completo al sistema',
            'permisos' => ['*'],
            'activo' => true,
        ]);

        Role::create([
            'nombre' => 'Gerente',
            'descripcion' => 'Gestión completa excepto configuración del sistema',
            'permisos' => ['productos', 'ventas', 'compras', 'clientes', 'proveedores', 'caja', 'reportes', 'promociones'],
            'activo' => true,
        ]);

        Role::create([
            'nombre' => 'Cajero',
            'descripcion' => 'Acceso al punto de venta y caja',
            'permisos' => ['ventas', 'caja', 'clientes'],
            'activo' => true,
        ]);

        Role::create([
            'nombre' => 'Almacenero',
            'descripcion' => 'Gestión de inventario y compras',
            'permisos' => ['productos', 'compras', 'proveedores', 'reportes'],
            'activo' => true,
        ]);
    }
}
