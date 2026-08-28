@extends('layouts.app')
@section('title', 'Cierre y Arqueo de Caja')
@section('header', 'Arqueo de Turno #' . $turno->id)

@section('content')
@php
    $moneda = $empresaGlobal->moneda ?? 'S/';
    $ventas = $turno->ventas;
    
    $ventasEfectivoPuro = $ventas->where('forma_pago', 'efectivo')->sum('total');
    $ventasYapePuro = $ventas->where('forma_pago', 'yape')->sum('total');
    $ventasPlinPuro = $ventas->where('forma_pago', 'plin')->sum('total');
    $ventasTarjetaPuro = $ventas->where('forma_pago', 'tarjeta')->sum('total');
    $ventasTransfPuro = $ventas->where('forma_pago', 'transferencia')->sum('total');
    
    $mixtasEfectivo = 0;
    $mixtasYape = 0;
    $mixtasPlin = 0;
    $mixtasTarjeta = 0;
    $mixtasOtros = 0;
    $cantMixtas = 0;

    foreach ($ventas->where('forma_pago', 'mixto') as $v) {
        $cantMixtas++;
        $dp = is_array($v->detalle_pago) ? $v->detalle_pago : (json_decode($v->detalle_pago, true) ?? []);
        
        $m1 = $dp['metodo_1'] ?? 'efectivo';
        $cant1 = floatval($dp['monto_1'] ?? 0);
        $m2 = $dp['metodo_2'] ?? 'yape';
        $cant2 = floatval($dp['monto_2'] ?? 0);

        if ($m1 === 'efectivo') $mixtasEfectivo += $cant1;
        elseif ($m1 === 'yape') $mixtasYape += $cant1;
        elseif ($m1 === 'plin') $mixtasPlin += $cant1;
        elseif ($m1 === 'tarjeta') $mixtasTarjeta += $cant1;
        else $mixtasOtros += $cant1;

        if ($m2 === 'efectivo') $mixtasEfectivo += $cant2;
        elseif ($m2 === 'yape') $mixtasYape += $cant2;
        elseif ($m2 === 'plin') $mixtasPlin += $cant2;
        elseif ($m2 === 'tarjeta') $mixtasTarjeta += $cant2;
        else $mixtasOtros += $cant2;
    }

    $totalEfectivoReal = $ventasEfectivoPuro + $mixtasEfectivo;
    $totalYapeReal = $ventasYapePuro + $mixtasYape;
    $totalPlinReal = $ventasPlinPuro + $mixtasPlin;
    $totalTarjetaReal = $ventasTarjetaPuro + $mixtasTarjeta;
    $totalTransfReal = $ventasTransfPuro + $mixtasOtros;
    $totalVentas = $ventas->sum('total');

    $egresosList = $turno->movimientos->where('tipo', 'egreso');
    $totalEgresos = $egresosList->sum('monto');

    $ingresosList = $turno->movimientos->where('tipo', 'ingreso');
    $totalIngresos = $ingresosList->sum('monto');

    // Garantías de envases
    $garantiasCobradas = class_exists(\App\Models\EnvaseGarantia::class)
        ? \App\Models\EnvaseGarantia::where('created_at', '>=', $turno->fecha_apertura)
            ->when($turno->fecha_cierre, fn($q) => $q->where('created_at', '<=', $turno->fecha_cierre))
            ->where('estado', 'prestado')
            ->sum('monto_garantia')
        : 0;

    $garantiasDevueltas = class_exists(\App\Models\EnvaseGarantia::class)
        ? \App\Models\EnvaseGarantia::where('fecha_devolucion', '>=', $turno->fecha_apertura)
            ->when($turno->fecha_cierre, fn($q) => $q->where('fecha_devolucion', '<=', $turno->fecha_cierre))
            ->where('estado', 'devuelto')
            ->sum('monto_garantia')
        : 0;

    $esperadoEnCaja = ($turno->monto_apertura + $totalEfectivoReal + $totalIngresos + $garantiasCobradas) - ($totalEgresos + $garantiasDevueltas);
    $totalDigitalReal = $totalYapeReal + $totalPlinReal + $totalTarjetaReal + $totalTransfReal;
