@extends('layouts.app')
@section('title', 'Ventas')
@section('header', 'Historial de Ventas')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<!-- Barra Superior con Filtros -->
<div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 mb-4 sm:mb-5 border border-slate-100">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-2.5 sm:gap-3">
        <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por N°, DNI o Cliente" class="px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-emerald-500">
        
        <select name="tipo_comprobante" class="px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:border-emerald-500">
            <option value="">Todos los Comprobantes</option>
            <option value="BOLETA" {{ request('tipo_comprobante')=='BOLETA'?'selected':'' }}>📄 Boletas</option>
            <option value="FACTURA" {{ request('tipo_comprobante')=='FACTURA'?'selected':'' }}>🏢 Facturas</option>
            <option value="TICKET" {{ request('tipo_comprobante')=='TICKET'?'selected':'' }}>🧾 Tickets / Notas</option>
        </select>

        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-emerald-500">
        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-emerald-500">
        
        <select name="estado" class="px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-emerald-500">
            <option value="">Todos los estados</option>
            <option value="completada" {{ request('estado')=='completada'?'selected':'' }}>Completadas</option>
            <option value="anulada" {{ request('estado')=='anulada'?'selected':'' }}>Anuladas</option>
        </select>

        <div class="flex gap-2">
            <button class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-1.5"><i class="fas fa-filter"></i>Filtrar</button>
            <a href="{{ route('ventas.pos') }}" class="gradient-primary text-white px-4 py-2 rounded-xl flex items-center justify-center font-bold text-xs sm:text-sm shadow-sm"><i class="fas fa-cash-register mr-1"></i>POS</a>
        </div>
    </form>
</div>

<!-- Banner de Acceso Rápido a Facturación SUNAT -->
<div class="mb-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 border border-emerald-200/70 p-3 sm:p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div>
            <h4 class="font-extrabold text-xs sm:text-sm text-slate-800">Panel de Facturación Electrónica SUNAT</h4>
            <p class="text-[11px] sm:text-xs text-slate-500">Consulta los XML firmados, respuestas CDR oficiales de SUNAT y descarga comprobantes en PDF.</p>
        </div>
    </div>
    <a href="{{ route('facturacion.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex-shrink-0">
        <i class="fas fa-external-link-alt text-[10px]"></i><span>Ir a Facturación SUNAT</span>
    </a>
</div>

