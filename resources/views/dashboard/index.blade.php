@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Panel de Control')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<!-- Saludo -->
<div class="mb-5 sm:mb-6">
    <h1 class="text-xl sm:text-2xl font-bold text-slate-800">¡Hola, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h1>
    <p class="text-sm sm:text-base text-slate-500">Resumen de tu negocio - {{ now()->translatedFormat('l, d \d\e F') }}</p>
</div>

<!-- Tarjetas de estadísticas -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-5 sm:mb-6">
    <div class="gradient-card-1 rounded-2xl p-4 sm:p-5 text-white shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
        <div class="flex justify-between items-start mb-2 sm:mb-3">
            <div class="bg-white/20 rounded-xl w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center">
                <i class="fas fa-coins text-lg sm:text-2xl"></i>
            </div>
            <span class="text-[10px] sm:text-xs bg-white/20 px-2 py-1 rounded-full">HOY</span>
        </div>
        <p class="text-white/80 text-xs sm:text-sm">Ventas del día</p>
        <h3 class="text-xl sm:text-3xl font-bold mt-1 break-all">{{ $moneda }} {{ number_format($stats['ventas_hoy'], 2) }}</h3>
        <p class="text-white/70 text-[10px] sm:text-xs mt-2"><i class="fas fa-receipt mr-1"></i>{{ $stats['cantidad_ventas_hoy'] }} tickets</p>
    </div>

    <div class="gradient-card-2 rounded-2xl p-4 sm:p-5 text-white shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
        <div class="flex justify-between items-start mb-2 sm:mb-3">
            <div class="bg-white/20 rounded-xl w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center">
                <i class="fas fa-chart-line text-lg sm:text-2xl"></i>
            </div>
            <span class="text-[10px] sm:text-xs bg-white/20 px-2 py-1 rounded-full">MES</span>
        </div>
        <p class="text-white/80 text-xs sm:text-sm">Ventas del mes</p>
        <h3 class="text-xl sm:text-3xl font-bold mt-1 break-all">{{ $moneda }} {{ number_format($stats['ventas_mes'], 2) }}</h3>
        <p class="text-white/70 text-[10px] sm:text-xs mt-2"><i class="fas fa-receipt mr-1"></i>{{ $stats['cantidad_ventas_mes'] }} tickets</p>
    </div>

    <div class="gradient-card-3 rounded-2xl p-4 sm:p-5 text-white shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
        <div class="flex justify-between items-start mb-2 sm:mb-3">
            <div class="bg-white/20 rounded-xl w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center">
                <i class="fas fa-box text-lg sm:text-2xl"></i>
            </div>
            <span class="text-[10px] sm:text-xs bg-white/20 px-2 py-1 rounded-full">STOCK</span>
        </div>
        <p class="text-white/80 text-xs sm:text-sm">Productos</p>
        <h3 class="text-xl sm:text-3xl font-bold mt-1">{{ $stats['productos_total'] }}</h3>
        <p class="text-white/70 text-[10px] sm:text-xs mt-2">
            <i class="fas fa-exclamation-triangle mr-1"></i>{{ $stats['productos_stock_bajo'] }} con stock bajo
        </p>
    </div>

    <div class="gradient-card-4 rounded-2xl p-4 sm:p-5 text-white shadow-lg hover:shadow-xl transition transform hover:-translate-y-1">
        <div class="flex justify-between items-start mb-2 sm:mb-3">
            <div class="bg-white/20 rounded-xl w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center">
                <i class="fas fa-users text-lg sm:text-2xl"></i>
            </div>
            <span class="text-[10px] sm:text-xs bg-white/20 px-2 py-1 rounded-full">ACTIVOS</span>
        </div>
        <p class="text-white/80 text-xs sm:text-sm">Clientes</p>
        <h3 class="text-xl sm:text-3xl font-bold mt-1">{{ $stats['clientes_total'] }}</h3>
        <p class="text-white/70 text-[10px] sm:text-xs mt-2"><i class="fas fa-clock mr-1"></i>{{ $stats['productos_vencer'] }} por vencer</p>
    </div>
