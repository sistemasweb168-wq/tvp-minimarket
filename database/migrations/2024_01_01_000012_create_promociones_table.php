<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['descuento_porcentaje', 'descuento_fijo', '2x1', '3x2', 'precio_especial']);
            $table->decimal('valor', 12, 2)->default(0);
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('cantidad_minima')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('puntos_fidelidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->enum('tipo', ['ganado', 'canjeado', 'expirado', 'ajuste']);
            $table->integer('puntos');
            $table->integer('saldo_anterior');
            $table->integer('saldo_nuevo');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puntos_fidelidad');
        Schema::dropIfExists('promociones');
    }
};
