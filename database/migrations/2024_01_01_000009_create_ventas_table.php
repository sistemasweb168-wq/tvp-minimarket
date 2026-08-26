<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_ticket', 30)->unique();
            $table->string('tipo_comprobante', 30)->default('TICKET'); // TICKET, BOLETA, FACTURA
            $table->string('serie', 10)->default('T001');
            $table->dateTime('fecha_venta');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('turno_caja_id')->nullable()->constrained('turnos_caja');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('monto_recibido', 12, 2)->default(0);
            $table->decimal('cambio', 12, 2)->default(0);
            $table->string('forma_pago', 30)->default('efectivo'); // efectivo, tarjeta, transferencia, mixto, credito
            $table->json('detalle_pago')->nullable();
            $table->enum('estado', ['completada', 'anulada', 'pendiente'])->default('completada');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('codigo', 50);
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');
    }
};
