<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja - Turno #{{ $turno->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; padding: 0; margin: 0; background: #f5f5f5; }
        .ticket {
            width: 80mm; max-width: 320px; margin: 20px auto; background: white;
            padding: 15px 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 11px; line-height: 1.35;
        }
        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 8px; margin-bottom: 8px; }
        .header h1 { margin: 3px 0; font-size: 15px; font-weight: bold; }
        .header p { margin: 1px 0; font-size: 10px; }
        
        .badge-title {
            background: #0f172a; color: white;
            padding: 4px; text-align: center; font-weight: bold; font-size: 12px;
            margin: 6px 0; border-radius: 4px;
        }
        
        .info { margin-bottom: 8px; font-size: 10.5px; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10.5px; }
        th { border-bottom: 1px solid #333; text-align: left; padding: 4px 1px; }
        td { padding: 3px 1px; vertical-align: top; }
        .row-section { border-top: 1px dashed #333; font-weight: bold; }
        .row-total { border-top: 2px solid #333; font-weight: bold; font-size: 13px; padding-top: 6px !important; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 9.5px; border-top: 2px dashed #333; padding-top: 8px; }
        .actions { text-align: center; margin: 20px; }
        .actions button { padding: 10px 20px; margin: 5px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-print { background: #10b981; color: white; }
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
        @if($empresaGlobal && $empresaGlobal->logo_url)
            <img src="{{ $empresaGlobal->logo_url }}" style="max-width: 65px; max-height: 55px; margin-bottom: 4px;" alt="logo">
        @endif
        <h1>{{ $empresaGlobal->nombre_comercial ?? 'MINIMARKET VALEZKA' }}</h1>
        @if($empresaGlobal && $empresaGlobal->ruc_nit)
            <p><strong>RUC: {{ $empresaGlobal->ruc_nit }}</strong></p>
        @endif
        @if($empresaGlobal && $empresaGlobal->direccion)
            <p>{{ $empresaGlobal->direccion }}</p>
        @endif
    </div>

    <div class="badge-title">CORTE DE CAJA / CIERRE Z</div>

    <div class="info">
        <p><strong>Turno N°:</strong> #{{ $turno->id }}</p>
        <p><strong>Caja:</strong> {{ $turno->caja->nombre ?? 'Caja Principal' }}</p>
        <p><strong>Cajero:</strong> {{ $turno->user->name ?? 'Usuario' }}</p>
        <p><strong>Apertura:</strong> {{ $turno->fecha_apertura->format('d/m/Y H:i') }}</p>
        @if($turno->fecha_cierre)
            <p><strong>Cierre:</strong> {{ $turno->fecha_cierre->format('d/m/Y H:i') }}</p>
        @else
            <p><strong>Estado:</strong> <span style="color:#059669; font-weight:bold;">TURNO EN CURSO (CORTE X)</span></p>
        @endif
    </div>

    @php
        $moneda = $empresaGlobal->moneda ?? 'S/';
        $ventasEfectivo = $turno->ventas()->where('forma_pago', 'efectivo')->sum('total');
        $ventasYape = $turno->ventas()->where('forma_pago', 'yape')->sum('total');
        $ventasPlin = $turno->ventas()->where('forma_pago', 'plin')->sum('total');
        $ventasTarjeta = $turno->ventas()->where('forma_pago', 'tarjeta')->sum('total');
        $ventasTransferencia = $turno->ventas()->where('forma_pago', 'transferencia')->sum('total');
        $totalVentas = $turno->ventas()->sum('total');
        $cantVentas = $turno->ventas()->count();
        
        $ingresosExtra = $turno->movimientos()->where('tipo', 'ingreso')->sum('monto');
        $egresosExtra = $turno->movimientos()->where('tipo', 'egreso')->sum('monto');
        $esperadoEnCaja = ($turno->monto_apertura + $ventasEfectivo + $ingresosExtra) - $egresosExtra;
    @endphp

    <table>
        <thead>
            <tr>
                <th>DESGLOSE VENTAS</th>
                <th style="text-align:right;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Efectivo:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasEfectivo, 2) }}</td>
            </tr>
            <tr>
                <td>Yape:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasYape, 2) }}</td>
            </tr>
            <tr>
                <td>Plin:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasPlin, 2) }}</td>
            </tr>
            <tr>
                <td>Tarjeta:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasTarjeta, 2) }}</td>
            </tr>
            @if($ventasTransferencia > 0)
            <tr>
                <td>Transferencia:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasTransferencia, 2) }}</td>
            </tr>
            @endif
            <tr class="row-section">
                <td>TOTAL VENTAS ({{ $cantVentas }} tickets):</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($totalVentas, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>ARQUEO DE EFECTIVO</th>
                <th style="text-align:right;">MONTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>(+) Monto Apertura:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($turno->monto_apertura, 2) }}</td>
            </tr>
            <tr>
                <td>(+) Ventas en Efectivo:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ventasEfectivo, 2) }}</td>
            </tr>
            @if($ingresosExtra > 0)
            <tr>
                <td>(+) Ingresos manuales:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($ingresosExtra, 2) }}</td>
            </tr>
            @endif
            @if($egresosExtra > 0)
            <tr>
                <td>(-) Egresos / Gastos:</td>
                <td style="text-align:right;">-{{ $moneda }}{{ number_format($egresosExtra, 2) }}</td>
            </tr>
            @endif
            <tr class="row-section">
                <td>Efectivo Esperado:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($esperadoEnCaja, 2) }}</td>
            </tr>
            @if($turno->monto_cierre !== null)
            <tr class="row-total">
                <td>EFECTIVO REAL:</td>
                <td style="text-align:right;">{{ $moneda }}{{ number_format($turno->monto_cierre, 2) }}</td>
            </tr>
            @php $diferencia = $turno->monto_cierre - $esperadoEnCaja; @endphp
            <tr>
                <td>Diferencia:</td>
                <td style="text-align:right; font-weight:bold; color: {{ $diferencia >= 0 ? '#059669' : '#dc2626' }};">
                    {{ $diferencia >= 0 ? '+' : '' }}{{ $moneda }}{{ number_format($diferencia, 2) }}
                    ({{ $diferencia == 0 ? 'Exacto' : ($diferencia > 0 ? 'Sobrante' : 'Faltante') }})
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: center;">
        <p style="border-top: 1px dashed #333; width: 80%; margin: 25px auto 4px auto;"></p>
        <p style="font-size: 10px; font-weight: bold;">Firma del Cajero</p>
    </div>

    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>--- Control Administrativo Interno ---</p>
    </div>
</div>
<script>setTimeout(() => window.print(), 600);</script>
</body>
</html>
