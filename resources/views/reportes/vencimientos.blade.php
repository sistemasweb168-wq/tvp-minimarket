@extends('layouts.app')
@section('title', 'Vencimientos')
@section('header', 'Productos por Vencer')

@section('content')
<div class="bg-white rounded-2xl shadow-md p-6">
    <table class="w-full">
        <thead class="text-xs uppercase text-slate-500 border-b">
            <tr>
                <th class="text-left py-2">Producto</th>
                <th class="text-left py-2">Categoría</th>
                <th class="text-left py-2">Lote</th>
                <th class="text-right py-2">Stock</th>
                <th class="text-left py-2">Vencimiento</th>
                <th class="text-center py-2">Estado</th>
            </tr>
        </thead>
        <tbody>
        @forelse($productos as $p)
            @php
                $diasFalta = (int) now()->diffInDays($p->fecha_vencimiento, false);
                $estado = $diasFalta < 0 ? 'vencido' : ($diasFalta <= 7 ? 'critico' : ($diasFalta <= 30 ? 'proximo' : 'ok'));
            @endphp
            <tr class="border-b hover:bg-slate-50">
                <td class="py-2"><p class="font-semibold">{{ $p->nombre }}</p><p class="text-xs text-slate-500 font-mono">{{ $p->codigo }}</p></td>
                <td class="py-2 text-sm">{{ $p->categoria?->nombre }}</td>
                <td class="py-2 font-mono text-xs">{{ $p->lote ?: '—' }}</td>
                <td class="py-2 text-right font-bold">{{ number_format($p->stock, 2) }}</td>
                <td class="py-2 text-sm">{{ $p->fecha_vencimiento->format('d/m/Y') }}</td>
                <td class="py-2 text-center">
                    @if($estado == 'vencido')
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">Vencido ({{ abs($diasFalta) }}d)</span>
                    @elseif($estado == 'critico')
                        <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs font-semibold">{{ $diasFalta }}d</span>
                    @elseif($estado == 'proximo')
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">{{ $diasFalta }}d</span>
                    @else
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">{{ $diasFalta }}d</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-8 text-slate-400">Sin productos con fecha de vencimiento</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
