@extends('layouts.app')
@section('title', 'Reporte de Utilidad Neta')
@section('header', 'Reporte de Utilidad Neta & Rentabilidad Real')

@section('content')
<div class="space-y-6">

    <!-- Barra de Filtro de Fechas -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-md flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
        <div>
            <h3 class="font-bold text-white text-base sm:text-lg flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-amber-500"></i>
                <span>Análisis Financiero de Ganancias</span>
            </h3>
            <p class="text-xs text-slate-400">Calculado sobre: Ventas Totales - Costo de Compra de Productos - Gastos de Caja</p>
        </div>

        <form method="GET" action="{{ route('reportes.utilidades') }}" class="flex flex-wrap items-center gap-2">
            <input type="date" name="desde" value="{{ $desde }}" class="px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
            <span class="text-slate-500 font-bold">a</span>
            <input type="date" name="hasta" value="{{ $hasta }}" class="px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs sm:text-sm shadow transition">
                <i class="fas fa-sync-alt mr-1"></i> Calcular
            </button>
        </form>
    </div>

    <!-- 4 Tarjetas de Métricas Financieras Clave -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Ventas Totales -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-md">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Ventas Totales</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-white">S/ {{ number_format($totalVentas, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-1">Ingresos brutos por cobros</p>
        </div>

        <!-- 2. Costo de Productos -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-md">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Costo Mercadería</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-amber-400">S/ {{ number_format($costoTotal, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-1">Inversión en botellas vendidas</p>
        </div>

        <!-- 3. Gastos Operativos -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-md">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Gastos / Egresos</span>
                <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center text-sm">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-rose-400">S/ {{ number_format($totalGastos, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-1">Salidas de dinero de caja</p>
        </div>

        <!-- 4. Utilidad Neta Real -->
        <div class="bg-gradient-to-br from-emerald-950/80 via-slate-900 to-slate-900 border-2 border-emerald-500/50 rounded-2xl p-5 shadow-xl shadow-emerald-950/30 relative overflow-hidden">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs text-emerald-400 font-bold uppercase tracking-wider">Utilidad Neta Real</span>
                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 rounded-full text-[10px] font-black">
                    {{ number_format($margenNeto, 1) }}% Margen
                </span>
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-400">S/ {{ number_format($utilidadNeta, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Ganancia limpia final en tu bolsillo</p>
        </div>
    </div>

    <!-- Gráfica de Ganancia Diaria & Desglose de Gastos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Gráfica Histórica -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-md flex flex-col justify-between">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-white text-sm sm:text-base flex items-center gap-2">
                    <i class="fas fa-chart-line text-emerald-400"></i>
                    <span>Evolución de Ganancia Diaria</span>
                </h4>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartUtilidades"></canvas>
            </div>
        </div>

        <!-- Desglose de Gastos por Categoría -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-md flex flex-col justify-between">
            <h4 class="font-bold text-white text-sm sm:text-base mb-3 flex items-center gap-2">
                <i class="fas fa-tags text-rose-400"></i>
                <span>Gastos por Categoría</span>
            </h4>
            <div class="space-y-3 flex-1 overflow-y-auto max-h-64">
                @forelse($gastosPorCategoria as $g)
                    <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60 flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold text-white capitalize">{{ str_replace('_', ' ', $g->categoria) }}</p>
                            <span class="text-[10px] text-slate-400">{{ $g->cantidad }} registro(s)</span>
                        </div>
                        <span class="font-mono font-bold text-sm text-rose-400">S/ {{ number_format($g->total_monto, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-slate-500 text-xs py-8">No hay egresos registrados en este periodo.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Ranking Top 10 Productos Más Rentables -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-bold text-white text-sm sm:text-base flex items-center gap-2">
                <i class="fas fa-trophy text-amber-400"></i>
                <span>Top 10 Licores y Productos Más Rentables</span>
            </h4>
            <span class="text-xs text-slate-400">Ordenado por Ganancia Neta Generada</span>
        </div>

        <!-- 📱 VISTA MÓVIL (LISTA MINIMALISTA DE TOP RENTABLES < md) -->
        <div class="md:hidden divide-y divide-slate-800">
            @forelse($topRentables as $idx => $p)
                <div class="py-2.5 flex items-center justify-between gap-2 hover:bg-slate-800/40 transition">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 font-bold text-xs flex items-center justify-center flex-shrink-0">
                            {{ $idx + 1 }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <h5 class="font-bold text-slate-100 text-xs truncate">{{ $p->nombre }}</h5>
                            <span class="text-[10px] text-slate-400 font-mono">{{ number_format($p->cantidad_vendida, 0) }} unids vendidas</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 font-mono">
                        <span class="font-black text-emerald-400 text-xs sm:text-sm block">+S/ {{ number_format($p->ganancia_soles, 2) }}</span>
                        <span class="text-[10px] text-slate-500 block">Venta: S/ {{ number_format($p->total_ingreso, 2) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-center py-6 text-slate-500 text-xs">No hay ventas registradas en este periodo.</p>
            @endforelse
        </div>

        <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 font-bold">#</th>
                        <th class="py-3 px-4 font-bold">Producto</th>
                        <th class="py-3 px-4 font-bold">Categoría</th>
                        <th class="py-3 px-4 font-bold text-center">Unids Vendidas</th>
                        <th class="py-3 px-4 font-bold text-right">Ingreso Venta</th>
                        <th class="py-3 px-4 font-bold text-right">Costo Total</th>
                        <th class="py-3 px-4 font-bold text-right text-emerald-400">Ganancia Real (S/)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-200">
                    @forelse($topRentables as $idx => $p)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-white">{{ $p->nombre }}</td>
                            <td class="py-3 px-4 text-slate-400">{{ $p->categoria ?? 'General' }}</td>
                            <td class="py-3 px-4 text-center font-mono font-bold">{{ number_format($p->cantidad_vendida, 0) }}</td>
                            <td class="py-3 px-4 text-right font-mono">S/ {{ number_format($p->total_ingreso, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-400">S/ {{ number_format($p->total_costo, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-black text-emerald-400 text-sm">
                                +S/ {{ number_format($p->ganancia_soles, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">No hay ventas registradas en el rango de fechas seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartUtilidades');
        if (!ctx) return;

        const dataDia = @json($utilidadPorDia);
        const labels = dataDia.map(d => d.fecha);
        const ventas = dataDia.map(d => d.ventas_dia);
        const ganancias = dataDia.map(d => d.ganancia);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Venta Total (S/)',
                        data: ventas,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Ganancia Limpia (S/)',
                        data: ganancias,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderWidth: 2.5,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#64748b', font: { family: 'Inter', size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#64748b', font: { family: 'Inter', size: 10 } }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
