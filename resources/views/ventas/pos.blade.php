@extends('layouts.app')
@section('title', 'Punto de Venta')
@section('header', 'Punto de Venta')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div x-data="pos()" x-init="init()">
    <!-- Selector Móvil: Catálogo vs Carrito -->
    <div class="lg:hidden flex bg-slate-800/80 p-1 rounded-2xl mb-3 shadow-inner border border-slate-700">
        <button type="button" @click="vistaMovil = 'productos'" 
                :class="vistaMovil === 'productos' ? 'bg-slate-700 text-amber-400 shadow-sm font-bold' : 'text-slate-400 font-semibold hover:text-slate-200'" 
                class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-1.5">
            <i class="fas fa-boxes"></i>
            <span>Catálogo</span>
        </button>
        <button type="button" @click="vistaMovil = 'carrito'" 
                :class="vistaMovil === 'carrito' ? 'bg-slate-700 text-amber-400 shadow-sm font-bold' : 'text-slate-400 font-semibold hover:text-slate-200'" 
                class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-1.5 relative">
            <i class="fas fa-shopping-cart"></i>
            <span>Carrito</span>
            <span x-show="carrito.length > 0" class="bg-emerald-600 text-white text-[10px] px-2 py-0.5 rounded-full font-extrabold ml-1" x-text="carrito.length"></span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Panel Productos (Visible siempre en Desktop, o si vistaMovil === 'productos' en móvil) -->
        <div class="lg:col-span-2 space-y-4" :class="vistaMovil === 'productos' ? 'block' : 'hidden lg:block'">
            <!-- Búsqueda + Botón Escáner Cámara -->
            <div class="bg-slate-900 rounded-2xl shadow-md p-3 sm:p-4 border border-slate-800">
                <div class="flex gap-2 sm:gap-2.5 items-center">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-3.5 sm:left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" x-model="busqueda" @input.debounce.300ms="buscarProductos()"
                               @keydown.enter.prevent="agregarPrimero()"
                               class="w-full pl-10 sm:pl-12 pr-3 py-2.5 sm:py-3 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-700 font-medium text-xs sm:text-sm"
                               placeholder="Buscar producto o código de barras (Enter para agregar)" autofocus>
                    </div>

                    <!-- Botón Escáner Cámara - onclick NATIVO para preservar gesto del usuario en Chrome Android -->
                    <button type="button" 
                            id="btn-abrir-escaner"
                            onclick="POS_AbrirCamara(event)"
                            title="Escanear con la Cámara del Celular / PC"
                            class="px-4 py-3.5 sm:py-3 gradient-primary hover:brightness-105 text-white rounded-xl transition text-xs sm:text-sm font-bold flex items-center gap-1.5 shadow-sm flex-shrink-0">
                        <i class="fas fa-camera text-sm sm:text-base"></i>
                        <span class="hidden sm:inline">Cámara</span>
                    </button>

                    <button type="button" @click="busqueda = ''; productosFiltrados = productosDestacados" class="px-3 py-2.5 sm:py-3 bg-slate-100 hover:bg-slate-200 rounded-xl transition text-slate-600 text-xs sm:text-sm flex-shrink-0">
                        <i class="fas fa-times"></i>
                    </button>

                    <!-- Toggle Sonido -->
                    <button type="button" @click="toggleSonido()" :title="sonidoSilenciado ? 'Activar Sonidos POS' : 'Silenciar Sonidos POS'"
                            class="px-3 py-2.5 sm:py-3 rounded-xl transition text-xs sm:text-sm font-bold flex items-center gap-1.5 flex-shrink-0"
                            :class="sonidoSilenciado ? 'bg-slate-100 text-slate-400' : 'bg-emerald-50 text-amber-400 border border-emerald-200'">
                        <i :class="sonidoSilenciado ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                    </button>
                </div>
            </div>

            <!-- Categorías rápidas -->
            <div class="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
                <button type="button" @click="filtrarCategoria(null)" :class="categoriaActiva === null ? 'bg-amber-500 text-slate-900 shadow-amber-500/20' : 'bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700'"
                        class="px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap shadow-sm transition">
                    <i class="fas fa-th-large mr-1.5"></i>Todos
                </button>
                @foreach($categorias as $cat)
                    <button type="button" @click="filtrarCategoria({{ $cat->id }})"
                            :class="categoriaActiva === {{ $cat->id }} ? 'text-slate-900 shadow-md' : 'bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700'"
                            :style="categoriaActiva === {{ $cat->id }} ? 'background: {{ $cat->color }}' : ''"
                            class="px-4 py-3 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap shadow-sm transition">
                        <i class="fas fa-{{ $cat->icono }} mr-1.5"></i>{{ $cat->nombre }}
                    </button>
                @endforeach
            </div>

            <!-- Grid Productos (2 columnas en celular, 3 en tablet, 4 en desktop) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 sm:gap-3">
                <template x-for="p in productosFiltrados" :key="p.id">
                    <button type="button" @click="agregarProducto(p)"
                            :disabled="parseFloat(p.stock) <= 0"
                            :class="{'opacity-60 grayscale cursor-not-allowed': parseFloat(p.stock) <= 0}"
                            class="relative bg-slate-900 rounded-2xl shadow-sm hover:shadow-md hover:border-amber-500 transition active:scale-95 p-2.5 sm:p-3 text-left border border-slate-800 flex flex-col justify-between overflow-hidden">
                        
                        <!-- Etiqueta de Agotado -->
                        <div x-show="parseFloat(p.stock) <= 0" class="absolute top-2 right-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-sm z-10">
                            AGOTADO
                        </div>

                        <div>
                            <div class="aspect-square bg-slate-800 rounded-xl mb-2 flex items-center justify-center overflow-hidden relative">
                                <template x-if="p.imagen">
                                    <img :src="`/uploads/productos/${p.imagen}`" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.imagen">
                                    <i class="fas fa-box text-2xl sm:text-3xl text-slate-300"></i>
                                </template>
                            </div>
                            <p class="text-[11px] sm:text-xs font-bold text-slate-800 line-clamp-2 mb-1" x-text="p.nombre"></p>
                        </div>
                        <div class="flex justify-between items-end mt-1 pt-1.5 border-t border-slate-50">
                            <span class="text-[10px] sm:text-[11px] font-bold" 
                                  :class="parseFloat(p.stock) <= 0 ? 'text-red-500' : (parseFloat(p.stock) <= 5 ? 'text-orange-500' : 'text-slate-400')"
                                  x-text="`Stk: ${parseFloat(p.stock).toFixed(0)}`"></span>
                            <span class="text-xs sm:text-sm font-black text-amber-400" x-text="`{{ $moneda }} ${parseFloat(p.precio_venta).toFixed(2)}`"></span>
                        </div>
                    </button>
                </template>
                <div x-show="productosFiltrados.length === 0" class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl">
                    <i class="fas fa-search text-4xl mb-2 text-slate-300"></i>
                    <p class="font-medium text-xs sm:text-sm">No se encontraron productos coincidentes</p>
                </div>
            </div>

            <!-- Botón flotante para ir al Carrito en móvil si hay productos -->
            <div x-show="vistaMovil === 'productos' && carrito.length > 0" class="lg:hidden fixed bottom-20 left-4 right-4 z-30">
                <button type="button" @click="vistaMovil = 'carrito'" 
                        class="w-full gradient-primary text-white py-3.5 px-4 rounded-2xl shadow-xl shadow-emerald-600/30 flex items-center justify-between font-bold text-sm active:scale-95 transition">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span x-text="`Ver Carrito (${carrito.length} ítems)`"></span>
                    </span>
                    <span class="flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-xl">
                        <span x-text="`{{ $moneda }} ${total.toFixed(2)}`"></span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </span>
                </button>
            </div>
        </div>

        <!-- Panel Carrito (Visible siempre en Desktop, o si vistaMovil === 'carrito' en móvil) -->
        <div class="bg-white rounded-2xl shadow-md flex flex-col" :class="vistaMovil === 'carrito' ? 'flex' : 'hidden lg:flex'" style="max-height: calc(100vh - 120px);">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center gradient-primary text-white rounded-t-2xl">
                <div>
                    <h3 class="font-bold flex items-center gap-2 text-base"><i class="fas fa-shopping-cart"></i>Carrito de Venta</h3>
                    <p class="text-xs text-emerald-100 font-medium">Turno #{{ $turnoActivo->id }} • {{ $turnoActivo->caja->nombre }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-white/20 text-white text-xs px-2.5 py-1 rounded-full font-bold" x-text="`${carrito.length} ítems`"></span>
                    <button type="button" @click="vaciarCarrito()" :disabled="carrito.length === 0" title="Vaciar Carrito"
                            class="text-white/80 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>

            <!-- Lista de Items en Carrito -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2.5 min-h-48">
                <template x-for="(item, idx) in carrito" :key="item.producto_id">
                    <div class="bg-slate-50 hover:bg-slate-100/70 transition rounded-xl p-3 border border-slate-200/60">
                        <div class="flex justify-between items-start mb-2">
                            <p class="font-bold text-xs sm:text-sm text-slate-800 flex-1 pr-2 leading-tight" x-text="item.nombre"></p>
                            <button type="button" @click="quitarItem(idx)" class="text-slate-400 hover:text-red-500 transition p-0.5">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center bg-white border border-slate-300 rounded-lg overflow-hidden shadow-sm">
                                <button type="button" @click="cambiarCantidad(idx, -1)" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 transition"><i class="fas fa-minus text-[10px]"></i></button>
                                <input type="number" step="0.01" x-model.number="item.cantidad"
                                       @input="actualizarTotal()" class="w-14 text-center border-x border-slate-200 py-1 text-xs font-bold text-slate-800 focus:outline-none">
                                <button type="button" @click="cambiarCantidad(idx, 1)" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 transition"><i class="fas fa-plus text-[10px]"></i></button>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-slate-400" x-text="`{{ $moneda }} ${item.precio_unitario.toFixed(2)}`"></p>
                                <p class="font-extrabold text-sm text-amber-400" x-text="`{{ $moneda }} ${(item.cantidad * item.precio_unitario).toFixed(2)}`"></p>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="carrito.length === 0" class="text-center py-16 text-slate-300">
                    <i class="fas fa-shopping-basket text-6xl mb-3 text-slate-200"></i>
                    <p class="text-sm font-medium text-slate-400">El carrito está vacío</p>
                    <p class="text-xs text-slate-300 mt-1">Haz clic o escanea un producto para agregarlo</p>
                </div>
            </div>

            <!-- Totales y Botón de Cobro -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-2 rounded-b-2xl">
                <div class="flex justify-between text-xs text-slate-500 font-medium">
                    <span>Subtotal:</span>
                    <span x-text="`{{ $moneda }} ${subtotal.toFixed(2)}`"></span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                    <span>Descuento:</span>
                    <div class="flex items-center gap-1">
                        <span>{{ $moneda }}</span>
                        <input type="number" x-model.number="descuento" @input="actualizarTotal()" min="0" step="0.01"
                               class="w-20 text-right px-2 py-0.5 border border-slate-300 rounded font-semibold text-slate-700 text-xs focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="flex justify-between text-xs text-slate-500 font-medium">
                    <span>Impuesto ({{ $empresaGlobal->impuesto ?? 0 }}%):</span>
                    <span x-text="`{{ $moneda }} ${impuesto.toFixed(2)}`"></span>
                </div>
                <div class="flex justify-between items-baseline text-2xl font-black text-amber-400 pt-2 border-t border-slate-200">
                    <span class="text-sm uppercase text-slate-600 font-bold tracking-wide">TOTAL:</span>
                    <span x-text="`{{ $moneda }} ${total.toFixed(2)}`"></span>
                </div>

                <button type="button" @click="abrirPago()" :disabled="carrito.length === 0"
                        class="w-full gradient-primary text-white py-3.5 rounded-xl font-extrabold text-base shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:brightness-105 transition transform active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none mt-2 flex items-center justify-center gap-2">
                    <i class="fas fa-cash-register text-lg"></i>
                    <span>Cobrar (F2)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- 🚀 MODAL PROFESIONAL DE CHECKOUT & EMISIÓN (ZERO SCROLL FIT)  -->
    <!-- ============================================================= -->
    <div id="modal-pago" x-show="modalPago" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-sm p-2 sm:p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[94vh] flex flex-col overflow-hidden border border-slate-100 transform transition-all" @click.outside="modalPago = false">
            
            <!-- Encabezado Compacto Fijo -->
            <div class="gradient-primary text-white px-4 sm:px-5 py-2.5 sm:py-3 flex justify-between items-center flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold leading-tight">Procesar Venta y Cobro</h3>
                        <p class="text-[10px] sm:text-[11px] text-emerald-100 font-medium">Turno #{{ $turnoActivo->id }} • {{ $turnoActivo->caja->nombre }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button type="button" @click="toggleSonido()" :title="sonidoSilenciado ? 'Activar Sonidos' : 'Silenciar Sonidos'"
                            class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition text-xs sm:text-sm">
                        <i :class="sonidoSilenciado ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                    </button>
                    <button type="button" @click="modalPago = false" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                        <i class="fas fa-times text-sm sm:text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Contenido con Scroll Interno Suave (Grid 2 Columnas) -->
            <div class="p-3 sm:p-5 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                
                <!-- COLUMNA IZQUIERDA: Comprobante & Cliente & Total -->
                <div class="space-y-2.5 sm:space-y-3 flex flex-col justify-between">
                    
                    <!-- Selector de Comprobante -->
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1">1. Tipo de Comprobante</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button type="button" @click="cambiarTipoComprobante('TICKET')"
                                    :class="tipoComprobante === 'TICKET' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20 font-bold' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:bg-slate-100'"
                                    class="border-2 py-1.5 sm:py-2 px-1 rounded-xl text-center text-xs transition flex flex-col items-center justify-center">
                                <span class="font-extrabold flex items-center gap-1 text-[11px] sm:text-xs"><i class="fas fa-receipt text-xs text-amber-400"></i>Ticket</span>
                                <span class="text-[9px] text-slate-400">Nota Venta</span>
                            </button>

                            <button type="button" @click="cambiarTipoComprobante('BOLETA')"
                                    :class="tipoComprobante === 'BOLETA' ? 'border-blue-500 bg-blue-50 text-blue-800 ring-2 ring-blue-500/20 font-bold' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:bg-slate-100'"
                                    class="border-2 py-1.5 sm:py-2 px-1 rounded-xl text-center text-xs transition flex flex-col items-center justify-center">
                                <span class="font-extrabold flex items-center gap-1 text-[11px] sm:text-xs"><i class="fas fa-file-invoice text-xs text-blue-600"></i>Boleta</span>
                                <span class="text-[9px] text-slate-400">DNI / Varios</span>
                            </button>

                            <button type="button" @click="cambiarTipoComprobante('FACTURA')"
                                    :class="tipoComprobante === 'FACTURA' ? 'border-purple-500 bg-purple-50 text-purple-800 ring-2 ring-purple-500/20 font-bold' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:bg-slate-100'"
                                    class="border-2 py-1.5 sm:py-2 px-1 rounded-xl text-center text-xs transition flex flex-col items-center justify-center">
                                <span class="font-extrabold flex items-center gap-1 text-[11px] sm:text-xs"><i class="fas fa-building text-xs text-purple-600"></i>Factura</span>
                                <span class="text-[9px] text-slate-400">RUC 11 Díg.</span>
                            </button>
                        </div>
                    </div>

                    <!-- Panel de Cliente -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-2.5 sm:p-3 space-y-2 flex-1">
                        <div class="flex justify-between items-center">
                            <label class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-slate-600 flex items-center gap-1">
                                <i class="fas fa-user-tag text-emerald-500 text-xs"></i>
                                <span>2. Identificación del Cliente</span>
                            </label>
                            
                            <template x-if="tipoComprobante !== 'FACTURA'">
                                <button type="button" @click="toggleClienteGenerico()" 
                                        class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-lg transition"
                                        :class="clienteModo === 'generico' ? 'bg-slate-200 text-slate-700' : 'text-amber-400 hover:underline'">
                                    <span x-text="clienteModo === 'generico' ? '✓ Modo Varios' : '+ DNI'"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Si está en modo Clientes Varios -->
                        <div x-show="clienteModo === 'generico' && tipoComprobante !== 'FACTURA'" 
                             class="bg-white p-2 rounded-xl border border-slate-200 text-xs text-slate-600 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                    <i class="fas fa-users text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs leading-tight">CLIENTES VARIOS</p>
                                    <p class="text-[9px] text-slate-400">Venta rápida al público</p>
                                </div>
                            </div>
                            <button type="button" @click="clienteModo = 'documento'" class="text-[10px] sm:text-[11px] font-bold text-amber-400 hover:underline">
                                Cambiar a DNI
                            </button>
                        </div>

                        <!-- Si está en modo Documento / Factura -->
                        <div x-show="clienteModo === 'documento' || tipoComprobante === 'FACTURA'" class="space-y-1.5">
                            <div class="flex gap-1.5">
                                <div class="relative flex-1">
                                    <input type="text" x-model="clienteDocumento" id="inputDocumentoPos"
                                           @input="autoConsultarDocumento()"
                                           @keydown.enter.prevent="consultarApiDocumento()"
                                           :placeholder="tipoComprobante === 'FACTURA' ? 'RUC de 11 dígitos' : 'DNI de 8 dígitos'"
                                           :maxlength="tipoComprobante === 'FACTURA' ? 11 : 11"
                                           class="w-full pl-2.5 pr-8 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-black text-slate-800 focus:outline-none focus:border-emerald-500">
                                    <span x-show="consultandoApi" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-amber-400 text-xs">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                                <button type="button" @click="consultarApiDocumento()" :disabled="consultandoApi || !clienteDocumento"
                                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-xs transition disabled:opacity-50 flex items-center gap-1">
                                    <i class="fas fa-search text-[10px]"></i>
                                    <span x-text="tipoComprobante === 'FACTURA' ? 'SUNAT' : 'RENIEC'"></span>
                                </button>
                            </div>

                            <!-- Feedback de Consulta -->
                            <template x-if="apiFeedback">
                                <div class="text-[10px] px-2 py-0.5 rounded-lg flex items-center gap-1 leading-tight"
                                     :class="apiFeedback.tipo === 'success' ? 'bg-emerald-100/80 text-emerald-800' : 'bg-amber-100/80 text-amber-800'">
                                    <i :class="apiFeedback.tipo === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle'" class="text-[9px]"></i>
                                    <span class="truncate" x-text="apiFeedback.mensaje"></span>
                                </div>
                            </template>

                            <input type="text" x-model="clienteNombre" 
                                   :placeholder="tipoComprobante === 'FACTURA' ? 'Razón Social' : 'Nombre del Cliente'"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-emerald-500">

                            <div x-show="tipoComprobante === 'FACTURA' || clienteDireccion">
                                <input type="text" x-model="clienteDireccion" placeholder="Dirección Fiscal (opcional)"
                                       class="w-full px-2.5 py-1 bg-white border border-slate-300 rounded-xl text-[10px] text-slate-600 focus:outline-none focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Totales Compacto -->
                    <div class="bg-slate-900 text-white rounded-2xl p-2.5 sm:p-3 flex justify-between items-center shadow-md">
                        <div>
                            <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total a Cobrar</span>
                            <p class="text-xl sm:text-2xl font-black text-emerald-400 leading-none mt-0.5" x-text="`{{ $moneda }} ${total.toFixed(2)}`"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] sm:text-[11px] bg-white/10 px-2 py-0.5 rounded-full text-slate-300 font-bold" x-text="`${carrito.length} productos`"></span>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: Forma de Pago & Monto -->
                <div class="space-y-2.5 sm:space-y-3 flex flex-col justify-between">
                    
                    <!-- Selector de Forma de Pago -->
                    <div>
                        <label class="block text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1">3. Forma de Pago</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="formaPago = 'efectivo'"
                                    :class="formaPago === 'efectivo' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-money-bill-wave text-xs sm:text-sm"></i>
                                <span class="text-[10px] sm:text-xs">Efectivo</span>
                            </button>
                            <button type="button" @click="formaPago = 'yape'"
                                    :class="formaPago === 'yape' ? 'bg-purple-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-mobile-alt text-xs sm:text-sm"></i>
                                <span class="text-[10px] sm:text-xs">Yape/Plin</span>
                            </button>
                            <button type="button" @click="formaPago = 'tarjeta'"
                                    :class="formaPago === 'tarjeta' ? 'bg-blue-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-credit-card text-xs sm:text-sm"></i>
                                <span class="text-[10px] sm:text-xs">Tarjeta</span>
                            </button>
                            <button type="button" @click="formaPago = 'transferencia'"
                                    :class="formaPago === 'transferencia' ? 'bg-slate-800 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-1.5 sm:py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-university text-xs sm:text-sm"></i>
                                <span class="text-[10px] sm:text-xs">Transfer.</span>
                            </button>
                        </div>
                    </div>

                    <!-- Detalle de Pago en Efectivo (Billetes rápidos y vuelto) -->
                    <div x-show="formaPago === 'efectivo'" class="bg-slate-50 border border-slate-200 p-2.5 sm:p-3 rounded-2xl space-y-1.5 sm:space-y-2 flex-1">
                        <div class="flex justify-between items-center">
                            <label class="text-[10px] sm:text-[11px] font-extrabold text-slate-700 uppercase">Monto Recibido</label>
                            <button type="button" @click="montoRecibido = total; playBeep()" class="text-[10px] sm:text-[11px] font-bold text-amber-400 hover:underline">
                                Monto Exacto
                            </button>
                        </div>
                        <input type="number" step="0.01" x-model.number="montoRecibido" id="inputMontoRecibido"
                               @keydown.enter.prevent="procesarVenta()"
                               class="w-full px-2.5 py-1 text-xl sm:text-2xl font-black text-center text-slate-800 bg-white border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500">

                        <!-- Billetes Rápidos -->
                        <div class="grid grid-cols-5 gap-1">
                            <template x-for="m in [10, 20, 50, 100, 200]" :key="m">
                                <button type="button" @click="montoRecibido = m; playBeep()" 
                                        class="py-1 bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 rounded-lg text-[11px] sm:text-xs font-black text-slate-700 transition"
                                        x-text="`S/${m}`"></button>
                            </template>
                        </div>

                        <!-- Recuadro Vuelto / Cambio -->
                        <div class="bg-emerald-100 border border-emerald-300 rounded-xl px-2.5 py-1.5 flex justify-between items-center shadow-inner">
                            <span class="text-emerald-900 font-extrabold text-[11px] sm:text-xs uppercase">Vuelto / Cambio:</span>
                            <span class="text-xl sm:text-2xl font-black text-emerald-700" x-text="`{{ $moneda }} ${cambio.toFixed(2)}`"></span>
                        </div>
                    </div>

                    <!-- Detalle Referencia para Medios Digitales -->
                    <div x-show="formaPago !== 'efectivo'" class="bg-slate-50 border border-slate-200 p-2.5 sm:p-3 rounded-2xl space-y-1.5 flex-1">
                        <label class="block text-[10px] sm:text-[11px] font-extrabold text-slate-700 uppercase">N° Operación / Referencia</label>
                        <input type="text" x-model="referenciaPago" placeholder="Ej: 984521 o N° Voucher"
                               @keydown.enter.prevent="procesarVenta()"
                               class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-emerald-500">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-2 text-[10px] text-blue-700 flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-xs"></i>
                            <span>Cobro exacto sin cálculo de vuelto.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PIE DE ACCIONES FIJO (STICKY FOOTER - SIEMPRE VISIBLE EN CELULAR) -->
            <div class="p-2.5 sm:p-4 bg-slate-50 border-t border-slate-200 flex gap-2 flex-shrink-0">
                <button type="button" @click="modalPago = false" 
                        class="w-1/3 py-2.5 sm:py-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-2xl font-bold text-xs sm:text-sm transition shadow-xs">
                    Cancelar (Esc)
                </button>
                <button type="button" @click="procesarVenta()" :disabled="procesando || (formaPago === 'efectivo' && montoRecibido < total)"
                        class="w-2/3 py-2.5 sm:py-3 gradient-primary text-white rounded-2xl font-extrabold text-xs sm:text-sm shadow-lg shadow-emerald-500/25 hover:brightness-105 transition active:scale-98 disabled:opacity-50 flex items-center justify-center gap-2">
                    <span x-show="!procesando" class="flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-sm sm:text-base"></i>
                        <span>Emitir y Cobrar (Enter)</span>
                    </span>
                    <span x-show="procesando" class="flex items-center gap-1.5">
                        <i class="fas fa-spinner fa-spin text-sm sm:text-base"></i>
                        <span>Procesando...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

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
                        <i class="fas fa-check-circle text-amber-400"></i>
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

    <!-- MODAL ESCÁNER CÁMARA FULLSCREEN — Control 100% JS nativo, sin Alpine.js -->
    <div id="pos-escaner-modal"
         style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: #000; z-index: 2147483647; flex-direction: column; justify-content: space-between; overflow: hidden;">
        
        <!-- Stream de Video de la Cámara en Pantalla Completa -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden;">
            <video id="pos-cam-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
        </div>

        <!-- Máscara con Marco Central Verde Traslúcido (Estilo Escáner Nativo) -->
        <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center">
            <!-- Fondo Oscurecido Superior -->
            <div class="w-full flex-1 bg-black/60 backdrop-blur-[1px]"></div>
            
            <div class="w-full flex items-center justify-center">
                <!-- Fondo Izquierda -->
                <div class="flex-1 h-56 bg-black/60 backdrop-blur-[1px]"></div>
                
                <!-- Marco Central de Escaneo -->
                <div class="w-72 h-56 border-2 border-emerald-400/90 rounded-3xl relative shadow-[0_0_20px_rgba(52,211,153,0.4)] flex items-center justify-center">
                    <!-- Esquinas estilizadas -->
                    <div class="absolute top-2 left-2 w-4 h-4 border-t-2 border-l-2 border-white rounded-tl-lg"></div>
                    <div class="absolute top-2 right-2 w-4 h-4 border-t-2 border-r-2 border-white rounded-tr-lg"></div>
                    <div class="absolute bottom-2 left-2 w-4 h-4 border-b-2 border-l-2 border-white rounded-bl-lg"></div>
                    <div class="absolute bottom-2 right-2 w-4 h-4 border-b-2 border-r-2 border-white rounded-br-lg"></div>
                    
                    <!-- Línea láser de escaneo animada -->
                    <div class="w-[90%] h-0.5 bg-gradient-to-r from-emerald-400/20 via-emerald-400 to-emerald-400/20 animate-pulse shadow-md shadow-emerald-400"></div>
                </div>

                <!-- Fondo Derecha -->
                <div class="flex-1 h-56 bg-black/60 backdrop-blur-[1px]"></div>
            </div>

            <!-- Fondo Oscurecido Inferior -->
            <div class="w-full flex-1 bg-black/60 backdrop-blur-[1px]"></div>
        </div>

        <!-- Barra Superior de Controles -->
        <div class="relative z-10 p-4 pt-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-2 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span id="pos-cam-status" class="text-xs font-bold tracking-wide">Iniciando cámara...</span>
            </div>
        </div>

        <!-- Barra Inferior: Linterna + Cerrar -->
        <div class="relative z-10 p-6 pb-10 flex items-center justify-center gap-8">
            <!-- Linterna -->
            <button type="button" 
                    id="btn-linterna"
                    onclick="POS_ToggleLinterna()"
                    class="w-14 h-14 rounded-full flex items-center justify-center shadow-2xl transition active:scale-90 border-2 border-white/40 bg-white text-slate-900">
                <i class="fas fa-lightbulb text-xl"></i>
            </button>
            <!-- Cerrar -->
            <button type="button" 
                    onclick="POS_CerrarCamara()"
                    class="w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transition active:scale-90 border-2 border-red-400/60 bg-red-600 text-white">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <!-- Botón X esquina superior derecha también -->
        <button type="button" 
                onclick="POS_CerrarCamara()"
                style="position: absolute; top: 1rem; right: 1rem; width: 3rem; height: 3rem; border-radius: 9999px; background-color: rgba(0,0,0,0.6); color: white; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; z-index: 30;">
            <i class="fas fa-times text-lg"></i>
        </button>

    </div>
