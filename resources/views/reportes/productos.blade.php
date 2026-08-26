@extends('layouts.app')
@section('title', 'Reporte de Productos')
@section('header', 'Productos más Vendidos')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 mb-4 border border-slate-100">
    <form method="GET" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div><label class="block text-xs font-bold uppercase text-slate-600 mb-1">Desde</label><input type="date" name="desde" value="{{ $desde }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-emerald-500"></div>
            <div><label class="block text-xs font-bold uppercase text-slate-600 mb-1">Hasta</label><input type="date" name="hasta" value="{{ $hasta }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-emerald-500"></div>
        </div>
        <div class="flex gap-2">
            <button class="flex-1 sm:flex-none gradient-primary text-white px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-xs hover:brightness-105 transition flex items-center justify-center gap-1.5"><i class="fas fa-search"></i><span>Generar</span></button>
            <button onclick="window.print()" type="button" class="flex-1 sm:flex-none bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center justify-center gap-1.5"><i class="fas fa-print"></i><span>Imprimir</span></button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100">
    <div class="p-4 border-b border-slate-100">
        <h3 class="font-extrabold text-xs sm:text-sm text-slate-800">
            Productos vendidos del {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        </h3>
    </div>

    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($productos as $i => $p)
            <div class="p-3.5 hover:bg-slate-50 transition flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl inline-flex items-center justify-center text-white text-xs font-black flex-shrink-0 shadow-xs
                    {{ $i==0?'bg-amber-500':($i==1?'bg-slate-400':($i==2?'bg-amber-700':'bg-slate-300 text-slate-700')) }}">
                    #{{ $i+1 }}
                </span>
                
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-slate-800 text-xs sm:text-sm truncate">{{ $p->nombre }}</h4>
                    <p class="text-[11px] text-slate-400 font-mono">{{ $p->codigo }} • {{ $p->categoria ?: 'Sin categoría' }}</p>
                    <div class="flex items-center justify-between mt-1 pt-1 border-t border-slate-100 text-xs">
                        <span class="text-slate-600 font-medium">{{ number_format($p->cantidad_vendida, 2) }} unid.</span>
                        <span class="font-black text-emerald-600 text-sm">{{ $moneda }}{{ number_format($p->total_ingresos, 2) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-xs">Sin ventas registradas en el período</div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Código</th>
                    <th class="py-3 px-4">Producto</th>
                    <th class="py-3 px-4">Categoría</th>
                    <th class="py-3 px-4 text-right">Cantidad</th>
                    <th class="py-3 px-4 text-right">Precio Prom.</th>
                    <th class="py-3 px-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($productos as $i => $p)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3 px-4">
                        <span class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-white text-xs font-black
                            {{ $i==0?'bg-amber-500':($i==1?'bg-slate-400':($i==2?'bg-amber-700':'bg-slate-200 text-slate-700')) }}">{{ $i+1 }}</span>
                    </td>
                    <td class="py-3 px-4 font-mono text-xs">{{ $p->codigo }}</td>
                    <td class="py-3 px-4 font-bold text-slate-800">{{ $p->nombre }}</td>
                    <td class="py-3 px-4 text-xs text-slate-500">{{ $p->categoria ?: '—' }}</td>
                    <td class="py-3 px-4 text-right font-semibold">{{ number_format($p->cantidad_vendida, 2) }}</td>
                    <td class="py-3 px-4 text-right text-slate-600">{{ $moneda }}{{ number_format($p->precio_promedio, 2) }}</td>
                    <td class="py-3 px-4 text-right font-black text-emerald-600">{{ $moneda }}{{ number_format($p->total_ingresos, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-10 text-slate-400 text-sm">Sin ventas en el período</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
