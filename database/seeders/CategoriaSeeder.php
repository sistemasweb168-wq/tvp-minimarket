<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Abarrotes', 'color' => '#f59e0b', 'icono' => 'cube', 'descripcion' => 'Productos básicos secos'],
            ['nombre' => 'Bebidas', 'color' => '#3b82f6', 'icono' => 'wine-bottle', 'descripcion' => 'Bebidas alcohólicas y no alcohólicas'],
            ['nombre' => 'Lácteos', 'color' => '#ec4899', 'icono' => 'cheese', 'descripcion' => 'Leche, queso, yogurt'],
            ['nombre' => 'Panadería', 'color' => '#d97706', 'icono' => 'bread-slice', 'descripcion' => 'Pan, pasteles'],
            ['nombre' => 'Frutas y Verduras', 'color' => '#10b981', 'icono' => 'apple-alt', 'descripcion' => 'Productos frescos'],
            ['nombre' => 'Carnes', 'color' => '#dc2626', 'icono' => 'drumstick-bite', 'descripcion' => 'Carnes y embutidos'],
            ['nombre' => 'Snacks', 'color' => '#fbbf24', 'icono' => 'cookie', 'descripcion' => 'Galletas, papas fritas'],
            ['nombre' => 'Limpieza', 'color' => '#06b6d4', 'icono' => 'soap', 'descripcion' => 'Productos de limpieza'],
            ['nombre' => 'Cuidado Personal', 'color' => '#a855f7', 'icono' => 'tshirt', 'descripcion' => 'Higiene personal'],
            ['nombre' => 'Bebés', 'color' => '#f472b6', 'icono' => 'baby', 'descripcion' => 'Productos para bebés'],
            ['nombre' => 'Mascotas', 'color' => '#84cc16', 'icono' => 'paw', 'descripcion' => 'Productos para mascotas'],
            ['nombre' => 'Helados', 'color' => '#06b6d4', 'icono' => 'ice-cream', 'descripcion' => 'Helados y postres congelados'],
        ];

        foreach ($categorias as $c) {
            Categoria::create(array_merge($c, ['activo' => true]));
        }
    }
}
