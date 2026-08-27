@extends('layouts.app')
@section('title', 'Editar Producto')
@section('header', 'Editar: ' . $producto->nombre)

@section('content')
<form x-data="comboForm()" method="POST" action="{{ route('productos.update', $producto->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    @csrf @method('PUT')

    <div class="lg:col-span-2 space-y-5">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-info-circle text-emerald-500 mr-2"></i>Información General</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $producto->codigo) }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código de barras</label>
                    <input type="text" name="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Categoría</label>
                    <select name="categoria_id" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                        <option value="">— Sin categoría —</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}" {{ $producto->categoria_id == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                        <option value="">— Sin proveedor —</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}" {{ $producto->proveedor_id == $p->id ? 'selected' : '' }}>{{ $p->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Unidad de Medida</label>
                    <select name="unidad_medida" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                        @foreach(['UND'=>'Unidad','KG'=>'Kilogramo','LT'=>'Litro','GR'=>'Gramo','ML'=>'Mililitro','CAJA'=>'Caja','PAQ'=>'Paquete'] as $k=>$v)
                            <option value="{{ $k }}" {{ $producto->unidad_medida == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" value="{{ $producto->ubicacion }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-dollar-sign text-emerald-500 mr-2"></i>Precios</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Compra</label>
                    <input type="number" step="0.01" name="precio_compra" value="{{ $producto->precio_compra }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Venta</label>
                    <input type="number" step="0.01" name="precio_venta" value="{{ $producto->precio_venta }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio Mayoreo</label>
                    <input type="number" step="0.01" name="precio_mayoreo" value="{{ $producto->precio_mayoreo }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Cant. Mín. Mayoreo</label>
                    <input type="number" name="cantidad_mayoreo" value="{{ $producto->cantidad_mayoreo }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-warehouse text-emerald-500 mr-2"></i>Inventario</h3>
            <p class="text-sm text-slate-300 mb-3">Stock actual: <strong>{{ number_format($producto->stock, 2) }}</strong> {{ $producto->unidad_medida }}. Para ajustar el stock use el botón "Ajustar Stock" en la vista del producto.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Mínimo</label>
                    <input type="number" step="0.001" name="stock_minimo" value="{{ $producto->stock_minimo }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Máximo</label>
                    <input type="number" step="0.001" name="stock_maximo" value="{{ $producto->stock_maximo }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Lote</label>
                    <input type="text" name="lote" value="{{ $producto->lote }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ $producto->fecha_vencimiento?->format('Y-m-d') }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4"><i class="fas fa-image text-emerald-500 mr-2"></i>Imagen</h3>
            @if($producto->imagen)
                <img src="{{ $producto->imagen_url }}" class="w-full aspect-square object-cover rounded-lg mb-3">
            @endif
            <input type="file" name="imagen" accept="image/*" class="block w-full text-sm">
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4">Opciones</h3>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="activo" value="1" {{ $producto->activo ? 'checked' : '' }} class="rounded text-emerald-500">
                <span class="text-sm text-slate-200">Activo</span>
            </label>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="controla_stock" value="1" {{ $producto->controla_stock ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Controla inventario</span>
            </label>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="aplica_impuesto" value="1" {{ $producto->aplica_impuesto ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Aplica impuesto</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="destacado" value="1" {{ $producto->destacado ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Destacado en POS</span>
            </label>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <button type="submit" class="w-full gradient-primary text-white py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Actualizar</button>
            <a href="{{ route('productos.index') }}" class="block text-center mt-2 py-3 text-slate-300 hover:bg-slate-900 rounded-lg">Cancelar</a>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function comboForm() {
        return {
            tipo_producto: '{{ $producto->tipo_producto }}',
            componentes: [
                @foreach($producto->componentesCombo as $comp)
                    { id: '{{ $comp->id }}', cantidad: {{ $comp->pivot->cantidad }} },
                @endforeach
            ],
            addComponent() {
                this.componentes.push({ id: '', cantidad: 1 });
            },
            removeComponent(index) {
                this.componentes.splice(index, 1);
            }
        }
    }
</script>
@endsection
