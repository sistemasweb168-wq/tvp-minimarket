@extends('layouts.app')
@section('title', 'Facturación Electrónica')
@section('header', 'Facturación Electrónica - SUNAT')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<!-- Stats SUNAT -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-5">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-slate-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-slate-400 font-semibold">TOTAL CPE</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-100">{{ $stats['total'] }}</p>
            </div>
            <i class="fas fa-file-invoice text-3xl text-slate-300"></i>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-green-600 font-semibold">ACEPTADOS</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-700">{{ $stats['aceptados'] }}</p>
            </div>
            <i class="fas fa-check-circle text-3xl text-green-300"></i>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-yellow-600 font-semibold">PENDIENTES</p>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-700">{{ $stats['pendientes'] }}</p>
            </div>
            <i class="fas fa-clock text-3xl text-yellow-300"></i>
        </div>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-red-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-red-600 font-semibold">RECHAZADOS</p>
                <p class="text-2xl sm:text-3xl font-bold text-red-700">{{ $stats['rechazados'] }}</p>
            </div>
            <i class="fas fa-exclamation-triangle text-3xl text-red-300"></i>
        </div>
    </div>
</div>

<!-- Aviso si SUNAT está desactivada -->
@if(!($empresaGlobal->facturacion_electronica_activa ?? false))
    <div class="mb-5 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 text-xl mt-1"></i>
        <div class="flex-1">
            <p class="font-semibold text-amber-800">Facturación electrónica no activada</p>
            <p class="text-sm text-amber-700">Para emitir comprobantes electrónicos debes configurar SUNAT y subir tu certificado digital.</p>
        </div>
        <a href="{{ route('facturacion.configuracion') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-semibold text-sm whitespace-nowrap">
            <i class="fas fa-cog mr-1"></i> Configurar
        </a>
    </div>
@endif

<!-- Filtros -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input name="buscar" value="{{ request('buscar') }}" placeholder="N° comprobante / Doc cliente"
               class="px-3 py-2.5 border border-slate-600 rounded-lg md:col-span-2">
        <select name="tipo" class="px-3 py-2.5 border border-slate-600 rounded-lg">
            <option value="">Todos los tipos</option>
            <option value="01" {{ request('tipo')=='01'?'selected':'' }}>Factura</option>
            <option value="03" {{ request('tipo')=='03'?'selected':'' }}>Boleta</option>
            <option value="07" {{ request('tipo')=='07'?'selected':'' }}>Nota de Crédito</option>
            <option value="08" {{ request('tipo')=='08'?'selected':'' }}>Nota de Débito</option>
        </select>
        <select name="estado" class="px-3 py-2.5 border border-slate-600 rounded-lg">
            <option value="">Todos los estados</option>
            @foreach(['pendiente'=>'Pendiente','enviado'=>'Enviado','aceptado'=>'Aceptado','rechazado'=>'Rechazado','observado'=>'Observado','anulado'=>'Anulado','baja'=>'Dado de baja'] as $k => $v)
                <option value="{{ $k }}" {{ request('estado')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
        <button class="bg-emerald-600 text-white px-4 py-2.5 rounded-lg"><i class="fas fa-search mr-1"></i>Buscar</button>
    </form>
</div>

<!-- Tabla de Comprobantes (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden border border-slate-800">
    
    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-800">
        @forelse($comprobantes as $c)
            @php
                $tipoColor = ['01'=>'purple', '03'=>'blue', '07'=>'amber', '08'=>'indigo'][$c->tipo_documento] ?? 'slate';
                $estado = $c->estado_sunat;
            @endphp
            <div class="p-3.5 hover:bg-slate-800 transition">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 bg-{{ $tipoColor }}-100 text-{{ $tipoColor }}-800 rounded-lg text-[10px] font-black">
                            {{ $c->tipo_documento_nombre }}
                        </span>
                        <span class="font-mono text-xs font-black text-slate-100">{{ $c->numero_completo }}</span>
                    </div>
                    <span class="font-black text-emerald-600 text-sm">{{ $moneda }}{{ number_format($c->importe_total, 2) }}</span>
                </div>

                <div class="text-xs text-slate-300 mb-2">
                    <p class="font-bold truncate">{{ $c->receptor_razon_social }}</p>
                    <p class="text-[11px] text-slate-400 font-mono">{{ $c->receptor_tipo_doc_label }}: {{ $c->receptor_numero_doc }} • {{ $c->fecha_emision->format('d/m/Y') }}</p>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                    <div>
                        @if($estado === 'aceptado')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-black"><i class="fas fa-check-circle mr-1"></i>Aceptado</span>
                        @elseif($estado === 'rechazado')
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-black"><i class="fas fa-times-circle mr-1"></i>Rechazado</span>
                        @else
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-black"><i class="fas fa-clock mr-1"></i>{{ ucfirst($estado) }}</span>
                        @endif
                    </div>
                    <div class="flex gap-1.5">
                        <a href="{{ route('facturacion.ticket', $c->id) }}" target="_blank" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-bold transition flex items-center gap-1">
                            <i class="fas fa-qrcode"></i><span>Ticket QR</span>
                        </a>
                        <a href="{{ route('facturacion.show', $c->id) }}" class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition flex items-center gap-1">
                            <i class="fas fa-eye"></i><span>Detalle</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-400 text-xs">
                <i class="fas fa-file-invoice text-4xl mb-2 text-slate-300"></i>
                <p>No hay comprobantes electrónicos emitidos</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="py-3.5 px-4">N° Comprobante</th>
                    <th class="py-3.5 px-4">Tipo</th>
                    <th class="py-3.5 px-4">Cliente</th>
                    <th class="py-3.5 px-4">Fecha</th>
                    <th class="py-3.5 px-4 text-right">Total</th>
                    <th class="py-3.5 px-4 text-center">Estado SUNAT</th>
                    <th class="py-3.5 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
            @forelse($comprobantes as $c)
                <tr class="hover:bg-slate-800/80 transition">
                    <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-100">{{ $c->numero_completo }}</td>
                    <td class="py-3.5 px-4">
                        @php
                        $tipoColor = ['01'=>'purple', '03'=>'blue', '07'=>'amber', '08'=>'indigo'][$c->tipo_documento] ?? 'slate';
                        @endphp
                        <span class="px-2.5 py-1 bg-{{ $tipoColor }}-100 text-{{ $tipoColor }}-800 rounded-full text-xs font-bold">
                            {{ $c->tipo_documento_nombre }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <p class="font-bold text-slate-100 text-xs sm:text-sm">{{ $c->receptor_razon_social }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $c->receptor_tipo_doc_label }}: {{ $c->receptor_numero_doc }}</p>
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-400">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                    <td class="py-3.5 px-4 text-right font-black text-emerald-600 whitespace-nowrap">{{ $moneda }}{{ number_format($c->importe_total, 2) }}</td>
                    <td class="py-3.5 px-4 text-center">
                        @php
                        $estado = $c->estado_sunat;
                        $color = $c->estado_color;
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-{{ $color }}-100 text-{{ $color }}-800 rounded-full text-xs font-bold">
                            {{ ucfirst($estado) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('facturacion.ticket', $c->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg inline-block" title="Ticket QR"><i class="fas fa-qrcode"></i></a>
                        <a href="{{ route('facturacion.show', $c->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg inline-block" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-12 text-slate-400">
                    <i class="fas fa-file-invoice text-4xl mb-2 text-slate-300"></i>
                    <p>Aún no hay comprobantes electrónicos emitidos</p>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 sm:p-4 border-t border-slate-800">{{ $comprobantes->withQueryString()->links() }}</div>
</div>
@endsection
