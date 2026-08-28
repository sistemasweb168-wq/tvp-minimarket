<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. ÍNDICES en tabla VENTAS (rendimiento en reportes y POS)
        // ============================================================
        Schema::table('ventas', function (Blueprint $table) {
            if (!$this->hasIndex('ventas', 'ventas_fecha_venta_index')) {
                $table->index('fecha_venta');
            }
            if (!$this->hasIndex('ventas', 'ventas_estado_index')) {
                $table->index('estado');
            }
            if (!$this->hasIndex('ventas', 'ventas_tipo_comprobante_index')) {
                $table->index('tipo_comprobante');
            }
        });

        // ============================================================
        // 2. ÍNDICES en tabla TURNOS_CAJA (consulta en cada request POS)
        // ============================================================
        Schema::table('turnos_caja', function (Blueprint $table) {
            if (!$this->hasIndex('turnos_caja', 'turnos_caja_estado_index')) {
                $table->index('estado');
            }
            if (!$this->hasIndex('turnos_caja', 'turnos_caja_user_id_estado_index')) {
                $table->index(['user_id', 'estado']);
            }
            if (!$this->hasIndex('turnos_caja', 'turnos_caja_fecha_apertura_index')) {
                $table->index('fecha_apertura');
            }
        });

        // ============================================================
        // 3. ÍNDICES en tabla MOVIMIENTOS_INVENTARIO (Kardex lento)
        // ============================================================
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            if (Schema::hasTable('movimientos_inventario')) {
                if (!$this->hasIndex('movimientos_inventario', 'movimientos_inventario_fecha_index')) {
                    $table->index('fecha');
                }
                if (!$this->hasIndex('movimientos_inventario', 'movimientos_inventario_ref_index')) {
                    $table->index(['referencia_tipo', 'referencia_id']);
                }
            }
        });

        // ============================================================
        // 4. ÍNDICE en PROVEEDORES (búsqueda por RUC)
        // ============================================================
        Schema::table('proveedores', function (Blueprint $table) {
            if (!$this->hasIndex('proveedores', 'proveedores_ruc_nit_index')) {
                $table->index('ruc_nit');
            }
        });

        // ============================================================
        // 5. ÍNDICES en ENVASES_GARANTIAS (cierre de caja)
        // ============================================================
        if (Schema::hasTable('envases_garantias')) {
            Schema::table('envases_garantias', function (Blueprint $table) {
                if (!$this->hasIndex('envases_garantias', 'envases_garantias_estado_index')) {
                    $table->index('estado');
                }
                if (!$this->hasIndex('envases_garantias', 'envases_garantias_turno_caja_id_index')) {
                    $table->index('turno_caja_id');
                }
            });
        }

        // ============================================================
        // 6. ÍNDICE COMPUESTO en PRODUCTOS (cuadrícula del POS)
        // ============================================================
        Schema::table('productos', function (Blueprint $table) {
            if (!$this->hasIndex('productos', 'productos_activo_destacado_index')) {
                $table->index(['activo', 'destacado']);
            }
        });

        // ============================================================
        // 7. CAMPO tipo_afectacion_igv en PRODUCTOS (para SUNAT)
        //    Valores: 10=Gravado, 20=Exonerado, 30=Inafecto
        // ============================================================
        if (!Schema::hasColumn('productos', 'tipo_afectacion_igv')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('tipo_afectacion_igv', 2)->default('10')->after('aplica_impuesto')
                    ->comment('Código afectación SUNAT: 10=Gravado, 20=Exonerado, 30=Inafecto');
            });
        }

        // ============================================================
        // 8. CAMPOS faltantes en VENTAS (metodo_pago_id, etc.)
        // ============================================================
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'numero_operacion')) {
                $table->string('numero_operacion', 100)->nullable()->after('referencia_pago');
            }
            if (!Schema::hasColumn('ventas', 'estado_entrega')) {
                $table->string('estado_entrega', 20)->default('entregado')->after('estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['fecha_venta']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['tipo_comprobante']);
            if (Schema::hasColumn('ventas', 'numero_operacion')) $table->dropColumn('numero_operacion');
            if (Schema::hasColumn('ventas', 'estado_entrega')) $table->dropColumn('estado_entrega');
        });

        Schema::table('turnos_caja', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['user_id', 'estado']);
            $table->dropIndex(['fecha_apertura']);
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropIndex(['ruc_nit']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['activo', 'destacado']);
            if (Schema::hasColumn('productos', 'tipo_afectacion_igv')) $table->dropColumn('tipo_afectacion_igv');
        });
    }

    /** Helper para verificar si un índice ya existe */
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
};
