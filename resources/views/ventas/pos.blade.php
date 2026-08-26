@extends('layouts.app')
@section('title', 'Punto de Venta')
@section('header', 'Punto de Venta')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div x-data="pos()" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Panel Productos -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Búsqueda -->
            <div class="bg-white rounded-2xl shadow-md p-4">
                <div class="flex gap-3 items-center">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" x-model="busqueda" @input.debounce.300ms="buscarProductos()"
                               @keydown.enter.prevent="agregarPrimero()"
                               class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-700 font-medium"
                               placeholder="Buscar producto por nombre, código o código de barras (Enter para agregar)" autofocus>
                    </div>
                    <button type="button" @click="busqueda = ''; productosFiltrados = productosDestacados" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 rounded-xl transition text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                    <!-- Toggle Sonido -->
                    <button type="button" @click="toggleSonido()" :title="sonidoSilenciado ? 'Activar Sonidos POS' : 'Silenciar Sonidos POS'"
                            class="px-3.5 py-3 rounded-xl transition text-sm font-bold flex items-center gap-1.5"
                            :class="sonidoSilenciado ? 'bg-slate-100 text-slate-400' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'">
                        <i :class="sonidoSilenciado ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                    </button>
                </div>
            </div>

            <!-- Categorías rápidas -->
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button type="button" @click="filtrarCategoria(null)" :class="categoriaActiva === null ? 'bg-emerald-500 text-white shadow-emerald-200' : 'bg-white text-slate-700 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap shadow-sm transition">
                    <i class="fas fa-th-large mr-1.5"></i>Todos
                </button>
                @foreach($categorias as $cat)
                    <button type="button" @click="filtrarCategoria({{ $cat->id }})"
                            :class="categoriaActiva === {{ $cat->id }} ? 'text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-50'"
                            :style="categoriaActiva === {{ $cat->id }} ? 'background: {{ $cat->color }}' : ''"
                            class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap shadow-sm transition">
                        <i class="fas fa-{{ $cat->icono }} mr-1.5"></i>{{ $cat->nombre }}
                    </button>
                @endforeach
            </div>

            <!-- Grid Productos -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <template x-for="p in productosFiltrados" :key="p.id">
                    <button type="button" @click="agregarProducto(p)"
                            class="bg-white rounded-2xl shadow-sm hover:shadow-md transition transform hover:-translate-y-1 p-3 text-left border border-slate-100 flex flex-col justify-between">
                        <div>
                            <div class="aspect-square bg-slate-100 rounded-xl mb-2 flex items-center justify-center overflow-hidden">
                                <template x-if="p.imagen">
                                    <img :src="`/uploads/productos/${p.imagen}`" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.imagen">
                                    <i class="fas fa-box text-3xl text-slate-300"></i>
                                </template>
                            </div>
                            <p class="text-xs font-bold text-slate-800 line-clamp-2 mb-1" x-text="p.nombre"></p>
                        </div>
                        <div class="flex justify-between items-end mt-2 pt-2 border-t border-slate-50">
                            <span class="text-[11px] font-medium text-slate-400" x-text="`Stock: ${parseFloat(p.stock).toFixed(0)}`"></span>
                            <span class="text-emerald-600 font-extrabold text-sm" x-text="`{{ $moneda }} ${parseFloat(p.precio_venta).toFixed(2)}`"></span>
                        </div>
                    </button>
                </template>
                <div x-show="productosFiltrados.length === 0" class="col-span-full text-center py-16 text-slate-400 bg-white rounded-2xl">
                    <i class="fas fa-search text-5xl mb-3 text-slate-300"></i>
                    <p class="font-medium">No se encontraron productos coincidentes</p>
                </div>
            </div>
        </div>

        <!-- Panel Carrito (Limpio y 100% enfocado en productos) -->
        <div class="bg-white rounded-2xl shadow-md flex flex-col" style="max-height: calc(100vh - 120px);">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center gradient-primary text-white rounded-t-2xl">
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
                                <p class="font-extrabold text-sm text-emerald-600" x-text="`{{ $moneda }} ${(item.cantidad * item.precio_unitario).toFixed(2)}`"></p>
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
                <div class="flex justify-between items-baseline text-2xl font-black text-emerald-600 pt-2 border-t border-slate-200">
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
    <div x-show="modalPago" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-2 sm:p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden border border-slate-100 transform transition-all" @click.outside="modalPago = false">
            
            <!-- Encabezado Compacto -->
            <div class="gradient-primary text-white px-5 py-3 flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-base">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold leading-tight">Procesar Venta y Comprobante</h3>
                        <p class="text-[11px] text-emerald-100 font-medium">Turno #{{ $turnoActivo->id }} • {{ $turnoActivo->caja->nombre }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="toggleSonido()" :title="sonidoSilenciado ? 'Activar Sonidos' : 'Silenciar Sonidos'"
                            class="text-white/80 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition text-sm">
                        <i :class="sonidoSilenciado ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                    </button>
                    <button type="button" @click="modalPago = false" class="text-white/80 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Contenido en Grid 2 Columnas (Cero Scroll) -->
            <div class="p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- COLUMNA IZQUIERDA: Comprobante & Cliente -->
                <div class="space-y-3 flex flex-col justify-between">
                    
                    <!-- Selector de Comprobante -->
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">1. Tipo de Comprobante</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button type="button" @click="cambiarTipoComprobante('TICKET')"
                                    :class="tipoComprobante === 'TICKET' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20 font-bold' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:bg-slate-100'"
                                    class="border-2 py-2 px-1.5 rounded-xl text-center text-xs transition flex flex-col items-center justify-center">
                                <span class="font-extrabold flex items-center gap-1"><i class="fas fa-receipt text-xs text-emerald-600"></i>Ticket</span>
                                <span class="text-[9px] text-slate-400">Nota Venta</span>
                            </button>

                            <button type="button" @click="cambiarTipoComprobante('BOLETA')"
                                    :class="tipoComprobante === 'BOLETA' ? 'border-blue-500 bg-blue-50 text-blue-800 ring-2 ring-blue-500/20 font-bold' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:bg-slate-100'"
                                    class="border-2 py-2 px-1.5 rounded-xl text-center text-xs transition flex flex-col items-center justify-center">
                                <span class="font-extrabold flex items-center gap-1"><i class="fas fa-file-invoice text-xs text-blue-600"></i>Boleta</span>
                                <span class="text-[9px] text-slate-400">DNI / Varios</span>
                            </button>

                            <button type="button" @click="cambiarTipoComprobante('FACTURA')"
                                    :class="tipoComprobante === 'FACTURA' ? 'border-purple-500 bg-purple-50 text-purple-800 ring-2 ring-purple-500/20 font-bold' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:bg-slate-100'"
                                    class="border-2 py-2 px-1.5 rounded-xl text-center text-xs transition flex flex-col items-center justify-center">
                                <span class="font-extrabold flex items-center gap-1"><i class="fas fa-building text-xs text-purple-600"></i>Factura</span>
                                <span class="text-[9px] text-slate-400">RUC 11 Díg.</span>
                            </button>
                        </div>
                    </div>

                    <!-- Panel de Cliente -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3 space-y-2.5 flex-1">
                        <div class="flex justify-between items-center">
                            <label class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 flex items-center gap-1">
                                <i class="fas fa-user-tag text-emerald-500 text-xs"></i>
                                <span>2. Identificación del Cliente</span>
                            </label>
                            
                            <template x-if="tipoComprobante !== 'FACTURA'">
                                <button type="button" @click="toggleClienteGenerico()" 
                                        class="text-[11px] font-bold px-2 py-0.5 rounded-lg transition"
                                        :class="clienteModo === 'generico' ? 'bg-slate-200 text-slate-700' : 'text-emerald-600 hover:underline'">
                                    <span x-text="clienteModo === 'generico' ? '✓ Modo Varios' : '+ DNI'"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Si está en modo Clientes Varios -->
                        <div x-show="clienteModo === 'generico' && tipoComprobante !== 'FACTURA'" 
                             class="bg-white p-2.5 rounded-xl border border-slate-200 text-xs text-slate-600 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 text-xs">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs leading-tight">CLIENTES VARIOS</p>
                                    <p class="text-[10px] text-slate-400">Venta rápida al público</p>
                                </div>
                            </div>
                            <button type="button" @click="clienteModo = 'documento'" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                Cambiar a DNI
                            </button>
                        </div>

                        <!-- Si está en modo Documento / Factura -->
                        <div x-show="clienteModo === 'documento' || tipoComprobante === 'FACTURA'" class="space-y-2">
                            <div class="flex gap-1.5">
                                <div class="relative flex-1">
                                    <input type="text" x-model="clienteDocumento" id="inputDocumentoPos"
                                           @input="autoConsultarDocumento()"
                                           @keydown.enter.prevent="consultarApiDocumento()"
                                           :placeholder="tipoComprobante === 'FACTURA' ? 'RUC de 11 dígitos' : 'DNI de 8 dígitos'"
                                           :maxlength="tipoComprobante === 'FACTURA' ? 11 : 11"
                                           class="w-full pl-2.5 pr-8 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-black text-slate-800 focus:outline-none focus:border-emerald-500">
                                    <span x-show="consultandoApi" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-emerald-600 text-xs">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                                <button type="button" @click="consultarApiDocumento()" :disabled="consultandoApi || !clienteDocumento"
                                        class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-xs transition disabled:opacity-50 flex items-center gap-1">
                                    <i class="fas fa-search text-[10px]"></i>
                                    <span x-text="tipoComprobante === 'FACTURA' ? 'SUNAT' : 'RENIEC'"></span>
                                </button>
                            </div>

                            <!-- Feedback de Consulta -->
                            <template x-if="apiFeedback">
                                <div class="text-[11px] px-2 py-1 rounded-lg flex items-center gap-1.5 leading-tight"
                                     :class="apiFeedback.tipo === 'success' ? 'bg-emerald-100/80 text-emerald-800' : 'bg-amber-100/80 text-amber-800'">
                                    <i :class="apiFeedback.tipo === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle'" class="text-[10px]"></i>
                                    <span class="truncate" x-text="apiFeedback.mensaje"></span>
                                </div>
                            </template>

                            <input type="text" x-model="clienteNombre" 
                                   :placeholder="tipoComprobante === 'FACTURA' ? 'Razón Social' : 'Nombre del Cliente'"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-emerald-500">

                            <div x-show="tipoComprobante === 'FACTURA' || clienteDireccion">
                                <input type="text" x-model="clienteDireccion" placeholder="Dirección Fiscal (opcional)"
                                       class="w-full px-2.5 py-1 bg-white border border-slate-300 rounded-xl text-[11px] text-slate-600 focus:outline-none focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Totales Compacto -->
                    <div class="bg-slate-900 text-white rounded-2xl p-3 flex justify-between items-center shadow-md">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total a Cobrar</span>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-400 leading-none mt-0.5" x-text="`{{ $moneda }} ${total.toFixed(2)}`"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] bg-white/10 px-2.5 py-1 rounded-full text-slate-300 font-bold" x-text="`${carrito.length} productos`"></span>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: Forma de Pago & Monto & Confirmación -->
                <div class="space-y-3 flex flex-col justify-between">
                    
                    <!-- Selector de Forma de Pago -->
                    <div>
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">3. Forma de Pago</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="formaPago = 'efectivo'"
                                    :class="formaPago === 'efectivo' ? 'bg-emerald-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-money-bill-wave text-sm"></i>
                                <span>Efectivo</span>
                            </button>
                            <button type="button" @click="formaPago = 'yape'"
                                    :class="formaPago === 'yape' ? 'bg-purple-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-mobile-alt text-sm"></i>
                                <span>Yape/Plin</span>
                            </button>
                            <button type="button" @click="formaPago = 'tarjeta'"
                                    :class="formaPago === 'tarjeta' ? 'bg-blue-600 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-credit-card text-sm"></i>
                                <span>Tarjeta</span>
                            </button>
                            <button type="button" @click="formaPago = 'transferencia'"
                                    :class="formaPago === 'transferencia' ? 'bg-slate-800 text-white shadow-md font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="py-2 px-1 rounded-xl text-xs transition flex flex-col items-center justify-center gap-0.5">
                                <i class="fas fa-university text-sm"></i>
                                <span>Transfer.</span>
                            </button>
                        </div>
                    </div>

                    <!-- Detalle de Pago en Efectivo (Billetes rápidos y vuelto) -->
                    <div x-show="formaPago === 'efectivo'" class="bg-slate-50 border border-slate-200 p-3 rounded-2xl space-y-2 flex-1">
                        <div class="flex justify-between items-center">
                            <label class="text-[11px] font-extrabold text-slate-700 uppercase">Monto Recibido</label>
                            <button type="button" @click="montoRecibido = total; playBeep()" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                Monto Exacto
                            </button>
                        </div>
                        <input type="number" step="0.01" x-model.number="montoRecibido" id="inputMontoRecibido"
                               @keydown.enter.prevent="procesarVenta()"
                               class="w-full px-3 py-1.5 text-2xl font-black text-center text-slate-800 bg-white border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500">

                        <!-- Billetes Rápidos -->
                        <div class="grid grid-cols-5 gap-1">
                            <template x-for="m in [10, 20, 50, 100, 200]" :key="m">
                                <button type="button" @click="montoRecibido = m; playBeep()" 
                                        class="py-1 bg-white border border-slate-200 hover:bg-emerald-50 hover:border-emerald-300 rounded-lg text-xs font-black text-slate-700 transition"
                                        x-text="`S/${m}`"></button>
                            </template>
                        </div>

                        <!-- Recuadro Vuelto / Cambio -->
                        <div class="bg-emerald-100 border border-emerald-300 rounded-xl px-3 py-2 flex justify-between items-center shadow-inner">
                            <span class="text-emerald-900 font-extrabold text-xs uppercase">Vuelto / Cambio:</span>
                            <span class="text-2xl font-black text-emerald-700" x-text="`{{ $moneda }} ${cambio.toFixed(2)}`"></span>
                        </div>
                    </div>

                    <!-- Detalle Referencia para Medios Digitales -->
                    <div x-show="formaPago !== 'efectivo'" class="bg-slate-50 border border-slate-200 p-3 rounded-2xl space-y-2 flex-1">
                        <label class="block text-[11px] font-extrabold text-slate-700 uppercase">N° de Operación / Referencia (Opcional)</label>
                        <input type="text" x-model="referenciaPago" placeholder="Ej: 984521 o N° Voucher"
                               @keydown.enter.prevent="procesarVenta()"
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-emerald-500">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-2.5 text-[11px] text-blue-700 flex items-center gap-2">
                            <i class="fas fa-info-circle text-xs"></i>
                            <span>El monto total se cobrará exactamente sin cálculo de vuelto.</span>
                        </div>
                    </div>

                    <!-- Botones de Acción Final -->
                    <div class="flex gap-2 pt-1">
                        <button type="button" @click="modalPago = false" 
                                class="w-1/3 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition">
                            Cancelar (Esc)
                        </button>
                        <button type="button" @click="procesarVenta()" :disabled="procesando || (formaPago === 'efectivo' && montoRecibido < total)"
                                class="w-2/3 py-3 gradient-primary text-white rounded-xl font-extrabold text-sm shadow-lg shadow-emerald-500/25 hover:brightness-105 transition disabled:opacity-50 flex items-center justify-center gap-2">
                            <span x-show="!procesando" class="flex items-center gap-1.5">
                                <i class="fas fa-check-circle text-base"></i>
                                <span>Emitir y Cobrar (Enter)</span>
                            </span>
                            <span x-show="procesando" class="flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin text-base"></i>
                                <span>Procesando...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
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
    beep(freq = 880, type = 'sine', duration = 0.08) {
        if (this.muted) return;
        try {
            this.init();
            if (this.ctx.state === 'suspended') this.ctx.resume();
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime);
            gain.gain.setValueAtTime(0.12, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + duration);
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
        productosDestacados: @json($productos),
        productosFiltrados: @json($productos),
        busqueda: '',
        categoriaActiva: null,
        carrito: [],
        
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
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') { 
                    e.preventDefault(); 
                    if (this.carrito.length > 0) this.abrirPago(); 
                }
                if (e.key === 'Escape') { 
                    if (this.modalPago) this.modalPago = false; 
                }
            });
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
</script>
@endsection
