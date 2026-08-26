@extends('layouts.app')
@section('title', $producto->nombre)
@section('header', 'Producto: ' . $producto->nombre)

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-1 space-y-5">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <div class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-4 flex items-center justify-center">
                @if($producto->imagen)
                    <img src="{{ $producto->imagen_url }}" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-box text-6xl text-slate-300"></i>
                @endif
            </div>
            <h2 class="text-xl font-bold text-slate-800">{{ $producto->nombre }}</h2>
            <p class="text-sm text-slate-500 font-mono mt-1">{{ $producto->codigo }}</p>
            @if($producto->categoria)
                <span class="inline-block px-3 py-1 mt-2 rounded-full text-xs text-white" style="background:{{ $producto->categoria->color }}">{{ $producto->categoria->nombre }}</span>
            @endif

            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="bg-emerald-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-emerald-600 font-semibold">PRECIO VENTA</p>
                    <p class="text-2xl font-bold text-emerald-700">{{ $moneda }}{{ number_format($producto->precio_venta, 2) }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <p class="text-xs text-blue-600 font-semibold">STOCK</p>
                    <p class="text-2xl font-bold {{ $producto->stock_bajo ? 'text-red-600' : 'text-blue-700' }}">{{ number_format($producto->stock, 2) }}</p>
                </div>
            </div>

            <a href="{{ route('productos.edit', $producto->id) }}" class="block text-center mt-4 gradient-primary text-white py-2.5 rounded-lg font-semibold">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4"><i class="fas fa-sliders-h mr-2 text-emerald-500"></i>Ajustar Stock</h3>
            <form method="POST" action="{{ route('productos.ajuste-stock', $producto->id) }}" class="space-y-3">
                @csrf
                <select name="tipo" required class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                    <option value="entrada">Entrada (sumar)</option>
                    <option value="salida">Salida (restar)</option>
                    <option value="ajuste">Ajuste (fijar valor)</option>
                    <option value="merma">Merma</option>
                </select>
                <input type="number" step="0.001" name="cantidad" placeholder="Cantidad" required class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                <input type="text" name="motivo" placeholder="Motivo" required class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                <textarea name="observaciones" placeholder="Observaciones" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
                <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg font-semibold"><i class="fas fa-check mr-1"></i>Registrar</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4">Información detallada</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-slate-500">Precio compra</p><p class="font-semibold">{{ $moneda }}{{ number_format($producto->precio_compra, 2) }}</p></div>
                <div><p class="text-slate-500">Margen</p><p class="font-semibold text-emerald-600">{{ $producto->margen }}%</p></div>
                <div><p class="text-slate-500">Stock mínimo</p><p class="font-semibold">{{ number_format($producto->stock_minimo, 2) }}</p></div>
                <div><p class="text-slate-500">Stock máximo</p><p class="font-semibold">{{ number_format($producto->stock_maximo, 2) }}</p></div>
                <div><p class="text-slate-500">Unidad</p><p class="font-semibold">{{ $producto->unidad_medida }}</p></div>
                <div><p class="text-slate-500">Ubicación</p><p class="font-semibold">{{ $producto->ubicacion ?: '—' }}</p></div>
                <div><p class="text-slate-500">Lote</p><p class="font-semibold">{{ $producto->lote ?: '—' }}</p></div>
                <div><p class="text-slate-500">Vencimiento</p><p class="font-semibold">{{ $producto->fecha_vencimiento?->format('d/m/Y') ?: '—' }}</p></div>
                <div><p class="text-slate-500">Proveedor</p><p class="font-semibold">{{ $producto->proveedor?->razon_social ?: '—' }}</p></div>
                <div><p class="text-slate-500">Código de barras</p><p class="font-semibold font-mono">{{ $producto->codigo_barras ?: '—' }}</p></div>
            </div>
            @if($producto->descripcion)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-slate-500 text-sm mb-1">Descripción</p>
                    <p class="text-slate-700">{{ $producto->descripcion }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4"><i class="fas fa-history mr-2 text-blue-500"></i>Movimientos de Inventario</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-slate-500 uppercase border-b">
                        <tr>
                            <th class="text-left py-2">Fecha</th>
                            <th class="text-left py-2">Tipo</th>
                            <th class="text-left py-2">Motivo</th>
                            <th class="text-right py-2">Cantidad</th>
                            <th class="text-right py-2">Stock Final</th>
                            <th class="text-left py-2">Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $m)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 text-xs">{{ $m->fecha->format('d/m/Y H:i') }}</td>
                                <td class="py-2">
                                    @php
                                        $colors = ['entrada' => 'green', 'salida' => 'red', 'ajuste' => 'blue', 'merma' => 'orange', 'transferencia' => 'purple'];
                                        $color = $colors[$m->tipo] ?? 'slate';
                                    @endphp
                                    <span class="px-2 py-1 bg-{{ $color }}-100 text-{{ $color }}-700 rounded-full text-xs">{{ ucfirst($m->tipo) }}</span>
                                </td>
                                <td class="py-2">{{ $m->motivo }}</td>
                                <td class="py-2 text-right font-semibold {{ $m->tipo == 'entrada' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $m->tipo == 'entrada' ? '+' : '-' }}{{ number_format($m->cantidad, 2) }}
                                </td>
                                <td class="py-2 text-right font-semibold">{{ number_format($m->stock_nuevo, 2) }}</td>
                                <td class="py-2 text-xs">{{ $m->user?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-6 text-slate-400">Sin movimientos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
