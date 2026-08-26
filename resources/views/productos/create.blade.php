@extends('layouts.app')
@section('title', 'Nuevo Producto')
@section('header', 'Nuevo Producto')

@section('content')
<form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    @csrf

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100"><i class="fas fa-info-circle text-emerald-500 mr-2"></i>Información General</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo" value="{{ old('codigo', $codigo) }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Código de barras</label>
                    <input type="text" name="codigo_barras" value="{{ old('codigo_barras') }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">{{ old('descripcion') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Categoría</label>
                    <select name="categoria_id" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        <option value="">— Sin categoría —</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        <option value="">— Sin proveedor —</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}">{{ $p->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Unidad de Medida</label>
                    <select name="unidad_medida" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        <option value="UND">Unidad</option>
                        <option value="KG">Kilogramo</option>
                        <option value="LT">Litro</option>
                        <option value="GR">Gramo</option>
                        <option value="ML">Mililitro</option>
                        <option value="CAJA">Caja</option>
                        <option value="PAQ">Paquete</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" placeholder="Ej: Pasillo 3 Estante 2" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100"><i class="fas fa-dollar-sign text-emerald-500 mr-2"></i>Precios</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Precio de Compra <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="precio_compra" value="0" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Precio de Venta <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="precio_venta" value="0" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Precio Mayoreo</label>
                    <input type="number" step="0.01" name="precio_mayoreo" value="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Cantidad Mínima Mayoreo</label>
                    <input type="number" name="cantidad_mayoreo" value="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100"><i class="fas fa-warehouse text-emerald-500 mr-2"></i>Inventario</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Stock Inicial</label>
                    <input type="number" step="0.001" name="stock" value="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Stock Mínimo</label>
                    <input type="number" step="0.001" name="stock_minimo" value="0" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Stock Máximo</label>
                    <input type="number" step="0.001" name="stock_maximo" value="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Lote</label>
                    <input type="text" name="lote" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4"><i class="fas fa-image text-emerald-500 mr-2"></i>Imagen</h3>
            <input type="file" name="imagen" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-800 mb-4"><i class="fas fa-cog text-emerald-500 mr-2"></i>Opciones</h3>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="controla_stock" value="1" checked class="rounded text-emerald-500">
                <span class="text-sm text-slate-700">Controla inventario</span>
            </label>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="aplica_impuesto" value="1" checked class="rounded text-emerald-500">
                <span class="text-sm text-slate-700">Aplica impuesto</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="destacado" value="1" class="rounded text-emerald-500">
                <span class="text-sm text-slate-700">Producto destacado (POS)</span>
            </label>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <button type="submit" class="w-full gradient-primary text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
                <i class="fas fa-save mr-2"></i>Guardar Producto
            </button>
            <a href="{{ route('productos.index') }}" class="block text-center mt-2 py-3 text-slate-600 hover:bg-slate-100 rounded-lg">Cancelar</a>
        </div>
    </div>
</form>
@endsection
