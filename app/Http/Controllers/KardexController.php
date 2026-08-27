<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KardexController extends Controller
{
    public function index(Request $request)
    {
        $query = MovimientoInventario::with(['producto.categoria', 'user'])->orderByDesc('fecha');

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        $movimientos = $query->paginate(25)->withQueryString();
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        // Resumen estadístico
        $totalEntradas = MovimientoInventario::where('tipo', 'entrada')
            ->when($request->filled('fecha_inicio'), fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->sum('cantidad');

        $totalSalidas = MovimientoInventario::where('tipo', 'salida')
            ->when($request->filled('fecha_inicio'), fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->sum('cantidad');

        $totalMermas = MovimientoInventario::where('tipo', 'merma')
            ->when($request->filled('fecha_inicio'), fn($q) => $q->whereDate('fecha', '>=', $request->fecha_inicio))
            ->when($request->filled('fecha_fin'), fn($q) => $q->whereDate('fecha', '<=', $request->fecha_fin))
            ->sum('cantidad');

        return view('kardex.index', compact('movimientos', 'productos', 'categorias', 'totalEntradas', 'totalSalidas', 'totalMermas'));
    }

    public function registrarMerma(Request $request)
    {
        $data = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:100', // Rotura de botella, Vencimiento, Defecto de fábrica, Consumo interno, etc.
            'observaciones' => 'nullable|string|max:255',
        ]);

        $producto = Producto::findOrFail($data['producto_id']);

        if (!$producto->controla_stock) {
            return back()->with('error', 'Este producto no controla stock.');
        }

        if ($producto->stock < $data['cantidad']) {
            return back()->with('error', 'La cantidad de merma supera el stock actual disponible (' . $producto->stock . ').');
        }

        DB::beginTransaction();
        try {
            $stockAnterior = $producto->stock;
            $stockNuevo = max(0, $stockAnterior - $data['cantidad']);
            $producto->update(['stock' => $stockNuevo]);

            MovimientoInventario::create([
                'producto_id' => $producto->id,
                'user_id' => auth()->id(),
                'tipo' => 'merma',
                'motivo' => $data['motivo'],
                'cantidad' => $data['cantidad'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia_tipo' => 'merma',
                'observaciones' => $data['observaciones'] ?? 'Registro manual de merma/rotura',
                'fecha' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Merma registrada correctamente. Stock descontado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar merma: ' . $e->getMessage());
        }
    }
}
