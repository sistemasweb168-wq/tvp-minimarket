<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'user']);
        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_compra', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->fecha_hasta);
        }
        $compras = $query->orderByDesc('fecha_compra')->paginate(15);
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        return view('compras.index', compact('compras', 'proveedores'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'numero_factura' => 'nullable|string|max:50',
            'fecha_compra' => 'required|date',
            'forma_pago' => 'required|string',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['cantidad'] * $item['precio_unitario'];
            }

            $ultimo = Compra::orderByDesc('id')->first();
            $numero = $ultimo ? ((int) substr($ultimo->numero, -8)) + 1 : 1;
            $numeroCompra = 'C-' . str_pad($numero, 8, '0', STR_PAD_LEFT);

            $compra = Compra::create([
                'numero' => $numeroCompra,
                'numero_factura' => $data['numero_factura'] ?? null,
                'fecha_compra' => $data['fecha_compra'],
                'proveedor_id' => $data['proveedor_id'],
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'descuento' => 0,
                'impuesto' => 0,
                'total' => $subtotal,
                'forma_pago' => $data['forma_pago'],
                'estado' => 'recibida',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $producto = Producto::find($item['producto_id']);
                $itemTotal = $item['cantidad'] * $item['precio_unitario'];

                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $itemTotal,
                    'total' => $itemTotal,
                ]);

                if ($producto->controla_stock) {
                    $stockAnterior = $producto->stock;
                    $stockNuevo = $stockAnterior + $item['cantidad'];
                    if ($stockNuevo > 0) {
                        $costoAnteriorTotal = $stockAnterior * ($producto->precio_compra ?? 0);
                        $costoNuevoTotal = $item['cantidad'] * $item['precio_unitario'];
                        $costoPromedio = ($costoAnteriorTotal + $costoNuevoTotal) / $stockNuevo;
                    } else {
                        $costoPromedio = $item['precio_unitario'];
                    }

                    $producto->update([
                        'stock' => $stockNuevo,
                        'precio_compra' => round($costoPromedio, 4),
                    ]);

                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'entrada',
                        'motivo' => 'Compra ' . $numeroCompra,
                        'cantidad' => $item['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'referencia_tipo' => 'compra',
                        'referencia_id' => $compra->id,
                        'fecha' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('compras.show', $compra->id)
                ->with('success', 'Compra registrada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'user', 'detalles.producto']);
        return view('compras.show', compact('compra'));
    }
}
