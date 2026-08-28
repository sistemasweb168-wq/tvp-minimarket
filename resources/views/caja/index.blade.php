@extends('layouts.app')
@section('title', 'Caja')
@section('header', 'Gestión de Caja')

@section('content')
@php 
    $moneda = $empresaGlobal->moneda ?? 'S/'; 
@endphp

@if($turnoActivo)
    @php
        $ventas = $turnoActivo->ventas;
        $ventasEfectivoPuro = $ventas->where('forma_pago', 'efectivo')->sum('total');
        $ventasYapePuro = $ventas->where('forma_pago', 'yape')->sum('total');
        $ventasPlinPuro = $ventas->where('forma_pago', 'plin')->sum('total');
        $ventasTarjetaPuro = $ventas->where('forma_pago', 'tarjeta')->sum('total');
        $ventasTransfPuro = $ventas->where('forma_pago', 'transferencia')->sum('total');

        $mixtasEfectivo = 0;
        $mixtasDigital = 0;
        $cantMixtas = 0;

        foreach ($ventas->where('forma_pago', 'mixto') as $v) {
            $cantMixtas++;
            $dp = is_array($v->detalle_pago) ? $v->detalle_pago : (json_decode($v->detalle_pago, true) ?? []);
            $m1 = $dp['metodo_1'] ?? 'efectivo';
            $cant1 = floatval($dp['monto_1'] ?? 0);
            $m2 = $dp['metodo_2'] ?? 'yape';
            $cant2 = floatval($dp['monto_2'] ?? 0);

            if ($m1 === 'efectivo') $mixtasEfectivo += $cant1;
            else $mixtasDigital += $cant1;

            if ($m2 === 'efectivo') $mixtasEfectivo += $cant2;
            else $mixtasDigital += $cant2;
        }

        $totalEfectivoReal = $ventasEfectivoPuro + $mixtasEfectivo;
        $totalDigitalReal = $ventasYapePuro + $ventasPlinPuro + $ventasTarjetaPuro + $ventasTransfPuro + $mixtasDigital;
        $totalVentas = $ventas->sum('total');

        $totalIngresos = $turnoActivo->movimientos->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $turnoActivo->movimientos->where('tipo', 'egreso')->sum('monto');

        $garantiasCobradas = 0;
        $garantiasDevueltas = 0;
        try {
            if (class_exists(\App\Models\EnvaseGarantia::class) && \Illuminate\Support\Facades\Schema::hasTable('envases_garantias')) {
                $garantiasCobradas = \App\Models\EnvaseGarantia::where('created_at', '>=', $turnoActivo->fecha_apertura)
                    ->where('estado', 'prestado')
                    ->sum('monto_garantia') ?? 0;

                $garantiasDevueltas = \App\Models\EnvaseGarantia::where('fecha_devolucion', '>=', $turnoActivo->fecha_apertura)
                    ->where('estado', 'devuelto')
                    ->sum('monto_garantia') ?? 0;
            }
        } catch (\Throwable $e) {
            $garantiasCobradas = 0;
            $garantiasDevueltas = 0;
        }

        $efectivoEsperado = ($turnoActivo->monto_apertura + $totalEfectivoReal + $totalIngresos + $garantiasCobradas) - ($totalEgresos + $garantiasDevueltas);
    @endphp

    <!-- 1. PANEL PRINCIPAL DEL TURNO ACTIVO -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-xl p-5 sm:p-7 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-5 border-b border-slate-800">
            <div>
                <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full text-xs font-black inline-flex items-center gap-1.5 uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>TURNO DE CAJA EN CURSO (CORTE X)</span>
                </span>
                <h2 class="text-xl sm:text-2xl font-black text-white mt-2">{{ $turnoActivo->caja->nombre ?? 'Caja Principal' }}</h2>
                <p class="text-xs sm:text-sm text-slate-400">
                    Abierto: <strong>{{ $turnoActivo->fecha_apertura->format('d/m/Y H:i') }}</strong> • Cajero: <strong class="text-slate-200">{{ $turnoActivo->user->name }}</strong>
                </p>
            </div>
            
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <a href="{{ route('caja.ticket', $turnoActivo->id) }}" target="_blank" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-bold text-xs sm:text-sm border border-slate-700 transition flex items-center justify-center gap-2">
                    <i class="fas fa-receipt text-amber-400"></i> Corte X (Ticket 80mm)
                </a>
                <button type="button" onclick="document.getElementById('modal-cerrar').classList.remove('hidden')" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-xs sm:text-sm shadow-lg shadow-rose-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fas fa-lock"></i> Cerrar Turno & Arqueo
                </button>
            </div>
        </div>

        <!-- 4 TARJETAS PRINCIPALES DEL ARQUEO EN VIVO -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 my-5">
            <!-- 1. Monto Apertura -->
            <div class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-3.5 sm:p-4">
                <p class="text-[10px] sm:text-xs text-slate-400 font-bold uppercase tracking-wider">Apertura (Sencillo)</p>
                <p class="text-base sm:text-2xl font-black text-white font-mono mt-0.5">{{ $moneda }} {{ number_format($turnoActivo->monto_apertura, 2) }}</p>
            </div>

            <!-- 2. Efectivo Esperado en Cajón -->
            <div class="bg-emerald-950/40 border-2 border-emerald-500/50 rounded-2xl p-3.5 sm:p-4">
                <p class="text-[10px] sm:text-xs text-emerald-400 font-black uppercase tracking-wider flex items-center justify-between">
                    <span>Efectivo en Cajón</span>
                    <i class="fas fa-money-bill-wave"></i>
                </p>
                <p class="text-base sm:text-2xl font-black text-emerald-400 font-mono mt-0.5">{{ $moneda }} {{ number_format($efectivoEsperado, 2) }}</p>
                <span class="text-[10px] text-slate-300 block mt-0.5">Apertura + Ventas Ef. - Egresos</span>
            </div>

            <!-- 3. Cobros Digitales (Yape/Plin/Tarjetas) -->
            <div class="bg-purple-950/40 border border-purple-500/40 rounded-2xl p-3.5 sm:p-4">
                <p class="text-[10px] sm:text-xs text-purple-400 font-bold uppercase tracking-wider flex items-center justify-between">
                    <span>Cobros Digitales</span>
                    <i class="fas fa-qrcode"></i>
                </p>
                <p class="text-base sm:text-2xl font-black text-purple-300 font-mono mt-0.5">{{ $moneda }} {{ number_format($totalDigitalReal, 2) }}</p>
                <span class="text-[10px] text-slate-400 block mt-0.5">Yape + Plin + Tarjetas</span>
            </div>

            <!-- 4. Total Ventas -->
            <div class="bg-amber-950/40 border border-amber-500/40 rounded-2xl p-3.5 sm:p-4">
                <p class="text-[10px] sm:text-xs text-amber-400 font-bold uppercase tracking-wider flex items-center justify-between">
                    <span>Total Ventas ({{ $ventas->count() }})</span>
                    <i class="fas fa-shopping-bag"></i>
                </p>
                <p class="text-base sm:text-2xl font-black text-amber-300 font-mono mt-0.5">{{ $moneda }} {{ number_format($totalVentas, 2) }}</p>
                <span class="text-[10px] text-slate-400 block mt-0.5">Efectivo + Digital + Mixtos</span>
            </div>
        </div>

        <!-- SECCIÓN DE MOVIMIENTOS Y GASTOS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-4 border-t border-slate-800">
            <!-- Registrar Movimiento / Gasto -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 sm:p-5">
                <h3 class="font-bold text-sm sm:text-base text-white mb-3 flex items-center gap-2">
                    <i class="fas fa-arrows-alt-h text-amber-400"></i><span>Registrar Gasto o Movimiento</span>
                </h3>
                <form method="POST" action="{{ route('caja.movimiento', $turnoActivo->id) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <select name="tipo" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs sm:text-sm font-bold text-white outline-none focus:border-amber-500">
                            <option value="egreso">🔴 Salida / Gasto (-)</option>
                            <option value="ingreso">🟢 Ingreso Adicional (+)</option>
                        </select>
                        <input type="number" step="0.01" name="monto" placeholder="Monto (0.00)" required 
                               class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs sm:text-sm font-black text-amber-400 outline-none focus:border-amber-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <select name="categoria" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-300 outline-none focus:border-amber-500">
                            <option value="gastos_operativos">Insumos (Hielo, bolsas, limones)</option>
                            <option value="servicios">Servicios (Luz, agua, internet)</option>
                            <option value="personal">Almuerzos / Personal</option>
                            <option value="proveedor">Pago Menor Proveedor</option>
                            <option value="general">Otros Egresos</option>
                        </select>
                        <input type="text" name="comprobante" placeholder="N° Recibo / Boleta (Opcional)" 
                               class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-300 outline-none focus:border-amber-500">
                    </div>
                    <input type="text" name="concepto" placeholder="Concepto (ej: Compra de 2 bolsas de hielo grande)" required 
                           class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs sm:text-sm text-white font-medium outline-none focus:border-amber-500">
                    <button type="submit" class="w-full gradient-primary text-white py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md hover:brightness-105 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fas fa-plus"></i><span>Registrar Salida de Dinero</span>
                    </button>
                </form>
            </div>

            <!-- Lista de Movimientos -->
            <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 sm:p-5 flex flex-col">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-sm sm:text-base text-white flex items-center gap-2">
                        <i class="fas fa-list text-emerald-400"></i><span>Gastos del Turno ({{ $turnoActivo->movimientos->count() }})</span>
                    </h3>
                    <span class="text-xs font-mono font-bold text-rose-400">Total: -{{ $moneda }} {{ number_format($totalEgresos, 2) }}</span>
                </div>
                <div class="max-h-56 overflow-y-auto space-y-2 flex-1 pr-1">
                    @forelse($turnoActivo->movimientos as $m)
                        <div class="flex justify-between items-center bg-slate-900 border border-slate-700/80 rounded-xl p-2.5 shadow-xs">
                            <div class="min-w-0 pr-2">
                                <p class="text-xs font-bold text-slate-100 truncate">{{ $m->concepto }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $m->created_at->format('H:i') }} • {{ ucfirst($m->categoria ?? $m->tipo) }}</p>
                            </div>
                            <span class="font-mono font-bold text-xs sm:text-sm whitespace-nowrap {{ $m->tipo == 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $m->tipo == 'ingreso' ? '+' : '-' }}{{ $moneda }} {{ number_format($m->monto, 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-500 text-xs">
                            <i class="fas fa-receipt text-2xl text-slate-600 mb-1"></i>
                            <p>Sin gastos registrados en este turno</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. MODAL DETALLADO DE ARQUEO Y CIERRE DE TURNO (LO QUE BUSCABA EL CLIENTE) -->
    <!-- ========================================================================= -->
    <div id="modal-cerrar" x-data="cierreCaja({{ $efectivoEsperado }})" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border-2 border-slate-700 rounded-3xl w-full max-w-lg p-5 sm:p-6 shadow-2xl transform transition-all my-8 max-h-[90vh] overflow-y-auto">
            
            <!-- Encabezado del Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-white leading-tight">Cerrar Turno & Arqueo Final</h3>
                        <p class="text-xs text-slate-400">{{ $turnoActivo->caja->nombre ?? 'Caja Principal' }} • {{ $turnoActivo->user->name }}</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modal-cerrar').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- CUADRO DE ARQUEO DESGLOSADO EN VIVO DENTRO DEL MODAL -->
            <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 mb-4 space-y-2 text-xs">
                <span class="text-[10px] font-black uppercase text-amber-400 tracking-wider block mb-1">
                    <i class="fas fa-calculator mr-1"></i> Balance Matemático del Turno
                </span>
                
                <div class="flex justify-between text-slate-300">
                    <span>(+) Monto Inicial (Apertura):</span>
                    <strong class="font-mono text-white">{{ $moneda }} {{ number_format($turnoActivo->monto_apertura, 2) }}</strong>
                </div>
                <div class="flex justify-between text-emerald-400">
                    <span>(+) Ventas en Efectivo Puro:</span>
                    <strong class="font-mono">+{{ $moneda }} {{ number_format($ventasEfectivoPuro, 2) }}</strong>
                </div>
                @if($mixtasEfectivo > 0)
                <div class="flex justify-between text-emerald-400">
                    <span>(+) Parte Efectivo de Ventas Mixtas:</span>
                    <strong class="font-mono">+{{ $moneda }} {{ number_format($mixtasEfectivo, 2) }}</strong>
                </div>
                @endif
                @if($totalDigitalReal > 0)
                <div class="flex justify-between text-purple-400">
                    <span><i class="fas fa-qrcode mr-1"></i> Cobros Digitales (Yape/Plin/Tarjetas):</span>
                    <strong class="font-mono">{{ $moneda }} {{ number_format($totalDigitalReal, 2) }}</strong>
                </div>
                @endif
                @if($garantiasCobradas > 0)
                <div class="flex justify-between text-emerald-400">
                    <span>(+) Garantías Envases Cobradas:</span>
                    <strong class="font-mono">+{{ $moneda }} {{ number_format($garantiasCobradas, 2) }}</strong>
                </div>
                @endif
                @if($totalEgresos > 0)
                <div class="flex justify-between text-rose-400">
                    <span>(-) Gastos y Egresos de Caja:</span>
                    <strong class="font-mono">-{{ $moneda }} {{ number_format($totalEgresos, 2) }}</strong>
                </div>
                @endif
                @if($garantiasDevueltas > 0)
                <div class="flex justify-between text-rose-400">
                    <span>(-) Garantías Reembolsadas:</span>
                    <strong class="font-mono">-{{ $moneda }} {{ number_format($garantiasDevueltas, 2) }}</strong>
                </div>
                @endif

                <!-- Total Esperado Resaltado -->
                <div class="pt-2 border-t border-slate-800 flex justify-between items-center">
                    <span class="font-bold text-white text-xs sm:text-sm">EFECTIVO ESPERADO EN CAJÓN:</span>
                    <div class="flex items-center gap-2">
                        <strong class="text-base sm:text-lg font-black text-emerald-400 font-mono">{{ $moneda }} {{ number_format($efectivoEsperado, 2) }}</strong>
                        <button type="button" @click="copiarEsperado()" title="Copiar al teclado" class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[10px] font-bold border border-emerald-500/30 hover:bg-emerald-500/30">
                            Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulario de Cierre -->
            <form method="POST" action="{{ route('caja.cerrar', $turnoActivo->id) }}" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-1.5">
                        Dinero Físico Contado en el Cajón *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">S/</span>
                        <input type="number" step="0.01" name="monto_cierre" x-model.number="montoContado" @input="recalcularDiferencia()" required 
                               class="w-full pl-10 pr-3 py-2.5 bg-slate-800 border-2 border-emerald-500/60 rounded-2xl text-2xl font-black text-center text-emerald-400 focus:outline-none focus:border-emerald-400 font-mono"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Insignia Dinámica de Diferencia en Vivo -->
                <div x-show="montoContado !== '' && !isNaN(montoContado)" class="p-3 rounded-xl border text-center transition"
                     :class="Math.abs(diferencia) < 0.01 ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300' : (diferencia > 0 ? 'bg-amber-500/20 border-amber-500/40 text-amber-300' : 'bg-rose-500/20 border-rose-500/40 text-rose-300')">
                    <span class="text-[10px] uppercase font-bold tracking-wider block">Diferencia de Cierre</span>
                    <strong class="text-xl font-black font-mono block my-0.5" x-text="`${diferencia >= 0 ? '+' : ''}{{ $moneda }} ${diferencia.toFixed(2)}`"></strong>
                    <span class="text-xs font-bold" x-text="Math.abs(diferencia) < 0.01 ? '✓ Caja 100% Cuadrada' : (diferencia > 0 ? '▲ Sobrante de dinero en caja' : '▼ Faltante de dinero en caja')"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Observaciones / Notas del Cierre</label>
                    <textarea name="observaciones" placeholder="Detalle de entrega de turno, billetes grandes retirados o diferencias..." rows="2" 
                              class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-slate-200 outline-none focus:border-amber-500"></textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-cerrar').classList.add('hidden')" 
                            class="w-1/3 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold text-xs transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="w-2/3 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-xs sm:text-sm transition shadow-lg shadow-rose-600/30 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fas fa-lock"></i><span>Confirmar y Cerrar Caja</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@else
    <!-- Sin turno activo -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 sm:p-12 mb-6 text-center shadow-xl max-w-xl mx-auto">
        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-amber-500/20 text-amber-400 border-2 border-amber-500/30 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <i class="fas fa-cash-register text-3xl sm:text-4xl"></i>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-white mb-2">No tienes un turno de caja abierto</h2>
        <p class="text-xs sm:text-sm text-slate-400 mb-6">Para empezar a vender en el POS y registrar operaciones, abre un nuevo turno de caja.</p>
        <button onclick="document.getElementById('modal-abrir').classList.remove('hidden')" 
                class="px-6 py-3.5 gradient-primary text-white rounded-2xl font-black text-sm shadow-lg hover:brightness-105 transition flex items-center gap-2 mx-auto cursor-pointer">
            <i class="fas fa-key"></i><span>Abrir Turno de Caja</span>
        </button>
    </div>

    <!-- Modal Abrir Turno -->
    <div id="modal-abrir" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl flex-shrink-0 font-bold">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-white">Apertura de Turno</h3>
                    <p class="text-xs text-slate-400">Ingresa el sencillo inicial en monedas y billetes</p>
                </div>
            </div>

            <form method="POST" action="{{ route('caja.abrir') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Caja Registradora</label>
                    <select name="caja_id" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm font-bold focus:border-amber-500 outline-none">
                        @foreach($cajas as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-emerald-400 mb-1">Monto Inicial de Apertura (Sencillo) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">S/</span>
                        <input type="number" step="0.01" name="monto_apertura" value="0.00" required 
                               class="w-full pl-9 pr-3 py-2.5 bg-slate-800 border-2 border-emerald-500/50 rounded-xl text-xl font-black text-emerald-400 font-mono focus:border-emerald-400 outline-none text-center">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Observaciones</label>
                    <textarea name="observaciones" placeholder="Ej: 50 soles en monedas de 1 y 2 soles..." rows="2" 
                              class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-slate-200 focus:border-amber-500 outline-none"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-abrir').classList.add('hidden')" 
                            class="w-1/3 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold text-xs transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="w-2/3 py-3 gradient-primary text-white rounded-xl font-black text-xs sm:text-sm shadow-lg hover:brightness-105 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fas fa-unlock"></i><span>Abrir Caja</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- HISTORIAL DE TURNOS ANTERIORES -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl">
    <h3 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center gap-2">
        <i class="fas fa-history text-amber-400"></i><span>Historial de Turnos y Cierres Z</span>
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-800/80 text-slate-400 uppercase text-[11px] border-b border-slate-700">
                <tr>
                    <th class="py-3 px-4">Turno #</th>
                    <th class="py-3 px-4">Cajero</th>
                    <th class="py-3 px-4">Apertura</th>
                    <th class="py-3 px-4">Cierre</th>
                    <th class="py-3 px-4 text-right">Ventas</th>
                    <th class="py-3 px-4 text-right">Efectivo Real</th>
                    <th class="py-3 px-4 text-center">Diferencia</th>
                    <th class="py-3 px-4 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($turnos as $t)
                    @php
                        $tVentas = $t->ventas ? $t->ventas->sum('total') : $t->total_ventas;
                    @endphp
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="py-3 px-4 font-bold text-white font-mono">#{{ $t->id }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-200">{{ $t->user->name ?? '—' }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $t->fecha_apertura->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $t->fecha_cierre ? $t->fecha_cierre->format('d/m/Y H:i') : 'Abierto' }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-amber-400">{{ $moneda }} {{ number_format($tVentas, 2) }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400">
                            {{ $t->monto_cierre !== null ? $moneda . ' ' . number_format($t->monto_cierre, 2) : '—' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($t->estado === 'cerrado')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ abs($t->diferencia) < 0.01 ? 'bg-emerald-500/20 text-emerald-400' : ($t->diferencia > 0 ? 'bg-amber-500/20 text-amber-400' : 'bg-rose-500/20 text-rose-400') }}">
                                    {{ $t->diferencia >= 0 ? '+' : '' }}{{ number_format($t->diferencia, 2) }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-400 rounded text-[10px] font-bold">En curso</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('caja.cierre', $t->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-bold border border-slate-700 transition" title="Ver Arqueo">
                                    <i class="fas fa-eye"></i> Arqueo
                                </a>
                                <a href="{{ route('caja.ticket', $t->id) }}" target="_blank" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-lg text-xs border border-slate-700 transition" title="Ticket 80mm">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500 text-xs">No hay historial de turnos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function cierreCaja(esperado) {
        return {
            esperado: parseFloat(esperado) || 0,
            montoContado: '',
            diferencia: 0,

            recalcularDiferencia() {
                const contado = parseFloat(this.montoContado) || 0;
                this.diferencia = contado - this.esperado;
            },
            copiarEsperado() {
                this.montoContado = this.esperado.toFixed(2);
                this.recalcularDiferencia();
            }
        }
    }
</script>
@endsection
