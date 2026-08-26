@extends('layouts.app')
@section('title', 'Comprobante ' . $comprobante->numero_completo)
@section('header', $comprobante->tipo_documento_nombre . ' ' . $comprobante->numero_completo)

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; $color = $comprobante->estado_color; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Lado izquierdo: detalle -->
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl shadow-md p-6">
            <div class="flex flex-wrap justify-between items-start gap-3 pb-4 border-b border-slate-200">
                <div>
                    <p class="text-xs text-slate-500 uppercase">{{ $comprobante->tipo_documento_nombre }}</p>
                    <h2 class="text-2xl font-bold text-slate-800 font-mono">{{ $comprobante->numero_completo }}</h2>
                    <p class="text-sm text-slate-500 mt-1">Emitida el {{ $comprobante->fecha_emision->format('d/m/Y') }} a las {{ $comprobante->hora_emision }}</p>
                </div>
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-{{ $color }}-100 text-{{ $color }}-700 rounded-full font-semibold">
                    <span class="w-2 h-2 bg-{{ $color }}-500 rounded-full animate-pulse"></span>
                    {{ ucfirst($comprobante->estado_sunat) }}
                </span>
            </div>

            <!-- Emisor / Receptor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                <div>
                    <h4 class="text-xs uppercase text-slate-500 font-semibold mb-2"><i class="fas fa-store mr-1"></i>Emisor</h4>
                    <p class="font-bold">{{ $comprobante->emisor_razon_social }}</p>
                    <p class="text-sm text-slate-600">RUC: {{ $comprobante->emisor_ruc }}</p>
                </div>
                <div>
                    <h4 class="text-xs uppercase text-slate-500 font-semibold mb-2"><i class="fas fa-user mr-1"></i>Receptor</h4>
                    <p class="font-bold">{{ $comprobante->receptor_razon_social }}</p>
                    <p class="text-sm text-slate-600">{{ $comprobante->receptor_tipo_doc_label }}: {{ $comprobante->receptor_numero_doc }}</p>
                    @if($comprobante->receptor_direccion)
                        <p class="text-xs text-slate-500">{{ $comprobante->receptor_direccion }}</p>
                    @endif
                </div>
            </div>

            <!-- Referencia (para notas) -->
            @if($comprobante->doc_referencia_serie_numero)
            <div class="mt-4 p-3 bg-orange-50 border-l-4 border-orange-400 rounded">
                <p class="text-sm"><strong>Documento de referencia:</strong> {{ $comprobante->doc_referencia_serie_numero }}</p>
                <p class="text-sm"><strong>Motivo:</strong> {{ $comprobante->motivo_referencia }}</p>
            </div>
            @endif

            <!-- Detalles -->
            @if($comprobante->venta && $comprobante->venta->detalles->count())
            <h4 class="font-bold mt-6 mb-3"><i class="fas fa-list mr-1 text-emerald-500"></i>Detalle</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="text-left py-2 px-2">Producto</th>
                            <th class="text-right py-2 px-2">Cant.</th>
                            <th class="text-right py-2 px-2">P.U.</th>
                            <th class="text-right py-2 px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($comprobante->venta->detalles as $d)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 px-2">
                                <p class="font-semibold">{{ $d->descripcion }}</p>
                                <p class="text-xs text-slate-400">{{ $d->codigo }}</p>
                            </td>
                            <td class="py-2 px-2 text-right">{{ number_format($d->cantidad, 2) }}</td>
                            <td class="py-2 px-2 text-right">{{ $moneda }}{{ number_format($d->precio_unitario, 2) }}</td>
                            <td class="py-2 px-2 text-right font-semibold">{{ $moneda }}{{ number_format($d->total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Totales -->
            <div class="mt-4 pt-4 border-t border-slate-200 space-y-2 text-right">
                <div class="flex justify-end gap-8 text-sm"><span class="text-slate-600">Op. Gravadas:</span><span class="w-32 font-semibold">{{ $moneda }}{{ number_format($comprobante->total_gravadas, 2) }}</span></div>
                <div class="flex justify-end gap-8 text-sm"><span class="text-slate-600">IGV (18%):</span><span class="w-32 font-semibold">{{ $moneda }}{{ number_format($comprobante->total_igv, 2) }}</span></div>
                @if($comprobante->total_descuentos > 0)
                    <div class="flex justify-end gap-8 text-sm"><span class="text-slate-600">Descuentos:</span><span class="w-32 font-semibold text-red-600">-{{ $moneda }}{{ number_format($comprobante->total_descuentos, 2) }}</span></div>
                @endif
                <div class="flex justify-end gap-8 text-2xl pt-2 border-t"><span class="font-bold">TOTAL:</span><span class="font-bold w-32 text-emerald-600">{{ $moneda }}{{ number_format($comprobante->importe_total, 2) }}</span></div>
                <p class="text-xs text-slate-500 italic mt-1">{{ $comprobante->importe_letras }}</p>
            </div>
        </div>

        <!-- Respuesta SUNAT -->
        @if($comprobante->mensaje_sunat)
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h4 class="font-bold mb-3"><i class="fas fa-comment-dots mr-1 text-blue-500"></i>Respuesta SUNAT</h4>
            <div class="bg-{{ $color }}-50 border-l-4 border-{{ $color }}-500 p-4 rounded">
                @if($comprobante->codigo_respuesta_sunat)
                    <p class="text-xs text-{{ $color }}-600 font-semibold">Código: {{ $comprobante->codigo_respuesta_sunat }}</p>
                @endif
                <p class="text-sm mt-1">{{ $comprobante->mensaje_sunat }}</p>
                @if($comprobante->fecha_envio_sunat)
                    <p class="text-xs text-slate-500 mt-2">Enviado el {{ $comprobante->fecha_envio_sunat->format('d/m/Y H:i:s') }} • Intentos: {{ $comprobante->intentos_envio }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Lado derecho: acciones -->
    <div class="space-y-5">
        <!-- Acciones SUNAT -->
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h4 class="font-bold mb-3"><i class="fas fa-shield-alt mr-1 text-emerald-500"></i>Acciones SUNAT</h4>

            @if($comprobante->estado_sunat === 'pendiente' || $comprobante->estado_sunat === 'rechazado' || $comprobante->estado_sunat === 'excepcion')
                <form method="POST" action="{{ route('facturacion.enviar', $comprobante->id) }}" class="mb-3">
                    @csrf
                    <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-lg font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Enviar a SUNAT
                    </button>
                </form>
            @endif

            <div class="grid grid-cols-2 gap-2 mb-3">
                <a href="{{ route('facturacion.pdf', $comprobante->id) }}" target="_blank" class="text-center bg-rose-500 hover:bg-rose-600 text-white py-2.5 rounded-lg font-semibold text-sm">
                    <i class="fas fa-file-pdf mr-1"></i> PDF A4
                </a>
                <a href="{{ route('facturacion.ticket', $comprobante->id) }}" target="_blank" class="text-center bg-slate-700 hover:bg-slate-800 text-white py-2.5 rounded-lg font-semibold text-sm">
                    <i class="fas fa-receipt mr-1"></i> Ticket 80mm
                </a>
            </div>

            @if($comprobante->xml_path)
                <a href="{{ route('facturacion.xml', $comprobante->id) }}" class="block text-center bg-blue-50 hover:bg-blue-100 text-blue-700 py-2.5 rounded-lg font-semibold text-sm mb-2">
                    <i class="fas fa-file-code mr-1"></i> Descargar XML firmado
                </a>
            @endif

            @if($comprobante->cdr_path)
                <a href="{{ route('facturacion.cdr', $comprobante->id) }}" class="block text-center bg-green-50 hover:bg-green-100 text-green-700 py-2.5 rounded-lg font-semibold text-sm mb-2">
                    <i class="fas fa-check-circle mr-1"></i> Descargar CDR SUNAT
                </a>
            @endif

            @if(in_array($comprobante->estado_sunat, ['aceptado']) && in_array($comprobante->tipo_documento, ['01', '03']))
                <button onclick="document.getElementById('modal-anular').classList.remove('hidden')"
                        class="w-full bg-red-50 hover:bg-red-100 text-red-700 py-2.5 rounded-lg font-semibold text-sm">
                    <i class="fas fa-ban mr-1"></i> Anular comprobante
                </button>
            @endif
        </div>

        <!-- Código QR -->
        @if($comprobante->qr_data)
        <div class="bg-white rounded-2xl shadow-md p-6 text-center">
            <h4 class="font-bold mb-3"><i class="fas fa-qrcode mr-1 text-slate-700"></i>Código QR SUNAT</h4>
            <img src="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($comprobante->qr_data) }}&size=200x200"
                 alt="QR SUNAT" class="mx-auto rounded-lg">
            <p class="text-xs text-slate-500 mt-2">Escanea para validar en SUNAT</p>
        </div>
        @endif

        <!-- Datos técnicos -->
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h4 class="font-bold mb-3"><i class="fas fa-info-circle mr-1 text-purple-500"></i>Datos técnicos</h4>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-slate-500">Tipo:</dt><dd class="font-semibold">{{ $comprobante->tipo_documento }} - {{ $comprobante->tipo_documento_nombre }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Serie:</dt><dd class="font-mono font-semibold">{{ $comprobante->serie }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Correlativo:</dt><dd class="font-mono font-semibold">{{ $comprobante->numero }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Moneda:</dt><dd class="font-semibold">{{ $comprobante->moneda }}</dd></div>
                @if($comprobante->hash)
                    <div><dt class="text-slate-500 text-xs">Hash XML:</dt><dd class="font-mono text-[10px] break-all bg-slate-50 p-2 rounded mt-1">{{ Str::limit($comprobante->hash, 80) }}</dd></div>
                @endif
            </dl>
        </div>

        <a href="{{ route('facturacion.index') }}" class="block text-center bg-slate-100 hover:bg-slate-200 py-2.5 rounded-lg text-slate-700 font-semibold">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>

<!-- Modal anular -->
<div id="modal-anular" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-3 text-red-600"><i class="fas fa-ban mr-1"></i>Anular comprobante</h3>
        <p class="text-sm text-slate-600 mb-4">Elige el método de anulación según SUNAT:</p>
        <form method="POST" action="{{ route('facturacion.anular', $comprobante->id) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">Método</label>
                <select name="metodo" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                    <option value="nota_credito">Nota de Crédito (recomendado)</option>
                    <option value="comunicacion_baja">Comunicación de Baja</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Motivo de anulación</label>
                <textarea name="motivo" rows="3" required class="w-full px-3 py-2 border border-slate-300 rounded-lg" placeholder="Ej: Anulación por error en datos del cliente"></textarea>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-anular').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-200 rounded-lg">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 bg-red-500 text-white rounded-lg font-semibold">Anular</button>
            </div>
        </form>
    </div>
</div>
@endsection