<!-- Vista de Ventas (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($ventas as $v)
            @php
                $numDoc = $v->comprobanteElectronico?->numero_completo ?? $v->numero_ticket;
                $esBoleta = $v->tipo_comprobante === 'BOLETA';
                $esFactura = $v->tipo_comprobante === 'FACTURA';
            @endphp
            <div class="p-3.5 hover:bg-slate-50 transition">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @if($esFactura)
                            <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-lg text-[10px] font-black"><i class="fas fa-building mr-1"></i>FACTURA</span>
                        @elseif($esBoleta)
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-lg text-[10px] font-black"><i class="fas fa-file-invoice mr-1"></i>BOLETA</span>
                        @else
                            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-lg text-[10px] font-black"><i class="fas fa-receipt mr-1"></i>TICKET</span>
                        @endif

                        <span class="font-mono text-xs font-black text-slate-800">{{ $numDoc }}</span>
                        
                        @if($v->estado == 'completada')
                            <span class="bg-green-100 text-green-700 px-1.5 py-0.2 rounded-full text-[9px] font-bold">Completada</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-1.5 py-0.2 rounded-full text-[9px] font-bold">Anulada</span>
                        @endif
                    </div>
                    <span class="font-black text-emerald-600 text-base">{{ $moneda }}{{ number_format($v->total, 2) }}</span>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                    <div>
                        <p class="font-bold text-slate-700 line-clamp-1">
                            <i class="fas fa-user text-[10px] text-slate-400 mr-1"></i>
                            {{ $v->cliente?->nombre_completo ?? 'CLIENTES VARIOS' }}
                            @if($v->cliente?->documento)
                                <span class="text-[10px] font-normal text-slate-400">({{ $v->cliente->documento }})</span>
                            @endif
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5"><i class="far fa-clock text-[10px] mr-1"></i>{{ $v->fecha_venta->format('d/m/Y H:i') }} • {{ $v->user->name }}</p>
                    </div>
                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-lg text-[10px] font-bold">{{ ucfirst($v->forma_pago) }}</span>
                </div>

                <!-- Botones de Acción Móvil -->
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('ventas.show', $v->id) }}" class="flex-1 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-eye"></i><span>Detalle</span>
                    </a>
                    
                    @if($v->comprobanteElectronico)
                        <a href="{{ route('facturacion.ticket', $v->comprobanteElectronico->id) }}" target="_blank" class="flex-1 py-1.5 gradient-primary text-white rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fas fa-qrcode"></i><span>CPE + QR</span>
                        </a>
                    @else
                        <a href="{{ route('ventas.ticket', $v->id) }}" target="_blank" class="flex-1 py-1.5 gradient-primary text-white rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fas fa-print"></i><span>Imprimir</span>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-sm">
                <i class="fas fa-receipt text-4xl mb-2 text-slate-300"></i>
                <p>No se encontraron ventas registradas</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3.5 px-4">Tipo / Comprobante</th>
                    <th class="py-3.5 px-4">Fecha/Cliente</th>
                    <th class="py-3.5 px-4">Cajero</th>
                    <th class="py-3.5 px-4">Pago</th>
                    <th class="py-3.5 px-4 text-right">Total</th>
                    <th class="py-3.5 px-4 text-center">Estado</th>
                    <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($ventas as $v)
                @php
                    $numDoc = $v->comprobanteElectronico?->numero_completo ?? $v->numero_ticket;
                    $esBoleta = $v->tipo_comprobante === 'BOLETA';
                    $esFactura = $v->tipo_comprobante === 'FACTURA';
                @endphp
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-2">
                            @if($esFactura)
                                <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-lg text-[10px] font-black">FACTURA</span>
                            @elseif($esBoleta)
                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-lg text-[10px] font-black">BOLETA</span>
                            @else
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-lg text-[10px] font-black">TICKET</span>
                            @endif
                            <span class="font-mono text-sm font-bold text-slate-800">{{ $numDoc }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-sm">
                        <p class="font-bold text-slate-800">{{ $v->cliente?->nombre_completo ?? 'CLIENTES VARIOS' }}</p>
                        <p class="text-xs text-slate-400">{{ $v->fecha_venta->format('d/m/Y H:i') }} @if($v->cliente?->documento) • Doc: {{ $v->cliente->documento }} @endif</p>
                    </td>
                    <td class="py-3.5 px-4 text-sm text-slate-600">{{ $v->user->name }}</td>
                    <td class="py-3.5 px-4"><span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ ucfirst($v->forma_pago) }}</span></td>
                    <td class="py-3.5 px-4 text-right font-extrabold text-sm text-emerald-600 whitespace-nowrap">{{ $moneda }}{{ number_format($v->total, 2) }}</td>
                    <td class="py-3.5 px-4 text-center">
                        @if($v->estado == 'completada')
                            <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">Completada</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">Anulada</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('ventas.show', $v->id) }}" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg inline-block text-sm" title="Ver detalle"><i class="fas fa-eye"></i></a>
                        
                        @if($v->comprobanteElectronico)
                            <a href="{{ route('facturacion.ticket', $v->comprobanteElectronico->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg inline-block text-sm" title="Imprimir Comprobante con QR"><i class="fas fa-qrcode"></i></a>
                        @else
                            <a href="{{ route('ventas.ticket', $v->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg inline-block text-sm" title="Imprimir ticket"><i class="fas fa-print"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-12 text-slate-400 text-sm">No se encontraron ventas registradas</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 sm:p-4 border-t border-slate-100">{{ $ventas->withQueryString()->links() }}</div>
</div>
@endsection
