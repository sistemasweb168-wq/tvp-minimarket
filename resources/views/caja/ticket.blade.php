@php
    $empresa = $empresa ?? $empresaGlobal ?? \App\Models\Empresa::first();
    $moneda = $empresa?->moneda ?? 'S/';
    $ventas = $turno->ventas ?? collect();
    
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
        
        $m1 = $dp['metodo_1'] ?? $dp['metodo_efectivo'] ?? 'efectivo';
        $cant1 = floatval($dp['monto_1'] ?? $dp['monto_efectivo'] ?? 0);
        $m2 = $dp['metodo_2'] ?? $dp['metodo_digital'] ?? 'yape';
        $cant2 = floatval($dp['monto_2'] ?? $dp['monto_digital'] ?? 0);

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

    $egresosList = ($turno->movimientos ?? collect())->where('tipo', 'egreso');
    $totalEgresos = $egresosList->sum('monto');

    $ingresosList = ($turno->movimientos ?? collect())->where('tipo', 'ingreso');
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

    // Fechas seguras
    $fechaApStr = '—';
    if ($turno->fecha_apertura) {
        $fechaApStr = is_string($turno->fecha_apertura) 
            ? \Carbon\Carbon::parse($turno->fecha_apertura)->format('d/m/Y H:i:s') 
            : $turno->fecha_apertura->format('d/m/Y H:i:s');
    }

    $fechaCiStr = null;
    if ($turno->fecha_cierre) {
        $fechaCiStr = is_string($turno->fecha_cierre) 
            ? \Carbon\Carbon::parse($turno->fecha_cierre)->format('d/m/Y H:i:s') 
            : $turno->fecha_cierre->format('d/m/Y H:i:s');
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Caja - Turno #{{ $turno->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; padding: 0; margin: 0; background: #f5f5f5; color: #000; }
        .ticket {
            width: 80mm; max-width: 320px; margin: 20px auto; background: white;
            padding: 15px 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 11px; line-height: 1.35;
        }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
        .header img.logo-ticket { max-width: 85px; max-height: 55px; margin-bottom: 4px; object-fit: contain; }
        .header h1 { margin: 2px 0; font-size: 14px; font-weight: bold; }
        .header p { margin: 1px 0; font-size: 10px; }
        
        .badge-title {
            background: #000; color: white;
            padding: 4px; text-align: center; font-weight: bold; font-size: 12px;
            margin: 6px 0; border-radius: 4px; text-transform: uppercase;
        }
        
        .info { margin-bottom: 8px; font-size: 10.5px; border-bottom: 1px dashed #000; padding-bottom: 6px; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 10.5px; }
        th { border-bottom: 1px solid #000; text-align: left; padding: 3px 1px; }
        td { padding: 2.5px 1px; vertical-align: top; }
        .row-section { border-top: 1px dashed #000; font-weight: bold; }
        .row-total { border-top: 2px solid #000; font-weight: bold; font-size: 12.5px; padding-top: 5px !important; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 9.5px; border-top: 2px dashed #000; padding-top: 8px; }
        .actions { text-align: center; margin: 20px; }
        .actions button { padding: 10px 20px; margin: 5px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-print { background: #10b981; color: white; font-weight: bold; }
        .btn-close { background: #64748b; color: white; }
        @media print {
            body { background: white; padding: 0; }
            .ticket { box-shadow: none; margin: 0; padding: 4mm 2mm; width: 100%; max-width: 100%; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
<div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Corte de Caja</button>
    <button class="btn-close" onclick="window.close()">Cerrar</button>
</div>

<div class="ticket">
    <div class="header">
        @if($empresa && $empresa->logo_url)
            <img src="{{ $empresa->logo_url }}" class="logo-ticket" alt="Logo"><br>
        @endif
        <h1>{{ $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'MIKITO\'S LICORERÍA' }}</h1>
        @if($empresa && $empresa->ruc_nit)
            <p><strong>RUC: {{ $empresa->ruc_nit }}</strong></p>
        @endif
        @if($empresa && $empresa->direccion)
            <p>{{ $empresa->direccion }}</p>
        @endif
    </div>

    <div class="badge-title">{{ $turno->estado === 'cerrado' ? 'CORTE DE CAJA / CIERRE Z' : 'CORTE DE CAJA PROVISIONAL (X)' }}</div>

    <div class="info">
        <p><strong>Turno N°:</strong> #{{ $turno->id }}</p>
        <p><strong>Caja:</strong> {{ $turno->caja?->nombre ?? 'Caja Principal' }}</p>
        <p><strong>Cajero:</strong> {{ $turno->user?->name ?? 'Usuario' }}</p>
        <p><strong>Apertura:</strong> {{ $fechaApStr }}</p>
        @if($fechaCiStr)
            <p><strong>Cierre:</strong> {{ $fechaCiStr }}</p>
        @else
            <p><strong>Estado:</strong> <strong>TURNO EN CURSO (CORTE X)</strong></p>
        @endif
    </div>

    <!-- 1. DESGLOSE DE MÉTODOS DE PAGO -->
    <table>
        <thead>
            <tr>
                <th>DESGLOSE VENTAS</th>
                <th style="text-align:right;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Efectivo Puro:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasEfectivoPuro, 2) }}</td>
            </tr>
            @if($cantMixtas > 0)
            <tr>
                <td>Pagos Mixtos (Efectivo):</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($mixtasEfectivo, 2) }}</td>
            </tr>
            <tr>
                <td>Pagos Mixtos (Digital):</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($mixtasYape + $mixtasPlin + $mixtasTarjeta + $mixtasOtros, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Yape Total:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($totalYapeReal, 2) }}</td>
            </tr>
            <tr>
                <td>Plin Total:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($totalPlinReal, 2) }}</td>
            </tr>
            <tr>
                <td>Tarjeta POS:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($totalTarjetaReal, 2) }}</td>
            </tr>
            @if($totalTransfReal > 0)
            <tr>
                <td>Transferencia:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($totalTransfReal, 2) }}</td>
            </tr>
            @endif
            <tr class="row-section">
                <td>TOTAL VENTAS ({{ $ventas->count() }} tickets):</td>
                <td style="text-align:right; font-size:12px;">{{ $moneda }}{{ number_format($totalVentas, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. EGRESOS / GASTOS DETALLADOS -->
    @if($egresosList->count() > 0)
    <table>
        <thead>
            <tr>
                <th>GASTOS / EGRESOS</th>
                <th style="text-align:right;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($egresosList as $eg)
            <tr>
                <td>- {{ $eg->concepto }} <small>({{ $eg->categoria ?? 'gasto' }})</small>:</td>
                <td style="text-align:right;">-{{ $moneda }}{{ number_format($eg->monto, 2) }}</td>
            </tr>
            @endforeach
            <tr class="row-section">
                <td>TOTAL EGRESOS:</td>
                <td style="text-align:right;">-{{ $moneda }}{{ number_format($totalEgresos, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- 3. ARQUEO DE EFECTIVO EN CAJÓN -->
    <table>
        <thead>
            <tr>
                <th>ARQUEO EFECTIVO (CAJÓN)</th>
                <th style="text-align:right;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>(+) Monto Apertura (Sencillo):</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($turno->monto_apertura, 2) }}</td>
            </tr>
            <tr>
                <td>(+) Total Ventas Efectivo:</td>
                <td style="text-align:right;">+{{ $moneda }}{{ number_format($totalEfectivoReal, 2) }}</td>
            </tr>
            @if($ingresosList->count() > 0)
            <tr>
                <td>(+) Ingresos Manuales:</td>
                <td style="text-align:right;">+{{ $moneda }}{{ number_format($totalIngresos, 2) }}</td>
            </tr>
            @endif
            @if($garantiasCobradas > 0)
            <tr>
                <td>(+) Garantías Envases Cobradas:</td>
                <td style="text-align:right;">+{{ $moneda }}{{ number_format($garantiasCobradas, 2) }}</td>
            </tr>
            @endif
            @if($totalEgresos > 0)
            <tr>
                <td>(-) Gastos / Egresos de Caja:</td>
                <td style="text-align:right;">-{{ $moneda }}{{ number_format($totalEgresos, 2) }}</td>
            </tr>
            @endif
            @if($garantiasDevueltas > 0)
            <tr>
                <td>(-) Garantías Reembolsadas:</td>
                <td style="text-align:right;">-{{ $moneda }}{{ number_format($garantiasDevueltas, 2) }}</td>
            </tr>
            @endif
            <tr class="row-section">
                <td>EFECTIVO ESPERADO:</td>
                <td style="text-align:right; font-size:12px;">{{ $moneda }}{{ number_format($esperadoEnCaja, 2) }}</td>
            </tr>
            @if($turno->monto_cierre !== null)
            <tr class="row-total">
                <td>EFECTIVO CONTADO:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($turno->monto_cierre, 2) }}</td>
            </tr>
            @php $dif = $turno->monto_cierre - $esperadoEnCaja; @endphp
            <tr>
                <td>DIFERENCIA:</td>
                <td style="text-align:right; font-weight:bold;">
                    {{ $dif >= 0 ? '+' : '' }}{{ $moneda }}{{ number_format($dif, 2) }}
                    ({{ abs($dif) < 0.01 ? 'Cuadrada' : ($dif > 0 ? 'Sobrante' : 'Faltante') }})
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- 4. TOTAL DIGITAL EN CUENTAS -->
    <div style="font-size:10px; border-top:1px dashed #000; padding-top:4px; margin-top:6px;">
        <p style="margin:2px 0;"><strong>TOTAL DIGITAL (Bancos/Apps):</strong> {{ $moneda }}{{ number_format($totalDigitalReal, 2) }}</p>
        <p style="margin:2px 0; color:#555;">(Yape: {{ $moneda }}{{ number_format($totalYapeReal, 2) }} | Plin: {{ $moneda }}{{ number_format($totalPlinReal, 2) }} | Tarjeta: {{ $moneda }}{{ number_format($totalTarjetaReal, 2) }})</p>
    </div>

    <div style="margin-top: 25px; text-align: center;">
        <p style="border-top: 1px dashed #000; width: 80%; margin: 20px auto 4px auto;"></p>
        <p style="font-size: 10px; font-weight: bold;">Firma del Cajero: {{ $turno->user?->name ?? 'Usuario' }}</p>
    </div>

    <div class="footer">
        <p>Reporte generado el {{ date('d/m/Y H:i:s') }}</p>
        <p>--- Control Administrativo Interno ---</p>
    </div>
</div>
</body>
</html>
