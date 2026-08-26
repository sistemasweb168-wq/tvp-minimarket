@extends('layouts.app')
@section('title', 'Productos')
@section('header', 'Gestión de Productos')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-white rounded-2xl shadow-md p-5 mb-5">
    <div class="flex flex-col md:flex-row gap-3 justify-between">
        <form method="GET" class="flex flex-1 gap-2 max-w-2xl">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, código o código de barras..."
                       class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-500">
            </div>
            <select name="categoria_id" class="px-3 py-2.5 border border-slate-300 rounded-lg">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $c)
                    <option value="{{ $c->id }}" {{ request('categoria_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                @endforeach
            </select>
            <select name="estado" class="px-3 py-2.5 border border-slate-300 rounded-lg">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado')=='activo'?'selected':'' }}>Activos</option>
                <option value="inactivo" {{ request('estado')=='inactivo'?'selected':'' }}>Inactivos</option>
                <option value="stock_bajo" {{ request('estado')=='stock_bajo'?'selected':'' }}>Stock bajo</option>
            </select>
            <button class="bg-slate-800 text-white px-4 py-2.5 rounded-lg hover:bg-slate-900"><i class="fas fa-filter"></i></button>
        </form>
        <a href="{{ route('productos.create') }}" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold hover:shadow-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i>Nuevo Producto
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="text-left py-3 px-4">Producto</th>
                    <th class="text-left py-3 px-4">Categoría</th>
                    <th class="text-right py-3 px-4">Precio</th>
                    <th class="text-right py-3 px-4">Stock</th>
                    <th class="text-center py-3 px-4">Estado</th>
                    <th class="text-right py-3 px-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $p)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($p->imagen)
                                        <img src="{{ $p->imagen_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-box text-slate-300 text-xl"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $p->nombre }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $p->codigo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($p->categoria)
                                <span class="px-2 py-1 rounded-full text-xs text-white" style="background:{{ $p->categoria->color }}">{{ $p->categoria->nombre }}</span>
                            @else
                                <span class="text-slate-400 text-sm">Sin categoría</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <p class="font-bold text-emerald-600">{{ $moneda }} {{ number_format($p->precio_venta, 2) }}</p>
                            <p class="text-xs text-slate-400">Compra: {{ $moneda }} {{ number_format($p->precio_compra, 2) }}</p>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <p class="font-bold {{ $p->stock_bajo ? 'text-red-600' : 'text-slate-800' }}">{{ number_format($p->stock, 2) }}</p>
                            <p class="text-xs text-slate-500">{{ $p->unidad_medida }}</p>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if(!$p->activo)
                                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full text-xs">Inactivo</span>
                            @elseif($p->stock_bajo)
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Stock bajo</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Activo</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('productos.show', $p->id) }}" class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('productos.edit', $p->id) }}" class="p-2 hover:bg-yellow-50 text-yellow-600 rounded-lg" title="Editar"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('productos.destroy', $p->id) }}" class="inline" onsubmit="return confirm('¿Desactivar producto?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 hover:bg-red-50 text-red-600 rounded-lg" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-12 text-slate-400">
                        <i class="fas fa-box-open text-5xl mb-2"></i>
                        <p>No hay productos registrados</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">{{ $productos->withQueryString()->links() }}</div>
</div>
@endsection
