function pos() {
    return {
        vistaMovil: 'productos', // 'productos' o 'carrito'
        productosDestacados: @json($productos),
        productosFiltrados: @json($productos),
        busqueda: '',
        categoriaActiva: null,
        carrito: [],
        
        // Escáner Cámara
        escanerAbierto: false,
        camaraMediaStream: null,
        camaraEstadoTexto: 'Iniciando cámara...',
        linternaEncendida: false,
        animFrameId: null,
        html5QrCodeInstance: null,

        // Modal de Checkout y Datos del Cliente
        modalPago: false,
        procesando: false,
        tipoComprobante: 'TICKET',
        clienteModo: 'generico', // 'generico' o 'documento'
        clienteDocumento: '',
        clienteNombre: '',
        clienteDireccion: '',
        clienteId: null,
        consultandoApi: false,
        apiFeedback: null,

        // Métodos de Pago
        formaPago: 'efectivo',
        referenciaPago: '',
        descuento: 0,
        montoRecibido: 0,
        sonidoSilenciado: AudioPOS.muted,

        sunatActivo: {{ ($empresaGlobal && $empresaGlobal->facturacion_electronica_activa) ? 'true' : 'false' }},
        impuestoTasa: {{ $empresaGlobal->impuesto ?? 0 }},
        impuestoIncluido: {{ $empresaGlobal && $empresaGlobal->impuesto_incluido ? 'true' : 'false' }},

        init() {
            // Registrar referencia a Alpine data para funciones globales del escáner
            window.POS_SetAlpineData(this);

            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') { 
                    e.preventDefault(); 
                    if (this.carrito.length > 0) this.abrirPago(); 
                }
                if (e.key === 'Escape') { 
                    // Escape cierra el escáner primero, luego el modal de pago
                    if (window.POS_EscanerActivo) { window.POS_CerrarCamara(); return; }
                    if (this.modalPago) this.modalPago = false; 
                }
            });
        },

        async abrirEscanerCamara() {
            // Si el modal de pago está abierto, cerrarlo primero para no interferir
            if (this.modalPago) return;
            
            this.camaraEstadoTexto = 'Conectando cámara...';
            
            // Solicitar cámara DIRECTAMENTE en el evento de click del usuario
            // Chrome/Safari móvil requiere que sea síncrono al gesto del usuario
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error("Tu navegador no soporta cámara web.");
                }

                let stream;
                try {
                    // Primero intentar cámara trasera (environment)
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: false
                    });
                } catch(e1) {
                    // Si falla, cualquier cámara
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                }

                this.camaraMediaStream = stream;
                this.escanerAbierto = true;

                // Vincular stream al elemento <video> - usar requestAnimationFrame para ser inmediato
                requestAnimationFrame(() => {
                    const video = document.getElementById('pos-cam-video');
                    if (!video) return;
                    video.muted = true;
                    video.srcObject = stream;
                    const playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise
                            .then(() => {
                                this.camaraEstadoTexto = 'Apunta al código de barras';
                                this.iniciarDetector(video);
                            })
                            .catch(() => {
                                // Autoplay bloqueado, intentar igual el detector
                                this.iniciarDetector(video);
                            });
                    }
                });

            } catch(err) {
                const msg = err.name === 'NotAllowedError' 
                    ? 'Permiso de cámara denegado. Ve a Configuración > Chrome > Permisos de sitio > Cámara y activa bodegavalezka.alwaysdata.net.'
                    : 'No se pudo activar la cámara: ' + (err.message || err.name);
                Swal.fire({
                    icon: 'warning',
                    title: 'Cámara no disponible',
                    text: msg,
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Entendido'
                });
                this.escanerAbierto = false;
            }
        },

        cerrarEscanerCamara() {
            if (this.animFrameId) {
                cancelAnimationFrame(this.animFrameId);
                this.animFrameId = null;
            }
            if (this.camaraMediaStream) {
                try {
                    this.camaraMediaStream.getTracks().forEach(track => track.stop());
                } catch(e){}
                this.camaraMediaStream = null;
            }
            this.linternaEncendida = false;
            this.escanerAbierto = false;
        },

        iniciarDetector(video) {
            // Si el navegador soporta BarcodeDetector nativo (Chrome Android / WebView)
            if ('BarcodeDetector' in window) {
                try {
                    const formats = ['ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e', 'qr_code'];
                    const detector = new BarcodeDetector({ formats: formats });

                    const scanLoop = async () => {
                        if (!this.escanerAbierto || !this.camaraMediaStream) return;
                        try {
                            if (video.readyState >= 2) {
                                const barcodes = await detector.detect(video);
                                if (barcodes && barcodes.length > 0) {
                                    const code = barcodes[0].rawValue;
                                    if (code) {
                                        this.onCodigoEscaneado(code);
                                        return;
                                    }
                                }
                            }
                        } catch(e) {}
                        this.animFrameId = requestAnimationFrame(scanLoop);
                    };
                    this.animFrameId = requestAnimationFrame(scanLoop);
                    return;
                } catch(e) {
                    console.warn('BarcodeDetector error:', e);
                }
            }

            // Fallback con escaneo por canvas si BarcodeDetector nativo no está disponible
            this.iniciarFallbackCanvas(video);
        },

        iniciarFallbackCanvas(video) {
            if (typeof Html5Qrcode === 'undefined') return;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            
            const scanCanvasLoop = async () => {
                if (!this.escanerAbierto || !this.camaraMediaStream) return;
                try {
                    if (video.videoWidth > 0 && video.videoHeight > 0) {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    }
                } catch(e){}
                this.animFrameId = requestAnimationFrame(scanCanvasLoop);
            };
            this.animFrameId = requestAnimationFrame(scanCanvasLoop);
        },

        async toggleLinterna() {
            if (!this.camaraMediaStream) return;
            const track = this.camaraMediaStream.getVideoTracks()[0];
            if (!track) return;
            try {
                this.linternaEncendida = !this.linternaEncendida;
                await track.applyConstraints({
                    advanced: [{ torch: this.linternaEncendida }]
                });
            } catch(e) {
                Toast.fire({ icon: 'info', title: 'Linterna no compatible con este dispositivo' });
            }
        },

        async onCodigoEscaneado(codigo) {
            AudioPOS.beep(1200, 'sine', 0.1);
            if (navigator.vibrate) navigator.vibrate(100);
            Toast.fire({ icon: 'success', title: `Detectado: ${codigo}` });
            this.busqueda = codigo;
            await this.buscarYAgregarCodigo(codigo);
            
            // Pausar y reanudar tras 1.5s
            setTimeout(() => {
                if (this.escanerAbierto && this.camaraMediaStream) {
                    if ('BarcodeDetector' in window && !this.animFrameId) {
                        this.iniciarCamaraNativa();
                    }
                }
            }, 1500);
        },

        async buscarYAgregarCodigo(codigo) {
            try {
                const res = await fetch(`/api/productos/buscar?q=${encodeURIComponent(codigo)}`);
                const productos = await res.json();
                if (productos && productos.length > 0) {
                    this.agregarAlCarrito(productos[0]);
                    Toast.fire({ icon: 'success', title: `+ ${productos[0].nombre}` });
                } else {
                    Toast.fire({ icon: 'warning', title: `Producto no encontrado: ${codigo}` });
                }
            } catch(e) {}
        },

        playBeep() { AudioPOS.beep(880, 'sine', 0.06); },
        toggleSonido() {
            this.sonidoSilenciado = AudioPOS.toggleMute();
            if (!this.sonidoSilenciado) AudioPOS.beep(1046, 'sine', 0.08);
            Toast.fire({ icon: 'info', title: this.sonidoSilenciado ? 'Sonidos silenciados' : 'Sonidos activados' });
        },

        get subtotal() {
            return this.carrito.reduce((sum, i) => sum + (i.cantidad * i.precio_unitario), 0);
        },
        get impuesto() {
            if (this.impuestoIncluido) {
                const base = (this.subtotal - this.descuento) / (1 + this.impuestoTasa/100);
                return (this.subtotal - this.descuento) - base;
            }
            return (this.subtotal - this.descuento) * (this.impuestoTasa/100);
        },
        get total() {
            return this.impuestoIncluido
                ? Math.max(0, this.subtotal - this.descuento)
                : Math.max(0, this.subtotal - this.descuento + this.impuesto);
        },
        get cambio() {
            if (this.formaPago !== 'efectivo') return 0;
            return Math.max(0, this.montoRecibido - this.total);
        },

        async buscarProductos() {
            if (!this.busqueda || this.busqueda.length < 2) {
                this.productosFiltrados = this.productosDestacados;
                return;
            }
            try {
                const res = await fetch(`/api/productos/buscar?q=${encodeURIComponent(this.busqueda)}`);
                this.productosFiltrados = await res.json();
            } catch(e) { console.error(e); }
        },

        filtrarCategoria(catId) {
            this.categoriaActiva = catId;
            if (!catId) {
                this.productosFiltrados = this.productosDestacados;
            } else {
                this.productosFiltrados = this.productosDestacados.filter(p => p.categoria_id === catId);
            }
        },

        agregarPrimero() {
            if (this.productosFiltrados.length > 0) this.agregarProducto(this.productosFiltrados[0]);
        },

        agregarProducto(p) {
            const existing = this.carrito.find(i => i.producto_id === p.id);
            if (existing) {
                existing.cantidad++;
            } else {
                this.carrito.push({
                    producto_id: p.id,
                    codigo: p.codigo,
                    nombre: p.nombre,
                    cantidad: 1,
                    precio_unitario: parseFloat(p.precio_venta),
                    stock: parseFloat(p.stock),
                });
            }
            AudioPOS.beep(950, 'sine', 0.05);
            this.busqueda = '';
            this.actualizarTotal();
        },

        cambiarCantidad(idx, delta) {
            this.carrito[idx].cantidad += delta;
            if (this.carrito[idx].cantidad <= 0) {
                this.quitarItem(idx);
            } else {
                AudioPOS.beep(800, 'sine', 0.04);
            }
        },

        quitarItem(idx) { 
            this.carrito.splice(idx, 1);
            AudioPOS.beep(600, 'triangle', 0.05);
        },

        vaciarCarrito() {
            AudioPOS.warning();
            Swal.fire({
                title: '¿Vaciar el carrito?',
                text: 'Se quitarán todos los productos seleccionados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    this.carrito = [];
                    Toast.fire({ icon: 'info', title: 'Carrito vaciado' });
                }
            });
        },

        actualizarTotal() {
            if (this.modalPago && this.formaPago === 'efectivo') {
                this.montoRecibido = this.total;
            }
        },

        abrirPago() {
            if (this.carrito.length === 0) {
                AudioPOS.warning();
                return;
            }
            this.montoRecibido = this.total;
            this.modalPago = true;
            AudioPOS.beep(1100, 'triangle', 0.08);

            this.$nextTick(() => {
                const el = document.getElementById('inputMontoRecibido');
                if (el) el.focus();
            });
        },

        cambiarTipoComprobante(tipo) {
            this.tipoComprobante = tipo;
            this.apiFeedback = null;
            AudioPOS.beep(850, 'sine', 0.05);
            if (tipo === 'FACTURA') {
                this.clienteModo = 'documento';
                if (this.clienteDocumento.length !== 11) {
                    this.clienteDocumento = '';
                    this.clienteNombre = '';
                    this.clienteDireccion = '';
                    this.clienteId = null;
                }
                this.$nextTick(() => {
                    const el = document.getElementById('inputDocumentoPos');
                    if (el) el.focus();
                });
            } else if (tipo === 'TICKET') {
                if (!this.clienteDocumento) {
                    this.clienteModo = 'generico';
                }
            }
        },

        toggleClienteGenerico() {
            AudioPOS.beep(750, 'sine', 0.04);
            if (this.clienteModo === 'generico') {
                this.clienteModo = 'documento';
                this.$nextTick(() => {
                    const el = document.getElementById('inputDocumentoPos');
                    if (el) el.focus();
                });
            } else {
                this.clienteModo = 'generico';
                this.clienteDocumento = '';
                this.clienteNombre = '';
                this.clienteDireccion = '';
                this.clienteId = null;
                this.apiFeedback = null;
            }
        },

        autoConsultarDocumento() {
            this.clienteDocumento = this.clienteDocumento.replace(/\D/g, '');
            if (this.tipoComprobante === 'FACTURA' && this.clienteDocumento.length === 11) {
                this.consultarApiDocumento();
            } else if (this.tipoComprobante !== 'FACTURA' && this.clienteDocumento.length === 8) {
                this.consultarApiDocumento();
            }
        },

        async consultarApiDocumento() {
            const doc = this.clienteDocumento.trim();
            if (!doc) return;

            const tipoDoc = doc.length === 11 ? 'RUC' : (doc.length === 8 ? 'DNI' : 'OTRO');
            if (this.tipoComprobante === 'FACTURA' && tipoDoc !== 'RUC') {
                AudioPOS.warning();
                Swal.fire({
                    title: 'RUC Inválido',
                    text: 'Para emitir Factura el RUC debe contener exactamente 11 dígitos.',
                    icon: 'warning',
                    confirmButtonColor: '#10b981'
                });
                return;
            }

            this.consultandoApi = true;
            this.apiFeedback = null;

            try {
                const res = await fetch(`/api/sunat/consulta-documento?documento=${encodeURIComponent(doc)}&tipo=${tipoDoc}`);
                const data = await res.json();

                if (data.success) {
                    this.clienteNombre = data.razon_social || data.nombre_completo || data.nombres || '';
                    this.clienteDireccion = data.direccion || '';
                    this.clienteId = data.cliente_id || null;

                    const origenTexto = data.origen === 'local' ? 'Base de Datos' : (tipoDoc === 'RUC' ? 'SUNAT' : 'RENIEC');
                    this.apiFeedback = {
                        tipo: 'success',
                        mensaje: `✓ ${origenTexto}: ${this.clienteNombre}`
                    };
                    AudioPOS.beep(1200, 'sine', 0.08);
                    Toast.fire({ icon: 'success', title: `Encontrado: ${this.clienteNombre}` });
                } else {
                    this.apiFeedback = {
                        tipo: 'warning',
                        mensaje: data.error || 'No encontrado. Ingrese el nombre manualmente.'
                    };
                    AudioPOS.warning();
                    Toast.fire({ icon: 'info', title: 'Ingrese el nombre del cliente' });
                }
            } catch(e) {
                this.apiFeedback = {
                    tipo: 'warning',
                    mensaje: 'Error de conexión. Ingrese el nombre manualmente.'
                };
                AudioPOS.warning();
            } finally {
                this.consultandoApi = false;
            }
        },

        async procesarVenta() {
            if (this.procesando) return;

            // Validación Factura
            if (this.tipoComprobante === 'FACTURA') {
                if (!this.clienteDocumento || this.clienteDocumento.length !== 11) {
                    AudioPOS.warning();
                    Swal.fire({
                        title: 'RUC Requerido',
                        text: 'Para emitir FACTURA debe ingresar un número de RUC válido de 11 dígitos.',
                        icon: 'warning',
                        confirmButtonColor: '#10b981'
                    });
                    return;
                }
                if (!this.clienteNombre) {
                    AudioPOS.warning();
                    Swal.fire({
                        title: 'Razón Social Requerida',
                        text: 'Ingrese o consulte la Razón Social del cliente con RUC.',
                        icon: 'warning',
                        confirmButtonColor: '#10b981'
                    });
                    return;
                }
            }

            // Validación Boleta con DNI
            if (this.tipoComprobante === 'BOLETA' && this.clienteModo === 'documento' && this.clienteDocumento) {
                if (this.clienteDocumento.length !== 8 && this.clienteDocumento.length !== 11) {
                    AudioPOS.warning();
                    Swal.fire({
                        title: 'Documento Inválido',
                        text: 'El DNI debe tener 8 dígitos (o RUC de 11 dígitos).',
                        icon: 'warning',
                        confirmButtonColor: '#10b981'
                    });
                    return;
                }
            }

            this.procesando = true;

            const payload = {
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
            };

            try {
                const res = await fetch('{{ route("ventas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const result = await res.json();

                if (result.success) {
                    // 🔊 Sonido de Éxito / Caja Registradora
                    AudioPOS.success();

                    // Si emitió un CPE electrónico abrir esa vista, sino el ticket normal
                    const urlImprimir = result.cpe_url || result.redirect;
                    if (urlImprimir) {
                        window.open(urlImprimir, '_blank');
                    }

                    this.carrito = [];
                    this.descuento = 0;
                    this.montoRecibido = 0;
                    this.modalPago = false;
                    this.clienteDocumento = '';
                    this.clienteNombre = '';
                    this.clienteDireccion = '';
                    this.clienteId = null;
                    this.referenciaPago = '';
                    this.apiFeedback = null;

                    let htmlMsg = `<div class="space-y-2.5 text-left">
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
                    });
                } else {
                    AudioPOS.warning();
                    Swal.fire({
                        title: 'No se pudo completar la venta',
                        text: result.error || 'Error desconocido al registrar la venta.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Cerrar'
                    });
                }
            } catch(e) {
                AudioPOS.warning();
                Swal.fire({
                    title: 'Error de comunicación',
                    text: 'Ocurrió un error al procesar la solicitud: ' + e.message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Cerrar'
                });
            } finally {
                this.procesando = false;
            }
        }
    }
}

// ================================================================
// 📷 ESCÁNER DE CÁMARA - FUNCIONES GLOBALES (100% DOM puro)
// IMPORTANTE: Alpine.js v3 NO usa posEl.__x — esa era API de v2.
// El modal del escáner es controlado exclusivamente con display flex/none.
// El modal de pago (checkout) usa Alpine pero el escáner no.
// ================================================================
window.POS_EscanerActivo   = false;
window.POS_MediaStream     = null;
window.POS_AnimFrame       = null;
window.POS_LinternaActiva  = false;
window._POS_Alpine         = null; // referencia a Alpine data, seteada en init()

// Llamado desde Alpine init() para registrar referencia
window.POS_SetAlpineData = function(data) {
    window._POS_Alpine = data;
};

window.POS_AbrirCamara = async function(event) {
    if (event) { event.preventDefault(); event.stopPropagation(); }

    // No abrir si el modal de pago está activo
    if (window._POS_Alpine && window._POS_Alpine.modalPago) return;

    // Si ya está activo, cerrar
    if (window.POS_EscanerActivo) {
        window.POS_CerrarCamara();
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        Swal.fire({ icon: 'warning', title: 'No soportado', text: 'Tu navegador no soporta acceso a cámara. Usa Chrome en Android.' });
        return;
    }

    // ABRIR MODAL PRIMERO — para feedback visual inmediato al usuario
    const modal = document.getElementById('pos-escaner-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('pos-cam-status').textContent = 'Iniciando cámara...';
    }
    window.POS_EscanerActivo = true;

    // Solicitar cámara — getUserMedia es el primer await, Chrome acepta esto en user gesture
    try {
        let stream;
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
        } catch(e1) {
            // Fallback: cualquier cámara disponible
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        }

        window.POS_MediaStream = stream;

        // Vincular stream al <video>
        const video = document.getElementById('pos-cam-video');
        if (!video) { window.POS_CerrarCamara(); return; }
        video.muted    = true;
        video.srcObject = stream;

        video.onloadedmetadata = function() {
            video.play().then(() => {
                document.getElementById('pos-cam-status').textContent = 'Apunta al código de barras';
                window.POS_IniciarDetector(video);
            }).catch(() => {
                window.POS_IniciarDetector(video);
            });
        };

    } catch(err) {
        window.POS_CerrarCamara();
        const msg = err.name === 'NotAllowedError'
            ? 'Permiso de cámara denegado.\n\nEn Chrome: toca los tres puntos → Configuración → Permisos de sitio → Cámara → activa bodegavalezka.alwaysdata.net'
            : 'No se pudo activar la cámara: ' + (err.message || err.name);
        Swal.fire({ icon: 'warning', title: 'Cámara no disponible', text: msg, confirmButtonColor: '#10b981' });
    }
};

window.POS_CerrarCamara = function() {
    window.POS_EscanerActivo  = false;
    window.POS_LinternaActiva = false;

    if (window.POS_AnimFrame) {
        cancelAnimationFrame(window.POS_AnimFrame);
        window.POS_AnimFrame = null;
    }
    if (window.POS_MediaStream) {
        window.POS_MediaStream.getTracks().forEach(t => { try { t.stop(); } catch(e){} });
        window.POS_MediaStream = null;
    }

    const video = document.getElementById('pos-cam-video');
    if (video) { video.srcObject = null; video.onloadedmetadata = null; }

    const modal = document.getElementById('pos-escaner-modal');
    if (modal) modal.style.display = 'none';

    const btn = document.getElementById('btn-linterna');
    if (btn) { btn.style.background = '#fff'; btn.style.color = '#111'; }
};

window.POS_IniciarDetector = function(video) {
    if (!('BarcodeDetector' in window)) {
        // BarcodeDetector no disponible — Chrome <88 o Safari. Mostrar aviso pero dejar cámara activa
        document.getElementById('pos-cam-status').textContent = '⚠️ Escáner no disponible en este navegador';
        return;
    }

    const detector = new BarcodeDetector({
        formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'code_93', 'upc_a', 'upc_e', 'qr_code', 'itf']
    });

    const loop = async () => {
        if (!window.POS_EscanerActivo || !window.POS_MediaStream) return;
        try {
            if (video.readyState >= 2 && video.videoWidth > 0) {
                const codigos = await detector.detect(video);
                if (codigos && codigos.length > 0 && codigos[0].rawValue) {
                    window.POS_OnCodigoDetectado(codigos[0].rawValue);
                    return; // parar hasta que se reanude
                }
            }
        } catch(e) { /* ignorar frames fallidos */ }
        window.POS_AnimFrame = requestAnimationFrame(loop);
    };

    window.POS_AnimFrame = requestAnimationFrame(loop);
};

window.POS_OnCodigoDetectado = async function(codigo) {
    if (!window.POS_EscanerActivo) return;

    // Parar el bucle temporalmente
    if (window.POS_AnimFrame) {
        cancelAnimationFrame(window.POS_AnimFrame);
        window.POS_AnimFrame = null;
    }

    // Feedback sensorial
    if (navigator.vibrate) navigator.vibrate([60, 20, 60]);
    try { AudioPOS.beep(1400, 'sine', 0.12); } catch(e) {}
    document.getElementById('pos-cam-status').textContent = `✅ ${codigo}`;

    // Buscar producto y agregar al carrito (vía Alpine data registrada)
    try {
        const res  = await fetch(`/api/productos/buscar?q=${encodeURIComponent(codigo)}`);
        const lista = await res.json();
        if (lista && lista.length > 0) {
            // Agregar vía Alpine.js data
            if (window._POS_Alpine) {
                window._POS_Alpine.agregarProducto(lista[0]);
            }
            Toast.fire({ icon: 'success', title: `✅ ${lista[0].nombre}` });
        } else {
            Toast.fire({ icon: 'warning', title: `Código no encontrado: ${codigo}` });
        }
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'Error al buscar el producto' });
    }

    // Reanudar detección después de 1.5 segundos
    setTimeout(() => {
        if (window.POS_EscanerActivo) {
            document.getElementById('pos-cam-status').textContent = 'Apunta al código de barras';
            const video = document.getElementById('pos-cam-video');
            if (video) window.POS_IniciarDetector(video);
        }
    }, 1500);
};

window.POS_ToggleLinterna = async function() {
    if (!window.POS_MediaStream) return;
    const track = window.POS_MediaStream.getVideoTracks()[0];
    if (!track) return;
    try {
        window.POS_LinternaActiva = !window.POS_LinternaActiva;
        await track.applyConstraints({ advanced: [{ torch: window.POS_LinternaActiva }] });
        const btn = document.getElementById('btn-linterna');
        if (btn) {
            btn.style.background = window.POS_LinternaActiva ? '#fbbf24' : '#fff';
            btn.style.color      = window.POS_LinternaActiva ? '#1e293b' : '#111';
        }
    } catch(e) {
        Toast.fire({ icon: 'info', title: 'Linterna no disponible en este dispositivo' });
    }
};

