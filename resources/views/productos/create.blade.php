@extends('layouts.app')
@section('title', 'Nuevo Producto')
@section('header', 'Nuevo Producto')

@section('content')
<form x-data="comboForm()" method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    @csrf

    <div class="lg:col-span-2 space-y-5">
        <!-- 1. Información General -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                <span><i class="fas fa-info-circle text-amber-500 mr-2"></i>Información General</span>
                <span x-show="tipo_producto === 'combo'" class="px-2.5 py-0.5 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                    <i class="fas fa-layer-group mr-1"></i> Modo Combo / Pack
                </span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-200 mb-1">Tipo de Producto <span class="text-amber-500">*</span></label>
                    <select name="tipo_producto" x-model="tipo_producto" class="w-full px-3 py-2.5 bg-slate-800 border-2 border-amber-500/40 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                        <option value="estandar">📦 Producto Estándar (Individual con Stock Propio)</option>
                        <option value="combo">🍸 Combo / Pack Promocional (Pack compuesto de varios productos)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código del Sistema <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo" value="{{ old('codigo', $codigo) }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Código de Barras (Escaner)</label>
                    <input type="text" name="codigo_barras" value="{{ old('codigo_barras') }}" placeholder="Escanear con lector o dejar vacío" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
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
                    <label class="block text-sm font-bold text-white mb-1">Nombre del Producto o Combo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Whisky Red Label 750ml o Pack Fiestero (1 Red Label + 2 Coca Cola)" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold text-base">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Descripción / Notas</label>
                    <textarea name="descripcion" rows="2" placeholder="Detalle de presentación, sabor o contenido..." class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">{{ old('descripcion') }}</textarea>
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
                        <option value="UND">Unidad / Botella</option>
                        <option value="CAJA">Caja</option>
                        <option value="PAQ">Pack / Paquete</option>
                        <option value="LT">Litro</option>
                        <option value="KG">Kilogramo</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. SECCIÓN COMPONENTES DEL COMBO (Visible cuando es Combo) -->
        <div x-show="tipo_producto === 'combo'" x-cloak class="bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-900 border-2 border-amber-500/50 rounded-2xl shadow-xl p-6 relative">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4 pb-3 border-b border-slate-800">
                <div>
                    <h3 class="font-bold text-white text-base sm:text-lg flex items-center gap-2">
                        <i class="fas fa-layer-group text-amber-400"></i>
                        <span>Armar Componentes del Combo / Pack</span>
                    </h3>
                    <p class="text-xs text-slate-400">Selecciona qué licores/insumos componen este pack y qué cantidad de cada uno se descontará del stock al venderlo.</p>
                </div>
                <button type="button" @click="addComponent()" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl text-xs shadow-lg transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-plus"></i> + Agregar Producto al Pack
                </button>
            </div>

            <!-- Lista de Componentes -->
            <div class="space-y-3">
                <template x-for="(comp, index) in componentes" :key="index">
                    <div class="p-3.5 bg-slate-800 border border-slate-700 rounded-xl flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shadow-sm">
                        
                        <!-- Selector del Producto -->
                        <div class="flex-1">
                            <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Producto a Incluir</label>
                            <select :name="`componente_id[${index}]`" x-model="comp.id" @change="recalcularCosto()" required class="w-full px-3 py-2 bg-slate-900 border border-slate-600 text-white rounded-xl text-xs sm:text-sm font-bold focus:border-amber-500 outline-none">
                                <option value="">— Seleccionar Producto de la Tienda —</option>
                                <template x-for="p in productosDisponibles" :key="p.id">
                                    <option :value="p.id" x-text="`${p.nombre} (Stock actual: ${p.stock} unids) - Costo: S/ ${parseFloat(p.precio_compra).toFixed(2)}`"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Stepper de Cantidad -->
                        <div class="w-full sm:w-44">
                            <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Cantidad en el Pack</label>
                            <div class="flex items-center bg-slate-900 border border-slate-600 rounded-xl p-0.5">
                                <button type="button" @click="if(comp.cantidad > 1) { comp.cantidad--; recalcularCosto(); }" class="px-2.5 py-1 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg text-xs font-bold transition">-</button>
                                <input type="number" :name="`componente_cantidad[${index}]`" x-model.number="comp.cantidad" @input="recalcularCosto()" min="1" step="1" required class="w-full text-center bg-transparent text-amber-400 font-black text-sm outline-none">
                                <button type="button" @click="comp.cantidad++; recalcularCosto();" class="px-2.5 py-1 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg text-xs font-bold transition">+</button>
                            </div>
                        </div>

                        <!-- Botón Eliminar -->
                        <div class="flex items-center justify-end sm:pt-4">
                            <button type="button" @click="removeComponent(index)" title="Quitar este producto del pack" class="p-2 text-slate-400 hover:text-rose-400 rounded-xl hover:bg-rose-500/20 transition cursor-pointer">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="componentes.length === 0" class="text-center py-8 border-2 border-dashed border-slate-800 rounded-2xl text-slate-500 text-xs">
                    <i class="fas fa-wine-bottle text-3xl mb-2 block text-slate-600"></i>
                    Aún no has agregado productos a este combo. Presiona <strong>"+ Agregar Producto al Pack"</strong> para empezar a armarlo.
                </div>
            </div>

            <!-- Resumen Informativo del Pack -->
            <div x-show="componentes.length > 0" class="mt-4 pt-3 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 text-xs text-slate-300">
                    <div>
                        <span>Costo Total de los Componentes: </span>
                        <strong class="text-amber-400 text-sm font-mono font-bold" x-text="`S/ ${costoTotalComponentes.toFixed(2)}`"></strong>
                    </div>
                    <div class="px-2.5 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 font-bold text-xs">
                        <i class="fas fa-boxes mr-1"></i> Puedes armar hasta: <span class="text-white text-sm" x-text="`${combosDisponibles} packs`"></span>
                    </div>
                </div>
                <button type="button" @click="aplicarCostoSugerido()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-xl text-xs font-bold border border-amber-500/30 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fas fa-magic"></i> Copiar a Precio de Compra
                </button>
            </div>
        </div>

        <!-- 3. Precios de Venta -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-dollar-sign text-emerald-500 mr-2"></i>Precios y Ganancia</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio de Compra / Costo Base <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">S/</span>
                        <input type="number" step="0.01" name="precio_compra" x-model.number="precioCompra" required class="w-full pl-9 pr-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500 font-bold">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-emerald-400 mb-1">Precio de Venta al Público <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 font-bold">S/</span>
                        <input type="number" step="0.01" name="precio_venta" value="{{ old('precio_venta', '0.00') }}" required class="w-full pl-9 pr-3 py-2.5 bg-slate-800 border-2 border-emerald-500/50 text-emerald-400 font-black rounded-xl focus:outline-none focus:border-emerald-500 text-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Precio Mayoreo (Opcional)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">S/</span>
                        <input type="number" step="0.01" name="precio_mayoreo" value="{{ old('precio_mayoreo', '0.00') }}" class="w-full pl-9 pr-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-200 mb-1">Cantidad Mínima Mayoreo</label>
                    <input type="number" name="cantidad_mayoreo" value="{{ old('cantidad_mayoreo', '0') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        <!-- 4. CONTROL DE STOCK E INVENTARIO (SIEMPRE VISIBLE) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                <span><i class="fas fa-warehouse text-emerald-500 mr-2"></i>Control de Stock e Inventario</span>
                <span x-show="tipo_producto === 'combo'" class="text-xs text-amber-400 font-semibold">Descuento dinámico por componentes</span>
            </h3>

            <!-- Si es Producto Estándar: Permite ingresar el Stock Inicial -->
            <div x-show="tipo_producto === 'estandar'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-slate-800/80 p-3.5 rounded-xl border border-slate-700">
                        <label class="block text-xs font-black uppercase tracking-wider text-emerald-400 mb-1.5">
                            Stock Inicial / Cantidad en Tienda <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="1" name="stock" value="{{ old('stock', '0') }}" required class="w-full px-3.5 py-2.5 bg-slate-900 border-2 border-emerald-500/60 text-emerald-400 font-black text-xl text-center rounded-xl focus:outline-none focus:border-emerald-400">
                        <span class="text-[11px] text-slate-400 block mt-1 text-center">Unidades físicas disponibles</span>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Mínimo (Alerta)</label>
                        <input type="number" step="1" name="stock_minimo" value="{{ old('stock_minimo', '5') }}" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1">Stock Máximo</label>
                        <input type="number" step="1" name="stock_maximo" value="{{ old('stock_maximo', '100') }}" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1">Lote (Opcional)</label>
                        <input type="text" name="lote" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-200 mb-1">Fecha de Vencimiento (Opcional)</label>
                        <input type="date" name="fecha_vencimiento" class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl focus:outline-none focus:border-amber-500">
                    </div>
                </div>
            </div>

            <!-- Si es Combo: Muestra explicación y stock disponible según ingredientes -->
            <div x-show="tipo_producto === 'combo'" class="bg-amber-950/20 border border-amber-500/30 p-4 rounded-xl space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg font-bold">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Stock Dinámico Automatizado</h4>
                        <p class="text-xs text-slate-300">
                            El stock de los combos no se escribe a mano porque se calcula en tiempo real a partir del stock de cada uno de sus productos individuales.
                        </p>
                    </div>
                </div>
                <div class="pt-2 flex justify-between items-center border-t border-amber-500/20 text-xs">
                    <span class="text-slate-400">Disponibilidad para vender en POS:</span>
                    <span class="font-bold text-emerald-400 text-sm font-mono" x-text="`${combosDisponibles} combos disponibles`"></span>
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
            <h3 class="font-bold text-slate-100 mb-4">Opciones de Publicación</h3>
            <div x-show="tipo_producto === 'estandar'">
                <label class="flex items-center gap-3 mb-3 cursor-pointer">
                    <input type="checkbox" name="controla_stock" value="1" checked class="rounded text-emerald-500">
                    <span class="text-sm text-slate-200">Controla inventario y alertas</span>
                </label>
            </div>
            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                <input type="checkbox" name="aplica_impuesto" value="1" checked class="rounded text-emerald-500">
                <span class="text-sm text-slate-200">Aplica impuesto</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="destacado" value="1" class="rounded text-emerald-500">
                <span class="text-sm text-slate-200">Destacado en pantalla rápida del POS</span>
            </label>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <button type="submit" class="w-full gradient-primary text-white py-3.5 rounded-xl font-bold hover:brightness-105 shadow-lg transition cursor-pointer text-base">
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
            combosDisponibles: 0,

            addComponent() {
                this.componentes.push({ id: '', cantidad: 1 });
            },
            removeComponent(index) {
                this.componentes.splice(index, 1);
                this.recalcularCosto();
            },
            recalcularCosto() {
                let totalCosto = 0;
                let maxCombos = 999999;

                this.componentes.forEach(c => {
                    if (c.id) {
                        const prod = this.productosDisponibles.find(p => p.id == c.id);
                        if (prod) {
                            const cant = parseInt(c.cantidad) || 1;
                            totalCosto += (parseFloat(prod.precio_compra) || 0) * cant;
                            
                            const stockProd = parseFloat(prod.stock) || 0;
                            const posibleConEste = Math.floor(stockProd / cant);
                            if (posibleConEste < maxCombos) {
                                maxCombos = posibleConEste;
                            }
                        }
                    }
                });

                this.costoTotalComponentes = totalCosto;
                this.combosDisponibles = (this.componentes.length > 0 && maxCombos !== 999999) ? Math.max(0, maxCombos) : 0;

                if (this.tipo_producto === 'combo' && (this.precioCompra === 0 || this.precioCompra === '0.00')) {
                    this.precioCompra = totalCosto.toFixed(2);
                }
            },
            aplicarCostoSugerido() {
                this.precioCompra = this.costoTotalComponentes.toFixed(2);
            }
        }
    }
</script>
@endsection
