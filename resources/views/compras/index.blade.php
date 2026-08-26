@extends('layouts.app')
@section('title', 'Compras')
@section('header', 'Historial de Compras')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-5 mb-5">
    <div class="flex flex-col md:flex-row gap-3 justify-between">
        <form method="GET" class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
            <select name="proveedor_id" class="px-3 py-2.5 border border-slate-300 rounded-lg">
                <option value="">Todos los proveedores</option>
                @foreach($proveedores as $p)
                    <option value="{{ $p->id }}" {{ request('proveedor_id') == $p->id ? 'selected' : '' }}>{{ $p->razon_social }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-2.5 border border-slate-300 rounded-lg">
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-2.5 border border-slate-300 rounded-lg">
        </form>
        <a href="{{ route('compras.create') }}" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2"><i class="fas fa-plus"></i>Nueva Compra</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="text-left py-3 px-4">N° Compra</th>
                <th class="text-left py-3 px-4">Fecha</th>
                <th class="text-left py-3 px-4">Proveedor</th>
                <th class="text-left py-3 px-4">Factura</th>
                <th class="text-right py-3 px-4">Total</th>
                <th class="text-center py-3 px-4">Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($compras as $c)
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="py-3 px-4 font-mono text-sm">{{ $c->numero }}</td>
                <td class="py-3 px-4 text-sm">{{ $c->fecha_compra->format('d/m/Y') }}</td>
                <td class="py-3 px-4">{{ $c->proveedor->razon_social }}</td>
                <td class="py-3 px-4 text-sm">{{ $c->numero_factura ?: '—' }}</td>
                <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($c->total, 2) }}</td>
                <td class="py-3 px-4 text-center"><span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">{{ ucfirst($c->estado) }}</span></td>
                <td class="py-3 px-4 text-right"><a href="{{ route('compras.show', $c->id) }}" class="text-blue-600 hover:bg-blue-50 p-2 rounded"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-12 text-slate-400">Sin compras</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-4">{{ $compras->withQueryString()->links() }}</div>
</div>
@endsection
