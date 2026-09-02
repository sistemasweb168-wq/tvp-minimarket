<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetProductionCommand extends Command
{
    protected $signature = "app:reset-production {--force : Forzar ejecucion sin confirmacion}";
    protected $description = "Limpia todas las ventas, boletas, facturas, resumenes, envases y datos de prueba para dejar el sistema listo para produccion conservando usuarios y empresa.";

    public function handle()
    {
        if (!$this->option("force") && !$this->confirm("¿Estas seguro de eliminar todas las ventas, boletas, compras, productos y envases de prueba? (Se conservaran usuarios, empresa y logo)")) {
            $this->info("Operacion cancelada.");
            return 0;
        }

        $this->info("Iniciando reseteo a 0 para Produccion...");

        $tablasResetear = [
            "comprobantes_electronicos",
            "resumenes_diarios",
            "comunicaciones_baja",
            "venta_detalles",
            "ventas",
            "puntos_fidelidad",
            "auditoria_cancelaciones_pos",
            "compra_detalles",
            "compras",
            "proveedores",
            "movimientos_inventario",
            "envases_garantias",
            "combo_productos",
            "promociones",
            "productos",
            "categorias",
            "movimientos_caja",
            "turnos_caja",
            "clientes",
            "actividad_log",
            "password_reset_tokens",
        ];

        DB::statement("SET FOREIGN_KEY_CHECKS=0");
        foreach ($tablasResetear as $tabla) {
            if (Schema::hasTable($tabla)) {
                DB::table($tabla)->truncate();
                $this->line(" - Tabla $tabla limpiada.");
            }
        }

        if (Schema::hasTable("series_documentos")) {
            DB::table("series_documentos")->update(["correlativo_actual" => 0]);
            $this->info(" - Series de comprobantes (Boletas B001, Facturas F001, Tickets T001) reseteadas a 0.");
        }

        if (Schema::hasTable("cajas") && DB::table("cajas")->count() === 0) {
            DB::table("cajas")->insert([
                "nombre" => "Caja Principal",
                "descripcion" => "Caja principal del negocio",
                "activo" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ]);
            $this->info(" - Caja Principal inicializada.");
        }

        if (Schema::hasTable("clientes") && DB::table("clientes")->count() === 0) {
            DB::table("clientes")->insert([
                "codigo" => "CLI-000001",
                "tipo_documento" => "DNI",
                "documento" => "00000000",
                "nombres" => "Clientes",
                "apellidos" => "Varios",
                "activo" => 1,
                "created_at" => now(),
                "updated_at" => now(),
            ]);
            $this->info(" - Cliente generico inicializado.");
        }

        DB::statement("SET FOREIGN_KEY_CHECKS=1");

        $this->newLine();
        $this->info("¡EXITO! El sistema ha sido reseteado a 0.");
        $this->info("Conservados intactos: Usuarios, Roles, Datos de Empresa y Logo.");
        return 0;
    }
}
