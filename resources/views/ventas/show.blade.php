@extends('layouts.app')
@section('title', 'Venta ' . $venta->numero_ticket)
@section('header', 'Detalle de Venta')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-md p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Ticket {{ $venta->numero_ticket }}</h2>
                <p class="text-slate-500">{{ $venta->fecha_venta->format('d/m/Y H:i:s') }}</p>
            </div>
            @if($venta->estado == 'completada')
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">Completada</span>
            @else
                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">Anulada</span>
            @endif
        </div>

        <table class="w-full">
            <thead class="border-b-2 border-slate-200 text-xs uppercase text-slate-500">
                <tr>
                    <th class="text-left py-2 px-2">Producto</th>
                    <th class="text-right py-2 px-2">Cantidad</th>
                    <th class="text-right py-2 px-2">Precio</th>
                    <th class="text-right py-2 px-2">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($venta->detalles as $d)
                <tr class="border-b border-slate-100">
                    <td class="py-3 px-2">
                        <p class="font-semibold">{{ $d->descripcion }}</p>
                        <p class="text-xs text-slate-500">{{ $d->codigo }}</p>
                    </td>
                    <td class="py-3 px-2 text-right">{{ number_format($d->cantidad, 2) }}</td>
                    <td class="py-3 px-2 text-right">{{ $moneda }}{{ number_format($d->precio_unitario, 2) }}</td>
                    <td class="py-3 px-2 text-right font-bold">{{ $moneda }}{{ number_format($d->total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-6 pt-4 border-t-2 border-slate-200 space-y-2 text-right">
            <div class="flex justify-end gap-8"><span class="text-slate-600">Subtotal:</span><span class="font-semibold w-32">{{ $moneda }}{{ number_format($venta->subtotal, 2) }}</span></div>
            @if($venta->descuento > 0)
                <div class="flex justify-end gap-8"><span class="text-slate-600">Descuento:</span><span class="font-semibold w-32 text-red-600">-{{ $moneda }}{{ number_format($venta->descuento, 2) }}</span></div>
            @endif
            <div class="flex justify-end gap-8"><span class="text-slate-600">Impuesto:</span><span class="font-semibold w-32">{{ $moneda }}{{ number_format($venta->impuesto, 2) }}</span></div>
            <div class="flex justify-end gap-8 text-2xl pt-2 border-t border-slate-200"><span class="font-bold">TOTAL:</span><span class="font-bold w-32 text-emerald-600">{{ $moneda }}{{ number_format($venta->total, 2) }}</span></div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h3 class="font-bold mb-3">Información</h3>
            <div class="space-y-2 text-sm">
                <p><span class="text-slate-500">Cliente:</span> <strong>{{ $venta->cliente?->nombre_completo ?? 'Genérico' }}</strong></p>
                <p><span class="text-slate-500">Cajero:</span> <strong>{{ $venta->user->name }}</strong></p>
                <p><span class="text-slate-500">Forma de pago:</span> <strong>{{ ucfirst($venta->forma_pago) }}</strong></p>
                <p><span class="text-slate-500">Recibido:</span> <strong>{{ $moneda }}{{ number_format($venta->monto_recibido, 2) }}</strong></p>
                <p><span class="text-slate-500">Cambio:</span> <strong>{{ $moneda }}{{ number_format($venta->cambio, 2) }}</strong></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 space-y-2">
            <a href="{{ route('ventas.ticket', $venta->id) }}" target="_blank" class="block text-center gradient-primary text-white py-2.5 rounded-lg font-semibold"><i class="fas fa-print mr-1"></i>Imprimir Ticket</a>

            @if($venta->estado == 'completada' && ($empresaGlobal->facturacion_electronica_activa ?? false))
                @if($venta->comprobante_electronico_id)
                    @php $cpe = \App\Models\ComprobanteElectronico::find($venta->comprobante_electronico_id); @endphp
                    @if($cpe)
                    <a href="{{ route('facturacion.show', $cpe->id) }}" class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-2.5 rounded-lg font-semibold">
                        <i class="fas fa-file-invoice mr-1"></i>Ver {{ $cpe->tipo_documento_nombre }} {{ $cpe->numero_completo }}
                    </a>
                    @endif
                @else
                    <form method="POST" action="{{ route('facturacion.emitir', $venta->id) }}" class="space-y-2">
                        @csrf
                        <p class="text-xs font-semibold text-slate-600 mb-1">Emitir comprobante electrónico:</p>
                        <button name="tipo_documento" value="03" class="block w-full text-center bg-emerald-500 hover:bg-emerald-600 text-white py-2.5 rounded-lg font-semibold">
                            <i class="fas fa-receipt mr-1"></i>Emitir Boleta
                        </button>
                        @if($venta->cliente && strtoupper($venta->cliente->tipo_documento) === 'RUC')
                        <button name="tipo_documento" value="01" class="block w-full text-center bg-purple-500 hover:bg-purple-600 text-white py-2.5 rounded-lg font-semibold">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>Emitir Factura
                        </button>
                        @endif
                    </form>
                @endif
            @endif

            @if($venta->estado == 'completada')
                <form method="POST" action="{{ route('ventas.anular', $venta->id) }}" onsubmit="return confirm('¿Anular esta venta? Se devolverá el stock.')">
                    @csrf
                    <button class="block w-full text-center bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-lg font-semibold"><i class="fas fa-ban mr-1"></i>Anular Venta</button>
                </form>
            @endif
            <a href="{{ route('ventas.index') }}" class="block text-center bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg">Volver</a>
        </div>
    </div>
</div>
@endsection
