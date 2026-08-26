@extends('layouts.app')
@section('title', 'Reporte de Inventario')
@section('header', 'Estado del Inventario')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="gradient-card-1 rounded-2xl p-5 text-white">
        <p class="text-sm opacity-80">Productos activos</p>
        <p class="text-3xl font-bold">{{ $productos->count() }}</p>
    </div>
    <div class="gradient-card-2 rounded-2xl p-5 text-white">
        <p class="text-sm opacity-80">Valor de Compra (Stock)</p>
        <p class="text-2xl font-bold">{{ $moneda }}{{ number_format($valorTotal, 2) }}</p>
    </div>
    <div class="gradient-card-3 rounded-2xl p-5 text-white">
        <p class="text-sm opacity-80">Valor Potencial de Venta</p>
        <p class="text-2xl font-bold">{{ $moneda }}{{ number_format($valorVenta, 2) }}</p>
        <p class="text-xs opacity-80">Margen: {{ $moneda }}{{ number_format($valorVenta - $valorTotal, 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md p-5 mb-3 flex justify-end">
    <button onclick="window.print()" class="bg-slate-800 text-white px-6 py-2.5 rounded-lg font-semibold"><i class="fas fa-print mr-2"></i>Imprimir</button>
</div>

<div class="bg-white rounded-2xl shadow-md p-6">
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-slate-500 border-b">
            <tr>
                <th class="text-left py-2">Código</th>
                <th class="text-left py-2">Producto</th>
                <th class="text-left py-2">Categoría</th>
                <th class="text-right py-2">Stock</th>
                <th class="text-right py-2">Mín</th>
                <th class="text-right py-2">P. Compra</th>
                <th class="text-right py-2">P. Venta</th>
                <th class="text-right py-2">Valor Stock</th>
                <th class="text-center py-2">Estado</th>
            </tr>
        </thead>
        <tbody>
        @foreach($productos as $p)
            <tr class="border-b hover:bg-slate-50">
                <td class="py-2 font-mono text-xs">{{ $p->codigo }}</td>
                <td class="py-2 font-semibold">{{ $p->nombre }}</td>
                <td class="py-2 text-xs">{{ $p->categoria?->nombre ?? '—' }}</td>
                <td class="py-2 text-right font-bold {{ $p->stock_bajo ? 'text-red-600' : '' }}">{{ number_format($p->stock, 2) }}</td>
                <td class="py-2 text-right text-xs text-slate-500">{{ number_format($p->stock_minimo, 2) }}</td>
                <td class="py-2 text-right">{{ $moneda }}{{ number_format($p->precio_compra, 2) }}</td>
                <td class="py-2 text-right">{{ $moneda }}{{ number_format($p->precio_venta, 2) }}</td>
                <td class="py-2 text-right font-semibold">{{ $moneda }}{{ number_format($p->stock * $p->precio_compra, 2) }}</td>
                <td class="py-2 text-center">
                    @if($p->stock_bajo)
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">Bajo</span>
                    @elseif($p->stock == 0)
                        <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full text-xs">Agotado</span>
                    @else
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">OK</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
