<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $comprobante->tipo_documento_nombre }} {{ $comprobante->numero_completo }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; padding: 20px; background: #f5f5f5; font-size: 11px; color: #1e293b; }
    .a4 { width: 210mm; max-width: 794px; margin: 0 auto; background: white; padding: 15mm; box-shadow: 0 4px 16px rgba(0,0,0,0.1); position: relative; }

    .header { display: table; width: 100%; margin-bottom: 16px; border-bottom: 2px solid #059669; padding-bottom: 12px; }
    .header > div { display: table-cell; vertical-align: top; }
    .col-emisor { width: 60%; }
    .col-comprobante { width: 40%; text-align: center; }
    .logo { font-size: 24px; font-weight: bold; color: #059669; }
    .razon-social { font-size: 14px; font-weight: bold; margin-top: 4px; }
    .info-emisor { font-size: 10px; color: #475569; margin-top: 4px; line-height: 1.4; }

    .box-comprobante { border: 2px solid #059669; border-radius: 6px; padding: 8px; }
    .box-comprobante .ruc { font-size: 10px; color: #059669; font-weight: bold; }
    .box-comprobante .tipo { font-size: 13px; font-weight: bold; margin: 4px 0; }
    .box-comprobante .numero { font-size: 16px; font-weight: bold; color: #059669; font-family: 'Courier New', monospace; }

    .section-title { background: #059669; color: white; padding: 4px 8px; font-size: 11px; font-weight: bold; margin-bottom: 4px; border-radius: 3px; }
    .receptor { display: table; width: 100%; margin-bottom: 12px; }
    .receptor > div { display: table-cell; padding: 4px 0; }
    .receptor .label { width: 80px; font-weight: bold; color: #64748b; }

    table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
    table.items th { background: #f1f5f9; padding: 6px 4px; font-size: 9px; font-weight: bold; text-align: left; border-bottom: 2px solid #cbd5e1; text-transform: uppercase; color: #475569; }
    table.items td { padding: 6px 4px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
    table.items td.num { text-align: right; }
    table.items .codigo { color: #94a3b8; font-size: 8px; }

    .totales { float: right; width: 280px; margin-top: 8px; }
    .totales table { width: 100%; }
    .totales td { padding: 4px 8px; font-size: 11px; }
    .totales tr.total td { font-size: 14px; font-weight: bold; background: #059669; color: white; }
    .total-letras { clear: both; padding: 12px 0; font-size: 10px; font-style: italic; color: #475569; border-top: 1px dashed #cbd5e1; margin-top: 16px; }

    .footer { margin-top: 24px; display: table; width: 100%; }
    .footer > div { display: table-cell; vertical-align: top; }
    .qr-box { width: 130px; text-align: center; }
    .qr-img { width: 100px; height: 100px; }
    .hash-text { font-family: 'Courier New', monospace; font-size: 7px; word-break: break-all; color: #64748b; margin-top: 6px; }

    .estado-banner { background: #ecfdf5; border-left: 4px solid #10b981; padding: 8px 12px; margin-top: 16px; font-size: 10px; }
    .estado-banner.rechazado { background: #fef2f2; border-left-color: #ef4444; }
    .estado-banner.pendiente { background: #fefce8; border-left-color: #eab308; }

    .actions { margin-bottom: 16px; text-align: center; }
    .actions button { padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin: 0 4px; font-size: 13px; }
    .btn-print { background: #059669; color: white; }
    .btn-back { background: #64748b; color: white; text-decoration: none; padding: 10px 18px; display: inline-block; border-radius: 6px; }

    @media print {
        body { background: white; padding: 0; }
        .a4 { box-shadow: none; padding: 12mm; width: 100%; }
        .actions { display: none; }
    }
</style>
</head>
<body>
<div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
    <a class="btn-back" href="{{ url()->previous() }}">← Volver</a>
</div>

<div class="a4">
    <!-- Header -->
    <div class="header">
        <div class="col-emisor">
            @if($empresaGlobal && $empresaGlobal->logo_url)
                <img src="{{ $empresaGlobal->logo_url }}" style="max-height: 56px; max-width: 140px; margin-bottom: 6px;">
            @else
                <div class="logo">{{ $empresaGlobal->nombre_comercial ?? 'MINIMARKET' }}</div>
            @endif
            <div class="razon-social">{{ $comprobante->emisor_razon_social }}</div>
            <div class="info-emisor">
                @if($empresaGlobal && $empresaGlobal->direccion)
                    {{ $empresaGlobal->direccion }}
                    @if($empresaGlobal->distrito) - {{ $empresaGlobal->distrito }} @endif
                    <br>
                @endif
                @if($empresaGlobal && $empresaGlobal->telefono)
                    Tel: {{ $empresaGlobal->telefono }} •
                @endif
                @if($empresaGlobal && $empresaGlobal->email)
                    {{ $empresaGlobal->email }}
                @endif
            </div>
        </div>
        <div class="col-comprobante">
            <div class="box-comprobante">
                <div class="ruc">R.U.C. {{ $comprobante->emisor_ruc }}</div>
                <div class="tipo">{{ strtoupper($comprobante->tipo_documento_nombre) }} ELECTRÓNICA</div>
                <div class="numero">{{ $comprobante->numero_completo }}</div>
            </div>
        </div>
    </div>

    <!-- Receptor -->
    <div class="section-title">DATOS DEL CLIENTE</div>
    <div class="receptor">
        <div class="label">Razón Social:</div>
        <div>{{ $comprobante->receptor_razon_social }}</div>
    </div>
    <div class="receptor">
        <div class="label">{{ $comprobante->receptor_tipo_doc_label }}:</div>
        <div>{{ $comprobante->receptor_numero_doc }}</div>
        <div class="label">Fecha emisión:</div>
        <div>{{ $comprobante->fecha_emision->format('d/m/Y') }} {{ substr($comprobante->hora_emision, 0, 5) }}</div>
    </div>
    @if($comprobante->receptor_direccion)
    <div class="receptor">
        <div class="label">Dirección:</div>
        <div colspan="3">{{ $comprobante->receptor_direccion }}</div>
    </div>
    @endif
    @if($comprobante->doc_referencia_serie_numero)
    <div class="receptor">
        <div class="label" style="color: #ea580c;">Doc. referencia:</div>
        <div>{{ $comprobante->doc_referencia_serie_numero }} - {{ $comprobante->motivo_referencia }}</div>
    </div>
    @endif

    <!-- Items -->
    @if($comprobante->venta && $comprobante->venta->detalles->count())
    <div class="section-title" style="margin-top: 12px;">DETALLE DE PRODUCTOS</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 8%;">Cant.</th>
                <th style="width: 12%;">Unidad</th>
                <th>Descripción</th>
                <th style="width: 14%;" class="num">P. Unitario</th>
                <th style="width: 14%;" class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comprobante->venta->detalles as $d)
            <tr>
                <td class="num">{{ number_format($d->cantidad, 2) }}</td>
                <td>UND</td>
                <td>
                    {{ $d->descripcion }}
                    <div class="codigo">Cód: {{ $d->codigo }}</div>
                </td>
                <td class="num">{{ number_format($d->precio_unitario, 2) }}</td>
                <td class="num"><strong>{{ number_format($d->total, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Totales -->
    <div class="totales">
        <table>
            <tr>
                <td>Op. Gravadas:</td>
                <td style="text-align: right;">{{ $comprobante->moneda }} {{ number_format($comprobante->total_gravadas, 2) }}</td>
            </tr>
            @if($comprobante->total_exoneradas > 0)
            <tr><td>Op. Exoneradas:</td><td style="text-align: right;">{{ $comprobante->moneda }} {{ number_format($comprobante->total_exoneradas, 2) }}</td></tr>
            @endif
            <tr>
                <td>IGV (18%):</td>
                <td style="text-align: right;">{{ $comprobante->moneda }} {{ number_format($comprobante->total_igv, 2) }}</td>
            </tr>
            @if($comprobante->total_descuentos > 0)
            <tr><td>Descuento:</td><td style="text-align: right; color: #dc2626;">-{{ $comprobante->moneda }} {{ number_format($comprobante->total_descuentos, 2) }}</td></tr>
            @endif
            <tr class="total">
                <td>TOTAL:</td>
                <td style="text-align: right;">{{ $comprobante->moneda }} {{ number_format($comprobante->importe_total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="total-letras">
        SON: <strong>{{ $comprobante->importe_letras }}</strong>
    </div>

    <!-- Footer con QR -->
    <div class="footer">
        <div class="qr-box">
            @if($comprobante->qr_data)
                <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($comprobante->qr_data) }}&size=200x200" alt="QR SUNAT">
                <div style="font-size: 8px; color: #64748b; margin-top: 4px;">Representación impresa</div>
            @else
                <div style="border: 2px dashed #cbd5e1; padding: 30px; color: #94a3b8; font-size: 9px;">
                    QR disponible<br>después de envío<br>a SUNAT
                </div>
            @endif
        </div>
        <div style="padding-left: 16px;">
            @if($empresaGlobal && $empresaGlobal->mensaje_ticket)
                <p style="font-size: 11px; font-style: italic; color: #059669; margin-bottom: 8px;">"{{ $empresaGlobal->mensaje_ticket }}"</p>
            @endif

            @if($comprobante->estado_sunat === 'aceptado')
                <div class="estado-banner">
                    <strong>✓ COMPROBANTE ACEPTADO POR SUNAT</strong><br>
                    {{ $comprobante->mensaje_sunat ?? 'La operación se aceptó conforme' }}
                </div>
            @elseif($comprobante->estado_sunat === 'rechazado')
                <div class="estado-banner rechazado">
                    <strong>✗ RECHAZADO POR SUNAT</strong><br>
                    {{ $comprobante->mensaje_sunat }}
                </div>
            @else
                <div class="estado-banner pendiente">
                    <strong>⏳ PENDIENTE DE ENVÍO A SUNAT</strong>
                </div>
            @endif

            @if($comprobante->hash)
                <div class="hash-text">Hash: {{ $comprobante->hash }}</div>
            @endif

            <p style="font-size: 8px; color: #94a3b8; margin-top: 8px;">
                Autorizado mediante Resolución de Intendencia Nº 034-005-0008085/SUNAT.<br>
                Representación impresa del comprobante electrónico. Puede consultarlo en: <strong>www.sunat.gob.pe</strong>
            </p>
        </div>
    </div>
</div>

<script>setTimeout(() => window.print(), 800);</script>
</body>
</html>
