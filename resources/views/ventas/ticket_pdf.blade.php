@php
    $empresa = $empresa ?? \App\Models\Empresa::first();
    $esCPE = in_array($venta->tipo_comprobante, ['BOLETA', 'FACTURA']) || $venta->comprobanteElectronico;
    $numComprobante = $venta->comprobanteElectronico?->numero_completo ?? $venta->numero_ticket;
    $tipoLabel = $venta->tipo_comprobante === 'FACTURA' ? 'FACTURA ELECTRÓNICA' : ($venta->tipo_comprobante === 'BOLETA' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'NOTA DE VENTA / TICKET');
    
    $fechaEmision = date('d/m/Y H:i');
    if ($venta->fecha_venta) {
        $fechaEmision = is_string($venta->fecha_venta) 
            ? \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') 
            : $venta->fecha_venta->format('d/m/Y H:i');
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tipoLabel }} {{ $numComprobante }}</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            padding: 0; 
            margin: 0; 
            background: #ffffff; 
            font-size: 9.5pt;
            line-height: 1.3;
            color: #000000;
        }
        .ticket {
            width: 100%;
            padding: 0;
        }
        .header { 
            text-align: center; 
            border-bottom: 1px dashed #000; 
            padding-bottom: 6px; 
            margin-bottom: 6px; 
        }
        .logo-ticket {
            max-width: 85px;
            max-height: 55px;
            margin: 0 auto 4px auto;
        }
        .header h1 { margin: 1px 0; font-size: 11.5pt; font-weight: bold; }
        .header p { margin: 1px 0; font-size: 8.5pt; }
        
        .cpe-badge {
            border: 1px solid #000; 
            padding: 3px; 
            text-align: center; 
            font-weight: bold; 
            font-size: 9pt;
            margin: 4px 0 2px 0;
            text-transform: uppercase;
        }
        .cpe-numero { font-size: 10.5pt; text-align: center; font-weight: bold; margin-bottom: 6px; }

        .info { margin-bottom: 6px; font-size: 8.5pt; }
        .info p { margin: 1px 0; }
        table { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 8.5pt; }
        th { border-bottom: 1px solid #000; text-align: left; padding: 3px 1px; }
        td { padding: 2px 1px; vertical-align: top; }
        .total-row { border-top: 1px dashed #000; padding-top: 3px; }
        .total-row td { padding: 1px 1px; }
        .total-final { font-size: 10.5pt; font-weight: bold; border-top: 1.5px solid #000; padding-top: 4px !important; }
        
        .qr-box { text-align: center; margin: 8px 0; padding-top: 6px; border-top: 1px dashed #999; }
        .qr-img { width: 95px; height: 95px; }
        
        .footer { text-align: center; margin-top: 8px; font-size: 8pt; border-top: 1px dashed #000; padding-top: 6px; }
    </style>
</head>
<body>

<div class="ticket">
    <div class="header">
        @if($empresa && $empresa->logo_base64)
            <img src="{{ $empresa->logo_base64 }}" class="logo-ticket" alt="Logo"><br>
        @elseif($empresa && $empresa->logo_url)
            <img src="{{ $empresa->logo_url }}" class="logo-ticket" alt="Logo"><br>
        @endif
        <h1>{{ $empresa?->nombre_comercial ?? $empresa?->razon_social ?? 'MIKITO\'S LICORERÍA' }}</h1>
        @if($empresa && $empresa->razon_social)
            <p>{{ $empresa->razon_social }}</p>
        @endif
        @if($empresa && $empresa->ruc_nit)
            <p><strong>RUC: {{ $empresa->ruc_nit }}</strong></p>
        @endif
        @if($empresa && $empresa->direccion)
            <p>{{ $empresa->direccion }}</p>
        @endif
        @if($empresa && $empresa->telefono)
            <p>Tel: {{ $empresa->telefono }}</p>
        @endif
    </div>

    <div class="cpe-badge">{{ $tipoLabel }}</div>
    <div class="cpe-numero">{{ $numComprobante }}</div>

    <div class="info">
        <p><strong>Fecha:</strong> {{ $fechaEmision }}</p>
        <p><strong>Cajero:</strong> {{ $venta->user?->name ?? 'Cajero' }}</p>
        <p><strong>Cliente:</strong> {{ $venta->cliente?->nombre_completo ?? $venta->cliente?->nombres ?? 'PÚBLICO GENERAL' }}</p>
        @if($venta->cliente && $venta->cliente->documento)
            <p><strong>{{ $venta->cliente->tipo_documento ?? 'DOC' }}:</strong> {{ $venta->cliente->documento }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Desc.</th>
                <th style="text-align: center; width: 15%;">Cant.</th>
                <th style="text-align: right; width: 17%;">P.U.</th>
                <th style="text-align: right; width: 18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles ?? [] as $d)
                <tr>
                    <td>{{ $d->producto?->nombre ?? $d->descripcion ?? 'Producto' }}</td>
                    <td style="text-align: center;">{{ number_format($d->cantidad ?? 1, 0) }}</td>
                    <td style="text-align: right;">{{ number_format($d->precio_unitario ?? 0, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($d->subtotal ?? 0, 2) }}</td>
                </tr>
            @endforeach

            @if(($venta->descuento ?? 0) > 0)
                <tr class="total-row">
                    <td colspan="3">Descuento Global:</td>
                    <td style="text-align: right;">-S/ {{ number_format($venta->descuento, 2) }}</td>
                </tr>
            @endif

            @if($esCPE)
                <tr class="total-row">
                    <td colspan="3">Op. Gravada:</td>
                    <td style="text-align: right;">S/ {{ number_format($venta->subtotal ?? (($venta->total ?? 0) / 1.18), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3">I.G.V. (18%):</td>
                    <td style="text-align: right;">S/ {{ number_format($venta->impuesto ?? (($venta->total ?? 0) - (($venta->total ?? 0) / 1.18)), 2) }}</td>
                </tr>
            @endif

            <tr class="total-final">
                <td colspan="2">TOTAL A PAGAR:</td>
                <td colspan="2" style="text-align: right;">S/ {{ number_format($venta->total ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="font-size: 8pt; margin-top: 3px; border-top: 1px dashed #000; padding-top: 3px;">
        <p style="margin: 1px 0;"><strong>Forma de Pago:</strong> {{ strtoupper($venta->forma_pago ?? $venta->metodo_pago ?? 'EFECTIVO') }}</p>
        @if(($venta->monto_recibido ?? 0) > 0)
            <p style="margin: 1px 0;"><strong>Recibido:</strong> S/ {{ number_format($venta->monto_recibido, 2) }} | <strong>Vuelto:</strong> S/ {{ number_format($venta->cambio ?? 0, 2) }}</p>
        @endif
    </div>

    @if($esCPE && $venta->comprobanteElectronico && $venta->comprobanteElectronico->qr_data)
        <div class="qr-box">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=95x95&data={{ urlencode($venta->comprobanteElectronico->qr_data) }}" class="qr-img" alt="QR SUNAT">
            <p style="font-size: 7.5pt; margin-top: 2px;">Representación impresa de CPE</p>
        </div>
    @endif

    <div class="footer">
        <p>{{ $empresa?->mensaje_ticket ?? '¡Gracias por su preferencia!' }}</p>
        <p style="font-weight: bold;">{{ $empresa?->nombre_comercial ?? 'Mikito\'s Licorería' }}</p>
        <p style="font-size: 7pt; color: #555;">{{ date('d/m/Y H:i:s') }}</p>
    </div>
</div>

</body>
</html>
