<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Categoria;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioSemana = Carbon::now()->startOfWeek();

        // Estadísticas principales
        $ventasHoy = Venta::whereDate('fecha_venta', $hoy)->where('estado', 'completada');
        $ventasMes = Venta::whereDate('fecha_venta', '>=', $inicioMes)->where('estado', 'completada');

        $stats = [
            'ventas_hoy' => (float) $ventasHoy->sum('total'),
            'cantidad_ventas_hoy' => $ventasHoy->count(),
            'ventas_mes' => (float) $ventasMes->sum('total'),
            'cantidad_ventas_mes' => $ventasMes->count(),
            'productos_total' => Producto::where('activo', true)->count(),
            'productos_stock_bajo' => Producto::where('activo', true)
                ->where('controla_stock', true)
                ->whereColumn('stock', '<=', 'stock_minimo')->count(),
            'clientes_total' => Cliente::where('activo', true)->count(),
            'productos_vencer' => Producto::whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
                ->whereDate('fecha_vencimiento', '>=', Carbon::now())
                ->where('activo', true)->count(),
        ];

        // Ventas por día (últimos 7 días)
        $ventasSemana = Venta::select(
                DB::raw('DATE(fecha_venta) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->where('estado', 'completada')
            ->where('fecha_venta', '>=', Carbon::now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Productos más vendidos del mes
        $productosTop = DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->select('productos.id', 'productos.nombre', 'productos.codigo',
                DB::raw('SUM(venta_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(venta_detalles.total) as total_ingresos'))
            ->where('ventas.estado', 'completada')
            ->whereDate('ventas.fecha_venta', '>=', $inicioMes)
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // Ventas por categoría
        $ventasCategoria = DB::table('venta_detalles')
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->select('categorias.nombre', 'categorias.color',
                DB::raw('SUM(venta_detalles.total) as total'))
            ->where('ventas.estado', 'completada')
            ->whereDate('ventas.fecha_venta', '>=', $inicioMes)
            ->groupBy('categorias.id', 'categorias.nombre', 'categorias.color')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Últimas ventas
        $ultimasVentas = Venta::with(['cliente', 'user'])
            ->where('estado', 'completada')
            ->orderByDesc('fecha_venta')
            ->limit(8)
            ->get();

        // Productos con stock crítico
        $stockCritico = Producto::where('activo', true)
            ->where('controla_stock', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // NUEVO: Ventas por día de la semana (últimos 30 días)
        $ventasDiaSemana = DB::table('ventas')
            ->select(
                DB::raw('DAYOFWEEK(fecha_venta) as dia'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total')
            )
            ->where('estado', 'completada')
            ->whereDate('fecha_venta', '>=', Carbon::now()->subDays(30))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');

        $diasSemanaLabels = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $ventasPorDia = [];
        for ($i = 1; $i <= 7; $i++) {
            $ventasPorDia[] = [
                'dia' => $diasSemanaLabels[$i - 1],
                'cantidad' => $ventasDiaSemana[$i]->cantidad ?? 0,
                'total' => (float) ($ventasDiaSemana[$i]->total ?? 0),
            ];
        }

        // NUEVO: Distribución por forma de pago (mes actual)
        $formasPago = Venta::select('forma_pago',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total'))
            ->where('estado', 'completada')
            ->whereDate('fecha_venta', '>=', $inicioMes)
            ->groupBy('forma_pago')
            ->get();

        // NUEVO: Top 5 clientes
        $topClientes = DB::table('ventas')
            ->join('clientes', 'clientes.id', '=', 'ventas.cliente_id')
            ->select('clientes.id', 'clientes.nombres', 'clientes.apellidos',
                'clientes.puntos_fidelidad',
                DB::raw('COUNT(ventas.id) as cantidad_compras'),
                DB::raw('SUM(ventas.total) as total_gastado'))
            ->where('ventas.estado', 'completada')
            ->whereDate('ventas.fecha_venta', '>=', $inicioMes)
            ->groupBy('clientes.id', 'clientes.nombres', 'clientes.apellidos', 'clientes.puntos_fidelidad')
            ->orderByDesc('total_gastado')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'ventasSemana', 'productosTop',
            'ventasCategoria', 'ultimasVentas', 'stockCritico',
            'ventasPorDia', 'formasPago', 'topClientes'
        ));
    }
}
