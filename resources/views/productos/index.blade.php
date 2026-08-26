@extends('layouts.app')
@section('title', 'Productos')
@section('header', 'Gestión de Productos')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 mb-4 sm:mb-5">
    <div class="flex flex-col md:flex-row gap-3 justify-between items-stretch md:items-center">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:flex flex-1 gap-2 max-w-2xl">
            <div class="relative flex-1 col-span-1 sm:col-span-2 md:col-span-1">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar producto, código..."
                       class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
            </div>
            <select name="categoria_id" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $c)
                    <option value="{{ $c->id }}" {{ request('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                @endforeach
            </select>
            <select name="estado" class="px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:border-emerald-500">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado')=='activo'?'selected':'' }}>Activos</option>
                <option value="inactivo" {{ request('estado')=='inactivo'?'selected':'' }}>Inactivos</option>
                <option value="stock_bajo" {{ request('estado')=='stock_bajo'?'selected':'' }}>Stock bajo</option>
            </select>
            <button class="bg-slate-800 text-white px-4 py-2 rounded-xl hover:bg-slate-900 font-semibold text-sm transition flex items-center justify-center gap-1.5"><i class="fas fa-filter"></i><span>Filtrar</span></button>
        </form>
        <a href="{{ route('productos.create') }}" class="gradient-primary text-white px-4 py-2.5 rounded-xl font-bold hover:shadow-lg transition flex items-center justify-center gap-2 text-sm shadow-sm">
            <i class="fas fa-plus"></i><span>Nuevo Producto</span>
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-[11px] sm:text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3 px-3 sm:px-4">Producto</th>
                    <th class="py-3 px-3 sm:px-4 hidden md:table-cell">Categoría</th>
                    <th class="py-3 px-3 sm:px-4 text-right">Precio</th>
                    <th class="py-3 px-3 sm:px-4 text-right">Stock</th>
                    <th class="py-3 px-3 sm:px-4 text-center hidden sm:table-cell">Estado</th>
                    <th class="py-3 px-3 sm:px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($productos as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-3 sm:px-4">
                            <div class="flex items-center gap-2.5 sm:gap-3">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($p->imagen)
                                        <img src="{{ $p->imagen_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-box text-slate-300 text-lg"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 text-xs sm:text-sm line-clamp-1">{{ $p->nombre }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $p->codigo }}</p>
                                    @if($p->categoria)
                                        <span class="inline-block md:hidden text-[10px] px-1.5 py-0.5 rounded text-white mt-0.5" style="background:{{ $p->categoria->color }}">{{ $p->categoria->nombre }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 sm:px-4 hidden md:table-cell">
                            @if($p->categoria)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold text-white shadow-xs" style="background:{{ $p->categoria->color }}">{{ $p->categoria->nombre }}</span>
                            @else
                                <span class="text-slate-400 text-xs">Sin categoría</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 sm:px-4 text-right whitespace-nowrap">
                            <p class="font-extrabold text-emerald-600 text-xs sm:text-sm">{{ $moneda }}{{ number_format($p->precio_venta, 2) }}</p>
                            <p class="text-[10px] sm:text-xs text-slate-400">C: {{ $moneda }}{{ number_format($p->precio_compra, 2) }}</p>
                        </td>
                        <td class="py-3 px-3 sm:px-4 text-right whitespace-nowrap">
                            <p class="font-extrabold text-xs sm:text-sm {{ $p->stock_bajo ? 'text-red-600' : 'text-slate-800' }}">{{ number_format($p->stock, 0) }}</p>
                            <p class="text-[10px] text-slate-400">{{ $p->unidad_medida }}</p>
                        </td>
                        <td class="py-3 px-3 sm:px-4 text-center hidden sm:table-cell">
                            @if(!$p->activo)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[11px] font-bold">Inactivo</span>
                            @elseif($p->stock_bajo)
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[11px] font-bold">Stock bajo</span>
                            @else
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[11px] font-bold">Activo</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 sm:px-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('productos.show', $p->id) }}" class="p-1.5 hover:bg-blue-50 text-blue-600 rounded-lg text-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('productos.edit', $p->id) }}" class="p-1.5 hover:bg-yellow-50 text-yellow-600 rounded-lg text-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('productos.destroy', $p->id) }}" class="inline" onsubmit="return confirm('¿Desactivar producto?')">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 hover:bg-red-50 text-red-600 rounded-lg text-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                            <i class="fas fa-box-open text-4xl mb-2 text-slate-300"></i>
                            <p>No se encontraron productos</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 sm:p-4 border-t border-slate-100">{{ $productos->withQueryString()->links() }}</div>
</div>
@endsection
