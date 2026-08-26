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

<!-- Vista de Ventas (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($ventas as $v)
            <div class="p-3.5 hover:bg-slate-50 transition">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-black text-slate-800">{{ $v->numero_ticket }}</span>
                        @if($v->estado == 'completada')
                            <span class="bg-green-100 text-green-700 px-2 py-0.2 rounded-full text-[10px] font-bold">Completada</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-0.2 rounded-full text-[10px] font-bold">Anulada</span>
                        @endif
                    </div>
                    <span class="font-black text-emerald-600 text-base">{{ $moneda }}{{ number_format($v->total, 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                    <div>
                        <p class="font-medium text-slate-700 line-clamp-1"><i class="fas fa-user text-[10px] text-slate-400 mr-1"></i>{{ $v->cliente?->nombre_completo ?? 'Cliente Genérico' }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5"><i class="far fa-clock text-[10px] mr-1"></i>{{ $v->fecha_venta->format('d/m/Y H:i') }} • {{ $v->user->name }}</p>
                    </div>
                    <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-lg text-[10px] font-bold">{{ ucfirst($v->forma_pago) }}</span>
                </div>

                <!-- Botones de Acción Móvil -->
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('ventas.show', $v->id) }}" class="flex-1 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-eye"></i><span>Detalle</span>
                    </a>
                    <a href="{{ route('ventas.ticket', $v->id) }}" target="_blank" class="flex-1 py-1.5 gradient-primary text-white rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fas fa-print"></i><span>Ticket</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fas fa-receipt text-4xl mb-2 text-slate-300"></i>
                <p>No se encontraron ventas registradas</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3.5 px-4">Ticket</th>
                    <th class="py-3.5 px-4">Fecha/Cliente</th>
                    <th class="py-3.5 px-4">Cajero</th>
                    <th class="py-3.5 px-4">Pago</th>
                    <th class="py-3.5 px-4 text-right">Total</th>
                    <th class="py-3.5 px-4 text-center">Estado</th>
                    <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($ventas as $v)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3.5 px-4 font-mono text-sm font-bold text-slate-800">{{ $v->numero_ticket }}</td>
                    <td class="py-3.5 px-4 text-sm">
                        <p class="font-medium text-slate-800">{{ $v->cliente?->nombre_completo ?? 'Cliente Genérico' }}</p>
                        <p class="text-xs text-slate-400">{{ $v->fecha_venta->format('d/m/Y H:i') }}</p>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-slate-600">{{ $v->user->name }}</td>
                    <td class="py-3.5 px-4"><span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ ucfirst($v->forma_pago) }}</span></td>
                    <td class="py-3.5 px-4 text-right font-extrabold text-sm text-emerald-600 whitespace-nowrap">{{ $moneda }}{{ number_format($v->total, 2) }}</td>
                    <td class="py-3.5 px-4 text-center">
                        @if($v->estado == 'completada')
                            <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">Completada</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">Anulada</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
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
