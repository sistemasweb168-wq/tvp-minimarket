import re

with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# 1. Add "Gasto Rápido" button to top bar next to sound button
old_sound_btn = '<button type="button" @click="toggleSonido()"'
new_gasto_and_sound = """<!-- Botón Registrar Gasto Rápido -->
                    <button type="button" @click="modalGasto = true" title="Registrar Salida de Dinero / Gasto de Caja"
                            class="px-3 py-2.5 sm:py-3 bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 rounded-xl transition text-xs sm:text-sm font-bold flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                        <i class="fas fa-receipt text-rose-400"></i>
                        <span class="hidden md:inline">Gasto</span>
                    </button>
                    
                    <button type="button" @click="toggleSonido()\""""

s = s.replace(old_sound_btn, new_gasto_and_sound, 1)

# 2. Add 'Mixto' to the payment buttons in the modal
old_payment_grid = """<div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="formaPago = 'efectivo'\""""

new_payment_grid = """<div class="grid grid-cols-5 gap-1.5">
                            <button type="button" @click="formaPago = 'efectivo'"
                                    :class="formaPago === 'efectivo' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-money-bill-wave text-xs sm:text-sm"></i>
                                <span class="text-[9px] sm:text-xs">Efectivo</span>
                            </button>
                            <button type="button" @click="formaPago = 'yape'"
                                    :class="formaPago === 'yape' ? 'bg-purple-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-mobile-alt text-xs sm:text-sm"></i>
                                <span class="text-[9px] sm:text-xs">Yape/Plin</span>
                            </button>
                            <button type="button" @click="formaPago = 'tarjeta'"
                                    :class="formaPago === 'tarjeta' ? 'bg-blue-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-credit-card text-xs sm:text-sm"></i>
                                <span class="text-[9px] sm:text-xs">Tarjeta</span>
                            </button>
                            <button type="button" @click="formaPago = 'transferencia'"
                                    :class="formaPago === 'transferencia' ? 'bg-slate-800 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-university text-xs sm:text-sm"></i>
                                <span class="text-[9px] sm:text-xs">Transf.</span>
                            </button>
                            <button type="button" @click="formaPago = 'mixto'; initPagoMixto()"
                                    :class="formaPago === 'mixto' ? 'bg-amber-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-layer-group text-xs sm:text-sm"></i>
                                <span class="text-[9px] sm:text-xs">Mixto</span>
                            </button>
                        </div>"""

s = re.sub(r'<div class="grid grid-cols-4 gap-1.5">.*?</div>\s*</div>\s*<!-- Detalle de Pago en Efectivo', new_payment_grid + '\n                    </div>\n\n                    <!-- Detalle de Pago en Efectivo', s, flags=re.DOTALL)

# 3. Add UI box for "Pago Mixto"
mixto_ui = """
                    <!-- Detalle Pago Mixto / Dividido -->
                    <div x-show="formaPago === 'mixto'" class="bg-amber-50 border border-amber-200 p-2.5 sm:p-3 rounded-2xl space-y-2 flex-1">
                        <div class="flex items-center gap-1.5 text-amber-900 font-extrabold text-[11px] uppercase pb-1 border-b border-amber-200/60">
                            <i class="fas fa-layer-group text-amber-600"></i>
                            <span>Desglose de Pago Dividido</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 uppercase">1. Monto Efectivo</label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">S/</span>
                                    <input type="number" step="0.50" min="0" x-model.number="montoEfectivo" @input="calcMixtoDigital()"
                                           class="w-full pl-7 pr-2 py-1.5 bg-white border border-slate-300 rounded-xl text-sm font-black text-slate-800 outline-none focus:border-amber-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 uppercase">2. Monto Digital</label>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">S/</span>
                                    <input type="number" step="0.50" min="0" x-model.number="montoDigital" @input="calcMixtoEfectivo()"
                                           class="w-full pl-7 pr-2 py-1.5 bg-white border border-slate-300 rounded-xl text-sm font-black text-purple-700 outline-none focus:border-purple-500">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 uppercase">Canal Digital</label>
                                <select x-model="metodoDigital" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-amber-500">
                                    <option value="yape">Yape</option>
                                    <option value="plin">Plin</option>
                                    <option value="tarjeta">Tarjeta (POS/Izipay)</option>
                                    <option value="transferencia">Transferencia BCP/BBVA</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 uppercase">N° Operación (Opcional)</label>
                                <input type="text" x-model="referenciaDigital" placeholder="Ej. 849201"
                                       class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 outline-none focus:border-amber-500">
                            </div>
                        </div>

                        <div class="flex justify-between items-center bg-white/80 p-2 rounded-xl border border-amber-200 text-xs">
                            <span class="font-bold text-slate-600">Suma Total Pagada:</span>
                            <span class="font-black text-sm" :class="(montoEfectivo + montoDigital) >= total ? 'text-emerald-600' : 'text-rose-600'" x-text="`S/ ${(montoEfectivo + montoDigital).toFixed(2)}`"></span>
                        </div>
                    </div>
"""

