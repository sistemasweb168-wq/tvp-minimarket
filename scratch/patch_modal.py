import re

with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Insert the modal after modalPago
modal_html = """

    <!-- 🚀 MODAL POST-VENTA PROFESIONAL (COMPROBANTE GENERADO) -->
    <div id="modal-post-venta" x-show="modalPostVenta" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-2 sm:p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden transform transition-all relative">
            <button @click="cerrarPostVenta()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <div class="p-5 sm:p-7 pb-2">
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-500 border border-emerald-200 shadow-sm flex-shrink-0">
                        <i class="fas fa-check text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold text-slate-400 tracking-wider">Comprobante Generado</p>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-800" x-text="ultimaVenta?.numero_ticket"></h2>
                    </div>
                </div>

                <!-- Alert Sunat / Aceptado -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-6 text-emerald-800 shadow-inner">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-emerald-600"></i>
                        <span class="text-sm font-semibold" x-text="`La venta ha sido procesada correctamente.`"></span>
                    </div>
                    <div class="font-black text-lg bg-white px-2 py-0.5 rounded shadow-sm border border-emerald-100" x-show="ultimaVenta?.cambio > 0">
                        Vuelto: <span x-text="`{{ $moneda }} ${ultimaVenta?.cambio.toFixed(2)}`"></span>
                    </div>
                </div>

                <p class="text-sm font-semibold text-slate-700 mb-3"><i class="fas fa-print text-slate-400 mr-1.5"></i>Formatos disponibles para imprimir:</p>

                <!-- Formats -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button type="button" @click="abrirTicket('a4')" class="py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-2xl font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-file-pdf text-lg"></i>Formato A4
                    </button>
                    <button type="button" @click="abrirTicket('ticket')" class="py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-2xl font-bold transition flex items-center justify-center gap-2 shadow-sm shadow-blue-500/30">
                        <i class="fas fa-receipt text-lg"></i>Ticket 80MM
                    </button>
                </div>

                <div class="border-t border-slate-100 my-5 relative">
                    <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-2 text-xs font-bold text-slate-300 uppercase">O Compartir Digital</span>
                </div>

                <!-- Envíos -->
                <div class="space-y-3 mb-4">
                    <!-- WhatsApp -->
                    <div class="flex group">
                        <span class="inline-flex items-center px-4 rounded-l-2xl border border-r-0 border-slate-200 bg-slate-50 text-slate-400 text-sm font-bold group-focus-within:border-emerald-500 group-focus-within:text-emerald-500 transition">
                            +51
                        </span>
                        <input type="text" x-model="telefonoWhatsApp" placeholder="Celular del cliente" class="flex-1 border-y border-slate-200 px-3 py-3 text-sm font-medium text-slate-800 focus:outline-none focus:border-emerald-500 transition w-full placeholder-slate-300">
                        <button type="button" @click="enviarWhatsApp()" class="inline-flex items-center px-4 rounded-r-2xl border border-emerald-500 bg-emerald-50 text-emerald-700 hover:bg-emerald-500 hover:text-white font-bold text-sm transition shadow-sm">
                            Enviar <i class="fab fa-whatsapp ml-2 text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-200 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                <button type="button" @click="cerrarPostVenta()" class="w-full sm:w-auto px-6 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition shadow-md shadow-slate-900/20 text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle text-slate-300"></i> Nueva Venta (Enter)
                </button>
            </div>
        </div>
    </div>
"""

s = s.replace('<!-- MODAL ESCÁNER CÁMARA FULLSCREEN', modal_html + '\n    <!-- MODAL ESCÁNER CÁMARA FULLSCREEN')


# Modify Alpine state
old_state = """        modalPago: false,
        procesando: false,"""
new_state = """        modalPago: false,
        modalPostVenta: false,
        ultimaVenta: null,
        telefonoWhatsApp: '',
        procesando: false,"""
s = s.replace(old_state, new_state)


# Modify procesarVenta success logic
old_logic = """                    let htmlMsg = `<div class="space-y-2.5 text-left">
                        <div class="bg-emerald-50 p-3 rounded-2xl border border-emerald-200">
                            <p class="text-xs uppercase font-bold text-emerald-800">Comprobante Emitido</p>
                            <p class="text-xl font-black text-emerald-700">${result.numero_ticket || ''}</p>
                        </div>`;
                    
                    if (result.cpe_numero) {
                        htmlMsg += `<div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 text-xs text-slate-700 space-y-0.5">
                            <p><strong>CPE SUNAT:</strong> <span class="font-bold text-slate-900">${result.cpe_numero}</span></p>
                            <p><strong>Estado:</strong> <span class="font-bold ${result.cpe_estado === 'Aceptado' || result.cpe_estado === 'ACEPTADO' ? 'text-emerald-600' : 'text-amber-600'}">${result.cpe_estado || 'REGISTRADO'}</span></p>
                        </div>`;
                    }

                    htmlMsg += `</div>`;

                    Swal.fire({
                        title: '¡Venta Cobrada con Éxito!',
                        html: htmlMsg,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: '<i class="fas fa-print mr-1.5"></i>Listo'
                    });"""

new_logic = """                    this.ultimaVenta = {
                        id: result.venta_id,
                        numero_ticket: result.numero_ticket,
                        url_ticket: result.redirect,
                        cambio: this.cambio,
                        total: this.total,
                        cliente_nombre: this.clienteNombre
                    };
                    this.telefonoWhatsApp = this.clienteSeleccionado && this.clienteSeleccionado.telefono ? this.clienteSeleccionado.telefono : '';
                    this.modalPostVenta = true;
                    
                    // Asegurar foco en ventana de post venta para cerrar con enter
                    setTimeout(() => {
                        window.focus();
                    }, 100);"""

s = s.replace(old_logic, new_logic)


# Add new methods at the end of pos()
old_methods_end = """        }
    }
}"""

new_methods_end = """
        abrirTicket(formato) {
            if (!this.ultimaVenta) return;
            // Si tuvieramos /factura para A4, usaríamos eso. Por ahora abrimos la misma URL
            window.open(this.ultimaVenta.url_ticket, '_blank');
        },

        enviarWhatsApp() {
            if (!this.telefonoWhatsApp || this.telefonoWhatsApp.length < 9) {
                Toast.fire({ icon: 'warning', title: 'Ingrese un número válido' });
                return;
            }
            const empresa = 'Nuestra Tienda'; // O de tu DB
            const url = window.location.origin + this.ultimaVenta.url_ticket;
            const mensaje = `¡Hola! 👋 Gracias por tu compra en nuestro Minimarket.\n\nAquí tienes tu comprobante electrónico *${this.ultimaVenta.numero_ticket}* por el total de *S/ ${this.ultimaVenta.total.toFixed(2)}*.\n\nPuedes verlo y descargarlo aquí: ${url}`;
            const link = `https://wa.me/51${this.telefonoWhatsApp.replace(/\\s+/g,'')}?text=${encodeURIComponent(mensaje)}`;
            window.open(link, '_blank');
        },

        cerrarPostVenta() {
            this.modalPostVenta = false;
            this.ultimaVenta = null;
        }
    }
}"""
s = s.replace(old_methods_end, new_methods_end)

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
