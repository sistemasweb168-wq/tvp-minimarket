<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("auditoria_cancelaciones_pos", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("turno_caja_id")->nullable()->constrained("turnos_caja")->nullOnDelete();
            $table->foreignId("producto_id")->nullable()->constrained("productos")->nullOnDelete();
            $table->string("producto_nombre");
            $table->string("tipo_evento", 50)->default("item_eliminado"); // item_eliminado, carrito_vaciado, cantidad_reducida
            $table->decimal("cantidad", 10, 3)->default(1);
            $table->decimal("precio_unitario", 10, 2)->default(0);
            $table->decimal("total_afectado", 10, 2)->default(0);
            $table->string("motivo")->nullable();
            $table->timestamps();

            $table->index(["turno_caja_id", "created_at"]);
            $table->index(["user_id", "created_at"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("auditoria_cancelaciones_pos");
    }
};
