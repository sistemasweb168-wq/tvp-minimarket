<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $venta->numero_ticket }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; padding: 0; margin: 0; background: #f5f5f5; }
        .ticket {
            width: 80mm; max-width: 300px; margin: 20px auto; background: white;
            padding: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 12px; line-height: 1.4;
        }
        .header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 10px; margin-bottom: 10px; }
        .header h1 { margin: 5px 0; font-size: 16px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 11px; }
        .info { margin-bottom: 10px; font-size: 11px; }
        .info p { margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
        th { border-bottom: 1px solid #333; text-align: left; padding: 5px 2px; }
        td { padding: 4px 2px; vertical-align: top; }
        .total-row { border-top: 1px dashed #333; padding-top: 5px; }
        .total-row td { padding: 3px 2px; }
        .total-final { font-size: 14px; font-weight: bold; border-top: 2px solid #333; padding-top: 8px !important; }
        .footer { text-align: center; margin-top: 15px; font-size: 11px; border-top: 2px dashed #333; padding-top: 10px; }
        .actions { text-align: center; margin: 20px; }
        .actions button { padding: 10px 20px; margin: 5px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-print { background: #10b981; color: white; }
        .btn-close { background: #64748b; color: white; }
        @media print {
            body { background: white; padding: 0; }
            .ticket { box-shadow: none; margin: 0; padding: 5mm; }
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
            <img src="{{ $empresaGlobal->logo_url }}" style="max-width: 60px; max-height: 60px;" alt="logo">
        @endif
        <h1>{{ $empresaGlobal->nombre_comercial ?? $empresaGlobal->razon_social ?? 'TPV Minimarket' }}</h1>
        @if($empresaGlobal && $empresaGlobal->ruc_nit)
            <p>RUC/NIT: {{ $empresaGlobal->ruc_nit }}</p>
        @endif
        @if($empresaGlobal && $empresaGlobal->direccion)
            <p>{{ $empresaGlobal->direccion }}</p>
        @endif
        @if($empresaGlobal && $empresaGlobal->telefono)
            <p>Tel: {{ $empresaGlobal->telefono }}</p>
        @endif
    </div>

    <div class="info">
        <p><strong>TICKET DE VENTA</strong></p>
        <p>N°: <strong>{{ $venta->numero_ticket }}</strong></p>
        <p>Fecha: {{ $venta->fecha_venta->format('d/m/Y H:i') }}</p>
        <p>Cajero: {{ $venta->user->name }}</p>
        @if($venta->cliente)
            <p>Cliente: {{ $venta->cliente->nombre_completo }}</p>
            @if($venta->cliente->documento)
                <p>Doc: {{ $venta->cliente->documento }}</p>
            @endif
        @else
            <p>Cliente: Genérico</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $d)
                <tr>
                    <td>
                        {{ $d->descripcion }}<br>
                        <span style="font-size:10px; color:#666;">
                            {{ number_format($d->cantidad, 2) }} x {{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($d->precio_unitario, 2) }}
                        </span>
                    </td>
                    <td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($d->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Subtotal:</td>
                <td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->subtotal, 2) }}</td>
            </tr>
            @if($venta->descuento > 0)
                <tr><td>Descuento:</td><td style="text-align:right;">-{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->descuento, 2) }}</td></tr>
            @endif
            <tr><td>Impuesto:</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->impuesto, 2) }}</td></tr>
            <tr class="total-final"><td>TOTAL:</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->total, 2) }}</td></tr>
            <tr><td>Pago ({{ ucfirst($venta->forma_pago) }}):</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->monto_recibido, 2) }}</td></tr>
            <tr><td>Cambio:</td><td style="text-align:right;">{{ ($empresaGlobal->moneda ?? 'S/') }}{{ number_format($venta->cambio, 2) }}</td></tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>{{ $empresaGlobal->mensaje_ticket ?? '¡Gracias por su compra!' }}</p>
        <p style="margin-top: 10px;">--- Documento no fiscal ---</p>
    </div>
</div>
<script>setTimeout(() => window.print(), 500);</script>
</body>
</html>
