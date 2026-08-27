@extends('layouts.app')
@section('title', 'Proveedores')
@section('header', 'Gestión de Proveedores')

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5 flex flex-col md:flex-row gap-3 justify-between">
    <form method="GET" class="flex-1 flex gap-2 max-w-2xl">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar proveedor..." class="w-full pl-12 pr-4 py-2.5 border border-slate-600 rounded-lg">
        </div>
        <button class="bg-slate-800 text-white px-4 py-2.5 rounded-lg"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('proveedores.create') }}" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2">
        <i class="fas fa-plus"></i>Nuevo Proveedor
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($proveedores as $p)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 hover:shadow-lg transition">
            <div class="flex justify-between items-start mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-truck text-blue-600 text-xl"></i>
                </div>
                <div class="flex gap-1">
                    <a href="{{ route('proveedores.show', $p->id) }}" class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('proveedores.edit', $p->id) }}" class="p-2 hover:bg-yellow-50 text-yellow-600 rounded-lg"><i class="fas fa-edit"></i></a>
                </div>
            </div>
            <h3 class="font-bold text-slate-100">{{ $p->razon_social }}</h3>
            <p class="text-sm text-slate-400 mb-3">{{ $p->codigo }} • {{ $p->ruc_nit ?: 'Sin RUC' }}</p>
            <div class="space-y-1 text-sm">
                @if($p->contacto)<p><i class="fas fa-user text-slate-400 w-4"></i>{{ $p->contacto }}</p>@endif
                @if($p->telefono)<p><i class="fas fa-phone text-slate-400 w-4"></i>{{ $p->telefono }}</p>@endif
                @if($p->email)<p><i class="fas fa-envelope text-slate-400 w-4"></i>{{ $p->email }}</p>@endif
            </div>
        </div>
    @empty
        <p class="col-span-full text-center text-slate-400 py-12">Sin proveedores</p>
    @endforelse
</div>
<div class="mt-4">{{ $proveedores->withQueryString()->links() }}</div>
@endsection
