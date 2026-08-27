@extends('layouts.app')
@section('title', 'Reporte de Ventas')
@section('header', 'Reporte de Ventas')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <div class="flex-1 grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-semibold mb-1">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
            <div><label class="block text-sm font-semibold mb-1">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
        </div>
        <button class="self-end gradient-primary text-white px-6 py-2.5 rounded-lg font-semibold"><i class="fas fa-search mr-2"></i>Generar</button>
        <button onclick="window.print()" type="button" class="self-end bg-slate-800 text-white px-6 py-2.5 rounded-lg font-semibold"><i class="fas fa-print mr-2"></i>Imprimir</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
    <div class="gradient-card-1 rounded-2xl p-4 text-white">
        <p class="text-sm opacity-80">Total Ventas</p>
        <p class="text-2xl font-bold">{{ $moneda }}{{ number_format($totales['total'], 2) }}</p>
        <p class="text-xs opacity-80">{{ $totales['cantidad'] }} tickets</p>
    </div>
    <div class="gradient-card-2 rounded-2xl p-4 text-white">
        <p class="text-sm opacity-80">Subtotal</p>
        <p class="text-2xl font-bold">{{ $moneda }}{{ number_format($totales['subtotal'], 2) }}</p>
    </div>
    <div class="gradient-card-3 rounded-2xl p-4 text-white">
        <p class="text-sm opacity-80">Impuestos</p>
        <p class="text-2xl font-bold">{{ $moneda }}{{ number_format($totales['impuesto'], 2) }}</p>
    </div>
    <div class="gradient-card-4 rounded-2xl p-4 text-white">
        <p class="text-sm opacity-80">Descuentos</p>
        <p class="text-2xl font-bold">{{ $moneda }}{{ number_format($totales['descuento'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
        <h3 class="font-bold mb-4">Por Forma de Pago</h3>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-400 border-b">
                <tr><th class="text-left py-2">Forma</th><th class="text-right py-2">Cantidad</th><th class="text-right py-2">Total</th></tr>
            </thead>
            <tbody>
            @foreach($porFormaPago as $forma => $datos)
                <tr class="border-b">
                    <td class="py-2"><span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs">{{ ucfirst($forma) }}</span></td>
                    <td class="py-2 text-right">{{ $datos['cantidad'] }}</td>
                    <td class="py-2 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($datos['total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
        <h3 class="font-bold mb-4">Ventas por Día</h3>
        <table class="w-full text-sm max-h-80 overflow-y-auto">
            <thead class="text-xs uppercase text-slate-400 border-b sticky top-0 bg-slate-900 border border-slate-800">
                <tr><th class="text-left py-2">Fecha</th><th class="text-right py-2">Tickets</th><th class="text-right py-2">Total</th></tr>
            </thead>
            <tbody>
            @foreach($porDia as $fecha => $datos)
                <tr class="border-b">
                    <td class="py-2">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
                    <td class="py-2 text-right">{{ $datos['cantidad'] }}</td>
                    <td class="py-2 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($datos['total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden border border-slate-800">
    <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
        <h3 class="font-extrabold text-sm sm:text-base text-slate-100">
            Detalle de Ventas ({{ $ventas->count() }} tickets)
        </h3>
    </div>

    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($ventas as $v)
            <div class="p-3.5 hover:bg-slate-800 transition">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-mono text-xs font-black text-slate-100">{{ $v->numero_ticket }}</span>
                    <span class="font-black text-emerald-600 text-sm">{{ $moneda }}{{ number_format($v->total, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                    <span class="font-medium text-slate-200 truncate"><i class="fas fa-user text-[10px] text-slate-400 mr-1"></i>{{ $v->cliente?->nombre_completo ?? 'Genérico' }}</span>
                    <span class="bg-slate-900 text-slate-200 px-2 py-0.2 rounded font-bold">{{ ucfirst($v->forma_pago) }}</span>
                </div>
                <div class="text-[10px] text-slate-400">
                    <i class="far fa-clock mr-1"></i>{{ $v->fecha_venta->format('d/m/Y H:i') }} • Cajero: {{ $v->user->name }}
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-xs">Sin ventas registradas</div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3 px-4">Ticket</th>
                    <th class="py-3 px-4">Fecha</th>
                    <th class="py-3 px-4">Cliente</th>
                    <th class="py-3 px-4">Cajero</th>
                    <th class="py-3 px-4">Pago</th>
                    <th class="py-3 px-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($ventas as $v)
                <tr class="hover:bg-slate-800/80 transition">
                    <td class="py-3 px-4 font-mono text-xs font-bold text-slate-100">{{ $v->numero_ticket }}</td>
                    <td class="py-3 px-4 text-xs text-slate-400">{{ $v->fecha_venta->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-4 font-medium text-slate-100">{{ $v->cliente?->nombre_completo ?? 'Genérico' }}</td>
                    <td class="py-3 px-4 text-xs text-slate-300">{{ $v->user->name }}</td>
                    <td class="py-3 px-4"><span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-xs font-semibold">{{ ucfirst($v->forma_pago) }}</span></td>
                    <td class="py-3 px-4 text-right font-black text-emerald-600">{{ $moneda }}{{ number_format($v->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-8 text-slate-400 text-sm">Sin ventas en el período</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
