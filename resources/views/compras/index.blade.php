@extends('layouts.app')
@section('title', 'Compras')
@section('header', 'Historial de Compras')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5">
    <div class="flex flex-col md:flex-row gap-3 justify-between">
        <form method="GET" class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
            <select name="proveedor_id" class="px-3 py-2.5 border border-slate-600 rounded-lg">
                <option value="">Todos los proveedores</option>
                @foreach($proveedores as $p)
                    <option value="{{ $p->id }}" {{ request('proveedor_id') == $p->id ? 'selected' : '' }}>{{ $p->razon_social }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-2.5 border border-slate-600 rounded-lg">
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-2.5 border border-slate-600 rounded-lg">
        </form>
        <a href="{{ route('compras.create') }}" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2"><i class="fas fa-plus"></i>Nueva Compra</a>
    </div>
</div>

<!-- Vista de Compras (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($compras as $c)
            <div class="p-3.5 hover:bg-slate-800 transition">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-black text-slate-100">{{ $c->numero }}</span>
                        <span class="bg-green-100 text-green-700 px-2 py-0.2 rounded-full text-[10px] font-bold">{{ ucfirst($c->estado) }}</span>
                    </div>
                    <span class="font-black text-emerald-600 text-base">{{ $moneda }}{{ number_format($c->total, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
                    <div>
                        <p class="font-medium text-slate-200"><i class="fas fa-truck mr-1 text-slate-400"></i>{{ $c->proveedor->razon_social }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5"><i class="far fa-calendar mr-1"></i>{{ $c->fecha_compra->format('d/m/Y') }} • Fac: {{ $c->numero_factura ?: '—' }}</p>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-800">
                    <a href="{{ route('compras.show', $c->id) }}" class="w-full py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-eye"></i><span>Ver Detalle de Compra</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fas fa-truck-loading text-4xl mb-2 text-slate-300"></i>
                <p>No se encontraron compras</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3.5 px-4">N° Compra</th>
                    <th class="py-3.5 px-4">Fecha</th>
                    <th class="py-3.5 px-4">Proveedor</th>
                    <th class="py-3.5 px-4">Factura</th>
                    <th class="py-3.5 px-4 text-right">Total</th>
                    <th class="py-3.5 px-4 text-center">Estado</th>
                    <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($compras as $c)
                <tr class="hover:bg-slate-800/80 transition">
                    <td class="py-3.5 px-4 font-mono text-sm font-bold text-slate-100">{{ $c->numero }}</td>
                    <td class="py-3.5 px-4 text-sm">{{ $c->fecha_compra->format('d/m/Y') }}</td>
                    <td class="py-3.5 px-4 text-sm font-medium">{{ $c->proveedor->razon_social }}</td>
                    <td class="py-3.5 px-4 text-sm text-slate-400">{{ $c->numero_factura ?: '—' }}</td>
                    <td class="py-3.5 px-4 text-right font-extrabold text-sm text-emerald-600 whitespace-nowrap">{{ $moneda }}{{ number_format($c->total, 2) }}</td>
                    <td class="py-3.5 px-4 text-center"><span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">{{ ucfirst($c->estado) }}</span></td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap"><a href="{{ route('compras.show', $c->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg inline-block text-sm"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-12 text-slate-400 text-sm">No se encontraron compras</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 sm:p-4 border-t border-slate-800">{{ $compras->withQueryString()->links() }}</div>
</div>
@endsection
