@extends('layouts.app')
@section('title', 'Vencimientos')
@section('header', 'Productos por Vencer')

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 mb-4 flex justify-between items-center border border-slate-800">
    <div>
        <h3 class="font-extrabold text-sm sm:text-base text-slate-100 flex items-center gap-2">
            <i class="fas fa-calendar-times text-amber-500"></i><span>Control de Vencimientos</span>
        </h3>
        <p class="text-[11px] sm:text-xs text-slate-400">Productos con fecha de caducidad próxima o vencida</p>
    </div>
    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-1.5 flex-shrink-0">
        <i class="fas fa-print"></i><span>Imprimir</span>
    </button>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden border border-slate-800">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-800">
        @forelse($productos as $p)
            @php
                $diasFalta = (int) now()->diffInDays($p->fecha_vencimiento, false);
                $estado = $diasFalta < 0 ? 'vencido' : ($diasFalta <= 7 ? 'critico' : ($diasFalta <= 30 ? 'proximo' : 'ok'));
            @endphp
            <div class="p-3.5 hover:bg-slate-800 transition">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-100 text-xs sm:text-sm truncate">{{ $p->nombre }}</span>
                    @if($estado == 'vencido')
                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[10px] font-black">Vencido (hace {{ abs($diasFalta) }}d)</span>
                    @elseif($estado == 'critico')
                        <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-[10px] font-black">Vence en {{ $diasFalta }}d</span>
                    @elseif($estado == 'proximo')
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full text-[10px] font-black">Vence en {{ $diasFalta }}d</span>
                    @else
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[10px] font-bold">OK ({{ $diasFalta }}d)</span>
                    @endif
                </div>

                <p class="text-[11px] text-slate-400 font-mono mb-1.5">{{ $p->codigo }} • {{ $p->categoria?->nombre ?? 'Sin categoría' }} @if($p->lote) • Lote: {{ $p->lote }} @endif</p>
                
                <div class="flex items-center justify-between pt-1.5 border-t border-slate-800 text-xs">
                    <span class="text-slate-300 font-semibold">Stock: {{ number_format($p->stock, 2) }} {{ $p->unidad_medida }}</span>
                    <span class="text-slate-400 font-medium">Vence: <strong class="text-slate-200">{{ $p->fecha_vencimiento->format('d/m/Y') }}</strong></span>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-xs">Sin productos con fecha de vencimiento próxima</div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3 px-4">Producto</th>
                    <th class="py-3 px-4">Categoría</th>
                    <th class="py-3 px-4">Lote</th>
                    <th class="py-3 px-4 text-right">Stock</th>
                    <th class="py-3 px-4">Vencimiento</th>
                    <th class="py-3 px-4 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
            @forelse($productos as $p)
                @php
                    $diasFalta = (int) now()->diffInDays($p->fecha_vencimiento, false);
                    $estado = $diasFalta < 0 ? 'vencido' : ($diasFalta <= 7 ? 'critico' : ($diasFalta <= 30 ? 'proximo' : 'ok'));
                @endphp
                <tr class="hover:bg-slate-800/80 transition">
                    <td class="py-3 px-4">
                        <p class="font-bold text-slate-100">{{ $p->nombre }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $p->codigo }}</p>
                    </td>
                    <td class="py-3 px-4 text-sm text-slate-300">{{ $p->categoria?->nombre ?? '—' }}</td>
                    <td class="py-3 px-4 font-mono text-xs text-slate-400">{{ $p->lote ?: '—' }}</td>
                    <td class="py-3 px-4 text-right font-black text-slate-100">{{ number_format($p->stock, 2) }}</td>
                    <td class="py-3 px-4 text-sm font-semibold text-slate-200">{{ $p->fecha_vencimiento->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-center">
                        @if($estado == 'vencido')
                            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">Vencido ({{ abs($diasFalta) }}d)</span>
                        @elseif($estado == 'critico')
                            <span class="bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full text-xs font-bold">Crítico ({{ $diasFalta }}d)</span>
                        @elseif($estado == 'proximo')
                            <span class="bg-yellow-100 text-yellow-800 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $diasFalta }} días</span>
                        @else
                            <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $diasFalta }} días</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-8 text-slate-400 text-sm">Sin productos con fecha de vencimiento</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
