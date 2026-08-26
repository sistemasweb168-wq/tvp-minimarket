@extends('layouts.app')
@section('title', 'Facturación Electrónica')
@section('header', 'Facturación Electrónica - SUNAT')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<!-- Stats SUNAT -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-slate-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-slate-500 font-semibold">TOTAL CPE</p>
                <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <i class="fas fa-file-invoice text-3xl text-slate-300"></i>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-green-600 font-semibold">ACEPTADOS</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-700">{{ $stats['aceptados'] }}</p>
            </div>
            <i class="fas fa-check-circle text-3xl text-green-300"></i>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-yellow-600 font-semibold">PENDIENTES</p>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-700">{{ $stats['pendientes'] }}</p>
            </div>
            <i class="fas fa-clock text-3xl text-yellow-300"></i>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-5 border-l-4 border-red-500">
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
<div class="bg-white rounded-2xl shadow-md p-5 mb-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input name="buscar" value="{{ request('buscar') }}" placeholder="N° comprobante / Doc cliente"
               class="px-3 py-2.5 border border-slate-300 rounded-lg md:col-span-2">
        <select name="tipo" class="px-3 py-2.5 border border-slate-300 rounded-lg">
            <option value="">Todos los tipos</option>
            <option value="01" {{ request('tipo')=='01'?'selected':'' }}>Factura</option>
            <option value="03" {{ request('tipo')=='03'?'selected':'' }}>Boleta</option>
            <option value="07" {{ request('tipo')=='07'?'selected':'' }}>Nota de Crédito</option>
            <option value="08" {{ request('tipo')=='08'?'selected':'' }}>Nota de Débito</option>
        </select>
        <select name="estado" class="px-3 py-2.5 border border-slate-300 rounded-lg">
            <option value="">Todos los estados</option>
            @foreach(['pendiente'=>'Pendiente','enviado'=>'Enviado','aceptado'=>'Aceptado','rechazado'=>'Rechazado','observado'=>'Observado','anulado'=>'Anulado','baja'=>'Dado de baja'] as $k => $v)
                <option value="{{ $k }}" {{ request('estado')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
        <button class="bg-emerald-600 text-white px-4 py-2.5 rounded-lg"><i class="fas fa-search mr-1"></i>Buscar</button>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="text-left py-3 px-4">N° Comprobante</th>
                    <th class="text-left py-3 px-4">Tipo</th>
                    <th class="text-left py-3 px-4 hide-mobile">Cliente</th>
                    <th class="text-left py-3 px-4 hide-mobile">Fecha</th>
                    <th class="text-right py-3 px-4">Total</th>
                    <th class="text-center py-3 px-4">Estado SUNAT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($comprobantes as $c)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-3 px-4 font-mono text-xs font-bold text-slate-700">{{ $c->numero_completo }}</td>
                    <td class="py-3 px-4">
                        @php
                        $tipoColor = ['01'=>'blue', '03'=>'emerald', '07'=>'orange', '08'=>'purple'][$c->tipo_documento] ?? 'slate';
                        @endphp
                        <span class="px-2 py-1 bg-{{ $tipoColor }}-100 text-{{ $tipoColor }}-700 rounded text-xs font-semibold">
                            {{ $c->tipo_documento_nombre }}
                        </span>
                    </td>
                    <td class="py-3 px-4 hide-mobile">
                        <p class="text-sm">{{ $c->receptor_razon_social }}</p>
                        <p class="text-xs text-slate-400">{{ $c->receptor_tipo_doc_label }}: {{ $c->receptor_numero_doc }}</p>
                    </td>
                    <td class="py-3 px-4 text-xs hide-mobile">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($c->importe_total, 2) }}</td>
                    <td class="py-3 px-4 text-center">
                        @php
                        $estado = $c->estado_sunat;
                        $color = $c->estado_color;
                        $icons = [
                            'pendiente' => 'fa-clock',
                            'enviado' => 'fa-paper-plane',
                            'aceptado' => 'fa-check-circle',
                            'rechazado' => 'fa-times-circle',
                            'observado' => 'fa-exclamation-triangle',
                            'anulado' => 'fa-ban',
                            'baja' => 'fa-trash-alt',
                            'excepcion' => 'fa-bug',
                        ];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-{{ $color }}-100 text-{{ $color }}-700 rounded-full text-xs font-semibold">
                            <i class="fas {{ $icons[$estado] ?? 'fa-question-circle' }}"></i>
                            {{ ucfirst($estado) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('facturacion.show', $c->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-12 text-slate-400">
                    <i class="fas fa-file-invoice text-5xl mb-2"></i>
                    <p>Aún no hay comprobantes electrónicos emitidos</p>
                    <p class="text-sm mt-2">Para emitir un comprobante, ve a una venta y haz clic en "Emitir Boleta/Factura"</p>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $comprobantes->withQueryString()->links() }}</div>
</div>
@endsection
