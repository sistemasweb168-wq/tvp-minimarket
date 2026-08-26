@extends('layouts.app')
@section('title', 'Reporte de Productos')
@section('header', 'Productos más Vendidos')

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

<div class="bg-white rounded-2xl shadow-md p-6">
    <h3 class="font-bold mb-4">Productos vendidos del {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</h3>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-slate-500 border-b">
            <tr>
                <th class="text-left py-2">#</th>
                <th class="text-left py-2">Código</th>
                <th class="text-left py-2">Producto</th>
                <th class="text-left py-2">Categoría</th>
                <th class="text-right py-2">Cantidad</th>
                <th class="text-right py-2">Precio Prom.</th>
                <th class="text-right py-2">Total</th>
            </tr>
        </thead>
        <tbody>
        @forelse($productos as $i => $p)
            <tr class="border-b hover:bg-slate-50">
                <td class="py-2">
                    <span class="w-7 h-7 rounded-full inline-flex items-center justify-center text-white text-xs font-bold
                        {{ $i==0?'bg-yellow-500':($i==1?'bg-slate-400':($i==2?'bg-orange-600':'bg-slate-300')) }}">{{ $i+1 }}</span>
                </td>
                <td class="py-2 font-mono text-xs">{{ $p->codigo }}</td>
                <td class="py-2 font-semibold">{{ $p->nombre }}</td>
                <td class="py-2 text-xs text-slate-500">{{ $p->categoria ?: '—' }}</td>
                <td class="py-2 text-right">{{ number_format($p->cantidad_vendida, 2) }}</td>
                <td class="py-2 text-right">{{ $moneda }}{{ number_format($p->precio_promedio, 2) }}</td>
                <td class="py-2 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($p->total_ingresos, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-8 text-slate-400">Sin ventas en el período</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
