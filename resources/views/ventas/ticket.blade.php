<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>
        @if($venta->tipo_comprobante === 'FACTURA')
            Factura {{ $venta->comprobanteElectronico?->numero_completo ?? $venta->numero_ticket }}
        @elseif($venta->tipo_comprobante === 'BOLETA')
            Boleta {{ $venta->comprobanteElectronico?->numero_completo ?? $venta->numero_ticket }}
        @else
            Ticket {{ $venta->numero_ticket }}
        @endif
    </title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; padding: 0; margin: 0; background: #f5f5f5; }
        .ticket {
            width: 75mm; max-width: 100%; margin: 20px auto; background: white;
            padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 11px; line-height: 1.35;
        }
        @media print {
            body { background: white; }
            .ticket { margin: 0; padding: 0; box-shadow: none; width: 75mm; }
            .no-print { display: none !important; }
        }
        @page { margin: 0; }
        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 8px; margin-bottom: 8px; }
        .header h1 { margin: 3px 0; font-size: 15px; font-weight: bold; }
        .header p { margin: 1px 0; font-size: 10px; }
        
        .cpe-badge {
            background: #f0fdf4; border: 1px solid #10b981; color: #065f46;
            padding: 4px; text-align: center; font-weight: bold; font-size: 12px;
            margin: 6px 0;
        }
        .cpe-numero { font-size: 13px; text-align: center; font-weight: bold; margin-bottom: 6px; }

        .info { margin-bottom: 8px; font-size: 10.5px; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10.5px; }
        th { border-bottom: 1px solid #333; text-align: left; padding: 4px 1px; }
        td { padding: 3px 1px; vertical-align: top; }
        .total-row { border-top: 1px dashed #333; padding-top: 4px; }
        .total-row td { padding: 2px 1px; }
        .total-final { font-size: 13px; font-weight: bold; border-top: 2px solid #333; padding-top: 6px !important; }
        
        .qr-box { text-align: center; margin: 10px 0; padding-top: 8px; border-top: 1px dashed #999; }
        .qr-img { width: 115px; height: 115px; }
        
        .footer { text-align: center; margin-top: 10px; font-size: 9.5px; border-top: 2px dashed #333; padding-top: 8px; }
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
<div class="actions no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
    <button class="btn-close" onclick="window.close()">Cerrar</button>
</div>

<div class="ticket">
    <div class="header">
        @if($empresaGlobal && $empresaGlobal->logo_url)
            <img src="{{ $empresaGlobal->logo_url }}" style="max-width: 65px; max-height: 55px; margin-bottom: 4px;" alt="logo">
        @endif
        <h1>{{ $empresaGlobal->nombre_comercial ?? $empresaGlobal->razon_social ?? 'MINIMARKET VALEZKA' }}</h1>
        @if($empresaGlobal && $empresaGlobal->razon_social)
            <p>{{ $empresaGlobal->razon_social }}</p>
        @endif
        @if($empresaGlobal && $empresaGlobal->ruc_nit)
            <p><strong>RUC: {{ $empresaGlobal->ruc_nit }}</strong></p>
        @endif
        @if($empresaGlobal && $empresaGlobal->direccion)
            <p>{{ $empresaGlobal->direccion }}</p>
        @endif
        @if($empresaGlobal && $empresaGlobal->telefono)
            <p>Tel: {{ $empresaGlobal->telefono }}</p>
        @endif
    </div>

    <!-- Título según tipo de comprobante -->
    @php
        $esCPE = in_array($venta->tipo_comprobante, ['BOLETA', 'FACTURA']) || $venta->comprobanteElectronico;
        $numComprobante = $venta->comprobanteElectronico?->numero_completo ?? $venta->numero_ticket;
        $tipoLabel = $venta->tipo_comprobante === 'FACTURA' ? 'FACTURA ELECTRÓNICA' : ($venta->tipo_comprobante === 'BOLETA' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'NOTA DE VENTA / TICKET');
    @endphp

    <div class="cpe-badge">{{ $tipoLabel }}</div>
    <div class="cpe-numero">{{ $numComprobante }}</div>

    <div class="info">
        <p><strong>Fecha Emisión:</strong> {{ $venta->fecha_venta->format('d/m/Y H:i') }}</p>
        <p><strong>Cajero:</strong> {{ $venta->user->name }}</p>
        @if($venta->cliente)
            <p><strong>Cliente:</strong> {{ $venta->cliente->nombre_completo }}</p>
            @if($venta->cliente->documento)
                <p><strong>{{ $venta->cliente->tipo_documento ?? 'Doc' }}:</strong> {{ $venta->cliente->documento }}</p>
            @endif
            @if($venta->cliente->direccion)
                <p><strong>Dirección:</strong> {{ Str::limit($venta->cliente->direccion, 40) }}</p>
            @endif
        @else
            <p><strong>Cliente:</strong> CLIENTES VARIOS</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>DESCRIPCIÓN</th>
                <th style="text-align:right;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $d)
                <tr>
                    <td>
                        <strong>{{ $d->descripcion }}</strong><br>
                        <span style="font-size:9.5px; color:#555;">
                            {{ number_format($d->cantidad, 2) }} × {{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($d->precio_unitario, 2) }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:bold;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($d->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Op. Gravadas:</td>
                <td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->subtotal, 2) }}</td>
            </tr>
            @if($venta->descuento > 0)
                <tr><td>Descuento:</td><td style="text-align:right;">-{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->descuento, 2) }}</td></tr>
            @endif
            <tr><td>I.G.V. (18%):</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->impuesto, 2) }}</td></tr>
            <tr class="total-final"><td>TOTAL A PAGAR:</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->total, 2) }}</td></tr>
            <tr><td>Forma de Pago ({{ ucfirst($venta->forma_pago) }}):</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->monto_recibido, 2) }}</td></tr>
            @if($venta->forma_pago === 'efectivo')
                <tr><td>Vuelto / Cambio:</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->cambio, 2) }}</td></tr>
            @endif
        </tfoot>
    </table>

    <!-- Código QR Oficial (para Boleta, Factura o Comprobante) -->
    @php
        $rucEmisor = $empresaGlobal->ruc_nit ?? '20123456789';
        $tipoDocSunat = $venta->tipo_comprobante === 'FACTURA' ? '01' : '03';
        $serieCorrelativo = $numComprobante;
        $docReceptor = $venta->cliente?->documento ?? '00000000';
        $tipoDocReceptor = strlen($docReceptor) === 11 ? '6' : (strlen($docReceptor) === 8 ? '1' : '0');
        
        $qrString = $venta->comprobanteElectronico?->qr_data ?: "{$rucEmisor}|{$tipoDocSunat}|{$serieCorrelativo}|{$venta->impuesto}|{$venta->total}|{$venta->fecha_venta->format('Y-m-d')}|{$tipoDocReceptor}|{$docReceptor}|";
    @endphp

    @if($esCPE)
        <div class="qr-box">
            <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($qrString) }}&size=200x200" alt="Código QR SUNAT">
            <p style="font-size:9px; margin-top:4px; font-weight:bold;">Representación impresa de la {{ $tipoLabel }}</p>
            @if($venta->comprobanteElectronico && $venta->comprobanteElectronico->hash)
                <p style="font-size:8px; color:#555; word-break:break-all; margin-top:2px;">Hash: {{ Str::limit($venta->comprobanteElectronico->hash, 35) }}</p>
            @endif
        </div>
    @endif

    <div class="footer">
        <p><strong>{{ $empresaGlobal->mensaje_ticket ?? '¡Gracias por su preferencia! Vuelva pronto.' }}</strong></p>
        @if($esCPE)
            <p style="margin-top: 4px;">Consulte su comprobante en <strong>www.sunat.gob.pe</strong></p>
        @else
            <p style="margin-top: 4px; color: #888;">--- Comprobante de Control Interno ---</p>
        @endif
    </div>
</div>
<script>setTimeout(() => window.print(), 600);</script>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
