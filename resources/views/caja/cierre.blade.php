@extends('layouts.app')
@section('title', 'Cierre de Caja')
@section('header', 'Cierre de Turno')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-8 max-w-3xl mx-auto">
    <div class="text-center mb-6">
        <i class="fas fa-clipboard-check text-5xl text-emerald-500 mb-3"></i>
        <h2 class="text-2xl font-bold">Reporte de Cierre</h2>
        <p class="text-slate-400">{{ $turno->caja->nombre }} - {{ $turno->user->name }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-slate-800 p-4 rounded-xl">
            <p class="text-xs text-slate-300">APERTURA</p>
            <p class="text-sm">{{ $turno->fecha_apertura->format('d/m/Y H:i') }}</p>
        </div>
        <div class="bg-slate-800 p-4 rounded-xl">
            <p class="text-xs text-slate-300">CIERRE</p>
            <p class="text-sm">{{ $turno->fecha_cierre?->format('d/m/Y H:i') ?? 'Abierto' }}</p>
        </div>
    </div>

    <div class="space-y-2 text-sm border-t border-b py-4 mb-4">
        <div class="flex justify-between"><span>Monto apertura:</span><span class="font-semibold">{{ $moneda }}{{ number_format($turno->monto_apertura, 2) }}</span></div>
        <div class="flex justify-between"><span>Total efectivo (ventas):</span><span class="font-semibold text-green-600">+{{ $moneda }}{{ number_format($turno->total_efectivo, 2) }}</span></div>
        <div class="flex justify-between"><span>Total tarjeta:</span><span class="font-semibold">{{ $moneda }}{{ number_format($turno->total_tarjeta, 2) }}</span></div>
        <div class="flex justify-between"><span>Total otros:</span><span class="font-semibold">{{ $moneda }}{{ number_format($turno->total_otros, 2) }}</span></div>
        <div class="flex justify-between border-t pt-2"><span>Total ventas:</span><span class="font-bold text-emerald-600">{{ $moneda }}{{ number_format($turno->total_ventas, 2) }}</span></div>
        <div class="flex justify-between"><span>Cantidad tickets:</span><span class="font-semibold">{{ $turno->cantidad_ventas }}</span></div>
    </div>

    @if($turno->estado == 'cerrado')
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-blue-50 p-4 rounded-xl text-center">
                <p class="text-xs text-blue-600">CALCULADO</p>
                <p class="text-2xl font-bold text-blue-800">{{ $moneda }}{{ number_format($turno->monto_calculado, 2) }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-xl text-center">
                <p class="text-xs text-green-600">CONTADO</p>
                <p class="text-2xl font-bold text-green-800">{{ $moneda }}{{ number_format($turno->monto_cierre, 2) }}</p>
            </div>
        </div>
        <div class="rounded-xl p-4 text-center {{ $turno->diferencia == 0 ? 'bg-green-100' : ($turno->diferencia > 0 ? 'bg-yellow-100' : 'bg-red-100') }}">
            <p class="text-xs">DIFERENCIA</p>
            <p class="text-3xl font-bold {{ $turno->diferencia == 0 ? 'text-green-700' : ($turno->diferencia > 0 ? 'text-yellow-700' : 'text-red-700') }}">
                {{ $moneda }}{{ number_format($turno->diferencia, 2) }}
            </p>
            <p class="text-xs mt-1">{{ $turno->diferencia == 0 ? '✓ Caja cuadrada' : ($turno->diferencia > 0 ? 'Sobrante' : 'Faltante') }}</p>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-3 mt-6">
        <a href="{{ route('caja.index') }}" class="flex-1 text-center py-3 bg-slate-900 hover:bg-slate-700 text-slate-200 rounded-xl font-bold transition">Volver</a>
        <a href="{{ route('caja.ticket', $turno->id) }}" target="_blank" class="flex-1 text-center py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition flex items-center justify-center gap-2">
            <i class="fas fa-receipt"></i><span>Ticket 80mm</span>
        </a>
        <button onclick="window.print()" class="flex-1 gradient-primary text-white py-3 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-sm">
            <i class="fas fa-print"></i><span>Imprimir A4</span>
        </button>
    </div>
</div>
@endsection
