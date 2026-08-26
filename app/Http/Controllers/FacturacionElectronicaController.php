<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ComprobanteElectronico;
use App\Models\Venta;
use App\Models\Empresa;
use App\Models\SerieDocumento;
use App\Models\ResumenDiario;
use App\Models\ComunicacionBaja;
use App\Services\SunatService;
use Carbon\Carbon;

class FacturacionElectronicaController extends Controller
{
    /** Listado de comprobantes electrónicos */
    public function index(Request $request)
    {
        $query = ComprobanteElectronico::with(['venta.cliente', 'user']);

        if ($request->filled('tipo')) {
            $query->where('tipo_documento', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado_sunat', $request->estado);
        }
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('numero_completo', 'LIKE', "%$b%")
                  ->orWhere('receptor_numero_doc', 'LIKE', "%$b%")
                  ->orWhere('receptor_razon_social', 'LIKE', "%$b%");
            });
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        $comprobantes = $query->orderByDesc('fecha_emision')->orderByDesc('id')->paginate(20);

        $stats = [
            'total' => ComprobanteElectronico::count(),
            'aceptados' => ComprobanteElectronico::where('estado_sunat', 'aceptado')->count(),
            'pendientes' => ComprobanteElectronico::whereIn('estado_sunat', ['pendiente', 'enviado'])->count(),
            'rechazados' => ComprobanteElectronico::whereIn('estado_sunat', ['rechazado', 'excepcion'])->count(),
        ];

