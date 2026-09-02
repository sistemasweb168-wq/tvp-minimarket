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
        // Normalizar claves a minúsculas y sin acentos
        $normalizedRow = [];
        foreach ($row as $k => $v) {
            $kNorm = strtolower(trim($k));
            $kNorm = str_replace(['á','é','í','ó','ú','ñ',' '], ['a','e','i','o','u','n','_'], $kNorm);
            $normalizedRow[$kNorm] = $v;
        }

        $nombre = trim(
            $normalizedRow['nombre_producto'] 
            ?? $normalizedRow['descripcion'] 
            ?? $normalizedRow['nombre'] 
            ?? $normalizedRow['producto'] 
            ?? ''
        );

        if (empty($nombre)) return null;

        // Limpieza de números y monedas (S/., s/., $, etc.)
        $cleanNum = function($val, $default = 0.0) {
            if ($val === null || $val === '') return $default;
            $v = trim((string)$val);
            $v = preg_replace('/^[sS]\/?\.?\s*/', '', $v);
            $v = preg_replace('/[^\d.,]/', '', $v);
            $v = str_replace(',', '.', $v);
            if (substr_count($v, '.') > 1) {
                $parts = explode('.', $v);
                $v = implode('', array_slice($parts, 0, -1)) . '.' . end($parts);
            }
            return is_numeric($v) ? floatval($v) : $default;
        };

        // Buscar o crear categoría
        $catName = trim(
            $normalizedRow['categoria'] 
            ?? $normalizedRow['departamento'] 
            ?? $normalizedRow['categoria_id'] 
            ?? 'GENERAL'
        );
        $categoria = Categoria::firstOrCreate(
            ['nombre' => mb_strtoupper($catName ?: 'GENERAL')],
            ['activo' => 1]
        );

        $codigo = trim(
            $normalizedRow['codigo_barras'] 
            ?? $normalizedRow['codigo'] 
            ?? $normalizedRow['codigo_interno'] 
            ?? ''
        );
        if (empty($codigo)) {
            $codigo = 'AUTO-' . strtoupper(Str::random(8));
        }

        $precioCompra  = $cleanNum($normalizedRow['precio_compra'] ?? $normalizedRow['precio_costo'] ?? $normalizedRow['costo'] ?? 0);
        $precioVenta   = $cleanNum($normalizedRow['precio_venta'] ?? $normalizedRow['precio'] ?? $normalizedRow['p_venta'] ?? 0);
        $precioMayoreo = $cleanNum($normalizedRow['precio_por_mayor'] ?? $normalizedRow['precio_mayoreo'] ?? $normalizedRow['mayoreo'] ?? 0);
        $cantMayoreo   = intval($cleanNum($normalizedRow['cantidad_al_por_mayor'] ?? $normalizedRow['cant_mayoreo'] ?? 0));
        $stock         = $cleanNum($normalizedRow['stock_inicial'] ?? $normalizedRow['inventario'] ?? $normalizedRow['stock'] ?? 0);
        $stockMinimo   = $cleanNum($normalizedRow['stock_minimo'] ?? $normalizedRow['inv_minimo'] ?? 0);

        $this->importados++;

        return Producto::updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre'          => mb_strtoupper($nombre),
                'precio_compra'   => $precioCompra,
                'precio_venta'    => $precioVenta,
                'precio_mayoreo'  => $precioMayoreo,
                'cantidad_mayoreo'=> $cantMayoreo,
                'stock'           => $stock,
                'stock_minimo'    => $stockMinimo,
                'categoria_id'    => $categoria->id,
                'codigo_interno'  => trim($normalizedRow['codigo_interno'] ?? $codigo),
                'codigo_barras'   => $codigo,
                'unidad_medida'   => strtoupper(trim($normalizedRow['unidad_medida'] ?? 'UND')),
                'controla_stock'  => 1,
                'activo'          => 1,
            ]
        );
    }

    public function rules(): array
    {
        return []; // Validación personalizada dentro de model() para máxima compatibilidad
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
