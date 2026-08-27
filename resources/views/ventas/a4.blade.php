<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $venta->numero_ticket }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .header td { vertical-align: top; }
        .logo { max-width: 200px; max-height: 80px; }
        .empresa-info { text-align: right; }
        .empresa-nombre { font-size: 20px; font-weight: bold; color: #1f2937; margin: 0 0 5px 0; }
        .comprobante-box { border: 2px solid #10b981; padding: 15px; text-align: center; border-radius: 8px; margin-top: 10px; width: 250px; float: right; clear: both; }
        .comprobante-tipo { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 0 0 5px 0; }
        .comprobante-numero { font-size: 16px; margin: 0; }
        .cliente-info { margin-bottom: 30px; background: #f9fafb; padding: 15px; border-radius: 8px; }
        .cliente-info table { width: 100%; }
        .cliente-info td { padding: 4px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #10b981; color: white; padding: 10px; text-align: left; }
        .items-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .items-table th.right, .items-table td.right { text-align: right; }
        .totales { width: 300px; float: right; }
        .totales table { width: 100%; }
        .totales td { padding: 5px 0; }
        .totales .total-row { font-size: 18px; font-weight: bold; border-top: 2px solid #10b981; padding-top: 10px; }
        .footer { clear: both; margin-top: 50px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 20px; }
        .estado-anulado { color: red; font-weight: bold; font-size: 24px; text-align: center; border: 3px solid red; padding: 10px; margin-bottom: 20px; transform: rotate(-5deg); }
    </style>
</head>
<body>
    @if($venta->estado === 'anulada')
        <div class="estado-anulado">VENTA ANULADA</div>
    @endif

    <div class="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    @if($empresa && $empresa->logo)
                        <img src="{{ public_path('uploads/logos/' . $empresa->logo) }}" class="logo">
                    @else
                        <h1 style="color: #10b981; margin:0;">BODEGA VALEZKA</h1>
                    @endif
                    <div style="margin-top: 15px;">
                        <strong>{{ $empresa->razon_social ?? 'BODEGA VALEZKA' }}</strong><br>
                        RUC: {{ $empresa->ruc ?? '20100100100' }}<br>
                        {{ $empresa->direccion ?? 'Av. Principal 123' }}<br>
                        Tel: {{ $empresa->telefono ?? '01-555-1234' }}
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="comprobante-box">
                        <h2 class="comprobante-tipo">
                            @if($venta->tipo_comprobante === 'FACTURA') FACTURA ELECTRÓNICA
                            @elseif($venta->tipo_comprobante === 'BOLETA') BOLETA DE VENTA ELECTRÓNICA
                            @else NOTA DE VENTA
                            @endif
                        </h2>
                        <p class="comprobante-numero">RUC: {{ $empresa->ruc ?? '20100100100' }}</p>
                        <p class="comprobante-numero">{{ $venta->comprobanteElectronico?->numero_completo ?? $venta->numero_ticket }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="cliente-info">
        <table>
            <tr>
                <td style="width: 15%;"><strong>Señor(es):</strong></td>
                <td style="width: 45%;">{{ $venta->cliente ? $venta->cliente->nombres . ' ' . $venta->cliente->apellidos : 'CLIENTES VARIOS' }}</td>
                <td style="width: 15%;"><strong>Fecha:</strong></td>
                <td style="width: 25%;">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Documento:</strong></td>
                <td>{{ $venta->cliente->documento ?? '---' }}</td>
                <td><strong>Forma de Pago:</strong></td>
                <td>{{ ucfirst($venta->forma_pago) }}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong></td>
                <td colspan="3">{{ $venta->cliente->direccion ?? '---' }}</td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;">CANT.</th>
                <th style="width: 50%;">DESCRIPCIÓN</th>
                <th class="right" style="width: 20%;">PRECIO UNIT.</th>
                <th class="right" style="width: 20%;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ number_format($detalle->cantidad, 2) }}</td>
                <td>{{ $detalle->producto ? $detalle->producto->nombre : 'Producto Eliminado' }}</td>
                <td class="right">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="right">S/ {{ number_format($detalle->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td style="text-align: right;">S/ {{ number_format($venta->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Descuento:</td>
                <td style="text-align: right;">S/ {{ number_format($venta->descuento, 2) }}</td>
            </tr>
            <tr>
                <td>I.G.V. (18%):</td>
                <td style="text-align: right;">S/ {{ number_format($venta->impuesto, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL:</td>
                <td style="text-align: right;">S/ {{ number_format($venta->total, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <div style="clear: both;"></div>

    <div class="footer">
        <p>Gracias por su preferencia.</p>
        <p>Representación impresa de la {{ $venta->tipo_comprobante ?? 'Nota de Venta' }}</p>
    </div>
</body>
</html>
