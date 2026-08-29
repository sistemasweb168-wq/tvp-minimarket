@extends('layouts.app')
@section('title', 'Recepción Express')
@section('header', 'Recepción Express de Mercadería')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<form method="POST" action="{{ route('compras.store') }}" x-data="compraExpress()" @submit="prepararSubmit">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <!-- PANTALLA TIPO POS PARA RECEPCIÓN RÁPIDA -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6 relative">
                <h3 class="font-bold mb-4 flex items-center gap-2 text-emerald-400">
                    <i class="fas fa-barcode"></i> Escáner de Ingreso Rápido
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4">
                    <!-- Escáner / Búsqueda -->
                    <div class="md:col-span-12 relative">
                        <label class="block text-xs font-semibold mb-1 text-slate-400">Código de barras o Nombre (Enter)</label>
                        <div class="flex relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" x-model="busqueda" x-ref="busquedaInput" 
                                   @keydown.enter.prevent="buscarProducto()" 
                                   @input.debounce.300ms="buscarProducto()"
                                   placeholder="Escanea el código aquí..." 
                                   class="w-full pl-10 pr-3 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white font-bold text-lg focus:border-emerald-500 outline-none transition">
                        </div>

                        <!-- Resultados de búsqueda rápidos -->
                        <div x-show="resultados.length > 0" @click.outside="resultados = []" class="absolute z-50 w-full mt-1 bg-white rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto">
                            <template x-for="p in resultados" :key="p.id">
                                <button type="button" @click="seleccionarProducto(p)" class="w-full text-left px-4 py-3 hover:bg-slate-100 border-b border-slate-100 transition flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm" x-text="p.nombre"></p>
                                        <p class="text-xs text-slate-500" x-text="p.codigo_barras || p.codigo"></p>
                                    </div>
                                    <span class="text-emerald-600 font-bold text-sm" x-text="`Costo: ${p.precio_compra}`"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Fila de Datos Rápidos -->
                    <div class="md:col-span-12 grid grid-cols-1 md:grid-cols-12 gap-3 p-4 bg-slate-800/50 rounded-xl border border-slate-700" x-show="productoSeleccionado">
                        <div class="md:col-span-12 mb-1 flex justify-between items-center">
                            <span class="font-bold text-emerald-400" x-text="productoSeleccionado ? productoSeleccionado.nombre : ''"></span>
                            <button type="button" @click="limpiarSeleccion()" class="text-slate-400 hover:text-rose-400"><i class="fas fa-times"></i> Cancelar</button>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold mb-1 text-slate-400">Cantidad</label>
                            <input type="number" step="0.001" x-model.number="nuevoItem.cantidad" x-ref="cantidadInput" @keydown.enter.prevent="$refs.fechaInput.focus()"
                                   class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-center font-bold">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold mb-1 text-slate-400">Costo Unit.</label>
                            <input type="number" step="0.01" x-model.number="nuevoItem.precio_unitario" @keydown.enter.prevent="$refs.fechaInput.focus()"
                                   class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-center">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold mb-1 text-slate-400">Vencimiento (Opcional)</label>
                            <input type="date" x-model="nuevoItem.fecha_vencimiento" x-ref="fechaInput" @keydown.enter.prevent="$refs.loteInput.focus()"
                                   class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold mb-1 text-slate-400">Lote (Auto)</label>
                            <input type="text" x-model="nuevoItem.lote" x-ref="loteInput" @keydown.enter.prevent="agregarItem()" placeholder="Automático"
                                   class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white placeholder-slate-500 uppercase">
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button type="button" @click="agregarItem()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded-lg transition shadow-lg shadow-emerald-500/20">
                                <i class="fas fa-check mr-1"></i> (Enter)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs uppercase text-slate-400 border-b border-slate-700">
                            <tr>
                                <th class="text-left py-2">Producto</th>
                                <th class="text-center py-2">Lote/Venc.</th>
                                <th class="text-right py-2">Cant.</th>
                                <th class="text-right py-2">Costo</th>
                                <th class="text-right py-2">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr class="border-b border-slate-800/50 hover:bg-slate-800/20 transition">
                                    <td class="py-2">
                                        <p class="font-bold text-white" x-text="item.nombre"></p>
                                    </td>
                                    <td class="py-2 text-center text-xs">
                                        <span class="bg-slate-800 px-2 py-0.5 rounded text-slate-300" x-text="item.lote || 'AUTO'"></span><br>
                                        <span class="text-emerald-400" x-text="item.fecha_vencimiento || 'Sin Venc.'"></span>
                                    </td>
                                    <td class="py-2 text-right font-bold text-white" x-text="item.cantidad"></td>
                                    <td class="py-2 text-right text-slate-300" x-text="`{{ $moneda }} ${item.precio_unitario.toFixed(2)}`"></td>
                                    <td class="py-2 text-right font-semibold text-amber-400" x-text="`{{ $moneda }} ${(item.cantidad * item.precio_unitario).toFixed(2)}`"></td>
                                    <td class="py-2 text-right">
                                        <button type="button" @click="items.splice(idx,1)" class="text-slate-500 hover:text-rose-500 transition p-1"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="6" class="text-center py-8 text-slate-500"><i class="fas fa-box-open text-3xl mb-2 block"></i>Escanea productos para agregarlos a la recepción</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <!-- Datos del Proveedor y Factura -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
                <h3 class="font-bold mb-4 flex items-center gap-2"><i class="fas fa-file-invoice"></i> Documento de Ingreso</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-slate-400">Proveedor *</label>
                        <select name="proveedor_id" required class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white">
                            <option value="">— Seleccionar —</option>
                            @foreach($proveedores as $p)<option value="{{ $p->id }}">{{ $p->razon_social }}</option>@endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1 text-slate-400">N° Factura</label>
                            <input type="text" name="numero_factura" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1 text-slate-400">Fecha Doc.</label>
                            <input type="date" name="fecha_compra" value="{{ now()->toDateString() }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-slate-400">Forma de pago</label>
                        <select name="forma_pago" class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Total y Guardar -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6 sticky top-20">
                <div class="space-y-2 text-sm pb-4 border-b border-slate-700">
                    <div class="flex justify-between"><span class="text-slate-400">Total Items:</span><span class="font-bold text-white" x-text="items.length"></span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Unidades:</span><span class="font-bold text-white" x-text="items.reduce((s,i) => s + parseFloat(i.cantidad), 0).toFixed(2)"></span></div>
                </div>
                <div class="flex justify-between items-baseline pt-4 mb-4">
                    <span class="text-slate-400 font-bold">TOTAL:</span>
                    <span class="text-3xl font-black text-emerald-400" x-text="`{{ $moneda }} ${total.toFixed(2)}`"></span>
                </div>
                <textarea name="observaciones" placeholder="Observaciones del ingreso..." rows="2" class="w-full mb-3 px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-xs text-white"></textarea>
                
                <button type="submit" :disabled="items.length === 0" class="w-full gradient-primary text-white py-3.5 rounded-xl font-extrabold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-save mr-2"></i> Procesar Recepción
                </button>
                
                <!-- Inputs Ocultos para Laravel -->
                <template x-for="(item, idx) in items" :key="idx">
                    <div>
                        <input type="hidden" :name="`items[${idx}][producto_id]`" :value="item.producto_id">
                        <input type="hidden" :name="`items[${idx}][cantidad]`" :value="item.cantidad">
                        <input type="hidden" :name="`items[${idx}][precio_unitario]`" :value="item.precio_unitario">
                        <input type="hidden" :name="`items[${idx}][fecha_vencimiento]`" :value="item.fecha_vencimiento">
                        <input type="hidden" :name="`items[${idx}][lote]`" :value="item.lote">
                    </div>
                </template>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