</div>
@endsection

@section('scripts')
<script>
// =============================================================
// 🔊 SINTETIZADOR DE EFECTOS DE AUDIO POS (WEB AUDIO API NATIVO)
// =============================================================
const AudioPOS = {
    ctx: null,
    muted: localStorage.getItem('pos_muted') === 'true',
    init() {
        if (!this.ctx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.ctx = new AudioContext();
        }
    },
    toggleMute() {
        this.muted = !this.muted;
        localStorage.setItem('pos_muted', this.muted);
        return this.muted;
    },
    beep(freq = 880, type = 'sine', duration = 0.08, vol = 1.0) {
        if (this.muted) return;
        try {
            this.init();
            if (this.ctx.state === 'suspended') this.ctx.resume();
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            // Hacer el sonido considerablemente más fuerte (1.0 es el máximo sin distorsión severa)
            gain.gain.setValueAtTime(vol, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + duration);
            osc.connect(gain);
            gain.connect(this.ctx.destination);
            osc.start();
            osc.stop(this.ctx.currentTime + duration);
        } catch(e) {}
    },
    success() {
        if (this.muted) return;
        try {
            this.init();
            if (this.ctx.state === 'suspended') this.ctx.resume();
            // Sonido clásico y satisfactorio de Caja Registradora (Ka-Ching)
            const now = this.ctx.currentTime;
            [523.25, 659.25, 783.99, 1046.50].forEach((freq, i) => {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(freq, now + (i * 0.06));
                gain.gain.setValueAtTime(0.18, now + (i * 0.06));
                gain.gain.exponentialRampToValueAtTime(0.001, now + (i * 0.06) + 0.28);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start(now + (i * 0.06));
                osc.stop(now + (i * 0.06) + 0.32);
            });
        } catch(e) {}
    },
    warning() {
        if (this.muted) return;
        try {
            this.init();
            if (this.ctx.state === 'suspended') this.ctx.resume();
            const now = this.ctx.currentTime;
            [340, 260].forEach((freq, i) => {
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(freq, now + (i * 0.09));
                gain.gain.setValueAtTime(0.1, now + (i * 0.09));
                gain.gain.exponentialRampToValueAtTime(0.001, now + (i * 0.09) + 0.14);
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.start(now + (i * 0.09));
                osc.stop(now + (i * 0.09) + 0.16);
            });
        } catch(e) {}
    }
};

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
        modalPostVenta: false,
        ultimaVenta: null,
        telefonoWhatsApp: '',
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
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') { 
                    e.preventDefault(); 
                    if (this.carrito.length > 0) this.abrirPago(); 
                }
                if (e.key === 'Escape') { 
                    // Escape cierra el escáner primero, luego el modal de pago
                    if (window.POS_EscanerActivo) { window.POS_CerrarCamara(); return; }
                    if (this.modalPostVenta) { this.cerrarPostVenta(); return; }
                    if (this.modalPago) this.modalPago = false; 
                }
            });

            // Escuchar eventos globales del escáner puro JS
            window.addEventListener('pos-producto-escaneado', (e) => {
                if (e.detail && e.detail.producto) {
                    this.agregarProducto(e.detail.producto);
                }
            });
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && this.modalPostVenta) {
                    e.preventDefault();
                    this.cerrarPostVenta();
                }
            });
        },

        // --- Resto del POS ---

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
            const stockActual = parseFloat(p.stock);
            const existing = this.carrito.find(i => i.producto_id === p.id);
            const cantidadDeseada = existing ? existing.cantidad + 1 : 1;

            if (stockActual < cantidadDeseada) {
                AudioPOS.warning();
                Toast.fire({ icon: 'error', title: `Sin Stock. Solo quedan ${stockActual} disponibles.` });
                return;
            }

            if (existing) {
                existing.cantidad++;
            } else {
                this.carrito.push({
                    producto_id: p.id,
                    codigo: p.codigo,
                    nombre: p.nombre,
                    cantidad: 1,
                    precio_unitario: parseFloat(p.precio_venta),
                    stock: stockActual,
                });
            }
            AudioPOS.beep(950, 'sine', 0.05);
            this.busqueda = '';
            this.actualizarTotal();
        },

        cambiarCantidad(idx, delta) {
            const item = this.carrito[idx]; 
            const nuevaCantidad = item.cantidad + delta; 
            
            if (delta > 0 && item.stock < nuevaCantidad) { 
                AudioPOS.warning(); 
                Toast.fire({ icon: 'error', title: `Límite de stock alcanzado (${item.stock})` }); 
                return; 
            } 
            
            item.cantidad = nuevaCantidad;
            
            if (this.carrito[idx].cantidad <= 0) {
                this.quitarItem(idx);
            } else {
                AudioPOS.beep(800, 'sine', 0.04, 1.0);
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

                    // La apertura del ticket ahora es controlada por el Modal Post-Venta
                    
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

                    this.ultimaVenta = {
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
                    }, 100);
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
        },

        abrirTicket(formato) {
            if (!this.ultimaVenta) return;
            let url = this.ultimaVenta.url_ticket;
            if (formato === 'a4') {
                url = url.replace('/ticket', '/pdf');
            }
            window.open(url, '_blank');
        },

        enviarWhatsApp() {
            if (!this.telefonoWhatsApp || this.telefonoWhatsApp.length < 9) {
                Toast.fire({ icon: 'warning', title: 'Ingrese un número válido' });
                return;
            }
            
            // Enviamos siempre la versión PDF en formato Ticket por WhatsApp
            let pdfUrl = this.ultimaVenta.url_ticket.replace('/ticket', '/ticket-pdf');
            const url = pdfUrl.startsWith('http') ? pdfUrl : window.location.origin + pdfUrl;
            
            const mensaje = `¡Hola! 👋 Gracias por tu compra en nuestro Minimarket.

Aquí tienes tu comprobante electrónico *${this.ultimaVenta.numero_ticket}* por el total de *S/ ${this.ultimaVenta.total.toFixed(2)}*.

Puedes verlo y descargarlo en formato PDF aquí: ${url}`;
            const link = `https://wa.me/51${this.telefonoWhatsApp.replace(/\s+/g,'')}?text=${encodeURIComponent(mensaje)}`;
            window.open(link, '_blank');
        },

        cerrarPostVenta() {
            this.modalPostVenta = false;
            this.ultimaVenta = null;
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

window.POS_AbrirCamara = async function(event) {
    if (event) { event.preventDefault(); event.stopPropagation(); }

    // No abrir si el modal de pago está activo (detectar vía DOM puro)
    const modalPago = document.getElementById('modal-pago'); 
    if (modalPago && window.getComputedStyle(modalPago).display !== 'none') {
        return;
    }

    // Si ya está activo, cerrar
    if (window.POS_EscanerActivo) {
        window.POS_CerrarCamara();
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        Swal.fire({ icon: 'warning', title: 'No soportado', text: 'Tu navegador no soporta acceso a cámara. Usa Chrome en Android.' });
        return;
    }

    // ABRIR MODAL PRIMERO
    const modal = document.getElementById('pos-escaner-modal');
    if (!modal) {
        alert("DEBUG: Modal NO encontrado en el DOM");
        return;
    }
    
    // Mover el modal al final del body para evitar problemas de z-index con otros contenedores
    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    modal.style.display = 'flex';
    document.getElementById('pos-cam-status').textContent = 'Iniciando cámara...';
    window.POS_EscanerActivo = true;

    try {
        let stream;
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false
            });
        } catch(e1) {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        }

        window.POS_MediaStream = stream;

        const video = document.getElementById('pos-cam-video');
        if (!video) { 
            alert("DEBUG: Video NO encontrado");
            window.POS_CerrarCamara(); 
            return; 
        }

        video.muted = true;
        video.srcObject = stream;
        video.setAttribute('playsinline', 'true'); // Forzar playsinline por si acaso

        video.onloadedmetadata = function() {
            video.play().then(() => {
                document.getElementById('pos-cam-status').textContent = 'Apunta al código de barras';
                window.POS_IniciarDetector(video);
            }).catch((err) => {
                alert("DEBUG: play() falló - " + err.message);
                window.POS_IniciarDetector(video);
            });
        };

        // Si onloadedmetadata nunca dispara (bug de algunos Androids), forzamos play después de 1s
        setTimeout(() => {
            if (video.readyState === 0) {
                alert("DEBUG: onloadedmetadata nunca disparó, forzando play()");
                video.play().catch(e => console.log(e));
                window.POS_IniciarDetector(video);
            }
        }, 1000);

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

    // Buscar producto y agregar al carrito
    const statusEl = document.getElementById('pos-cam-status');
    try {
        const res  = await fetch(`/api/productos/buscar?q=${encodeURIComponent(codigo)}`);
        const lista = await res.json();
        if (lista && lista.length > 0) {
            // Despachar evento para que Alpine lo reciba y actualice el carrito
            window.dispatchEvent(new CustomEvent('pos-producto-escaneado', { 
                detail: { producto: lista[0] } 
            }));
            
            statusEl.textContent = `✅ ${lista[0].nombre} (Agregado)`;
            statusEl.style.color = '#4ade80'; // Verde
            Toast.fire({ icon: 'success', title: `✅ ${lista[0].nombre}` });
        } else {
            statusEl.textContent = `❌ No encontrado: ${codigo}`;
            statusEl.style.color = '#f87171'; // Rojo
            Toast.fire({ icon: 'warning', title: `Código no encontrado: ${codigo}` });
        }
    } catch(e) {
        statusEl.textContent = '❌ Error de conexión';
        statusEl.style.color = '#f87171'; // Rojo
        Toast.fire({ icon: 'error', title: 'Error al buscar el producto' });
    }

    // Reanudar detección después de 1.5 segundos
    setTimeout(() => {
        if (window.POS_EscanerActivo) {
            statusEl.textContent = 'Apunta al código de barras';
            statusEl.style.color = 'white';
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
</script>
@endsection

