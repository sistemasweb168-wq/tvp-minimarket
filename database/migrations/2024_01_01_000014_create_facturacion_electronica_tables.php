<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla principal de comprobantes electrónicos (CPE)
        Schema::create('comprobantes_electronicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->string('tipo_documento', 2); // 01=Factura, 03=Boleta, 07=Nota Credito, 08=Nota Debito
            $table->string('serie', 4);
            $table->string('numero', 8);
            $table->string('numero_completo', 20)->unique(); // F001-00000001

            // Datos del emisor (snapshot al momento de emitir)
            $table->string('emisor_ruc', 15);
            $table->string('emisor_razon_social');

            // Datos del receptor
            $table->string('receptor_tipo_doc', 2); // 1=DNI, 6=RUC
            $table->string('receptor_numero_doc', 20);
            $table->string('receptor_razon_social');
            $table->string('receptor_direccion')->nullable();
            $table->string('receptor_email')->nullable();

            $table->date('fecha_emision');
            $table->time('hora_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('moneda', 3)->default('PEN');

            // Totales
            $table->decimal('total_gravadas', 12, 2)->default(0);
            $table->decimal('total_exoneradas', 12, 2)->default(0);
            $table->decimal('total_inafectas', 12, 2)->default(0);
            $table->decimal('total_gratuitas', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total_isc', 12, 2)->default(0);
            $table->decimal('total_descuentos', 12, 2)->default(0);
            $table->decimal('importe_total', 12, 2);
            $table->string('importe_letras', 255);

            // Referencias para notas de crédito/débito
            $table->string('doc_referencia_tipo', 2)->nullable();
            $table->string('doc_referencia_serie_numero', 20)->nullable();
            $table->string('motivo_referencia', 255)->nullable();
            $table->string('codigo_motivo_nc', 3)->nullable(); // 01=Anulación, 02=Anulación por error, etc.

            // SUNAT
            $table->enum('estado_sunat', [
                'pendiente', 'enviado', 'aceptado', 'rechazado', 'observado',
                'anulado', 'baja', 'excepcion'
            ])->default('pendiente');
            $table->string('codigo_respuesta_sunat', 10)->nullable();
            $table->text('mensaje_sunat')->nullable();
            $table->string('hash')->nullable(); // hash del XML firmado
            $table->string('xml_path')->nullable(); // ruta al XML firmado
            $table->string('cdr_path')->nullable(); // ruta al CDR de SUNAT
            $table->string('pdf_path')->nullable(); // ruta al PDF generado
            $table->string('qr_data', 500)->nullable(); // datos para el QR

            $table->timestamp('fecha_envio_sunat')->nullable();
            $table->integer('intentos_envio')->default(0);

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['tipo_documento', 'serie', 'numero']);
            $table->index('estado_sunat');
            $table->index('fecha_emision');
        });

        // Series de documentos (correlativos)
        Schema::create('series_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 2);
            $table->string('serie', 4);
            $table->integer('correlativo_actual')->default(0);
            $table->integer('correlativo_max')->default(99999999);
            $table->boolean('activo')->default(true);
            $table->string('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['tipo_documento', 'serie']);
        });

        // Resúmenes diarios de boletas
        Schema::create('resumenes_diarios', function (Blueprint $table) {
            $table->id();
            $table->string('identificador', 20)->unique(); // RC-20240115-001
            $table->date('fecha_resumen'); // fecha de las boletas
            $table->date('fecha_generacion'); // fecha cuando se generó el resumen
            $table->integer('cantidad_comprobantes')->default(0);
            $table->decimal('total_general', 12, 2)->default(0);
            $table->enum('estado_sunat', ['pendiente', 'enviado', 'aceptado', 'rechazado'])->default('pendiente');
            $table->string('ticket_sunat', 100)->nullable();
            $table->string('codigo_respuesta', 10)->nullable();
            $table->text('mensaje_respuesta')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Comunicaciones de baja (anulaciones)
        Schema::create('comunicaciones_baja', function (Blueprint $table) {
            $table->id();
            $table->string('identificador', 20)->unique(); // RA-20240115-001
            $table->foreignId('comprobante_id')->constrained('comprobantes_electronicos');
            $table->date('fecha_generacion');
            $table->string('motivo', 255);
            $table->enum('estado_sunat', ['pendiente', 'enviado', 'aceptado', 'rechazado'])->default('pendiente');
            $table->string('ticket_sunat', 100)->nullable();
            $table->string('codigo_respuesta', 10)->nullable();
            $table->text('mensaje_respuesta')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Agregar campos SUNAT a la tabla empresas
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('ubigeo', 6)->nullable()->after('ciudad');
            $table->string('departamento', 100)->nullable()->after('ubigeo');
            $table->string('provincia', 100)->nullable()->after('departamento');
            $table->string('distrito', 100)->nullable()->after('provincia');
            $table->string('codigo_pais', 2)->default('PE')->after('distrito');

            // Configuración SUNAT
            $table->enum('sunat_modo', ['beta', 'produccion'])->default('beta');
            $table->string('sunat_usuario_sol', 100)->nullable();
            $table->string('sunat_clave_sol', 100)->nullable();
            $table->string('sunat_certificado_path')->nullable();
            $table->string('sunat_certificado_password')->nullable();
            $table->boolean('facturacion_electronica_activa')->default(false);
        });

        // Agregar campos al venta para vincular con comprobante electrónico
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('comprobante_electronico_id')->nullable()->after('turno_caja_id')
                  ->constrained('comprobantes_electronicos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['comprobante_electronico_id']);
            $table->dropColumn('comprobante_electronico_id');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'ubigeo', 'departamento', 'provincia', 'distrito', 'codigo_pais',
                'sunat_modo', 'sunat_usuario_sol', 'sunat_clave_sol',
                'sunat_certificado_path', 'sunat_certificado_password',
                'facturacion_electronica_activa',
            ]);
        });

        Schema::dropIfExists('comunicaciones_baja');
        Schema::dropIfExists('resumenes_diarios');
        Schema::dropIfExists('series_documentos');
        Schema::dropIfExists('comprobantes_electronicos');
    }
};
