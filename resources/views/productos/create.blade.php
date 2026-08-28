@extends('layouts.app')
@section('title', 'Nuevo Producto')
@section('header', 'Nuevo Producto')

@section('content')
<form x-data="comboForm()" method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    @csrf

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
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo" value="{{ old('codigo', $codigo) }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código de barras</label>
                    <input type="text" name="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="Opcional" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Categoría</label>
                    <select name="categoria_id" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                        <option value="">— Sin categoría —</option>
                        @foreach($categorias as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Nombre del Producto / Combo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Pack Fiestero: 1 Whisky Red Label + 2 Coca Cola + 1 Hielo" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2" placeholder="Detalle de lo que incluye el pack..." class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">{{ old('descripcion') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                        <option value="">— Sin proveedor —</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id }}">{{ $p->razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Unidad de Medida</label>
                    <select name="unidad_medida" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                        <option value="UND">Unidad / Pack</option>
                        <option value="CAJA">Caja</option>
                        <option value="PAQ">Paquete</option>
                        <option value="LT">Litro</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" placeholder="Ej: Mostrador / Nevera Principal" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
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
                    <p class="text-xs text-slate-400">Al vender este combo en el POS, el sistema descontará automáticamente el stock de cada uno de estos productos.</p>
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
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-dollar-sign text-emerald-500 mr-2"></i>Precios de Venta</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Compra / Costo Total <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="precio_compra" x-model.number="precioCompra" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Venta al Público <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="precio_venta" value="{{ old('precio_venta', '0.00') }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-emerald-400 font-black rounded-xl focus:outline-none focus:border-emerald-500 text-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio Mayoreo (Opcional)</label>
                    <input type="number" step="0.01" name="precio_mayoreo" value="{{ old('precio_mayoreo', '0.00') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Cantidad Mínima Mayoreo</label>
                    <input type="number" name="cantidad_mayoreo" value="{{ old('cantidad_mayoreo', '0') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        <!-- Inventario (Visible solo para productos estándar) -->
        <div x-show="tipo_producto === 'estandar'" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-warehouse text-emerald-500 mr-2"></i>Inventario y Stock</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Inicial</label>
                    <input type="number" step="0.001" name="stock" value="{{ old('stock', '0') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Mínimo</label>
                    <input type="number" step="0.001" name="stock_minimo" value="{{ old('stock_minimo', '5') }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Máximo</label>
                    <input type="number" step="0.001" name="stock_maximo" value="{{ old('stock_maximo', '100') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Lote</label>
                    <input type="text" name="lote" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Barra Lateral -->
    <div class="space-y-5">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4"><i class="fas fa-image text-emerald-500 mr-2"></i>Foto del Producto / Combo</h3>
            <input type="file" name="imagen" accept="image/*" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-500/20 file:text-emerald-400 hover:file:bg-emerald-500/30">
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4">Opciones</h3>
            <div x-show="tipo_producto === 'estandar'">
                <label class="flex items-center gap-3 mb-3 cursor-pointer">
                    <input type="checkbox" name="controla_stock" value="1" checked class="rounded text-emerald-500">
                    <span class="text-sm text-slate-200">Controla inventario</span>
                </label>
            </div>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="aplica_impuesto" value="1" checked class="rounded text-emerald-500">
                <span class="text-sm text-slate-200">Aplica impuesto</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="destacado" value="1" class="rounded text-emerald-500">
                <span class="text-sm text-slate-200">Destacado en el POS</span>
            </label>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <button type="submit" class="w-full gradient-primary text-white py-3.5 rounded-xl font-bold hover:brightness-105 shadow-lg transition cursor-pointer">
                <i class="fas fa-save mr-2"></i>Guardar Producto
            </button>
            <a href="{{ route('productos.index') }}" class="block text-center mt-2 py-3 text-slate-400 hover:text-white rounded-lg">Cancelar</a>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function comboForm() {
        return {
            tipo_producto: 'estandar',
            productosDisponibles: @json($productosList),
            componentes: [],
            precioCompra: 0,
            costoTotalComponentes: 0,

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
                if (this.tipo_producto === 'combo' && (this.precioCompra === 0 || this.precioCompra === '0.00')) {
                    this.precioCompra = total.toFixed(2);
                }
            },
            aplicarCostoSugerido() {
                this.precioCompra = this.costoTotalComponentes.toFixed(2);
            }
        }
    }
</script>
@endsection
