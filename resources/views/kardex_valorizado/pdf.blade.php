<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Valorizado</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #065f46; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 10px; }
        .resumen { background: #ecfdf5; border: 1px solid #10b981; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; text-align: right; }
        .resumen h2 { margin: 0; color: #047857; font-size: 20px; }
        .resumen p { margin: 0; font-weight: bold; font-size: 12px; }
        table { w-full; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 9px; padding: 8px; border-bottom: 1px solid #cbd5e1; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-row { background-color: #f8fafc; font-weight: bold; }
        .footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inventario Valorizado General</h1>
        <p>Generado el: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="resumen">
        <p>VALOR TOTAL EN TIENDA:</p>
        <h2>S/ {{ number_format($totalValorizado, 2) }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto / Categoría</th>
                <th class="text-center">Stock Físico</th>
                <th class="text-right">Costo Und.</th>
                <th class="text-right">Total Valorizado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
                <tr>
                    <td>{{ $producto->codigo }}</td>
                    <td>
                        <strong>{{ $producto->nombre }}</strong><br>
                        <span style="color:#64748b; font-size: 9px;">{{ $producto->categoria?->nombre }}</span>
                    </td>
                    <td class="text-center font-bold">{{ number_format($producto->stock, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($producto->precio_compra, 2) }}</td>
                    <td class="text-right font-bold" style="color: #047857;">S/ {{ number_format($producto->valor_total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">GRAN TOTAL:</td>
                <td class="text-right" style="color: #047857;">S/ {{ number_format($totalValorizado, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Este documento es un resumen administrativo del valor del inventario basado en el costo de compra actual registrado en el sistema.
    </div>
</body>
</html>
