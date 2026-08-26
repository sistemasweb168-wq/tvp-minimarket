@extends('layouts.app')
@section('title', 'Clientes')
@section('header', 'Gestión de Clientes')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-5 mb-5 flex flex-col md:flex-row gap-3 justify-between">
    <form method="GET" class="flex-1 flex gap-2 max-w-2xl">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar cliente..." class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-lg">
        </div>
        <button class="bg-slate-800 text-white px-4 py-2.5 rounded-lg"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('clientes.create') }}" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2">
        <i class="fas fa-user-plus"></i>Nuevo Cliente
    </a>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="text-left py-3 px-4">Cliente</th>
                <th class="text-left py-3 px-4">Documento</th>
                <th class="text-left py-3 px-4">Contacto</th>
                <th class="text-center py-3 px-4">Puntos</th>
                <th class="text-right py-3 px-4">Crédito</th>
                <th class="text-right py-3 px-4">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $c)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 gradient-primary rounded-full flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($c->nombres, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $c->nombre_completo }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $c->codigo }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm">
                        <span class="text-xs text-slate-500">{{ $c->tipo_documento }}:</span>
                        <span class="font-mono">{{ $c->documento ?: '—' }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm">
                        @if($c->telefono)<p><i class="fas fa-phone text-xs text-slate-400 mr-1"></i>{{ $c->telefono }}</p>@endif
                        @if($c->email)<p class="text-xs"><i class="fas fa-envelope text-xs text-slate-400 mr-1"></i>{{ $c->email }}</p>@endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-semibold"><i class="fas fa-star mr-1"></i>{{ $c->puntos_fidelidad }}</span>
                    </td>
                    <td class="py-3 px-4 text-right text-sm">
                        <p class="font-semibold">{{ $moneda }}{{ number_format($c->credito_disponible, 2) }}</p>
                        <p class="text-xs text-slate-400">de {{ $moneda }}{{ number_format($c->credito_limite, 2) }}</p>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('clientes.show', $c->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('clientes.edit', $c->id) }}" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('clientes.destroy', $c->id) }}" class="inline" onsubmit="return confirm('¿Desactivar?')">
                            @csrf @method('DELETE')
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-12 text-slate-400">Sin clientes</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4">{{ $clientes->withQueryString()->links() }}</div>
</div>
@endsection
