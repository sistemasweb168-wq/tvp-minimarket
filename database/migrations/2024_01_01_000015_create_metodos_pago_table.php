<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->string('icono', 50)->default('fa-money-bill');
            $table->string('color', 30)->default('#10b981');
            $table->boolean('requiere_referencia')->default(false);
            $table->boolean('permite_vueltos')->default(true);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('metodo_pago_id')->nullable()->after('forma_pago')->constrained('metodos_pago')->nullOnDelete();
            $table->string('numero_operacion', 100)->nullable()->after('metodo_pago_id');
            $table->string('estado_entrega', 30)->default('entregado')->after('estado'); // entregado, pendiente
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['metodo_pago_id']);
            $table->dropColumn(['metodo_pago_id', 'numero_operacion', 'estado_entrega']);
        });

        Schema::dropIfExists('metodos_pago');
    }
};
