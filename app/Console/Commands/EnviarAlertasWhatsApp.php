<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;
use App\Models\Empresa;
use Illuminate\Support\Facades\Http;

class EnviarAlertasWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alertas:whatsapp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica stock bajo y vencimientos para enviar alerta por WhatsApp al administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $empresa = Empresa::first();

        if (!$empresa || empty($empresa->whatsapp_alertas)) {
            $this->error('No hay un número de WhatsApp configurado.');
            return;
        }

        $numero = $empresa->whatsapp_alertas;
        $mensaje = "🚨 *REPORTE DIARIO DE LICORERÍA* 🚨\n\n";

        // 1. Stock Bajo
        $stockBajo = Producto::where('controla_stock', 1)
            ->where('activo', 1)
            ->whereRaw('stock <= stock_minimo')
            ->get();

        if ($stockBajo->count() > 0) {
            $mensaje .= "📉 *Stock Crítico:*\n";
            foreach ($stockBajo as $p) {
                $mensaje .= "- {$p->nombre}: quedan solo {$p->stock} unidades.\n";
            }
            $mensaje .= "\n";
        }

        // 2. Vencimientos en los próximos 30 días
        $vencimientos = Producto::whereNotNull('fecha_vencimiento')
            ->where('activo', 1)
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(30))
            ->get();

        if ($vencimientos->count() > 0) {
            $mensaje .= "⏰ *Próximos a Vencer (30 días):*\n";
            foreach ($vencimientos as $p) {
                $mensaje .= "- {$p->nombre}: vence el {$p->fecha_vencimiento->format('d/m/Y')}\n";
            }
        }

        if ($stockBajo->count() === 0 && $vencimientos->count() === 0) {
            $mensaje .= "✅ Todo está en orden. No hay stock bajo ni productos por vencer.";
        }

        // Aquí iría la integración con tu API de WhatsApp favorita (UltraMsg, CallMeBot, Twilio, Meta)
        // Ejemplo genérico (comentado)
        /*
        Http::post('https://api.tu-proveedor.com/send', [
            'phone' => $numero,
            'message' => $mensaje,
            'apikey' => env('WHATSAPP_API_KEY')
        ]);
        */

        $this->info("Mensaje generado para: {$numero}");
        $this->line($mensaje);

        // Como alternativa inmediata y gratuita para el administrador, podemos guardar esto en la BD 
        // para mostrarlo en el Dashboard con un link "wa.me" o simplemente imprimirlo en consola.
        return Command::SUCCESS;
    }
}
