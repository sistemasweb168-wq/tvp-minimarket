<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoriaId = DB::table('categorias')->where('nombre', 'Varios')->value('id');
        if (!$categoriaId) {
            $categoriaId = DB::table('categorias')->insertGetId([
                'nombre' => 'Varios',
                'descripcion' => 'Productos comunes y servicios',
                'icono' => 'tags',
                'color' => '#64748b',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $exists = DB::table('productos')->where('codigo', 'ART-COMUN')->exists();
        if (!$exists) {
            DB::table('productos')->insert([
                'codigo' => 'ART-COMUN',
                'nombre' => 'Artículo Común',
                'descripcion' => 'Venta rápida genérica',
                'categoria_id' => $categoriaId,
                'tipo_producto' => 'estandar',
                'precio_compra' => 0,
                'precio_venta' => 0,
                'stock' => 9999,
                'stock_minimo' => 0,
                'controla_stock' => false,
                'aplica_impuesto' => false,
                'destacado' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('productos')->where('codigo', 'ART-COMUN')->delete();
    }
};
