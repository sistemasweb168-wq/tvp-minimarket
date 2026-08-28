@extends('layouts.app')
@section('title', 'Manual de Usuario')
@section('header', 'Manual Oficial de Usuario & Guía Operativa')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Encabezado con Botón de Descarga PDF -->
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-emerald-600 rounded-3xl p-6 sm:p-8 text-slate-950 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="px-3 py-1 bg-slate-950/20 text-slate-950 font-black text-xs rounded-full uppercase tracking-wider">Documentación Oficial v2.0</span>
            <h2 class="text-2xl sm:text-3xl font-black mt-2 text-slate-950">Manual de Usuario & Operaciones</h2>
            <p class="text-xs sm:text-sm font-semibold text-slate-900 mt-1 max-w-xl">
                Guía completa módulo por módulo para la administración y atención en mostrador de {{ $empresa->nombre_comercial ?? 'tu Licorería & Minimarket' }}.
            </p>
        </div>

        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('manual.pdf') }}" target="_blank" class="px-5 py-3 bg-slate-950 hover:bg-slate-900 text-white font-black rounded-2xl text-xs sm:text-sm shadow-xl transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-file-pdf text-rose-500 text-base sm:text-lg"></i>
                <span>Descargar Manual en PDF</span>
            </a>
            <button onclick="window.print()" class="px-4 py-3 bg-white/30 hover:bg-white/40 text-slate-950 font-bold rounded-2xl text-xs sm:text-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-print text-sm sm:text-base"></i>
                <span>Imprimir</span>
            </button>
        </div>
    </div>

    <!-- Navegación por Capítulos en Pestañas -->
    <div x-data="{ tab: 'pos' }" class="space-y-6">
        
        <!-- Pestañas -->
        <div class="flex bg-slate-900 p-1.5 rounded-2xl border border-slate-800 overflow-x-auto shadow-md gap-1">
            <button type="button" @click="tab = 'pos'" :class="tab === 'pos' ? 'bg-amber-500 text-slate-950 font-black shadow' : 'text-slate-400 hover:text-white font-semibold'" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-cash-register"></i> 1. Punto de Venta (POS)
            </button>
            <button type="button" @click="tab = 'productos'" :class="tab === 'productos' ? 'bg-amber-500 text-slate-950 font-black shadow' : 'text-slate-400 hover:text-white font-semibold'" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-beer-mug-empty"></i> 2. Latas & Six-Packs
            </button>
            <button type="button" @click="tab = 'caja'" :class="tab === 'caja' ? 'bg-amber-500 text-slate-950 font-black shadow' : 'text-slate-400 hover:text-white font-semibold'" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-money-bill-wave"></i> 3. Caja & Gastos
            </button>
            <button type="button" @click="tab = 'envases'" :class="tab === 'envases' ? 'bg-amber-500 text-slate-950 font-black shadow' : 'text-slate-400 hover:text-white font-semibold'" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-box-open"></i> 4. Envases & Cascos
            </button>
            <button type="button" @click="tab = 'kardex'" :class="tab === 'kardex' ? 'bg-amber-500 text-slate-950 font-black shadow' : 'text-slate-400 hover:text-white font-semibold'" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-clipboard-list"></i> 5. Mermas & Kardex
            </button>
            <button type="button" @click="tab = 'utilidades'" :class="tab === 'utilidades' ? 'bg-amber-500 text-slate-950 font-black shadow' : 'text-slate-400 hover:text-white font-semibold'" class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition whitespace-nowrap flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar"></i> 6. Utilidad Neta
            </button>
        </div>

        <!-- CONTENIDO TAB 1: POS -->
        <div x-show="tab === 'pos'" x-cloak class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Módulo 1: Punto de Venta Rápido (POS)</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Atención rápida en mostrador con escáner, teclado o pantalla táctil.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl space-y-2">
                    <span class="w-7 h-7 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">1</span>
                    <h4 class="font-bold text-white text-sm">Buscar o Escanear</h4>
                    <p class="text-xs text-slate-400">Escribe el nombre del licor o apunta el lector de código de barras a la botella o sixpack.</p>
                </div>
                <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl space-y-2">
                    <span class="w-7 h-7 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">2</span>
                    <h4 class="font-bold text-white text-sm">Elegir Forma de Pago</h4>
                    <p class="text-xs text-slate-400">Presiona <strong>Cobrar (F2)</strong> y elige Efectivo, Yape/Plin, Tarjeta o <strong>Pago Mixto</strong> (ej. mitad efectivo y mitad Yape).</p>
                </div>
                <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl space-y-2">
                    <span class="w-7 h-7 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">3</span>
                    <h4 class="font-bold text-white text-sm">Emitir y Compartir</h4>
                    <p class="text-xs text-slate-400">Imprime el ticket en tu ticketera térmica de 80mm o envíalo en un clic al WhatsApp del cliente.</p>
                </div>
            </div>
        </div>

        <!-- CONTENIDO TAB 2: PRODUCTOS & SIX-PACKS -->
        <div x-show="tab === 'productos'" x-cloak class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-beer-mug-empty"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Módulo 2: Cómo Crear Latas y Six-Packs</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Control de paquetes derivados de productos individuales (Estilo Sistema Abarrotes / Eleventa).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Paso 1 -->
                <div class="bg-slate-800/60 border-2 border-emerald-500/40 p-5 rounded-2xl space-y-3">
                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 font-bold text-xs rounded-full uppercase">Paso 1: Producto Individual</span>
                    <h4 class="text-base font-bold text-white">Registrar la Lata Suelta</h4>
                    <ul class="text-xs text-slate-300 space-y-1.5 list-disc list-inside">
                        <li>Elige <strong>1. Estándar</strong> en el tipo de producto.</li>
                        <li>Nombre: <code>Cerveza Pilsen Lata 355ml</code>.</li>
                        <li>Escanea el código de barras de 1 lata.</li>
                        <li>Ingresa tu <strong>Stock Inicial</strong> (ej. 120 latas).</li>
                        <li>Precio de Venta: S/ 5.00.</li>
                    </ul>
                </div>

                <!-- Paso 2 -->
                <div class="bg-slate-800/60 border-2 border-amber-500/40 p-5 rounded-2xl space-y-3">
                    <span class="px-2.5 py-1 bg-amber-500/20 text-amber-300 font-bold text-xs rounded-full uppercase">Paso 2: Paquete / Six-Pack</span>
                    <h4 class="text-base font-bold text-white">Registrar el Six-Pack / Caja</h4>
                    <ul class="text-xs text-slate-300 space-y-1.5 list-disc list-inside">
                        <li>Elige <strong>2. Paquete / Six-Pack</strong>.</li>
                        <li>Nombre: <code>Six-Pack Cerveza Pilsen</code>.</li>
                        <li>Escanea el código de barras del <strong>cartón del Six-pack</strong>.</li>
                        <li>Selecciona la lata creada en el Paso 1 y pon cantidad: <strong>6</strong>.</li>
                        <li>Precio del Pack: S/ 26.00.</li>
                    </ul>
                </div>
            </div>
            
            <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-xs text-amber-300">
                <i class="fas fa-check-circle mr-1 text-sm"></i>
                <strong>Descuento Automático:</strong> Al vender 1 Six-pack en el POS, el sistema descuenta <strong>6 latas</strong> del inventario. ¡El Six-pack nunca se desconfigura ni requiere stock manual!
            </div>
        </div>

        <!-- CONTENIDO TAB 3: CAJA -->
        <div x-show="tab === 'caja'" x-cloak class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Módulo 3: Caja Registradora, Gastos y Cuadres</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Control de apertura, registro de salidas de dinero menores y arqueo de cierre.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-300">
                <div class="p-4 bg-slate-800 rounded-2xl border border-slate-700 space-y-2">
                    <h4 class="font-bold text-white text-sm flex items-center gap-2"><i class="fas fa-receipt text-rose-400"></i> Botón de "Gasto" en el POS</h4>
                    <p>Si durante el turno compras hielo, limones o pagas un almuerzo, presiona el botón <strong>"Gasto"</strong> en la parte superior del POS para registrar la salida de dinero en 5 segundos.</p>
                </div>
                <div class="p-4 bg-slate-800 rounded-2xl border border-slate-700 space-y-2">
                    <h4 class="font-bold text-white text-sm flex items-center gap-2"><i class="fas fa-balance-scale text-emerald-400"></i> Cuadre de Caja de Cierre</h4>
                    <p>El sistema calcula el efectivo exacto: <code>Apertura + Ventas Efectivo + Garantías - Gastos = Total Esperado</code>. Al cerrar el turno, te indicará si la caja está perfectamente cuadrada.</p>
                </div>
            </div>
        </div>

        <!-- CONTENIDO TAB 4: ENVASES RETORNABLES -->
        <div x-show="tab === 'envases'" x-cloak class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-box-open"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Módulo 4: Envases Retornables y Garantías (Cascos)</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Préstamo de botellas de cerveza de 620ml y cajas con garantía en efectivo.</p>
                </div>
            </div>

            <div class="space-y-3 text-xs text-slate-300">
                <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center flex-shrink-0">1</div>
                    <div>
                        <strong class="text-white">Préstamo con Garantía:</strong> Si el cliente no trae botellas vacías, ve a <strong>Envases & Cascos > Prestar Envases</strong> y registra la salida con garantía de S/ 20. El dinero ingresa en custodia a tu caja.
                    </div>
                </div>
                <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center flex-shrink-0">2</div>
                    <div>
                        <strong class="text-white">Recepción y Reembolso:</strong> Cuando el cliente regrese las botellas vacías, presiona <strong>"Recibir & Reembolsar"</strong>. El sistema devolverá los S/ 20 de la caja y marcará los envases como recuperados.
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO TAB 5: KARDEX Y MERMAS -->
        <div x-show="tab === 'kardex'" x-cloak class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Módulo 5: Kardex y Registro de Botellas Rotas / Mermas</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Auditoría completa de movimientos y bajas por accidentes o vencimiento.</p>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-slate-300">
                Si se rompe una botella en el mostrador o se vence un producto, entra a <strong>Inventario > Kardex & Mermas</strong> y presiona el botón rojo <strong>"Registrar Merma / Rotura"</strong>. El sistema descontará el stock de inmediato y guardará el registro contable para no afectar tus márgenes.
            </p>
        </div>

        <!-- CONTENIDO TAB 6: UTILIDAD NETA -->
        <div x-show="tab === 'utilidades'" x-cloak class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-md space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Módulo 6: Reporte de Utilidad Neta Real</h3>
                    <p class="text-xs sm:text-sm text-slate-400">Análisis financiero de ganancia limpia en el bolsillo.</p>
                </div>
            </div>

            <div class="p-5 bg-gradient-to-br from-emerald-950/80 to-slate-900 border border-emerald-500/40 rounded-2xl text-xs sm:text-sm text-slate-200 space-y-2">
                <p class="font-bold text-emerald-400 text-base">Fórmula Financiera Real:</p>
                <p class="font-mono bg-slate-950/60 p-3 rounded-xl border border-slate-800 text-amber-300">
                    Utilidad Neta = Ventas Totales - Costo de Compra de Mercadería - Gastos Operativos de Caja
                </p>
                <p class="text-slate-400">
                    Incluye el <strong>Top 10 de Licores Más Rentables</strong> para que sepas qué marcas te dejan más dinero limpio al final del mes.
                </p>
            </div>
        </div>

    </div>

</div>
@endsection
