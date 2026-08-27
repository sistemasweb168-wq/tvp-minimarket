<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla para control de envases retornables y garantías (cascos de cerveza)
        if (!Schema::hasTable('envases_garantias')) {
            Schema::create('envases_garantias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
                $table->string('cliente_nombre')->nullable();
                $table->string('tipo_envase', 100); // Ej: Caja de Cerveza 12u, Botella 620ml, Botella 1L, etc.
                $table->integer('cantidad');
                $table->decimal('monto_garantia', 12, 2)->default(0);
                $table->enum('estado', ['prestado', 'devuelto', 'cobrado'])->default('prestado');
                $table->dateTime('fecha_prestamo');
                $table->dateTime('fecha_devolucion')->nullable();
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('turno_caja_id')->nullable()->constrained('turnos_caja');
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        // 2. Agregar columna 'categoria' y 'comprobante' a 'movimientos_caja' para gastos operativos
        Schema::table('movimientos_caja', function (Blueprint $table) {
            if (!Schema::hasColumn('movimientos_caja', 'categoria')) {
                $table->string('categoria', 50)->default('general')->after('tipo');
            }
            if (!Schema::hasColumn('movimientos_caja', 'comprobante')) {
                $table->string('comprobante', 50)->nullable()->after('concepto');
            }
        });

        // 3. Agregar columna 'precio_compra' a 'venta_detalles' para cálculo histórico exacto de Utilidad Neta Real
        Schema::table('venta_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('venta_detalles', 'precio_compra')) {
                $table->decimal('precio_compra', 12, 2)->default(0)->after('precio_unitario');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envases_garantias');

        Schema::table('movimientos_caja', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos_caja', 'categoria')) {
                $table->dropColumn('categoria');
            }
            if (Schema::hasColumn('movimientos_caja', 'comprobante')) {
                $table->dropColumn('comprobante');
            }
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            if (Schema::hasColumn('venta_detalles', 'precio_compra')) {
                $table->dropColumn('precio_compra');
            }
        });
    }
};
