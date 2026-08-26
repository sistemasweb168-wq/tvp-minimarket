<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $comprobante->numero_completo }}</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Courier New', monospace; padding: 0; margin: 0; background: #e5e7eb; }
    .ticket {
        width: 80mm; max-width: 320px; margin: 20px auto; background: white;
        padding: 14px 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        font-size: 11px; line-height: 1.4;
    }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .small { font-size: 9px; }
    .dashed { border-bottom: 1px dashed #94a3b8; margin: 8px 0; }
    .header { text-align: center; margin-bottom: 8px; }
    .header h1 { font-size: 14px; margin: 4px 0; }
    .header p { margin: 1px 0; font-size: 10px; }

    .titulo-comprobante { text-align: center; background: #f0fdf4; border: 1px solid #10b981; padding: 4px; margin: 8px 0; font-weight: bold; font-size: 12px; }
    .numero-comp { font-size: 14px; text-align: center; margin: 4px 0; font-weight: bold; }

    table.items { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 10px; }
    table.items th { border-bottom: 1px solid #1e293b; padding: 3px 1px; text-align: left; }
    table.items td { padding: 3px 1px; vertical-align: top; }
    table.items .producto-nombre { font-weight: bold; font-size: 10px; }
    table.items .producto-detalle { font-size: 9px; color: #64748b; }

    .totales { margin-top: 8px; font-size: 11px; }
    .totales p { display: flex; justify-content: space-between; margin: 2px 0; }
    .total-final { font-size: 14px; font-weight: bold; padding-top: 6px; border-top: 2px solid #1e293b; margin-top: 4px; }

    .qr-section { text-align: center; margin: 12px 0; padding: 10px 0; border-top: 1px dashed #94a3b8; border-bottom: 1px dashed #94a3b8; }
    .qr-img { width: 110px; height: 110px; }
    .hash { font-size: 7px; word-break: break-all; color: #64748b; margin-top: 4px; }

    .estado { text-align: center; margin: 8px 0; padding: 4px; font-size: 9px; font-weight: bold; }
    .estado.ok { background: #d1fae5; color: #065f46; }
    .estado.rj { background: #fee2e2; color: #7f1d1d; }
    .estado.pe { background: #fef3c7; color: #78350f; }

    .footer { text-align: center; margin-top: 10px; font-size: 9px; }

    .actions { text-align: center; margin: 20px; }
    .actions button { padding: 8px 16px; margin: 4px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }
    .btn-print { background: #10b981; color: white; }
    .btn-close { background: #64748b; color: white; }

    @media print {
        body { background: white; }
        .ticket { box-shadow: none; margin: 0; padding: 4mm 3mm; }
        .actions { display: none; }
    }
</style>
</head>
<body>
<div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
    <button class="btn-close" onclick="window.close()">Cerrar</button>
</div>

<div class="ticket">
    <div class="header">
        @if($empresaGlobal && $empresaGlobal->logo_url)
            <img src="{{ $empresaGlobal->logo_url }}" style="max-width: 80px; max-height: 50px;">
        @endif
        <h1>{{ $empresaGlobal->nombre_comercial ?? $comprobante->emisor_razon_social }}</h1>
        <p>{{ $comprobante->emisor_razon_social }}</p>
        <p class="bold">RUC: {{ $comprobante->emisor_ruc }}</p>
        @if($empresaGlobal && $empresaGlobal->direccion)
            <p class="small">{{ $empresaGlobal->direccion }}</p>
        @endif
        @if($empresaGlobal && $empresaGlobal->telefono)
            <p class="small">Tel: {{ $empresaGlobal->telefono }}</p>
        @endif
    </div>

    <div class="titulo-comprobante">{{ strtoupper($comprobante->tipo_documento_nombre) }} ELECTRÓNICA</div>
    <div class="numero-comp">{{ $comprobante->numero_completo }}</div>

    <p class="small">
        <strong>Fecha:</strong> {{ $comprobante->fecha_emision->format('d/m/Y') }} {{ substr($comprobante->hora_emision, 0, 5) }}
    </p>
    <p class="small">
        <strong>Cliente:</strong> {{ $comprobante->receptor_razon_social }}
    </p>
    <p class="small">
        <strong>{{ $comprobante->receptor_tipo_doc_label }}:</strong> {{ $comprobante->receptor_numero_doc }}
    </p>
    @if($comprobante->receptor_direccion)
    <p class="small"><strong>Dir:</strong> {{ Str::limit($comprobante->receptor_direccion, 40) }}</p>
    @endif

    @if($comprobante->venta && $comprobante->venta->detalles->count())
    <div class="dashed"></div>
    <table class="items">
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comprobante->venta->detalles as $d)
            <tr>
                <td>
                    <div class="producto-nombre">{{ $d->descripcion }}</div>
                    <div class="producto-detalle">
                        {{ number_format($d->cantidad, 2) }} × {{ $comprobante->moneda }}{{ number_format($d->precio_unitario, 2) }}
                    </div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">{{ $comprobante->moneda }}{{ number_format($d->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="dashed"></div>

    <div class="totales">
        <p><span>Op. Gravadas:</span><span>{{ $comprobante->moneda }} {{ number_format($comprobante->total_gravadas, 2) }}</span></p>
        <p><span>IGV (18%):</span><span>{{ $comprobante->moneda }} {{ number_format($comprobante->total_igv, 2) }}</span></p>
        @if($comprobante->total_descuentos > 0)
        <p><span>Descuento:</span><span>-{{ $comprobante->moneda }} {{ number_format($comprobante->total_descuentos, 2) }}</span></p>
        @endif
        <p class="total-final"><span>TOTAL:</span><span>{{ $comprobante->moneda }} {{ number_format($comprobante->importe_total, 2) }}</span></p>
    </div>

    <p class="small" style="margin-top: 6px; font-style: italic;">SON: {{ $comprobante->importe_letras }}</p>

    @if($comprobante->qr_data)
    <div class="qr-section">
        <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($comprobante->qr_data) }}&size=200x200" alt="QR">
        <p class="small bold" style="margin-top: 4px;">Representación impresa</p>
    </div>
    @endif

    @if($comprobante->estado_sunat === 'aceptado')
        <div class="estado ok">✓ ACEPTADO POR SUNAT</div>
    @elseif($comprobante->estado_sunat === 'rechazado')
        <div class="estado rj">✗ RECHAZADO</div>
    @else
        <div class="estado pe">⏳ PENDIENTE</div>
    @endif

    @if($comprobante->hash)
    <p class="hash"><strong>Hash:</strong> {{ Str::limit($comprobante->hash, 60) }}</p>
    @endif

    <div class="footer">
        @if($empresaGlobal && $empresaGlobal->mensaje_ticket)
            <p class="bold">{{ $empresaGlobal->mensaje_ticket }}</p>
        @endif
        <p style="margin-top: 6px;">Consulte su comprobante en:</p>
        <p class="bold">www.sunat.gob.pe</p>
    </div>
</div>

<script>setTimeout(() => window.print(), 600);</script>
</body>
</html>