@php
    $fechaApStr = $turno->fecha_apertura ? (is_string($turno->fecha_apertura) ? \Carbon\Carbon::parse($turno->fecha_apertura)->format('d/m/Y H:i:s') : $turno->fecha_apertura->format('d/m/Y H:i:s')) : '—';
    $fechaCiStr = $turno->fecha_cierre ? (is_string($turno->fecha_cierre) ? \Carbon\Carbon::parse($turno->fecha_cierre)->format('d/m/Y H:i:s') : $turno->fecha_cierre->format('d/m/Y H:i:s')) : 'En operación activa';
@endphp

@if(session('success'))
<!-- MODAL DE CONFIRMACIÓN POST-CIERRE -->
<div id="modal-post-cierre" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
    <div class="bg-slate-900 border-2 border-emerald-500/50 rounded-3xl w-full max-w-md p-6 sm:p-7 shadow-2xl text-center space-y-5 transform transition-all">
        <div class="w-16 h-16 rounded-3xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-emerald-500/20">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <h3 class="text-xl font-black text-white">¡Turno Cerrado Correctamente!</h3>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">El arqueo ha sido registrado con éxito. ¿Deseas imprimir el comprobante de cierre ahora?</p>
        </div>

        <div class="space-y-2.5 pt-2">
            <a href="{{ route('caja.ticket', $turno->id) }}" target="_blank" onclick="document.getElementById('modal-post-cierre').remove()" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black rounded-2xl text-sm transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                <i class="fas fa-receipt text-lg"></i> Imprimir Ticket 80mm
            </a>
            <button type="button" onclick="window.print(); document.getElementById('modal-post-cierre').remove()" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-2xl text-xs sm:text-sm border border-slate-700 transition flex items-center justify-center gap-2">
                <i class="fas fa-file-pdf text-amber-400"></i> Imprimir Reporte A4
            </button>
            <button type="button" onclick="document.getElementById('modal-post-cierre').remove()" class="w-full py-2.5 bg-transparent hover:bg-slate-800 text-slate-400 hover:text-slate-200 font-semibold rounded-xl text-xs transition">
                No imprimir / Finalizar
            </button>
        </div>
    </div>
