@extends('layouts.app')
@section('title', $cliente->nombre_completo)
@section('header', $cliente->nombre_completo)

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl shadow-md p-6">
        <div class="text-center mb-4">
            <div class="w-24 h-24 mx-auto gradient-primary rounded-full flex items-center justify-center text-white text-4xl font-bold mb-3">
                {{ strtoupper(substr($cliente->nombres, 0, 1)) }}
            </div>
            <h2 class="text-xl font-bold">{{ $cliente->nombre_completo }}</h2>
            <p class="text-sm text-slate-500">{{ $cliente->codigo }}</p>
        </div>
        <div class="space-y-2 text-sm">
            <p><i class="fas fa-id-card text-slate-400 w-5"></i> {{ $cliente->tipo_documento }}: {{ $cliente->documento ?: '—' }}</p>
            <p><i class="fas fa-phone text-slate-400 w-5"></i> {{ $cliente->telefono ?: '—' }}</p>
            <p><i class="fas fa-envelope text-slate-400 w-5"></i> {{ $cliente->email ?: '—' }}</p>
            <p><i class="fas fa-map-marker-alt text-slate-400 w-5"></i> {{ $cliente->direccion ?: '—' }}</p>
        </div>
        <a href="{{ route('clientes.edit', $cliente->id) }}" class="block mt-4 text-center gradient-primary text-white py-2.5 rounded-lg font-semibold"><i class="fas fa-edit mr-1"></i>Editar</a>
    </div>

    <div class="lg:col-span-2 space-y-5">
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-yellow-50 rounded-xl p-4 text-center">
                <i class="fas fa-star text-yellow-500 text-2xl mb-1"></i>
                <p class="text-sm text-slate-600">Puntos</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $cliente->puntos_fidelidad }}</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 text-center">
                <i class="fas fa-credit-card text-blue-500 text-2xl mb-1"></i>
                <p class="text-sm text-slate-600">Crédito</p>
                <p class="text-2xl font-bold text-blue-600">{{ $moneda }}{{ number_format($cliente->credito_disponible, 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4 text-center">
                <i class="fas fa-shopping-cart text-green-500 text-2xl mb-1"></i>
                <p class="text-sm text-slate-600">Compras</p>
                <p class="text-2xl font-bold text-green-600">{{ $cliente->ventas->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold mb-4"><i class="fas fa-receipt mr-2 text-emerald-500"></i>Historial de compras</h3>
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-500 border-b">
                    <tr><th class="text-left py-2">Ticket</th><th class="text-left py-2">Fecha</th><th class="text-left py-2">Pago</th><th class="text-right py-2">Total</th></tr>
                </thead>
                <tbody>
                @forelse($cliente->ventas as $v)
                    <tr class="border-b">
                        <td class="py-2 font-mono text-xs">{{ $v->numero_ticket }}</td>
                        <td class="py-2">{{ $v->fecha_venta->format('d/m/Y H:i') }}</td>
                        <td class="py-2">{{ ucfirst($v->forma_pago) }}</td>
                        <td class="py-2 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($v->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-6 text-slate-400">Sin compras</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