function compraExpress() {
    return {
        busqueda: '',
        resultados: [],
        productoSeleccionado: null,
        items: [],
        nuevoItem: { cantidad: 1, precio_unitario: 0, fecha_vencimiento: '', lote: '' },
        
        get total() { return this.items.reduce((s, i) => s + i.cantidad * i.precio_unitario, 0); },
        
        async buscarProducto() {
            if (!this.busqueda || this.busqueda.length < 2) {
                this.resultados = [];
                return;
            }
            try {
                const res = await fetch(`/api/productos/buscar?q=${encodeURIComponent(this.busqueda)}`);
                const data = await res.json();
                
                if (data.length === 1 && this.busqueda === data[0].codigo_barras) {
                    // Si el scanner lee el código exacto, seleccionarlo automáticamente
                    this.seleccionarProducto(data[0]);
                    this.resultados = [];
                } else {
                    this.resultados = data;
                }
            } catch(e) { console.error(e); }
        },

        seleccionarProducto(p) {
            this.productoSeleccionado = p;
            this.nuevoItem = { 
                cantidad: 1, 
                precio_unitario: p.precio_compra || 0,
                fecha_vencimiento: '',
                lote: ''
            };
            this.busqueda = '';
            this.resultados = [];
            
            // Foco automático a cantidad para escribir rápido
            setTimeout(() => this.$refs.cantidadInput.select(), 100);
        },

        limpiarSeleccion() {
            this.productoSeleccionado = null;
            setTimeout(() => this.$refs.busquedaInput.focus(), 100);
        },

        agregarItem() {
            if (!this.productoSeleccionado || this.nuevoItem.cantidad <= 0) {
                return Toast.fire({ icon: 'warning', title: 'Complete la cantidad' });
            }
            
            let loteFinal = this.nuevoItem.lote.trim();
            if (loteFinal === '' && this.nuevoItem.fecha_vencimiento) {
                // Autogenerar lote si hay fecha pero no lote
                const d = new Date(this.nuevoItem.fecha_vencimiento);
                loteFinal = `LOT-${d.getFullYear()}${String(d.getMonth()+1).padStart(2,'0')}`;
            }

            this.items.unshift({
                producto_id: this.productoSeleccionado.id,
                nombre: this.productoSeleccionado.nombre,
                cantidad: parseFloat(this.nuevoItem.cantidad),
                precio_unitario: parseFloat(this.nuevoItem.precio_unitario),
                fecha_vencimiento: this.nuevoItem.fecha_vencimiento,
                lote: loteFinal
            });
            
            this.limpiarSeleccion();
            AudioPOS?.success();
            Toast.fire({ icon: 'success', title: 'Agregado. Siguiente producto...' });
        },

        prepararSubmit(e) {
            if (this.items.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Lista vacía',
                    text: 'Agregue al menos un producto escaneándolo.',
                    icon: 'warning',
                    confirmButtonColor: '#10b981'
                });
            }
        }
    }
}
</script>
@endsection
@endsection
