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

<!-- Vista de Productos (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS NATIVAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($productos as $p)
            <div class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition">
                <!-- Imagen -->
                <div class="w-14 h-14 bg-slate-100 rounded-2xl overflow-hidden flex-shrink-0 flex items-center justify-center border border-slate-100 shadow-xs">
                    @if($p->imagen)
                        <img src="{{ $p->imagen_url }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-box text-slate-300 text-xl"></i>
                    @endif
                </div>

                <!-- Info Central -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-1 mb-0.5">
                        <h4 class="font-extrabold text-slate-800 text-xs sm:text-sm leading-snug break-words">{{ $p->nombre }}</h4>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                        <span class="font-mono text-[10px] text-slate-400 font-semibold">{{ $p->codigo }}</span>
                        @if($p->categoria)
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md text-white" style="background:{{ $p->categoria->color }}">{{ $p->categoria->nombre }}</span>
                        @endif
                        @if(!$p->activo)
                            <span class="text-[10px] font-bold px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded">Inactivo</span>
                        @elseif($p->stock_bajo)
                            <span class="text-[10px] font-bold px-1.5 py-0.2 bg-red-100 text-red-700 rounded">Stock bajo</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-1 pt-1.5 border-t border-slate-100/80">
                        <div>
                            <span class="font-black text-emerald-600 text-sm sm:text-base">{{ $moneda }}{{ number_format($p->precio_venta, 2) }}</span>
                            <span class="text-[10px] text-slate-400 ml-1">C: {{ $moneda }}{{ number_format($p->precio_compra, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-black {{ $p->stock_bajo ? 'text-red-600' : 'text-slate-700' }}">{{ number_format($p->stock, 0) }} {{ $p->unidad_medida }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="flex flex-col gap-1 pl-1 flex-shrink-0">
                    <a href="{{ route('productos.edit', $p->id) }}" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs shadow-xs" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('productos.show', $p->id) }}" class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs shadow-xs" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fas fa-box-open text-4xl mb-2 text-slate-300"></i>
                <p>No se encontraron productos</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA COMPLETA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3.5 px-4">Producto</th>
                    <th class="py-3.5 px-4">Categoría</th>
                    <th class="py-3.5 px-4 text-right">Precio</th>
                    <th class="py-3.5 px-4 text-right">Stock</th>
                    <th class="py-3.5 px-4 text-center">Estado</th>
                    <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($productos as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-slate-100 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($p->imagen)
                                        <img src="{{ $p->imagen_url }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-box text-slate-300 text-lg"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 text-sm">{{ $p->nombre }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $p->codigo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($p->categoria)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold text-white shadow-xs" style="background:{{ $p->categoria->color }}">{{ $p->categoria->nombre }}</span>
                            @else
                                <span class="text-slate-400 text-xs">Sin categoría</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <p class="font-extrabold text-emerald-600 text-sm">{{ $moneda }}{{ number_format($p->precio_venta, 2) }}</p>
                            <p class="text-xs text-slate-400">Compra: {{ $moneda }}{{ number_format($p->precio_compra, 2) }}</p>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <p class="font-extrabold text-sm {{ $p->stock_bajo ? 'text-red-600' : 'text-slate-800' }}">{{ number_format($p->stock, 0) }}</p>
                            <p class="text-xs text-slate-400">{{ $p->unidad_medida }}</p>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(!$p->activo)
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">Inactivo</span>
                            @elseif($p->stock_bajo)
                                <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Stock bajo</span>
                            @else
                                <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Activo</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('productos.show', $p->id) }}" class="p-2 hover:bg-blue-50 text-blue-600 rounded-lg text-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('productos.edit', $p->id) }}" class="p-2 hover:bg-yellow-50 text-yellow-600 rounded-lg text-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('productos.destroy', $p->id) }}" class="inline" onsubmit="return confirm('¿Desactivar producto?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 hover:bg-red-50 text-red-600 rounded-lg text-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
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
