<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->enum('tipo_producto', ['estandar', 'combo'])->default('estandar')->after('id');
        });

        Schema::create('combo_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->decimal('cantidad', 12, 3)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_productos');
        
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('tipo_producto');
        });
    }
};
