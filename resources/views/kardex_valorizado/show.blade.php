@extends('layouts.app')

@section('titulo', 'Kardex Individual: ' . $producto->nombre)

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="{{ route('kardex-valorizado.index') }}" class="text-slate-400 hover:text-slate-600 font-medium text-sm transition mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Volver al General
            </a>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                <i class="fas fa-box text-emerald-500"></i> Historial Valorizado: {{ $producto->nombre }}
            </h1>
        </div>
    </div>

    <!-- Info del Producto -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs uppercase font-bold text-slate-400 mb-1">Costo Compra</p>
            <p class="text-xl font-black text-slate-700">S/ {{ number_format($producto->precio_compra, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs uppercase font-bold text-slate-400 mb-1">Precio Venta</p>
            <p class="text-xl font-black text-slate-700">S/ {{ number_format($producto->precio_venta, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs uppercase font-bold text-slate-400 mb-1">Stock Actual</p>
            <p class="text-xl font-black text-slate-700">{{ number_format($producto->stock, 2) }} Und.</p>
        </div>
        <div class="bg-emerald-500 rounded-xl shadow-sm border border-emerald-600 p-4 text-white">
            <p class="text-xs uppercase font-bold text-emerald-200 mb-1">Saldo Valorizado Total</p>
            <p class="text-xl font-black">S/ {{ number_format($producto->stock * $producto->precio_compra, 2) }}</p>
        </div>
    </div>

    <!-- Tabla Movimientos -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-700">Libro de Movimientos</h3>
            <span class="text-xs text-slate-400">Mostrando historial más reciente primero</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-xs uppercase font-bold">
                        <th class="px-4 py-3 border-r border-slate-200" rowspan="2">Fecha y Hora</th>
                        <th class="px-4 py-3 border-r border-slate-200" rowspan="2">Motivo / Operación</th>
                        <th class="px-4 py-2 border-r border-slate-200 text-center bg-blue-50" colspan="3">Entradas (Ingresos)</th>
                        <th class="px-4 py-2 border-r border-slate-200 text-center bg-rose-50" colspan="3">Salidas (Egresos)</th>
                        <th class="px-4 py-2 text-center bg-emerald-50" colspan="2">Saldos (Actual)</th>
                    </tr>
                    <tr class="text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                        <th class="px-3 py-2 bg-blue-50/50 text-right">Cant.</th>
                        <th class="px-3 py-2 bg-blue-50/50 text-right">Costo Unit.</th>
                        <th class="px-3 py-2 bg-blue-50/50 text-right border-r border-slate-200">Total S/</th>

                        <th class="px-3 py-2 bg-rose-50/50 text-right">Cant.</th>
                        <th class="px-3 py-2 bg-rose-50/50 text-right">Costo Unit.</th>
                        <th class="px-3 py-2 bg-rose-50/50 text-right border-r border-slate-200">Total S/</th>

                        <th class="px-3 py-2 bg-emerald-50/50 text-right">Cant.</th>
                        <th class="px-3 py-2 bg-emerald-50/50 text-right">Valorizado S/</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movimientos as $mov)
                        @php
                            $esEntrada = in_array($mov->tipo, ['entrada', 'ajuste_positivo']);
                            // Ajustes de inventario que suman o restan
                            if ($mov->tipo === 'ajuste') {
                                $esEntrada = $mov->stock_nuevo > $mov->stock_anterior;
                            }
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 border-r border-slate-100">
                                {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y h:i A') }}
                                <br><small class="text-slate-400">{{ $mov->user->name ?? 'Sistema' }}</small>
                            </td>
                            <td class="px-4 py-2 border-r border-slate-100 font-medium">
                                @if($mov->tipo == 'venta' || str_contains(strtolower($mov->motivo), 'venta'))
                                    <span class="inline-block px-2 py-0.5 rounded bg-rose-100 text-rose-700 text-[10px] mr-1">VENTA</span>
                                @elseif($mov->tipo == 'compra' || str_contains(strtolower($mov->motivo), 'compra'))
                                    <span class="inline-block px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-[10px] mr-1">COMPRA</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded bg-slate-200 text-slate-600 text-[10px] mr-1">{{ strtoupper($mov->tipo) }}</span>
                                @endif
                                {{ $mov->motivo }}
                            </td>
                            
                            <!-- Entradas -->
                            <td class="px-3 py-2 text-right text-blue-700 {{ !$esEntrada ? 'bg-slate-50/50 text-slate-300' : '' }}">
                                {{ $esEntrada ? number_format($mov->cantidad, 2) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right {{ !$esEntrada ? 'bg-slate-50/50 text-slate-300' : '' }}">
                                {{ $esEntrada ? number_format($mov->costo_unitario, 2) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right font-bold text-blue-700 border-r border-slate-100 {{ !$esEntrada ? 'bg-slate-50/50 text-slate-300' : '' }}">
                                {{ $esEntrada ? number_format($mov->valor_movimiento, 2) : '-' }}
                            </td>

                            <!-- Salidas -->
                            <td class="px-3 py-2 text-right text-rose-600 {{ $esEntrada ? 'bg-slate-50/50 text-slate-300' : '' }}">
                                {{ !$esEntrada ? number_format($mov->cantidad, 2) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right {{ $esEntrada ? 'bg-slate-50/50 text-slate-300' : '' }}">
                                {{ !$esEntrada ? number_format($mov->costo_unitario, 2) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right font-bold text-rose-600 border-r border-slate-100 {{ $esEntrada ? 'bg-slate-50/50 text-slate-300' : '' }}">
                                {{ !$esEntrada ? number_format($mov->valor_movimiento, 2) : '-' }}
                            </td>

                            <!-- Saldos -->
                            <td class="px-3 py-2 text-right font-black text-slate-700 bg-emerald-50/30">
                                {{ number_format($mov->stock_nuevo, 2) }}
                            </td>
                            <td class="px-3 py-2 text-right font-black text-emerald-600 bg-emerald-50/30">
                                S/ {{ number_format($mov->saldo_valorizado, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                                Sin movimientos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
