<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\TurnoCaja;
use App\Models\MovimientoInventario;
use App\Models\Empresa;
use App\Models\PuntoFidelidad;
use App\Services\SunatService;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'user', 'comprobanteElectronico']);

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->where('numero_ticket', 'LIKE', "%$b%")
                  ->orWhereHas('cliente', function($qc) use ($b) {
                      $qc->where('nombres', 'LIKE', "%$b%")
                        ->orWhere('documento', 'LIKE', "%$b%");
                  })
                  ->orWhereHas('comprobanteElectronico', function($qce) use ($b) {
                      $qce->where('numero_completo', 'LIKE', "%$b%");
                  });
            });
        }
        if ($request->filled('tipo_comprobante')) {
            $query->where('tipo_comprobante', $request->tipo_comprobante);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $ventas = $query->orderByDesc('fecha_venta')->paginate(20);
        return view('ventas.index', compact('ventas'));
    }

    public function pos()
    {
        $turnoActivo = TurnoCaja::where('user_id', auth()->id())
            ->where('estado', 'abierto')->first();

        if (!$turnoActivo) {
            return redirect()->route('caja.index')
                ->with('warning', 'Debe abrir un turno de caja antes de realizar ventas');
        }

        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->where('destacado', true)
            ->limit(12)->get();

        $categorias = \App\Models\Categoria::where('activo', true)->orderBy('nombre')->get();
        $clientes = Cliente::where('activo', true)->orderBy('nombres')->limit(50)->get();
        $metodosPago = class_exists(\App\Models\MetodoPago::class) && \Illuminate\Support\Facades\Schema::hasTable('metodos_pago')
            ? \App\Models\MetodoPago::activo()->get()
            : collect();

        return view('ventas.pos', compact('productos', 'categorias', 'clientes', 'turnoActivo', 'metodosPago'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_documento' => 'nullable|string|max:30',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_direccion' => 'nullable|string|max:255',
            'forma_pago' => 'required|string',
            'referencia_pago' => 'nullable|string|max:100',
            'monto_recibido' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
            'tipo_comprobante' => 'nullable|in:TICKET,BOLETA,FACTURA',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $turnoActivo = TurnoCaja::where('user_id', auth()->id())
            ->where('estado', 'abierto')->first();

        if (!$turnoActivo) {
            return response()->json(['error' => 'No hay turno de caja abierto'], 400);
        }

        $empresa = Empresa::first();
        $tasaImpuesto = $empresa ? $empresa->impuesto / 100 : 0;
        $impuestoIncluido = $empresa ? $empresa->impuesto_incluido : true;

        DB::beginTransaction();
        try {
            // Resolver o auto-crear cliente si se enviaron datos desde el modal
            $clienteId = $data['cliente_id'] ?? null;
            if (!$clienteId && !empty($request->cliente_documento)) {
                $doc = preg_replace('/\D/', '', (string) $request->cliente_documento);
                $clienteExistente = Cliente::where('documento', $doc)->first();
                if ($clienteExistente) {
                    $clienteId = $clienteExistente->id;
                } else {
                    $tipoDoc = strlen($doc) === 11 ? 'RUC' : (strlen($doc) === 8 ? 'DNI' : 'OTRO');
                    $nuevoCl = Cliente::create([
                        'codigo' => 'CL' . str_pad(Cliente::count() + 1, 5, '0', STR_PAD_LEFT),
                        'tipo_documento' => $tipoDoc,
                        'documento' => $doc,
                        'nombres' => $request->cliente_nombre ?: ($tipoDoc === 'RUC' ? 'Cliente RUC ' . $doc : 'Cliente DNI ' . $doc),
                        'apellidos' => '',
                        'razon_social' => $tipoDoc === 'RUC' ? ($request->cliente_nombre ?: null) : null,
                        'direccion' => $request->cliente_direccion ?: null,
                        'activo' => true,
                    ]);
                    $clienteId = $nuevoCl->id;
                }
            }

            $subtotal = 0;
            $impuestoTotal = 0;

            foreach ($data['items'] as $item) {
                $producto = Producto::find($item['producto_id']);
                $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
                $subtotal += $itemSubtotal;

                if ($producto->aplica_impuesto && !$impuestoIncluido) {
                    $impuestoTotal += $itemSubtotal * $tasaImpuesto;
                }
            }

            $descuento = $data['descuento'] ?? 0;
            $total = $subtotal - $descuento + ($impuestoIncluido ? 0 : $impuestoTotal);

            if ($impuestoIncluido) {
                $impuestoTotal = $total - ($total / (1 + $tasaImpuesto));
            }

            $cambio = $data['monto_recibido'] - $total;
            if ($cambio < 0) $cambio = 0;

            // Generar número de ticket seguro contra colisiones
            $serieTicket = 'T001';
            $maxCorrelativo = Venta::where('numero_ticket', 'LIKE', "{$serieTicket}-%")
                ->selectRaw("MAX(CAST(SUBSTRING_INDEX(numero_ticket, '-', -1) AS UNSIGNED)) as max_num")
                ->value('max_num') ?? 0;

            $numero = max((int)$maxCorrelativo, Venta::count()) + 1;
            $numeroTicket = $serieTicket . '-' . str_pad($numero, 8, '0', STR_PAD_LEFT);

            while (Venta::where('numero_ticket', $numeroTicket)->exists()) {
                $numero++;
                $numeroTicket = $serieTicket . '-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
            }

            $tipoComprobante = $data['tipo_comprobante'] ?? 'TICKET';
            $observaciones = $data['observaciones'] ?? null;
            if (!empty($request->referencia_pago)) {
                $observaciones = trim(($observaciones ? $observaciones . ' | ' : '') . 'Ref. Pago: ' . $request->referencia_pago);
            }

            $venta = Venta::create([
                'numero_ticket' => $numeroTicket,
                'tipo_comprobante' => $tipoComprobante,
                'serie' => 'T001',
                'fecha_venta' => now(),
                'cliente_id' => $clienteId,
                'user_id' => auth()->id(),
                'turno_caja_id' => $turnoActivo->id,
                'subtotal' => $subtotal - $impuestoTotal,
                'descuento' => $descuento,
                'impuesto' => $impuestoTotal,
                'total' => $total,
                'monto_recibido' => $data['monto_recibido'],
                'cambio' => $cambio,
                'forma_pago' => $data['forma_pago'],
                'estado' => 'completada',
                'observaciones' => $observaciones,
            ]);

            foreach ($data['items'] as $item) {
                $producto = Producto::find($item['producto_id']);
                $itemSubtotal = $item['cantidad'] * $item['precio_unitario'];
                $itemImpuesto = ($producto->aplica_impuesto && !$impuestoIncluido)
                    ? $itemSubtotal * $tasaImpuesto : 0;

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => 0,
                    'impuesto' => $itemImpuesto,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemSubtotal + $itemImpuesto,
                ]);

                if ($producto->controla_stock) {
                    $stockAnterior = $producto->stock;
                    $stockNuevo = max(0, $stockAnterior - $item['cantidad']);
                    $producto->update(['stock' => $stockNuevo]);

                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'salida',
                        'motivo' => 'Venta #' . $numeroTicket,
                        'cantidad' => $item['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'referencia_tipo' => 'venta',
                        'referencia_id' => $venta->id,
                        'fecha' => now(),
                    ]);
                }
            }

            // Actualizar totales del turno
            $turnoActivo->increment('total_ventas', $total);
            $turnoActivo->increment('cantidad_ventas');
            if ($data['forma_pago'] === 'efectivo') {
                $turnoActivo->increment('total_efectivo', $total);
            } elseif ($data['forma_pago'] === 'tarjeta') {
                $turnoActivo->increment('total_tarjeta', $total);
            } else {
                $turnoActivo->increment('total_otros', $total);
            }

            // Puntos de fidelidad (1 punto por cada $10)
            if ($data['cliente_id']) {
                $cliente = Cliente::find($data['cliente_id']);
                $puntos = floor($total / 10);
                if ($puntos > 0) {
                    $saldoAnterior = $cliente->puntos_fidelidad;
                    $cliente->increment('puntos_fidelidad', $puntos);
                    PuntoFidelidad::create([
                        'cliente_id' => $cliente->id,
                        'venta_id' => $venta->id,
                        'tipo' => 'ganado',
                        'puntos' => $puntos,
                        'saldo_anterior' => $saldoAnterior,
                        'saldo_nuevo' => $saldoAnterior + $puntos,
                        'descripcion' => 'Venta ' . $numeroTicket,
                    ]);
                }
            }

            // Auto-emisión de comprobante electrónico (Boleta/Factura)
            $cpeInfo = null;
            if (in_array($tipoComprobante, ['BOLETA', 'FACTURA']) && $empresa && $empresa->facturacion_electronica_activa) {
                try {
                    $sunatService = new SunatService();
                    $tipoSunat = $tipoComprobante === 'FACTURA' ? '01' : '03';
                    $cpe = $sunatService->emitirComprobante($venta, $tipoSunat);

                    // Intentar enviar automáticamente a SUNAT
                    try {
                        $resultEnvio = $sunatService->enviarASunat($cpe);
                        $cpe->refresh();
                    } catch (\Throwable $sunatError) {
                        // Si falla SUNAT, el CPE queda como 'pendiente' para reintento manual
                    }

                    $cpeInfo = [
                        'cpe_id' => $cpe->id,
                        'cpe_numero' => $cpe->numero_completo,
                        'cpe_estado' => ucfirst($cpe->estado_sunat),
                        'cpe_url' => route('facturacion.show', $cpe->id),
                        'redirect' => route('facturacion.ticket', $cpe->id),
                    ];
                } catch (\Throwable $cpeError) {
                    // No bloqueamos la venta si falla el CPE
                    $cpeInfo = [
                        'cpe_error' => $cpeError->getMessage(),
                    ];
                }
            }

            DB::commit();
            $redirectUrl = (!empty($cpeInfo['redirect'])) ? $cpeInfo['redirect'] : route('ventas.ticket', $venta->id);

            return response()->json(array_merge([
                'success' => true,
                'venta_id' => $venta->id,
                'numero_ticket' => $numeroTicket,
                'redirect' => $redirectUrl,
            ], $cpeInfo ?? []));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar venta: ' . $e->getMessage()], 500);
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['cliente', 'user', 'detalles.producto']);
        return view('ventas.show', compact('venta'));
    }

    public function ticket(Venta $venta)
    {
        $venta->load(['cliente', 'user', 'detalles']);
        return view('ventas.ticket', compact('venta'));
    }

    public function anular(Venta $venta)
    {
        if ($venta->estado === 'anulada') {
            return back()->with('error', 'La venta ya está anulada');
        }

        DB::beginTransaction();
        try {
            // Devolver stock
            foreach ($venta->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto && $producto->controla_stock) {
                    $stockAnterior = $producto->stock;
                    $stockNuevo = $stockAnterior + $detalle->cantidad;
                    $producto->update(['stock' => $stockNuevo]);

                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'entrada',
                        'motivo' => 'Anulación venta #' . $venta->numero_ticket,
                        'cantidad' => $detalle->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'referencia_tipo' => 'venta_anulada',
                        'referencia_id' => $venta->id,
                        'fecha' => now(),
                    ]);
                }
            }

            $venta->update(['estado' => 'anulada']);
            DB::commit();

            return back()->with('success', 'Venta anulada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }
}
