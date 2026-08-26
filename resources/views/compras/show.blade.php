@extends('layouts.app')
@section('title', 'Compra ' . $compra->numero)
@section('header', 'Detalle de Compra')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6">
        <div class="flex justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">{{ $compra->numero }}</h2>
                <p class="text-slate-500">{{ $compra->fecha_compra->format('d/m/Y H:i') }}</p>
                <p class="text-sm">Factura: {{ $compra->numero_factura ?: '—' }}</p>
            </div>
            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">{{ ucfirst($compra->estado) }}</span>
        </div>

        <table class="w-full">
            <thead class="border-b-2 text-xs uppercase text-slate-500">
                <tr><th class="text-left py-2">Producto</th><th class="text-right py-2">Cantidad</th><th class="text-right py-2">Precio</th><th class="text-right py-2">Total</th></tr>
            </thead>
            <tbody>
            @foreach($compra->detalles as $d)
                <tr class="border-b">
                    <td class="py-3"><p class="font-semibold">{{ $d->descripcion }}</p><p class="text-xs text-slate-500">{{ $d->codigo }}</p></td>
                    <td class="py-3 text-right">{{ number_format($d->cantidad, 2) }}</td>
                    <td class="py-3 text-right">{{ $moneda }}{{ number_format($d->precio_unitario, 2) }}</td>
                    <td class="py-3 text-right font-bold">{{ $moneda }}{{ number_format($d->total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4 pt-4 border-t-2 text-right">
            <div class="text-2xl font-bold">Total: <span class="text-emerald-600">{{ $moneda }}{{ number_format($compra->total, 2) }}</span></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-6 h-fit">
        <h3 class="font-bold mb-3">Información</h3>
        <div class="space-y-2 text-sm">
            <p><span class="text-slate-500">Proveedor:</span> <strong>{{ $compra->proveedor->razon_social }}</strong></p>
            <p><span class="text-slate-500">Registrado por:</span> <strong>{{ $compra->user->name }}</strong></p>
            <p><span class="text-slate-500">Forma de pago:</span> <strong>{{ ucfirst($compra->forma_pago) }}</strong></p>
        </div>
        @if($compra->observaciones)
            <p class="mt-4 text-sm text-slate-600 italic">{{ $compra->observaciones }}</p>
        @endif
        <a href="{{ route('compras.index') }}" class="block text-center mt-4 bg-slate-100 hover:bg-slate-200 py-2.5 rounded-lg">Volver</a>
    </div>
</div>
@endsection