</div>

<!-- Gráficos principales -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-800">Ventas - Últimos 7 días</h3>
                <p class="text-sm text-slate-500">Evolución de las ventas diarias</p>
            </div>
            <div class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold w-fit">
                <i class="fas fa-arrow-up mr-1"></i>En tiempo real
            </div>
        </div>
        <div class="relative" style="height: 280px;">
            <canvas id="ventasChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-5 sm:p-6">
        <h3 class="font-bold text-slate-800 mb-4">Ventas por Categoría</h3>
        <div class="relative" style="height: 200px;">
            <canvas id="categoriasChart"></canvas>
        </div>
        <div class="mt-4 space-y-2 max-h-32 overflow-y-auto">
            @foreach($ventasCategoria as $cat)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: {{ $cat->color }}"></span>
                        <span class="text-slate-700">{{ $cat->nombre }}</span>
                    </div>
                    <span class="font-semibold text-slate-800">{{ $moneda }}{{ number_format($cat->total, 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ====== NUEVOS GRÁFICOS ESTADÍSTICOS ====== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <!-- Gráfico: Ventas por Día de la Semana -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
            <div>
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-calendar-week text-purple-600 text-sm"></i>
                    </span>
                    Ventas por Día de la Semana
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 ml-10">Últimos 30 días - identifica tus días más fuertes</p>
            </div>
            <div class="bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold w-fit">
                <i class="fas fa-chart-bar mr-1"></i>30 días
            </div>
        </div>
        <div class="relative" style="min-height:240px;">
            <canvas id="diaSemanaChart"></canvas>
        </div>
    </div>

    <!-- Gráfico: Distribución por Forma de Pago -->
    <div class="bg-white rounded-2xl shadow-md p-5 sm:p-6">
        <div class="mb-4">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-credit-card text-blue-600 text-sm"></i>
                </span>
                Forma de Pago
            </h3>
            <p class="text-xs text-slate-500 ml-10">Mes actual</p>
        </div>
        <div class="relative" style="min-height:180px;">
            <canvas id="formaPagoChart"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            @foreach($formasPago as $fp)
                @php
                    $iconos = ['efectivo' => ['fa-money-bill-wave', '#10b981'], 'tarjeta' => ['fa-credit-card', '#3b82f6'], 'transferencia' => ['fa-mobile-alt', '#a855f7'], 'credito' => ['fa-handshake', '#f59e0b'], 'mixto' => ['fa-coins', '#ec4899']];
                    $info = $iconos[$fp->forma_pago] ?? ['fa-coins', '#64748b'];
                @endphp
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:{{ $info[1] }}20">
                            <i class="fas {{ $info[0] }} text-xs" style="color:{{ $info[1] }}"></i>
                        </span>
                        <span class="text-slate-700 capitalize">{{ $fp->forma_pago }}</span>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-slate-800 text-sm">{{ $moneda }}{{ number_format($fp->total, 2) }}</p>
                        <p class="text-[10px] text-slate-400">{{ $fp->cantidad }} tickets</p>
                    </div>
                </div>
            @endforeach
            @if($formasPago->isEmpty())
                <p class="text-center text-slate-400 text-sm py-3">Sin datos del mes</p>
            @endif
        </div>
    </div>
</div>

<!-- Top Clientes -->
<div class="bg-white rounded-2xl shadow-md p-5 sm:p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
        <div>
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center">
                    <i class="fas fa-crown text-pink-600 text-sm"></i>
                </span>
                Top 5 Clientes del Mes
            </h3>
            <p class="text-xs sm:text-sm text-slate-500 ml-10">Clientes que más han gastado este mes</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 w-fit">Ver todos →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @forelse($topClientes as $i => $cl)
            <div class="relative bg-gradient-to-br {{ ['from-yellow-50 to-amber-50', 'from-slate-50 to-gray-50', 'from-orange-50 to-red-50', 'from-blue-50 to-indigo-50', 'from-emerald-50 to-teal-50'][$i] }} rounded-xl p-4 hover:shadow-md transition">
                <div class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold
                    {{ ['bg-yellow-500', 'bg-slate-400', 'bg-orange-600', 'bg-blue-500', 'bg-emerald-500'][$i] }}">
                    {{ $i + 1 }}
                </div>
                <div class="w-12 h-12 gradient-primary rounded-full flex items-center justify-center text-white font-bold text-lg mb-2">
                    {{ strtoupper(substr($cl->nombres, 0, 1)) }}
                </div>
                <p class="font-semibold text-slate-800 text-sm truncate">{{ $cl->nombres }} {{ $cl->apellidos }}</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $moneda }}{{ number_format($cl->total_gastado, 0) }}</p>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-slate-500"><i class="fas fa-shopping-bag mr-1"></i>{{ $cl->cantidad_compras }}</span>
                    <span class="text-yellow-600"><i class="fas fa-star mr-1"></i>{{ $cl->puntos_fidelidad }}</span>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-slate-400 py-6 text-sm">No hay compras registradas con clientes este mes</p>
        @endforelse
    </div>
</div>

<!-- Productos top y stock crítico -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-2xl shadow-md p-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-slate-800 text-sm sm:text-base"><i class="fas fa-trophy text-yellow-500 mr-2"></i>Más vendidos del mes</h3>
            <a href="{{ route('reportes.productos') }}" class="text-xs sm:text-sm text-emerald-600 hover:text-emerald-700">Ver todos →</a>
        </div>
        <div class="space-y-3">
            @forelse($productosTop as $i => $p)
                <div class="flex items-center gap-3 p-3 hover:bg-slate-50 rounded-lg transition">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold flex-shrink-0
                        {{ $i == 0 ? 'bg-yellow-500' : ($i == 1 ? 'bg-slate-400' : ($i == 2 ? 'bg-orange-600' : 'bg-slate-300')) }}">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $p->nombre }}</p>
                        <p class="text-xs text-slate-500">{{ $p->codigo }} • {{ number_format($p->total_vendido, 0) }} unidades</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-emerald-600">{{ $moneda }}{{ number_format($p->total_ingresos, 2) }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-slate-400 py-8">No hay ventas registradas este mes</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-slate-800 text-sm sm:text-base"><i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>Stock crítico</h3>
            <a href="{{ route('productos.index', ['estado' => 'stock_bajo']) }}" class="text-xs sm:text-sm text-emerald-600 hover:text-emerald-700">Ver todos →</a>
        </div>
        <div class="space-y-3">
            @forelse($stockCritico as $p)
                <div class="flex items-center gap-3 p-3 bg-red-50 rounded-lg">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-box text-red-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $p->nombre }}</p>
                        <p class="text-xs text-slate-500">{{ $p->codigo }} • Mín: {{ number_format($p->stock_minimo, 2) }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-2xl font-bold text-red-600">{{ number_format($p->stock, 2) }}</p>
                        <p class="text-xs text-slate-500">{{ $p->unidad_medida }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-emerald-600">
                    <i class="fas fa-check-circle text-4xl mb-2"></i>
                    <p>¡Todos los productos tienen stock adecuado!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Últimas ventas -->
<div class="bg-white rounded-2xl shadow-md p-5 sm:p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-slate-800 text-sm sm:text-base"><i class="fas fa-history text-blue-500 mr-2"></i>Últimas ventas</h3>
        <a href="{{ route('ventas.index') }}" class="text-xs sm:text-sm text-emerald-600 hover:text-emerald-700">Ver todas →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-500 uppercase border-b border-slate-200">
                <tr>
                    <th class="text-left py-2 px-2">Ticket</th>
                    <th class="text-left py-2 px-2">Cliente</th>
                    <th class="text-left py-2 px-2 hide-mobile">Cajero</th>
                    <th class="text-left py-2 px-2 hide-mobile">Pago</th>
                    <th class="text-right py-2 px-2">Total</th>
                    <th class="text-left py-2 px-2 hide-mobile">Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($ultimasVentas as $v)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-3 px-2 font-mono text-xs font-semibold text-slate-700">{{ $v->numero_ticket }}</td>
                        <td class="py-3 px-2">{{ $v->cliente?->nombre_completo ?? 'Cliente Genérico' }}</td>
                        <td class="py-3 px-2 hide-mobile">{{ $v->user->name }}</td>
                        <td class="py-3 px-2 hide-mobile">
                            <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">{{ ucfirst($v->forma_pago) }}</span>
                        </td>
                        <td class="py-3 px-2 text-right font-semibold text-emerald-600">{{ $moneda }} {{ number_format($v->total, 2) }}</td>
                        <td class="py-3 px-2 text-xs text-slate-500 hide-mobile">{{ $v->fecha_venta->diffForHumans() }}</td>
                        <td class="py-3 px-2 text-right">
                            <a href="{{ route('ventas.show', $v->id) }}" class="text-emerald-600 hover:text-emerald-700">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-slate-400">Sin ventas registradas aún</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
const ventasData = @json($ventasSemana);
const labels = ventasData.map(v => {
    const d = new Date(v.fecha);
    return d.toLocaleDateString('es', { day:'numeric', month:'short' });
});
const valores = ventasData.map(v => parseFloat(v.total));

new Chart(document.getElementById('ventasChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Ventas',
            data: valores,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: ctx => '{{ $moneda }} ' + parseFloat(ctx.parsed.y).toFixed(2)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { callback: v => '{{ $moneda }}' + v, font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});

const categoriasData = @json($ventasCategoria);
new Chart(document.getElementById('categoriasChart'), {
    type: 'doughnut',
    data: {
        labels: categoriasData.map(c => c.nombre),
        datasets: [{
            data: categoriasData.map(c => parseFloat(c.total)),
            backgroundColor: categoriasData.map(c => c.color),
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '65%'
    }
});

// === NUEVO: Gráfico Ventas por Día de la Semana ===
const diaSemanaData = @json($ventasPorDia);
const ctxDiaSemana = document.getElementById('diaSemanaChart').getContext('2d');
const gradient1 = ctxDiaSemana.createLinearGradient(0, 0, 0, 250);
gradient1.addColorStop(0, 'rgba(168, 85, 247, 0.85)');
gradient1.addColorStop(1, 'rgba(168, 85, 247, 0.3)');

new Chart(ctxDiaSemana, {
    type: 'bar',
    data: {
        labels: diaSemanaData.map(d => d.dia.substring(0, 3)),
        datasets: [{
            label: 'Ventas (' + '{{ $moneda }}' + ')',
            data: diaSemanaData.map(d => parseFloat(d.total)),
            backgroundColor: gradient1,
            borderColor: '#a855f7',
            borderWidth: 0,
            borderRadius: 10,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: ctx => '{{ $moneda }} ' + parseFloat(ctx.parsed.y).toFixed(2) +
                        ' (' + diaSemanaData[ctx.dataIndex].cantidad + ' tickets)'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: {
                    callback: v => '{{ $moneda }}' + v,
                    font: { size: 11 }
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});

// === NUEVO: Gráfico Forma de Pago (Donut) ===
const fpData = @json($formasPago);
const fpColors = {
    'efectivo': '#10b981',
    'tarjeta': '#3b82f6',
    'transferencia': '#a855f7',
    'credito': '#f59e0b',
    'mixto': '#ec4899'
};

if (fpData.length > 0) {
    new Chart(document.getElementById('formaPagoChart'), {
        type: 'doughnut',
        data: {
            labels: fpData.map(f => f.forma_pago.charAt(0).toUpperCase() + f.forma_pago.slice(1)),
            datasets: [{
                data: fpData.map(f => parseFloat(f.total)),
                backgroundColor: fpData.map(f => fpColors[f.forma_pago] || '#64748b'),
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ctx.label + ': {{ $moneda }} ' + parseFloat(ctx.parsed).toFixed(2)
                    }
                }
            },
            cutout: '70%'
        }
    });
}

// Hacer responsivos los charts también el de la línea de ventas semanales y categorías:
[document.getElementById('ventasChart')?.chart, document.getElementById('categoriasChart')?.chart].forEach(c => {
    if (c && c.options) c.options.maintainAspectRatio = false;
});
</script>
@endsection
