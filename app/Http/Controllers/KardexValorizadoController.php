<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KardexValorizadoExport; // Si decido usar excel, pero mejor devuelvo una vista con dompdf o csv directo para no complicar

class KardexValorizadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria')->where('controla_stock', true)->where('activo', true);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->buscar.'%')
                  ->orWhere('codigo', 'like', '%'.$request->buscar.'%');
            });
        }

        $productos = $query->orderBy('nombre')->get();

        // Calculate valued stock
        $productos->transform(function ($producto) {
            $producto->valor_total = $producto->stock * $producto->precio_compra;
            return $producto;
        });

        $totalValorizado = $productos->sum('valor_total');
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('kardex_valorizado.index', compact('productos', 'totalValorizado', 'categorias'));
    }

    public function show(Producto $producto, Request $request)
    {
        if (!$producto->controla_stock) {
            return redirect()->route('kardex-valorizado.index')->with('warning', 'Este producto no controla stock.');
        }

        $movimientos = MovimientoInventario::with('user')
            ->where('producto_id', $producto->id)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Calcularemos el historial de valor
        // Para esto necesitamos iterar de abajo hacia arriba (desde el más antiguo) 
        // y calcular Saldo * Precio Compra del producto (usaremos el precio actual como base, a menos que haya registro histórico de compras)
        // Para simplificar "no inventar nada", multiplicaremos por el precio_compra actual.
        $precio_compra = $producto->precio_compra;

        $movimientos->transform(function($mov) use ($precio_compra) {
            $mov->costo_unitario = $precio_compra;
            $mov->valor_movimiento = $mov->cantidad * $precio_compra;
            $mov->saldo_valorizado = $mov->stock_nuevo * $precio_compra;
            return $mov;
        });

        return view('kardex_valorizado.show', compact('producto', 'movimientos'));
    }

    public function exportar(Request $request)
    {
        $query = Producto::with('categoria')->where('controla_stock', true)->where('activo', true);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos = $query->orderBy('nombre')->get();

        $productos->transform(function ($producto) {
            $producto->valor_total = $producto->stock * $producto->precio_compra;
            return $producto;
        });

        $totalValorizado = $productos->sum('valor_total');

        $pdf = Pdf::loadView('kardex_valorizado.pdf', compact('productos', 'totalValorizado'));
        return $pdf->download('Inventario_Valorizado_' . date('Y-m-d') . '.pdf');
    }
}
