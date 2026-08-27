<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ProductosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Find or create category
        $categoriaNombre = trim($row['categoria'] ?? 'Sin Categoría');
        $categoria = Categoria::firstOrCreate(
            ['nombre' => $categoriaNombre],
            ['activo' => 1] // Activate by default
        );

        $codigo = trim($row['codigo_barras'] ?? '');
        if (empty($codigo)) {
            // Generate random barcode if not provided
            $codigo = rand(10000000, 99999999);
        }

        // Update if exists by barcode, else create
        return Producto::updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => mb_strtoupper(trim($row['nombre_producto'])),
                'precio_venta' => floatval($row['precio_venta'] ?? 0),
                'precio_mayoreo' => floatval($row['precio_por_mayor'] ?? 0),
                'cantidad_mayoreo' => intval($row['cantidad_al_por_mayor'] ?? 0),
                'stock' => floatval($row['stock_inicial'] ?? 0),
                'categoria_id' => $categoria->id,
                'codigo_interno' => trim($row['codigo_interno'] ?? ''),
                'activo' => 1
            ]
        );
    }
}