# Replace in payment method details
s = s.replace(
    '<!-- Detalle Referencia para Medios Digitales -->\n                    <div x-show="formaPago !== \'efectivo\'"',
    mixto_ui + '\n                    <!-- Detalle Referencia para Medios Digitales -->\n                    <div x-show="formaPago !== \'efectivo\' && formaPago !== \'mixto\'"'
)

# 4. Add modal for Gastos de Caja right before @endsection
modal_gasto_html = """
    <!-- ============================================================== -->
    <!-- 💸 MODAL REGISTRO DE GASTOS / EGRESOS DE CAJA EN POS           -->
    <!-- ============================================================== -->
    <div x-show="modalGasto" x-cloak class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-md p-6 shadow-2xl" @click.outside="modalGasto = false">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fas fa-receipt text-rose-500"></i>
                    <span>Registrar Salida de Dinero / Gasto</span>
                </h3>
                <button type="button" @click="modalGasto = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('caja.movimiento', $turnoActivo->id) }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="tipo" value="egreso">
                
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Concepto del Gasto</label>
                    <input type="text" name="concepto" required placeholder="Ej. Compra de 2 bolsas de hielo, limones..." class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-rose-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Categoría</label>
                        <select name="categoria" required class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs focus:border-rose-500 outline-none">
                            <option value="insumos">Insumos (Hielo/Limón)</option>
                            <option value="servicios">Servicios (Luz/Agua/Net)</option>
                            <option value="personal">Personal / Almuerzo</option>
                            <option value="proveedores">Pago a Proveedor Menor</option>
                            <option value="delivery">Gastos de Envío/Flete</option>
                            <option value="otros">Otros Gastos Varios</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Monto en Soles</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">S/</span>
                            <input type="number" name="monto" step="0.10" min="0.10" required placeholder="0.00" class="w-full pl-8 pr-3 py-2.5 bg-slate-800 border border-slate-700 text-rose-400 font-black rounded-xl text-sm focus:border-rose-500 outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">N° Boleta / Recibo (Opcional)</label>
                    <input type="text" name="comprobante" placeholder="Ej. B001-492 o Sin comprobante" class="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs focus:border-rose-500 outline-none">
                </div>

                <div class="flex gap-2.5 pt-3 border-t border-slate-800">
                    <button type="button" @click="modalGasto = false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-rose-600/30 transition">Registrar Egreso</button>
                </div>
            </form>
        </div>
    </div>
"""

# Insert modal before </script> or closing div
s = s.replace('</div>\n@endsection', modal_gasto_html + '\n</div>\n@endsection')

# 5. Add Alpine JS variables and methods for Multi-pago and Gasto modal in pos()
js_vars = """        // Multi-pago y Gastos
        modalGasto: false,
        montoEfectivo: 0,
        montoDigital: 0,
        metodoDigital: 'yape',
        referenciaDigital: '',
        
        initPagoMixto() {
            this.montoEfectivo = Math.floor(this.total / 2);
            this.montoDigital = Math.round((this.total - this.montoEfectivo) * 100) / 100;
        },
        calcMixtoDigital() {
            this.montoDigital = Math.max(0, Math.round((this.total - this.montoEfectivo) * 100) / 100);
        },
        calcMixtoEfectivo() {
            this.montoEfectivo = Math.max(0, Math.round((this.total - this.montoDigital) * 100) / 100);
        },
"""

s = s.replace("formaPago: 'efectivo',", "formaPago: 'efectivo',\n" + js_vars)

# 6. Update payload in procesarVenta()
old_payload = """            const payload = {
                cliente_id: this.clienteId || null,
                cliente_documento: this.clienteDocumento || null,
                cliente_nombre: this.clienteNombre || null,
                cliente_direccion: this.clienteDireccion || null,
                forma_pago: this.formaPago,
                referencia_pago: this.referenciaPago || null,
                monto_recibido: this.formaPago === 'efectivo' ? this.montoRecibido : this.total,
                descuento: this.descuento,
                tipo_comprobante: this.tipoComprobante,
                items: this.carrito.map(i => ({
                    producto_id: i.producto_id,
                    cantidad: i.cantidad,
                    precio_unitario: i.precio_unitario,
                })),
            };"""

new_payload = """            const payload = {
                cliente_id: this.clienteId || null,
                cliente_documento: this.clienteDocumento || null,
                cliente_nombre: this.clienteNombre || null,
                cliente_direccion: this.clienteDireccion || null,
                forma_pago: this.formaPago,
                referencia_pago: this.referenciaPago || null,
                monto_recibido: this.formaPago === 'efectivo' ? this.montoRecibido : (this.formaPago === 'mixto' ? (this.montoEfectivo + this.montoDigital) : this.total),
                monto_efectivo: this.formaPago === 'mixto' ? this.montoEfectivo : null,
                monto_digital: this.formaPago === 'mixto' ? this.montoDigital : null,
                metodo_digital: this.formaPago === 'mixto' ? this.metodoDigital : null,
                referencia_digital: this.formaPago === 'mixto' ? this.referenciaDigital : null,
                descuento: this.descuento,
                tipo_comprobante: this.tipoComprobante,
                items: this.carrito.map(i => ({
                    producto_id: i.producto_id,
                    cantidad: i.cantidad,
                    precio_unitario: i.precio_unitario,
                })),
            };"""

s = s.replace(old_payload, new_payload)

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
