<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $cats = Categoria::pluck('id', 'nombre')->toArray();
        $prov = Proveedor::pluck('id')->first();

        $productos = [
            // Abarrotes
            ['Arroz Costeño 5kg', 'Abarrotes', 22.50, 28.90, 'KG', 50, 10],
            ['Aceite Primor 1L', 'Abarrotes', 9.80, 12.50, 'LT', 30, 5],
            ['Azúcar Rubia 1kg', 'Abarrotes', 4.20, 5.50, 'KG', 80, 15],
            ['Sal Yodada 1kg', 'Abarrotes', 1.50, 2.20, 'KG', 60, 10],
            ['Fideos Don Vittorio 500g', 'Abarrotes', 3.20, 4.50, 'PAQ', 100, 20],
            ['Atún Real Lata 170g', 'Abarrotes', 4.50, 6.20, 'UND', 80, 15],
            // Bebidas
            ['Inca Kola 1.5L', 'Bebidas', 4.50, 6.50, 'UND', 80, 12],
            ['Coca Cola 1.5L', 'Bebidas', 4.80, 6.80, 'UND', 70, 10],
            ['Agua Cielo 625ml', 'Bebidas', 1.20, 2.00, 'UND', 120, 20],
            ['Cerveza Cristal 630ml', 'Bebidas', 4.50, 6.50, 'UND', 60, 12],
            ['Jugo Frugos Manzana 1L', 'Bebidas', 3.80, 5.20, 'UND', 40, 8],
            // Lácteos
            ['Leche Gloria Entera 1L', 'Lácteos', 3.80, 5.00, 'UND', 60, 10],
            ['Yogurt Gloria Fresa 1kg', 'Lácteos', 7.50, 9.90, 'UND', 25, 5],
            ['Queso Fresco 250g', 'Lácteos', 6.50, 9.00, 'UND', 20, 4],
            ['Mantequilla Gloria 200g', 'Lácteos', 6.20, 8.50, 'UND', 15, 3],
            // Panadería
            ['Pan Francés', 'Panadería', 0.20, 0.40, 'UND', 200, 30],
            ['Pan Integral', 'Panadería', 0.40, 0.70, 'UND', 100, 20],
            ['Tostadas Bimbo', 'Panadería', 4.20, 5.80, 'PAQ', 30, 8],
            // Frutas y verduras
            ['Manzana Roja', 'Frutas y Verduras', 4.50, 6.90, 'KG', 30, 5],
            ['Plátano de Seda', 'Frutas y Verduras', 1.80, 2.80, 'KG', 40, 8],
            ['Tomate', 'Frutas y Verduras', 3.50, 5.00, 'KG', 25, 5],
            ['Cebolla', 'Frutas y Verduras', 2.50, 3.80, 'KG', 30, 6],
            ['Limón', 'Frutas y Verduras', 4.20, 6.00, 'KG', 20, 4],
            // Snacks
            ['Lays Original 105g', 'Snacks', 4.50, 6.50, 'UND', 50, 10],
            ['Doritos Nacho 110g', 'Snacks', 4.80, 6.80, 'UND', 45, 8],
            ['Chocman Costa', 'Snacks', 0.80, 1.20, 'UND', 100, 20],
            ['Galletas Oreo', 'Snacks', 2.50, 3.80, 'UND', 60, 12],
            // Limpieza
            ['Detergente Ariel 850g', 'Limpieza', 12.50, 16.50, 'UND', 30, 5],
            ['Lejía Clorox 1L', 'Limpieza', 4.50, 6.50, 'LT', 40, 8],
            ['Jabón Bolívar 250g', 'Limpieza', 2.20, 3.50, 'UND', 50, 10],
            // Cuidado Personal
            ['Shampoo H&S 200ml', 'Cuidado Personal', 14.50, 18.90, 'UND', 25, 5],
            ['Pasta Dental Colgate', 'Cuidado Personal', 5.20, 7.50, 'UND', 40, 8],
            ['Papel Higiénico Suave x4', 'Cuidado Personal', 7.50, 10.50, 'PAQ', 50, 10],
        ];

        $i = 1;
        foreach ($productos as $p) {
            Producto::create([
                'codigo' => 'P' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'codigo_barras' => '7501' . str_pad($i, 9, '0', STR_PAD_LEFT),
                'nombre' => $p[0],
                'categoria_id' => $cats[$p[1]] ?? null,
                'proveedor_id' => $prov,
                'precio_compra' => $p[2],
                'precio_venta' => $p[3],
                'precio_mayoreo' => round($p[3] * 0.95, 2),
                'cantidad_mayoreo' => 12,
                'unidad_medida' => $p[4],
                'stock' => $p[5],
                'stock_minimo' => $p[6],
                'stock_maximo' => $p[5] * 2,
                'controla_stock' => true,
                'aplica_impuesto' => true,
                'activo' => true,
                'destacado' => $i <= 12,
            ]);
            $i++;
        }
    }
}