</div>
@endif

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Encabezado de Cierre -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-3xl font-black">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-black text-white">Arqueo Detallado de Caja</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase {{ $turno->estado === 'cerrado' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                            {{ $turno->estado === 'cerrado' ? 'Corte Final (Z)' : 'Turno en Curso (X)' }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                        <i class="fas fa-store text-amber-500 mr-1"></i> {{ $turno->caja->nombre ?? 'Caja Principal' }} 
                        <span class="mx-2">•</span> 
                        <i class="fas fa-user text-emerald-400 mr-1"></i> Cajero: <strong class="text-slate-200">{{ $turno->user->name ?? 'Usuario' }}</strong>
                    </p>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <a href="{{ route('caja.ticket', $turno->id) }}" target="_blank" class="flex-1 sm:flex-none px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs sm:text-sm border border-slate-700 shadow transition flex items-center justify-center gap-2">
                    <i class="fas fa-receipt text-amber-400"></i> Imprimir Ticket 80mm
                </a>
                <a href="{{ route('caja.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs sm:text-sm border border-slate-700 transition">
                    Volver
                </a>
            </div>
        </div>

        <!-- Tiempos de Turno -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
            <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Fecha y Hora Apertura</span>
                    <strong class="text-white text-sm sm:text-base">{{ $fechaApStr }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider block">Monto Inicial</span>
                    <strong class="text-emerald-400 text-base sm:text-lg font-mono">{{ $moneda }} {{ number_format($turno->monto_apertura, 2) }}</strong>
                </div>
            </div>

            <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Fecha y Hora Cierre</span>
                    <strong class="text-white text-sm sm:text-base">{{ $fechaCiStr }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider block">Total Ventas</span>
                    <strong class="text-amber-400 text-base sm:text-lg font-mono">{{ $moneda }} {{ number_format($totalVentas, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Resumen: EFECTIVO vs DIGITAL vs GASTOS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Tarjeta 1: Efectivo Físico -->
        <div class="bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-900 border-2 border-emerald-500/40 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                    <i class="fas fa-money-bill-wave"></i> Efectivo en Cajón
                </span>
                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[10px] font-bold">Físico</span>
            </div>
            
            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between">
                    <span>(+) Monto Inicial (Sencillo):</span>
                    <strong class="text-white font-mono">{{ $moneda }} {{ number_format($turno->monto_apertura, 2) }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>(+) Ventas Efectivo Puro:</span>
                    <strong class="text-emerald-400 font-mono">+{{ $moneda }} {{ number_format($ventasEfectivoPuro, 2) }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>(+) Parte Efectivo de Pagos Mixtos:</span>
                    <strong class="text-emerald-400 font-mono">+{{ $moneda }} {{ number_format($mixtasEfectivo, 2) }}</strong>
                </div>
                @if($ingresosExtra > 0)
                <div class="flex justify-between">
                    <span>(+) Ingresos manuales:</span>
                    <strong class="text-emerald-400 font-mono">+{{ $moneda }} {{ number_format($ingresosExtra, 2) }}</strong>
                </div>
                @endif
                @if($garantiasCobradas > 0)
                <div class="flex justify-between">
                    <span>(+) Garantías Envases Cobradas:</span>
                    <strong class="text-emerald-400 font-mono">+{{ $moneda }} {{ number_format($garantiasCobradas, 2) }}</strong>
                </div>
                @endif
                @if($totalEgresos > 0)
                <div class="flex justify-between text-rose-400">
                    <span>(-) Gastos / Egresos de Caja:</span>
                    <strong class="font-mono">-{{ $moneda }} {{ number_format($totalEgresos, 2) }}</strong>
                </div>
                @endif
                @if($garantiasDevueltas > 0)
                <div class="flex justify-between text-rose-400">
                    <span>(-) Garantías Reembolsadas:</span>
                    <strong class="font-mono">-{{ $moneda }} {{ number_format($garantiasDevueltas, 2) }}</strong>
                </div>
                @endif
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-300">Efectivo Esperado:</span>
                <strong class="text-xl font-black text-emerald-400 font-mono">{{ $moneda }} {{ number_format($esperadoEnCaja, 2) }}</strong>
            </div>
        </div>

        <!-- Tarjeta 2: Cobros Digitales (Yape/Plin/Tarjeta) -->
        <div class="bg-gradient-to-br from-purple-950/40 via-slate-900 to-slate-900 border-2 border-purple-500/40 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <span class="text-xs font-black uppercase tracking-wider text-purple-400 flex items-center gap-2">
                    <i class="fas fa-qrcode"></i> Cobros Digitales
                </span>
                <span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 rounded text-[10px] font-bold">En Cuentas</span>
            </div>

            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between">
                    <span><i class="fas fa-mobile-screen text-purple-400 mr-1"></i> Yape (Puro + Mixto):</span>
                    <strong class="text-white font-mono">{{ $moneda }} {{ number_format($totalYapeReal, 2) }}</strong>
                </div>
                <div class="flex justify-between">
                    <span><i class="fas fa-mobile-screen text-sky-400 mr-1"></i> Plin (Puro + Mixto):</span>
                    <strong class="text-white font-mono">{{ $moneda }} {{ number_format($totalPlinReal, 2) }}</strong>
                </div>
                <div class="flex justify-between">
                    <span><i class="fas fa-credit-card text-amber-400 mr-1"></i> Tarjeta POS:</span>
                    <strong class="text-white font-mono">{{ $moneda }} {{ number_format($totalTarjetaReal, 2) }}</strong>
                </div>
                <div class="flex justify-between">
                    <span><i class="fas fa-building-columns text-slate-400 mr-1"></i> Transferencias:</span>
                    <strong class="text-white font-mono">{{ $moneda }} {{ number_format($totalTransfReal, 2) }}</strong>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-300">Total en Cuentas:</span>
                <strong class="text-xl font-black text-purple-400 font-mono">{{ $moneda }} {{ number_format($totalDigitalReal, 2) }}</strong>
            </div>
        </div>

        <!-- Tarjeta 3: Balance y Diferencia de Cierre -->
        <div class="bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-900 border-2 border-amber-500/40 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-800">
                <span class="text-xs font-black uppercase tracking-wider text-amber-400 flex items-center gap-2">
                    <i class="fas fa-scale-balanced"></i> Cuadre de Cierre
                </span>
                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded text-[10px] font-bold">Balance</span>
            </div>

            <div class="space-y-2 text-xs text-slate-300">
                <div class="flex justify-between">
                    <span>Efectivo Calculado:</span>
                    <strong class="text-white font-mono">{{ $moneda }} {{ number_format($esperadoEnCaja, 2) }}</strong>
                </div>
                <div class="flex justify-between">
                    <span>Efectivo Contado por Cajero:</span>
                    <strong class="text-emerald-400 font-mono text-sm">{{ $turno->monto_cierre !== null ? $moneda . ' ' . number_format($turno->monto_cierre, 2) : 'Pendiente' }}</strong>
                </div>
            </div>

            @if($turno->monto_cierre !== null)
                @php $dif = $turno->monto_cierre - $esperadoEnCaja; @endphp
                <div class="p-3.5 rounded-2xl border text-center {{ abs($dif) < 0.01 ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300' : ($dif > 0 ? 'bg-amber-500/20 border-amber-500/40 text-amber-300' : 'bg-rose-500/20 border-rose-500/40 text-rose-300') }}">
                    <span class="text-[10px] uppercase font-bold tracking-wider block">Diferencia de Caja</span>
                    <strong class="text-2xl font-black font-mono block my-1">
                        {{ $dif >= 0 ? '+' : '' }}{{ $moneda }} {{ number_format($dif, 2) }}
                    </strong>
                    <span class="text-xs font-bold">
                        {{ abs($dif) < 0.01 ? '✓ Caja 100% Cuadrada' : ($dif > 0 ? '▲ Sobrante de dinero' : '▼ Faltante de dinero') }}
                    </span>
                </div>
            @else
                <div class="p-3 bg-slate-800/80 rounded-xl text-center text-xs text-slate-400">
                    El turno aún no ha sido cerrado formalmente.
                </div>
            @endif
        </div>
    </div>

    <!-- TABLA 1: DESGLOSE DETALLADO DE MÉTODOS DE PAGO Y VENTAS MIXTAS -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-4">
        <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
            <i class="fas fa-coins text-amber-400"></i> Desglose Detallado por Métodos de Pago
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-800/80 text-slate-400 uppercase text-[11px] border-b border-slate-700">
                    <tr>
                        <th class="py-3 px-4">Método de Pago</th>
                        <th class="py-3 px-4 text-center">Tipo de Transacción</th>
                        <th class="py-3 px-4 text-center">Tickets</th>
                        <th class="py-3 px-4 text-right">Monto Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-200">
                    <tr>
                        <td class="py-3 px-4 font-bold flex items-center gap-2"><i class="fas fa-money-bill-wave text-emerald-400"></i> Efectivo Puro</td>
                        <td class="py-3 px-4 text-center"><span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[10px] font-bold">Cajón Físico</span></td>
                        <td class="py-3 px-4 text-center">{{ $ventas->where('forma_pago', 'efectivo')->count() }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-emerald-400">{{ $moneda }} {{ number_format($ventasEfectivoPuro, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-bold flex items-center gap-2"><i class="fas fa-layer-group text-amber-400"></i> Pagos Mixtos (Parte Efectivo)</td>
                        <td class="py-3 px-4 text-center"><span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded text-[10px] font-bold">Cajón Físico</span></td>
                        <td class="py-3 px-4 text-center">{{ $cantMixtas }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-amber-400">{{ $moneda }} {{ number_format($mixtasEfectivo, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-bold flex items-center gap-2"><i class="fas fa-layer-group text-purple-400"></i> Pagos Mixtos (Parte Digital)</td>
                        <td class="py-3 px-4 text-center"><span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 rounded text-[10px] font-bold">Billetera / Banco</span></td>
                        <td class="py-3 px-4 text-center">{{ $cantMixtas }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-purple-400">{{ $moneda }} {{ number_format($mixtasYape + $mixtasPlin + $mixtasTarjeta + $mixtasOtros, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-bold flex items-center gap-2"><i class="fas fa-mobile-screen text-purple-400"></i> Yape Directo</td>
                        <td class="py-3 px-4 text-center"><span class="px-2 py-0.5 bg-purple-500/20 text-purple-300 rounded text-[10px] font-bold">Billetera Digital</span></td>
                        <td class="py-3 px-4 text-center">{{ $ventas->where('forma_pago', 'yape')->count() }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white">{{ $moneda }} {{ number_format($ventasYapePuro, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-bold flex items-center gap-2"><i class="fas fa-mobile-screen text-sky-400"></i> Plin Directo</td>
                        <td class="py-3 px-4 text-center"><span class="px-2 py-0.5 bg-sky-500/20 text-sky-300 rounded text-[10px] font-bold">Billetera Digital</span></td>
                        <td class="py-3 px-4 text-center">{{ $ventas->where('forma_pago', 'plin')->count() }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white">{{ $moneda }} {{ number_format($ventasPlinPuro, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 font-bold flex items-center gap-2"><i class="fas fa-credit-card text-amber-400"></i> Tarjetas (POS Niubiz / Izipay)</td>
                        <td class="py-3 px-4 text-center"><span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded text-[10px] font-bold">Cuenta Bancaria</span></td>
                        <td class="py-3 px-4 text-center">{{ $ventas->where('forma_pago', 'tarjeta')->count() }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white">{{ $moneda }} {{ number_format($ventasTarjetaPuro, 2) }}</td>
                    </tr>
                    <tr class="bg-slate-800/80 font-black text-white text-sm">
                        <td class="py-3 px-4" colspan="2">TOTAL VENTAS REGISTRADAS:</td>
                        <td class="py-3 px-4 text-center">{{ $ventas->count() }} tickets</td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-400 text-base">{{ $moneda }} {{ number_format($totalVentas, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLA 2: DESGLOSE DE GASTOS / EGRESOS DE CAJA -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-receipt text-rose-400"></i> Gastos y Egresos Descontados de Caja
            </h3>
            <span class="text-xs font-bold font-mono text-rose-400">Total: -{{ $moneda }} {{ number_format($totalEgresos, 2) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-800/80 text-slate-400 uppercase text-[11px] border-b border-slate-700">
                    <tr>
                        <th class="py-3 px-4">Hora</th>
                        <th class="py-3 px-4">Categoría</th>
                        <th class="py-3 px-4">Concepto / Motivo</th>
                        <th class="py-3 px-4">Comprobante</th>
                        <th class="py-3 px-4 text-right">Monto Descontado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-200">
                    @forelse($egresosList as $eg)
                        <tr>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $eg->created_at ? (is_string($eg->created_at) ? \Carbon\Carbon::parse($eg->created_at)->format('H:i') : $eg->created_at->format('H:i')) : '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 bg-rose-500/20 text-rose-300 rounded text-[10px] font-bold uppercase">
                                    {{ str_replace('_', ' ', $eg->categoria ?? 'gasto') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-white">{{ $eg->concepto }}</td>
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ $eg->comprobante ?? '—' }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-400">-{{ $moneda }} {{ number_format($eg->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 text-xs">
                                <i class="fas fa-check-circle text-emerald-500 text-xl mb-1 block"></i>
                                No se registraron egresos ni gastos durante este turno.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
