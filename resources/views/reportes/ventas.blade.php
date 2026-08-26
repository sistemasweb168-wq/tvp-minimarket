@extends('layouts.app')
@section('title', 'Reporte de Ventas')
@section('header', 'Reporte de Ventas')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-5 mb-5">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <div class="flex-1 grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-semibold mb-1">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
            <div><label class="block text-sm font-semibold mb-1">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
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
    <div class="bg-white rounded-2xl shadow-md p-6">
        <h3 class="font-bold mb-4">Por Forma de Pago</h3>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 border-b">
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

    <div class="bg-white rounded-2xl shadow-md p-6">
        <h3 class="font-bold mb-4">Ventas por Día</h3>
        <table class="w-full text-sm max-h-80 overflow-y-auto">
            <thead class="text-xs uppercase text-slate-500 border-b sticky top-0 bg-white">
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

<div class="bg-white rounded-2xl shadow-md p-6">
    <h3 class="font-bold mb-4">Detalle de Ventas ({{ $ventas->count() }} tickets)</h3>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-slate-500 border-b">
            <tr>
                <th class="text-left py-2">Ticket</th>
                <th class="text-left py-2">Fecha</th>
                <th class="text-left py-2">Cliente</th>
                <th class="text-left py-2">Cajero</th>
                <th class="text-left py-2">Pago</th>
                <th class="text-right py-2">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($ventas as $v)
            <tr class="border-b hover:bg-slate-50">
                <td class="py-2 font-mono text-xs">{{ $v->numero_ticket }}</td>
                <td class="py-2">{{ $v->fecha_venta->format('d/m/Y H:i') }}</td>
                <td class="py-2">{{ $v->cliente?->nombre_completo ?? 'Genérico' }}</td>
                <td class="py-2">{{ $v->user->name }}</td>
                <td class="py-2">{{ ucfirst($v->forma_pago) }}</td>
                <td class="py-2 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($v->total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
