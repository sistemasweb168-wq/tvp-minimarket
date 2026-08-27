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

    public function utilidades(Request $request)
    {
        $desde = $request->get('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', Carbon::now()->toDateString());

        // 1. Ingresos por ventas y costo total de mercadería vendida (COGS)
        $ventasData = DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->select(
                DB::raw('SUM(venta_detalles.total) as total_ventas'),
                DB::raw('SUM(venta_detalles.cantidad * IF(venta_detalles.precio_compra > 0, venta_detalles.precio_compra, productos.precio_compra)) as costo_total')
            )
            ->first();

        $totalVentas = $ventasData->total_ventas ?? 0;
        $costoTotal = $ventasData->costo_total ?? 0;
        $utilidadBruta = $totalVentas - $costoTotal;

        // 2. Gastos operativos y egresos de caja
        $gastosQuery = DB::table('movimientos_caja')
            ->where('tipo', 'egreso')
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);

        $totalGastos = $gastosQuery->sum('monto') ?? 0;
        $gastosPorCategoria = (clone $gastosQuery)
            ->select('categoria', DB::raw('SUM(monto) as total_monto'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('categoria')
            ->get();

        // 3. Utilidad neta y margen
        $utilidadNeta = $utilidadBruta - $totalGastos;
        $margenNeto = $totalVentas > 0 ? ($utilidadNeta / $totalVentas) * 100 : 0;

        // 4. Ranking de productos más rentables (Top 10 en ganancia en Soles)
        $topRentables = DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->select(
                'productos.id', 'productos.nombre', 'categorias.nombre as categoria',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad_vendida'),
                DB::raw('SUM(venta_detalles.total) as total_ingreso'),
                DB::raw('SUM(venta_detalles.cantidad * IF(venta_detalles.precio_compra > 0, venta_detalles.precio_compra, productos.precio_compra)) as total_costo'),
                DB::raw('SUM(venta_detalles.total) - SUM(venta_detalles.cantidad * IF(venta_detalles.precio_compra > 0, venta_detalles.precio_compra, productos.precio_compra)) as ganancia_soles')
            )
            ->groupBy('productos.id', 'productos.nombre', 'categorias.nombre')
            ->orderByDesc('ganancia_soles')
            ->limit(10)
            ->get();

        // 5. Utilidad diaria para gráfico
        $utilidadPorDia = DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->select(
                DB::raw('DATE(ventas.fecha_venta) as fecha'),
                DB::raw('SUM(venta_detalles.total) as ventas_dia'),
                DB::raw('SUM(venta_detalles.cantidad * IF(venta_detalles.precio_compra > 0, venta_detalles.precio_compra, productos.precio_compra)) as costo_dia')
            )
            ->groupBy(DB::raw('DATE(ventas.fecha_venta)'))
            ->orderBy('fecha')
            ->get()
            ->map(function($row) {
                $row->ganancia = $row->ventas_dia - $row->costo_dia;
                return $row;
            });

        return view('reportes.utilidades', compact(
            'totalVentas', 'costoTotal', 'utilidadBruta', 'totalGastos',
            'utilidadNeta', 'margenNeto', 'gastosPorCategoria', 'topRentables',
            'utilidadPorDia', 'desde', 'hasta'
        ));
    }
}
