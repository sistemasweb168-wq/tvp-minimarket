<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Producto;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function ventas(Request $request)
    {
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', Carbon::now()->toDateString());

        $ventas = Venta::with(['cliente', 'user'])
            ->where('estado', 'completada')
            ->whereBetween('fecha_venta', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderByDesc('fecha_venta')
            ->get();

        $totales = [
            'total' => $ventas->sum('total'),
            'subtotal' => $ventas->sum('subtotal'),
            'impuesto' => $ventas->sum('impuesto'),
            'descuento' => $ventas->sum('descuento'),
            'cantidad' => $ventas->count(),
        ];

        $porFormaPago = $ventas->groupBy('forma_pago')->map(fn($g) => [
            'cantidad' => $g->count(),
            'total' => $g->sum('total'),
        ]);

        $porDia = $ventas->groupBy(fn($v) => Carbon::parse($v->fecha_venta)->toDateString())
            ->map(fn($g) => ['cantidad' => $g->count(), 'total' => $g->sum('total')]);

        return view('reportes.ventas', compact('ventas', 'totales', 'porFormaPago', 'porDia', 'desde', 'hasta'));
    }

    public function productos(Request $request)
    {
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', Carbon::now()->toDateString());

        $productos = DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->select(
                'productos.id', 'productos.codigo', 'productos.nombre',
                'categorias.nombre as categoria',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad_vendida'),
                DB::raw('SUM(venta_detalles.total) as total_ingresos'),
                DB::raw('AVG(venta_detalles.precio_unitario) as precio_promedio')
            )
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->groupBy('productos.id', 'productos.codigo', 'productos.nombre', 'categorias.nombre')
            ->orderByDesc('cantidad_vendida')
            ->get();

        return view('reportes.productos', compact('productos', 'desde', 'hasta'));
    }

    public function inventario()
    {
        $productos = Producto::with(['categoria', 'proveedor'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $valorTotal = $productos->sum(fn($p) => $p->stock * $p->precio_compra);
        $valorVenta = $productos->sum(fn($p) => $p->stock * $p->precio_venta);

        return view('reportes.inventario', compact('productos', 'valorTotal', 'valorVenta'));
    }

    public function vencimientos()
    {
        $productos = Producto::with('categoria')
            ->whereNotNull('fecha_vencimiento')
            ->where('activo', true)
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('reportes.vencimientos', compact('productos'));
    }
}
