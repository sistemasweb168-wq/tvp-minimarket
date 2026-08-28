@extends('layouts.app')
@section('title', 'Editar Producto')
@section('header', 'Editar: ' . $producto->nombre)

@section('content')
<form x-data="comboForm()" method="POST" action="{{ route('productos.update', $producto->id) }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    @csrf @method('PUT')

    <div class="lg:col-span-2 space-y-5">
        <!-- Información General -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                <span><i class="fas fa-info-circle text-amber-500 mr-2"></i>Información General</span>
                <span x-show="tipo_producto === 'combo'" class="px-2.5 py-0.5 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    <i class="fas fa-layer-group mr-1"></i> Modo Combo / Pack
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Tipo de Producto <span class="text-amber-500">*</span></label>
                    <select name="tipo_producto" x-model="tipo_producto" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                        <option value="estandar">📦 Producto Estándar (Individual)</option>
                        <option value="combo">🍸 Combo / Pack Promocional (Varios productos juntos)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $producto->codigo) }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código de barras</label>
                    <input type="text" name="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Categoría</label>
                    <select name="categoria_id" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                        <option value="">— Sin categoría —</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}" {{ $producto->categoria_id == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                        <option value="">— Sin proveedor —</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}" {{ $producto->proveedor_id == $p->id ? 'selected' : '' }}>{{ $p->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Unidad de Medida</label>
                    <select name="unidad_medida" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                        @foreach(['UND'=>'Unidad / Pack','CAJA'=>'Caja','PAQ'=>'Paquete','LT'=>'Litro','KG'=>'Kilogramo','GR'=>'Gramo','ML'=>'Mililitro'] as $k=>$v)
                            <option value="{{ $k }}" {{ $producto->unidad_medida == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" value="{{ $producto->ubicacion }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        <!-- SECCIÓN COMPONENTES DEL COMBO (Visible solo si es Combo) -->
        <div x-show="tipo_producto === 'combo'" x-cloak class="bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-900 border-2 border-amber-500/50 rounded-2xl shadow-xl p-6 relative">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4 pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-bold text-white text-base sm:text-lg flex items-center gap-2">
                        <i class="fas fa-layer-group text-amber-400"></i>
                        <span>Productos que integran este Combo / Pack</span>
                    </h3>
                    <p class="text-xs text-slate-400">Al vender este combo en el POS, se descontará automáticamente el stock de estos productos.</p>
                </div>
                <button type="button" @click="addComponent()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs shadow transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>

            <!-- Tabla de Componentes -->
            <div class="space-y-3">
                <template x-for="(comp, index) in componentes" :key="index">
                    <div class="p-3 bg-slate-800/90 border border-slate-700 rounded-xl flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Seleccionar Producto</label>
                            <select :name="`componente_id[${index}]`" x-model="comp.id" @change="recalcularCosto()" required class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-white rounded-lg text-xs font-semibold focus:border-amber-500 outline-none">
                                <option value="">— Elegir Producto —</option>
                                <template x-for="p in productosDisponibles" :key="p.id">
                                    <option :value="p.id" x-text="`${p.nombre} (Stock: ${p.stock}) - Costo: S/ ${parseFloat(p.precio_compra).toFixed(2)}`"></option>
                                </template>
                            </select>
                        </div>
                        <div class="w-full sm:w-28">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cantidad</label>
                            <input type="number" :name="`componente_cantidad[${index}]`" x-model.number="comp.cantidad" @input="recalcularCosto()" min="1" step="1" required class="w-full px-3 py-2 bg-slate-900 border border-slate-700 text-amber-400 font-black text-center rounded-lg text-xs focus:border-amber-500 outline-none">
                        </div>
                        <div class="flex items-center justify-end sm:pt-4">
                            <button type="button" @click="removeComponent(index)" title="Quitar del combo" class="p-2 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/20 transition cursor-pointer">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="componentes.length === 0" class="text-center py-8 border-2 border-dashed border-slate-800 rounded-xl text-slate-500 text-xs">
                    <i class="fas fa-wine-bottle text-2xl mb-2 block text-slate-600"></i>
                    Aún no has agregado productos a este combo. Haz clic en <strong>"+ Agregar Producto"</strong> para armar el pack.
                </div>
            </div>

            <!-- Resumen de Costo de los componentes -->
            <div x-show="componentes.length > 0" class="mt-4 pt-3 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-xs text-slate-300">
                    <span>Costo total acumulado de los productos: </span>
                    <strong class="text-amber-400 text-sm font-mono" x-text="`S/ ${costoTotalComponentes.toFixed(2)}`"></strong>
                </div>
                <button type="button" @click="aplicarCostoSugerido()" class="text-xs text-amber-400 hover:underline font-bold">
                    <i class="fas fa-magic mr-1"></i> Copiar a Precio de Compra
                </button>
            </div>
        </div>

        <!-- Precios -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-dollar-sign text-emerald-500 mr-2"></i>Precios</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Compra / Costo Total</label>
                    <input type="number" step="0.01" name="precio_compra" x-model.number="precioCompra" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Venta</label>
                    <input type="number" step="0.01" name="precio_venta" value="{{ $producto->precio_venta }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-emerald-400 font-black rounded-xl focus:outline-none focus:border-emerald-500 text-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio Mayoreo</label>
                    <input type="number" step="0.01" name="precio_mayoreo" value="{{ $producto->precio_mayoreo }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Cant. Mín. Mayoreo</label>
                    <input type="number" name="cantidad_mayoreo" value="{{ $producto->cantidad_mayoreo }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        <!-- Inventario (Visible solo para productos estándar) -->
        <div x-show="tipo_producto === 'estandar'" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-warehouse text-emerald-500 mr-2"></i>Inventario</h3>
            <p class="text-sm text-slate-300 mb-3">Stock actual: <strong>{{ number_format($producto->stock, 2) }}</strong> {{ $producto->unidad_medida }}. Para ajustar el stock use el botón "Ajustar Stock" en la vista del producto.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Mínimo</label>
                    <input type="number" step="0.001" name="stock_minimo" value="{{ $producto->stock_minimo }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Máximo</label>
                    <input type="number" step="0.001" name="stock_maximo" value="{{ $producto->stock_maximo }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Lote</label>
                    <input type="text" name="lote" value="{{ $producto->lote }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ $producto->fecha_vencimiento?->format('Y-m-d') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Barra Lateral -->
    <div class="space-y-5">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4"><i class="fas fa-image text-emerald-500 mr-2"></i>Foto del Producto</h3>
            @if($producto->imagen)
                <img src="{{ $producto->imagen_url }}" class="w-full aspect-square object-cover rounded-xl mb-3 border border-slate-700">
            @endif
            <input type="file" name="imagen" accept="image/*" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-400 hover:file:bg-emerald-500/30">
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4">Opciones</h3>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="activo" value="1" {{ $producto->activo ? 'checked' : '' }} class="rounded text-emerald-500">
                <span class="text-sm text-slate-200">Activo en catálogo</span>
            </label>
            <div x-show="tipo_producto === 'estandar'">
                <label class="flex items-center gap-3 mb-3 cursor-pointer">
                    <input type="checkbox" name="controla_stock" value="1" {{ $producto->controla_stock ? 'checked' : '' }} class="rounded">
                    <span class="text-sm text-slate-200">Controla inventario</span>
                </label>
            </div>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="aplica_impuesto" value="1" {{ $producto->aplica_impuesto ? 'checked' : '' }} class="rounded">
                <span class="text-sm text-slate-200">Aplica impuesto</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="destacado" value="1" {{ $producto->destacado ? 'checked' : '' }} class="rounded">
                <span class="text-sm text-slate-200">Destacado en POS</span>
            </label>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <button type="submit" class="w-full gradient-primary text-white py-3.5 rounded-xl font-bold hover:brightness-105 shadow-lg transition cursor-pointer">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
            <a href="{{ route('productos.index') }}" class="block text-center mt-2 py-3 text-slate-400 hover:text-white rounded-lg">Cancelar</a>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function comboForm() {
        const compsInit = @json($producto->componentesCombo->map(fn($c) => ['id' => $c->id, 'cantidad' => $c->pivot->cantidad]));
        return {
            tipo_producto: '{{ $producto->tipo_producto }}',
            productosDisponibles: @json($productosList),
            componentes: compsInit.length > 0 ? compsInit : [],
            precioCompra: {{ $producto->precio_compra }},
            costoTotalComponentes: 0,

            init() {
                this.recalcularCosto();
            },
            addComponent() {
                this.componentes.push({ id: '', cantidad: 1 });
            },
            removeComponent(index) {
                this.componentes.splice(index, 1);
                this.recalcularCosto();
            },
            recalcularCosto() {
                let total = 0;
                this.componentes.forEach(c => {
                    if (c.id) {
                        const prod = this.productosDisponibles.find(p => p.id == c.id);
                        if (prod) {
                            total += (parseFloat(prod.precio_compra) || 0) * (parseInt(c.cantidad) || 1);
                        }
                    }
                });
                this.costoTotalComponentes = total;
            },
            aplicarCostoSugerido() {
                this.precioCompra = this.costoTotalComponentes.toFixed(2);
            }
        }
    }
</script>
@endsection
