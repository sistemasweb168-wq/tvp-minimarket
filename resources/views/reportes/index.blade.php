@extends('layouts.app')
@section('title', 'Reportes')
@section('header', 'Centro de Reportes')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
    <a href="{{ route('reportes.ventas') }}" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl hover:-translate-y-1 transition group">
        <div class="w-14 h-14 gradient-card-1 rounded-2xl flex items-center justify-center text-white mb-4 group-hover:scale-110 transition">
            <i class="fas fa-chart-line text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-1">Reporte de Ventas</h3>
        <p class="text-sm text-slate-400">Análisis detallado de ventas por período, forma de pago y cajero.</p>
    </a>

    <a href="{{ route('reportes.productos') }}" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl hover:-translate-y-1 transition group">
        <div class="w-14 h-14 gradient-card-2 rounded-2xl flex items-center justify-center text-white mb-4 group-hover:scale-110 transition">
            <i class="fas fa-trophy text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-1">Productos más Vendidos</h3>
        <p class="text-sm text-slate-400">Ranking de productos con mayor rotación y mejores ingresos.</p>
    </a>

    <a href="{{ route('reportes.inventario') }}" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl hover:-translate-y-1 transition group">
        <div class="w-14 h-14 gradient-card-3 rounded-2xl flex items-center justify-center text-white mb-4 group-hover:scale-110 transition">
            <i class="fas fa-warehouse text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-1">Estado de Inventario</h3>
        <p class="text-sm text-slate-400">Valor total del stock, productos bajo mínimo, etc.</p>
    </a>

    <a href="{{ route('reportes.vencimientos') }}" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6 hover:shadow-xl hover:-translate-y-1 transition group">
        <div class="w-14 h-14 gradient-danger rounded-2xl flex items-center justify-center text-white mb-4 group-hover:scale-110 transition">
            <i class="fas fa-clock text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-1">Vencimientos</h3>
        <p class="text-sm text-slate-400">Productos próximos a vencer o ya vencidos.</p>
    </a>
</div>
@endsection
