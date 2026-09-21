@extends('layouts.app')

@section('titulo', 'Inventario Valorizado')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                <i class="fas fa-file-invoice-dollar text-emerald-500"></i> Inventario Valorizado Total
            </h1>
            <p class="text-slate-500 text-sm mt-1">Conoce el valor total de la mercadería en tienda basada en su costo de compra.</p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('kardex-valorizado.exportar', request()->all()) }}" class="btn bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-red-500/30 transition flex items-center gap-2 text-sm">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('kardex-valorizado.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1 block">Buscar Producto</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o Código..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1 block">Categoría</label>
                <select name="categoria_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-4 rounded-lg transition">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Resumen Valorizado -->
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg p-6 mb-6 text-white flex items-center justify-between">
        <div>
            <p class="text-emerald-100 font-bold uppercase tracking-wider text-sm mb-1">Valor Total en Tienda</p>
            <h2 class="text-4xl font-black">S/ {{ number_format($totalValorizado, 2) }}</h2>
            <p class="text-emerald-50 text-sm mt-1">Costo total sumado de todo tu stock físico actual.</p>
        </div>
        <div class="hidden md:block opacity-20">
            <i class="fas fa-coins text-7xl"></i>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase font-bold tracking-wider">
                        <th class="px-5 py-4">Código</th>
                        <th class="px-5 py-4">Producto</th>
                        <th class="px-5 py-4 text-center">Stock Físico</th>
                        <th class="px-5 py-4 text-right">Costo Compra (Und)</th>
                        <th class="px-5 py-4 text-right text-emerald-600">Total Valorizado</th>
                        <th class="px-5 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productos as $producto)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 text-sm text-slate-500">{{ $producto->codigo }}</td>
                            <td class="px-5 py-3">
                                <p class="text-sm font-bold text-slate-800">{{ $producto->nombre }}</p>
                                <p class="text-xs text-slate-500">{{ $producto->categoria?->nombre }}</p>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold {{ $producto->stock <= $producto->stock_minimo ? 'text-red-500' : 'text-slate-700' }}">
                                    {{ number_format($producto->stock, 2) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm text-slate-600 font-medium">S/ {{ number_format($producto->precio_compra, 2) }}</td>
                            <td class="px-5 py-3 text-right text-sm font-black text-emerald-600 bg-emerald-50/30">S/ {{ number_format($producto->valor_total, 2) }}</td>
                            <td class="px-5 py-3 text-center">
                                <a href="{{ route('kardex-valorizado.show', $producto->id) }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                    <i class="fas fa-eye"></i> Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                <i class="fas fa-box-open text-4xl mb-3"></i>
                                <p>No se encontraron productos con los filtros aplicados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
