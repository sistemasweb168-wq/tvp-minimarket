@extends('layouts.app')
@section('title', $proveedor->razon_social)
@section('header', $proveedor->razon_social)

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
        <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mb-4">
            <i class="fas fa-truck text-blue-600 text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold">{{ $proveedor->razon_social }}</h2>
        <p class="text-sm text-slate-400">{{ $proveedor->codigo }}</p>
        <div class="space-y-2 text-sm mt-4">
            @if($proveedor->ruc_nit)<p><i class="fas fa-id-card text-slate-400 w-5"></i> {{ $proveedor->ruc_nit }}</p>@endif
            @if($proveedor->contacto)<p><i class="fas fa-user text-slate-400 w-5"></i> {{ $proveedor->contacto }}</p>@endif
            @if($proveedor->telefono)<p><i class="fas fa-phone text-slate-400 w-5"></i> {{ $proveedor->telefono }}</p>@endif
            @if($proveedor->email)<p><i class="fas fa-envelope text-slate-400 w-5"></i> {{ $proveedor->email }}</p>@endif
            @if($proveedor->direccion)<p><i class="fas fa-map-marker-alt text-slate-400 w-5"></i> {{ $proveedor->direccion }}</p>@endif
        </div>
        <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="block mt-4 text-center gradient-primary text-white py-2.5 rounded-lg font-semibold"><i class="fas fa-edit mr-1"></i>Editar</a>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
        <h3 class="font-bold mb-4"><i class="fas fa-receipt mr-2 text-emerald-500"></i>Historial de Compras</h3>
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-400 border-b">
                <tr><th class="text-left py-2">N°</th><th class="text-left py-2">Fecha</th><th class="text-left py-2">Factura</th><th class="text-right py-2">Total</th></tr>
            </thead>
            <tbody>
            @forelse($proveedor->compras as $c)
                <tr class="border-b">
                    <td class="py-2 font-mono text-xs">{{ $c->numero }}</td>
                    <td class="py-2">{{ $c->fecha_compra->format('d/m/Y') }}</td>
                    <td class="py-2">{{ $c->numero_factura ?: '—' }}</td>
                    <td class="py-2 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($c->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-6 text-slate-400">Sin compras</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
