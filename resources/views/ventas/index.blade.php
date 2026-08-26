@extends('layouts.app')
@section('title', 'Ventas')
@section('header', 'Historial de Ventas')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 mb-4 sm:mb-5">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5 sm:gap-3">
        <input name="buscar" value="{{ request('buscar') }}" placeholder="N° Ticket o Cliente" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
        <select name="estado" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
            <option value="">Todos los estados</option>
            <option value="completada" {{ request('estado')=='completada'?'selected':'' }}>Completadas</option>
            <option value="anulada" {{ request('estado')=='anulada'?'selected':'' }}>Anuladas</option>
        </select>
        <div class="flex gap-2">
            <button class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-xl text-sm font-semibold transition flex items-center justify-center gap-1.5"><i class="fas fa-filter"></i>Filtrar</button>
            <a href="{{ route('ventas.pos') }}" class="gradient-primary text-white px-4 py-2 rounded-xl flex items-center justify-center font-bold text-sm shadow-sm"><i class="fas fa-cash-register mr-1"></i>POS</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-[11px] sm:text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3 px-3 sm:px-4">Ticket</th>
                    <th class="py-3 px-3 sm:px-4">Fecha/Cliente</th>
                    <th class="py-3 px-3 sm:px-4 hidden sm:table-cell">Cajero</th>
                    <th class="py-3 px-3 sm:px-4 hidden md:table-cell">Pago</th>
                    <th class="py-3 px-3 sm:px-4 text-right">Total</th>
                    <th class="py-3 px-3 sm:px-4 text-center">Estado</th>
                    <th class="py-3 px-3 sm:px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($ventas as $v)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3 px-3 sm:px-4 font-mono text-xs sm:text-sm font-bold text-slate-800">
                        {{ $v->numero_ticket }}
                        <span class="block sm:hidden text-[10px] text-slate-400 font-sans font-normal">{{ $v->user->name }}</span>
                    </td>
                    <td class="py-3 px-3 sm:px-4 text-xs sm:text-sm">
                        <p class="font-medium text-slate-800 line-clamp-1">{{ $v->cliente?->nombre_completo ?? 'Cliente Genérico' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $v->fecha_venta->format('d/m/Y H:i') }}</p>
                    </td>
                    <td class="py-3 px-3 sm:px-4 text-xs sm:text-sm text-slate-600 hidden sm:table-cell">{{ $v->user->name }}</td>
                    <td class="py-3 px-3 sm:px-4 hidden md:table-cell"><span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ ucfirst($v->forma_pago) }}</span></td>
                    <td class="py-3 px-3 sm:px-4 text-right font-extrabold text-xs sm:text-sm text-emerald-600 whitespace-nowrap">{{ $moneda }}{{ number_format($v->total, 2) }}</td>
                    <td class="py-3 px-3 sm:px-4 text-center">
                        @if($v->estado == 'completada')
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[11px] font-bold">Completada</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-[11px] font-bold">Anulada</span>
                        @endif
                    </td>
                    <td class="py-3 px-3 sm:px-4 text-right whitespace-nowrap">
                        <a href="{{ route('ventas.show', $v->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg inline-block text-sm" title="Ver detalle"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('ventas.ticket', $v->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg inline-block text-sm" title="Imprimir ticket"><i class="fas fa-print"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-12 text-slate-400 text-sm">No se encontraron ventas registradas</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 sm:p-4 border-t border-slate-100">{{ $ventas->withQueryString()->links() }}</div>
</div>
@endsection