        return view('facturacion.index', compact('comprobantes', 'stats'));
    }

    /** Detalle de un comprobante */
    public function show(ComprobanteElectronico $comprobante)
    {
        $comprobante->load(['venta.detalles.producto', 'venta.cliente', 'user']);
        return view('facturacion.show', compact('comprobante'));
    }

    /** Emite un comprobante electrónico a partir de una venta */
    public function emitir(Request $request, Venta $venta)
    {
        $request->validate([
            'tipo_documento' => 'required|in:01,03',
        ]);

        try {
            $service = new SunatService();
            $comprobante = $service->emitirComprobante($venta, $request->tipo_documento);

            return redirect()->route('facturacion.show', $comprobante->id)
                ->with('success', 'Comprobante ' . $comprobante->numero_completo . ' generado. Click en "Enviar a SUNAT" para validarlo.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /** Envía a SUNAT un comprobante pendiente */
    public function enviar(ComprobanteElectronico $comprobante)
    {
        try {
            $service = new SunatService();
            $result = $service->enviarASunat($comprobante);

            if ($result['success']) {
                return back()->with('success', '✓ Aceptado por SUNAT: ' . $result['mensaje']);
            } else {
                return back()->with('error', '✗ SUNAT rechazó: ' . $result['mensaje'] . ' (código ' . $result['codigo'] . ')');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /** Descarga el XML firmado */
    public function descargarXml(ComprobanteElectronico $comprobante)
    {
        if (!$comprobante->xml_path || !Storage::exists($comprobante->xml_path)) {
            return back()->with('error', 'El XML no existe. Primero envía el comprobante a SUNAT.');
        }
        return Storage::download($comprobante->xml_path, $comprobante->numero_completo . '.xml');
    }

    /** Descarga el CDR de SUNAT */
    public function descargarCdr(ComprobanteElectronico $comprobante)
    {
        if (!$comprobante->cdr_path || !Storage::exists($comprobante->cdr_path)) {
            return back()->with('error', 'El CDR no existe aún.');
        }
        return Storage::download($comprobante->cdr_path, 'R-' . $comprobante->numero_completo . '.zip');
    }

    /** Anula un comprobante mediante Nota de Crédito o Comunicación de Baja */
    public function anular(Request $request, ComprobanteElectronico $comprobante)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'metodo' => 'required|in:nota_credito,comunicacion_baja',
        ]);

        try {
            $service = new SunatService();

            if ($request->metodo === 'nota_credito') {
                $nc = $service->emitirNotaCredito($comprobante->venta, $request->motivo, '01');
                return redirect()->route('facturacion.show', $nc->id)
                    ->with('success', 'Nota de Crédito ' . $nc->numero_completo . ' generada');
            } else {
                $cb = ComunicacionBaja::create([
                    'identificador' => 'RA-' . now()->format('Ymd') . '-' . str_pad(ComunicacionBaja::whereDate('created_at', now())->count() + 1, 3, '0', STR_PAD_LEFT),
                    'comprobante_id' => $comprobante->id,
                    'fecha_generacion' => now()->toDateString(),
                    'motivo' => $request->motivo,
                    'estado_sunat' => 'pendiente',
                    'user_id' => auth()->id(),
                ]);
                $comprobante->update(['estado_sunat' => 'baja']);
                return back()->with('success', 'Comunicación de baja ' . $cb->identificador . ' generada');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /** Configuración SUNAT */
    public function configuracion()
    {
        $empresa = Empresa::first() ?? new Empresa();
        $series = SerieDocumento::orderBy('tipo_documento')->orderBy('serie')->get();
        return view('facturacion.configuracion', compact('empresa', 'series'));
    }

    /** Guardar configuración SUNAT */
    public function guardarConfiguracion(Request $request)
    {
        $data = $request->validate([
            'sunat_modo' => 'required|in:beta,produccion',
            'sunat_usuario_sol' => 'nullable|string|max:100',
            'sunat_clave_sol' => 'nullable|string|max:100',
            'sunat_certificado_password' => 'nullable|string|max:100',
            'ubigeo' => 'nullable|string|size:6',
            'departamento' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'distrito' => 'nullable|string|max:100',
            'facturacion_electronica_activa' => 'nullable|boolean',
        ]);

        $data['facturacion_electronica_activa'] = $request->boolean('facturacion_electronica_activa');

        if ($request->hasFile('sunat_certificado')) {
            $file = $request->file('sunat_certificado');
            $filename = 'certificados/sunat_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::put($filename, file_get_contents($file->getRealPath()));
            $data['sunat_certificado_path'] = $filename;
        }

        $empresa = Empresa::first();
        if ($empresa) {
            $empresa->update($data);
        } else {
            Empresa::create($data + ['razon_social' => 'Empresa Demo', 'moneda' => 'S/']);
        }

        return back()->with('success', 'Configuración SUNAT guardada correctamente');
    }

    /** Crear serie de documento */
    public function crearSerie(Request $request)
    {
        $data = $request->validate([
            'tipo_documento' => 'required|in:01,03,07,08',
            'serie' => 'required|string|size:4',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $data['correlativo_actual'] = 0;
        $data['activo'] = true;
        SerieDocumento::create($data);
        return back()->with('success', 'Serie creada');
    }

    /** Vista PDF A4 del comprobante */
    public function pdf(ComprobanteElectronico $comprobante)
    {
        $comprobante->load(['venta.detalles', 'venta.cliente']);
        return view('facturacion.pdf', compact('comprobante'));
    }

    /** Vista ticket 80mm con QR */
    public function ticket(ComprobanteElectronico $comprobante)
    {
        $comprobante->load(['venta.detalles', 'venta.cliente']);
        return view('facturacion.ticket', compact('comprobante'));
    }

    /** Listado de resúmenes diarios */
    public function resumenes()
    {
        $resumenes = ResumenDiario::with('user')->orderByDesc('fecha_resumen')->paginate(15);
        return view('facturacion.resumenes', compact('resumenes'));
    }

    /** Generar resumen diario */
    public function generarResumen(Request $request)
    {
        $request->validate(['fecha' => 'required|date|before_or_equal:today']);
        try {
            $service = new SunatService();
            $resumen = $service->generarResumenDiario(Carbon::parse($request->fecha));
            return back()->with('success', 'Resumen ' . $resumen->identificador . ' generado con ' . $resumen->cantidad_comprobantes . ' comprobantes');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
