<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Str;

class ProductosImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable;

    /** Errores acumulados para mostrar al usuario */
    public array $errors = [];
    public int $importados = 0;
    public int $omitidos   = 0;

    /** Filas con error de validación son omitidas (no detienen la importación) */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            $this->omitidos++;
        }
    }

    /** Filas con excepción PHP son omitidas */
    public function onError(\Throwable $e): void
    {
        $this->errors[] = 'Error inesperado: ' . $e->getMessage();
        $this->omitidos++;
    }

    public function model(array $row): ?Producto
    {
        $nombre = trim($row['nombre_producto'] ?? '');
        if (empty($nombre)) return null; // omitir filas completamente vacías

        // Buscar o crear categoría
        $categoriaNombre = trim($row['categoria'] ?? 'Sin Categoría');
        $categoria = Categoria::firstOrCreate(
            ['nombre' => $categoriaNombre],
            ['activo' => 1]
        );

        $codigo = trim($row['codigo_barras'] ?? '');
        if (empty($codigo)) {
            $codigo = 'AUTO-' . strtoupper(Str::random(8));
        }

        $this->importados++;

        return Producto::updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre'          => mb_strtoupper($nombre),
                'precio_venta'    => floatval($row['precio_venta']    ?? 0),
                'precio_mayoreo'  => floatval($row['precio_por_mayor'] ?? 0),
                'cantidad_mayoreo'=> intval($row['cantidad_al_por_mayor'] ?? 0),
                'stock'           => floatval($row['stock_inicial']    ?? 0),
                'categoria_id'    => $categoria->id,
                'codigo_interno'  => trim($row['codigo_interno']       ?? ''),
                'unidad_medida'   => strtoupper(trim($row['unidad_medida'] ?? 'UND')),
                'activo'          => 1,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nombre_producto' => 'required|string|max:255',
            'precio_venta'    => 'required|numeric|min:0',
            'stock_inicial'   => 'nullable|numeric|min:0',
            'precio_por_mayor'=> 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nombre_producto.required' => 'El campo NOMBRE_PRODUCTO es obligatorio.',
            'precio_venta.required'    => 'El campo PRECIO_VENTA es obligatorio.',
            'precio_venta.numeric'     => 'PRECIO_VENTA debe ser un número (ej: 12.50).',
        ];
    }
}
