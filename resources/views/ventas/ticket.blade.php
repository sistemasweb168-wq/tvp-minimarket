@php
    $empresa = $empresa ?? $empresaGlobal ?? \App\Models\Empresa::first();
    $esCPE = in_array($venta->tipo_comprobante, ['BOLETA', 'FACTURA']) || $venta->comprobanteElectronico;
    $numComprobante = $venta->comprobanteElectronico?->numero_completo ?? $venta->numero_ticket;
    $tipoLabel = $venta->tipo_comprobante === 'FACTURA' ? 'FACTURA ELECTRÓNICA' : ($venta->tipo_comprobante === 'BOLETA' ? 'BOLETA DE VENTA ELECTRÓNICA' : 'NOTA DE VENTA / TICKET');
    
    // Fecha segura
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tipoLabel }} {{ $numComprobante }} - {{ $empresa?->nombre_comercial ?? 'Mikito\'s Licorería' }}</title>
    
    <!-- OpenGraph Tags para vista previa de WhatsApp -->
    <meta property="og:title" content="¡Gracias por tu compra en {{ $empresa?->nombre_comercial ?? 'Mikito\'s Licorería' }}!">
    <meta property="og:description" content="Comprobante Digital {{ $numComprobante }} por S/ {{ number_format($venta->total ?? 0, 2) }}">
    <meta property="og:image" content="{{ asset('img/banner_agradecimiento.jpg') }}">
    <meta property="og:type" content="website">

    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
            padding: 0; 
            margin: 0; 
            background: #0f172a; 
            color: #1e293b;
        }

        /* Banner de Agradecimiento solo en Web / Celular */
        .digital-wrapper {
            max-width: 480px;
            margin: 0 auto;
            padding: 15px 12px 40px 12px;
        }
        .thank-you-banner {
            width: 100%;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.5);
            display: block;
            margin-bottom: 15px;
            border: 2px solid rgba(245, 158, 11, 0.4);
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
        }
        .btn-action {
            flex: 1;
            padding: 12px 16px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 13px;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-print { background: #10b981; color: white; }
        .btn-print:hover { background: #059669; }
        .btn-pdf { background: #f59e0b; color: #0f172a; }
        .btn-pdf:hover { background: #d97706; }

        /* El Ticket Térmico Limpio y Formal */
        .ticket {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            background: #ffffff;
            padding: 16px 14px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
            color: #000000;
        }

        .header { 
            text-align: center; 
            border-bottom: 2px dashed #000; 
            padding-bottom: 10px; 
            margin-bottom: 10px; 
        }
        .header img.logo-ticket {
            max-width: 100px;
            max-height: 65px;
            object-fit: contain;
            display: block;
            margin: 0 auto 6px auto;
        }
        .header h1 { margin: 2px 0; font-size: 15px; font-weight: bold; }
        .header p { margin: 1px 0; font-size: 11px; font-weight: 700; }
        
        .cpe-badge {
            background: #f1f5f9; 
            border: 1px solid #000; 
            color: #000;
            padding: 4px; 
            text-align: center; 
            font-weight: bold; 
            font-size: 12px;
            margin: 8px 0 4px 0;
            text-transform: uppercase;
        }
        .cpe-numero { font-size: 14px; text-align: center; font-weight: bold; margin-bottom: 8px; }

        .info { margin-bottom: 8px; font-size: 11px; font-weight: 700; }
        .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 11px; font-weight: 700; }
        th { border-bottom: 1px solid #000; text-align: left; padding: 4px 1px; font-weight: 900; }
        td { padding: 3px 1px; vertical-align: top; font-weight: 700; }
        .total-row { border-top: 1px dashed #000; padding-top: 4px; }
        .total-row td { padding: 2px 1px; }
        .total-final { font-size: 14px; font-weight: 900; border-top: 2px solid #000; padding-top: 6px !important; }
        
        .qr-box { text-align: center; margin: 10px 0; padding-top: 8px; border-top: 1px dashed #999; }
        .qr-img { width: 115px; height: 115px; }
        
        .footer { text-align: center; margin-top: 10px; font-size: 10px; font-weight: 700; border-top: 2px dashed #000; padding-top: 8px; }

        /* ================= IMPRESIÓN (OCULTA BANNER Y DEJA TICKET LIMPIO) ================= */
        @media print {
            body { background: white !important; padding: 0 !important; font-weight: 700 !important; color: #000 !important; }
            .no-print { display: none !important; }
            .digital-wrapper { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .ticket { 
                box-shadow: none !important; 
                border-radius: 0 !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 75mm !important; 
                max-width: 75mm !important; 
            }
        }
        @page { margin: 0; }
    </style>
</head>
<body>

<div class="digital-wrapper">
    
    <!-- BANNER DE AGRADECIMIENTO EN PANTALLA DIGITAL -->
    <div class="no-print">
        <img src="{{ asset('img/banner_agradecimiento.jpg') }}" alt="¡Gracias por tu compra!" class="thank-you-banner">

        <div class="action-buttons">
            <button type="button" class="btn-action btn-print" onclick="window.print()">
                🖨️ Imprimir Ticket
            </button>
            <a href="{{ route('ventas.ticket-pdf', $venta->id) }}" class="btn-action btn-pdf">
                📄 Descargar en PDF
            </a>
        </div>
    </div>

    <!-- TICKET FÍSICO / FORMAL -->
    <div class="ticket">
        <div class="header">
            @if($empresa && $empresa->logo_url)
                <img src="{{ $empresa->logo_url }}" class="logo-ticket" alt="Logo">
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
            <p><strong>Atendido por:</strong> {{ $venta->user?->name ?? 'Cajero' }}</p>
            <p><strong>Cliente:</strong> {{ $venta->cliente?->nombre_completo ?? $venta->cliente?->nombres ?? 'PÚBLICO GENERAL' }}</p>
            @if($venta->cliente && $venta->cliente->documento)
                <p><strong>{{ $venta->cliente->tipo_documento ?? 'DOC' }}:</strong> {{ $venta->cliente->documento }}</p>
            @endif
            @if($venta->cliente && $venta->cliente->direccion)
                <p><strong>Dirección:</strong> {{ $venta->cliente->direccion }}</p>
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
                        <td>
                            {{ $d->descripcion ?? $d->producto?->nombre ?? 'Producto' }}
                            @if(($d->descuento ?? 0) > 0)
                                <br><small style="color: #000;">(Desc. -S/ {{ number_format($d->descuento, 2) }})</small>
                            @endif
                        </td>
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
                    <td colspan="2" style="font-size: 13px;">TOTAL A PAGAR:</td>
                    <td colspan="2" style="text-align: right; font-size: 14px;">S/ {{ number_format($venta->total ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Métodos de Pago -->
        <div style="font-size: 10px; margin-top: 4px; border-top: 1px dashed #000; padding-top: 4px;">
            <p style="margin: 2px 0;"><strong>Forma de Pago:</strong> {{ strtoupper($venta->forma_pago ?? $venta->metodo_pago ?? 'EFECTIVO') }}</p>
            @if(($venta->monto_recibido ?? 0) > 0)
                <p style="margin: 2px 0;"><strong>Recibido:</strong> S/ {{ number_format($venta->monto_recibido, 2) }} | <strong>Vuelto:</strong> S/ {{ number_format($venta->cambio ?? 0, 2) }}</p>
            @endif
        </div>

        @if($esCPE && $venta->comprobanteElectronico && $venta->comprobanteElectronico->qr_data)
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=115x115&data={{ urlencode($venta->comprobanteElectronico->qr_data) }}" class="qr-img" alt="QR SUNAT">
                <p style="font-size: 9px; margin-top: 2px;">Representación impresa de CPE</p>
            </div>
        @endif

        <div class="footer">
            <p>{{ $empresa?->mensaje_ticket ?? '¡Gracias por su preferencia!' }}</p>
            <p style="font-weight: bold;">{{ $empresa?->nombre_comercial ?? 'Mikito\'s Licorería' }}</p>
            <p style="font-size: 8.5px; color: #555;">{{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>

</div>

</body>
</html>
