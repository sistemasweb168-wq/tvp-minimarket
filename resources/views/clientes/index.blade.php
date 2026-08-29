@extends('layouts.app')
@section('title', 'Clientes')
@section('header', 'Gestión de Clientes')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5 flex flex-col md:flex-row gap-3 justify-between">
    <form method="GET" class="flex-1 flex gap-2 max-w-2xl">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar cliente..." class="w-full pl-12 pr-4 py-2.5 border border-slate-600 rounded-lg">
        </div>
        <button class="bg-slate-800 text-white px-4 py-2.5 rounded-lg"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('clientes.create') }}" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2">
        <i class="fas fa-user-plus"></i>Nuevo Cliente
    </a>
</div>

<!-- Vista de Clientes (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-800">
        @forelse($clientes as $c)
            <div class="p-3.5 flex items-start gap-3 hover:bg-slate-800 transition">
                <div class="w-12 h-12 gradient-primary rounded-2xl flex items-center justify-center text-white font-bold text-base flex-shrink-0 shadow-xs">
                    {{ strtoupper(substr($c->nombres, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-extrabold text-slate-100 text-xs sm:text-sm leading-snug">{{ $c->nombre_completo }}</h4>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 my-0.5">
                        <span class="font-mono">{{ $c->tipo_documento }}: {{ $c->documento ?: '—' }}</span>
                        <span class="bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded-md font-bold text-[10px]"><i class="fas fa-star text-amber-500 mr-0.5"></i>{{ $c->puntos_fidelidad }} pts</span>
                    </div>
                    @if($c->telefono)
                        <p class="text-[11px] text-slate-400"><i class="fas fa-phone mr-1"></i>{{ $c->telefono }}</p>
                    @endif
                </div>
                <div class="flex flex-col gap-1 pl-1 flex-shrink-0">
                    <a href="{{ route('clientes.edit', $c->id) }}" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-xs" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('clientes.show', $c->id) }}" class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-xs" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fas fa-users text-4xl mb-2 text-slate-300"></i>
                <p>No se encontraron clientes</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3.5 px-4">Cliente</th>
                    <th class="py-3.5 px-4">Documento</th>
                    <th class="py-3.5 px-4">Contacto</th>
                    <th class="py-3.5 px-4 text-center">Puntos</th>
                    <th class="py-3.5 px-4 text-right">Crédito</th>
                    <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($clientes as $c)
                    <tr class="hover:bg-slate-800/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 gradient-primary rounded-full flex items-center justify-center text-white font-semibold shadow-xs">
                                    {{ strtoupper(substr($c->nombres, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-100 text-sm">{{ $c->nombre_completo }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $c->codigo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-sm">
                            <span class="text-xs text-slate-400">{{ $c->tipo_documento }}:</span>
                            <span class="font-mono font-medium">{{ $c->documento ?: '—' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-sm">
                            @if($c->telefono)<p class="text-xs text-slate-300"><i class="fas fa-phone text-xs text-slate-400 mr-1"></i>{{ $c->telefono }}</p>@endif
                            @if($c->email)<p class="text-xs text-slate-400"><i class="fas fa-envelope text-xs text-slate-300 mr-1"></i>{{ $c->email }}</p>@endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full text-xs font-semibold"><i class="fas fa-star mr-1"></i>{{ $c->puntos_fidelidad }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right text-sm whitespace-nowrap">
                            <p class="font-bold text-slate-100">{{ $moneda }}{{ number_format($c->credito_disponible, 2) }}</p>
                            <p class="text-xs text-slate-400">de {{ $moneda }}{{ number_format($c->credito_limite, 2) }}</p>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('clientes.show', $c->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg text-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('clientes.edit', $c->id) }}" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg text-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('clientes.destroy', $c->id) }}" class="inline" onsubmit="return confirm('¿Desactivar cliente?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg text-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-400 text-sm">No se encontraron clientes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 sm:p-4 border-t border-slate-800">{{ $clientes->withQueryString()->links() }}</div>
</div>
@endsection
