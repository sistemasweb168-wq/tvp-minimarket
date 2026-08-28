<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SunatSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía automáticamente facturas pendientes y el resumen diario de boletas a SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización con SUNAT...');
        $empresa = Empresa::first();

        if (!$empresa || empty($empresa->ruc_nit)) {
            $this->error('Empresa no configurada.');
            return 1;
        }

        if (!$empresa->facturacion_electronica_activa) {
            $this->info('La facturación electrónica está desactivada.');
            return 0;
        }

        $sunatService = new SunatService($empresa);

        // 1. Reintentar facturas pendientes o con excepción (de los últimos 3 días)
        $facturasPendientes = \App\Models\ComprobanteElectronico::where('tipo_documento', '01')
            ->whereIn('estado_sunat', ['pendiente', 'excepcion'])
            ->whereDate('fecha_emision', '>=', now()->subDays(3))
            ->get();

        foreach ($facturasPendientes as $factura) {
            $this->info("Reintentando Factura: {$factura->numero_completo}");
            try {
                $resultado = $sunatService->enviarASunat($factura);
                if ($resultado['success']) {
                    $this->info("Factura {$factura->numero_completo} ACEPTADA.");
                } else {
                    $this->error("Factura {$factura->numero_completo} FALLO: {$resultado['mensaje']}");
                }
            } catch (\Exception $e) {
                $this->error("Excepción en {$factura->numero_completo}: " . $e->getMessage());
                Log::error("Cron SUNAT Factura {$factura->numero_completo}: " . $e->getMessage());
            }
        }

        // 2. Generar Resumen Diario para las Boletas de HOY (y ayer si quedaron pendientes)
        $fechas = [now()->subDay()->startOfDay(), now()->startOfDay()];
        
        foreach ($fechas as $fecha) {
            $hayBoletas = \App\Models\ComprobanteElectronico::boletas()
                ->whereDate('fecha_emision', $fecha)
                ->whereIn('estado_sunat', ['pendiente', 'rechazado', 'excepcion'])
                ->exists();

            if ($hayBoletas) {
                $this->info("Generando Resumen Diario de Boletas para la fecha: " . $fecha->toDateString());
                try {
                    $sunatService->generarResumenDiario($fecha);
                    $this->info("Resumen generado y enviado con éxito.");
                } catch (\Exception $e) {
                    $this->error("Error enviando Resumen Diario: " . $e->getMessage());
                    Log::error("Cron SUNAT Resumen Diario ({$fecha->toDateString()}): " . $e->getMessage());
                }
            }
        }

        $this->info('Sincronización terminada.');
        return 0;
    }
}
