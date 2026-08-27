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

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 mb-4 flex justify-between items-center">
    <h3 class="font-extrabold text-sm sm:text-base text-slate-100"><i class="fas fa-boxes text-emerald-500 mr-2"></i>Detalle de Inventario</h3>
    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2"><i class="fas fa-print"></i><span>Imprimir Reporte</span></button>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden border border-slate-800">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($productos as $p)
            <div class="p-3.5 hover:bg-slate-800 transition">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5 min-w-0 pr-2">
                        <span class="font-bold text-slate-100 text-xs sm:text-sm truncate">{{ $p->nombre }}</span>
                    </div>
                    @if($p->stock_bajo)
                        <span class="bg-red-100 text-red-700 px-2 py-0.2 rounded-full text-[10px] font-bold flex-shrink-0">Bajo</span>
                    @elseif($p->stock == 0)
                        <span class="bg-slate-900 text-slate-200 px-2 py-0.2 rounded-full text-[10px] font-bold flex-shrink-0">Agotado</span>
                    @else
                        <span class="bg-green-100 text-green-700 px-2 py-0.2 rounded-full text-[10px] font-bold flex-shrink-0">Stock OK</span>
                    @endif
                </div>

                <div class="flex items-center gap-2 text-[11px] text-slate-400 font-mono mb-1.5">
                    <span>{{ $p->codigo }}</span>
                    <span>•</span>
                    <span class="text-slate-300 font-sans">{{ $p->categoria?->nombre ?? 'Sin categoría' }}</span>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1.5 border-t border-slate-800 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Stock Actual / Mín</span>
                        <span class="font-black {{ $p->stock_bajo ? 'text-red-600' : 'text-slate-100' }}">{{ number_format($p->stock, 2) }}</span>
                        <span class="text-[10px] text-slate-400">/ mín {{ number_format($p->stock_minimo, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">P. Venta / Valor</span>
                        <span class="font-black text-emerald-600">{{ $moneda }}{{ number_format($p->precio_venta, 2) }}</span>
                        <span class="text-[10px] text-slate-400 block">Val: {{ $moneda }}{{ number_format($p->stock * $p->precio_compra, 2) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-xs">No hay productos en inventario</div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3 px-4">Código</th>
                    <th class="py-3 px-4">Producto</th>
                    <th class="py-3 px-4">Categoría</th>
                    <th class="py-3 px-4 text-right">Stock</th>
                    <th class="py-3 px-4 text-right">Mín</th>
                    <th class="py-3 px-4 text-right">P. Compra</th>
                    <th class="py-3 px-4 text-right">P. Venta</th>
                    <th class="py-3 px-4 text-right">Valor Stock</th>
                    <th class="py-3 px-4 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach($productos as $p)
                <tr class="hover:bg-slate-800/80 transition">
                    <td class="py-3 px-4 font-mono text-xs">{{ $p->codigo }}</td>
                    <td class="py-3 px-4 font-bold text-slate-100">{{ $p->nombre }}</td>
                    <td class="py-3 px-4 text-xs text-slate-300">{{ $p->categoria?->nombre ?? '—' }}</td>
                    <td class="py-3 px-4 text-right font-black {{ $p->stock_bajo ? 'text-red-600' : 'text-slate-100' }}">{{ number_format($p->stock, 2) }}</td>
                    <td class="py-3 px-4 text-right text-xs text-slate-400">{{ number_format($p->stock_minimo, 2) }}</td>
                    <td class="py-3 px-4 text-right text-slate-300">{{ $moneda }}{{ number_format($p->precio_compra, 2) }}</td>
                    <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($p->precio_venta, 2) }}</td>
                    <td class="py-3 px-4 text-right font-black text-slate-100">{{ $moneda }}{{ number_format($p->stock * $p->precio_compra, 2) }}</td>
                    <td class="py-3 px-4 text-center">
                        @if($p->stock_bajo)
                            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">Bajo</span>
                        @elseif($p->stock == 0)
                            <span class="bg-slate-900 text-slate-200 px-2.5 py-1 rounded-full text-xs font-bold">Agotado</span>
                        @else
                            <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">OK</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
