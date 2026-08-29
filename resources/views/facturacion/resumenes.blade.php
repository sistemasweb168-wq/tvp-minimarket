@extends('layouts.app')
@section('title', 'Resúmenes Diarios')
@section('header', 'Resúmenes Diarios de Boletas')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5">
    <div class="flex flex-col md:flex-row md:items-end gap-3">
        <form method="POST" action="{{ route('facturacion.resumenes.generar') }}" class="flex flex-1 gap-2">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-semibold mb-1">Fecha del resumen</label>
                <input type="date" name="fecha" required value="{{ now()->subDay()->toDateString() }}" max="{{ now()->toDateString() }}"
                       class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
            </div>
            <button class="self-end gradient-primary text-white px-6 py-2.5 rounded-lg font-semibold">
                <i class="fas fa-plus mr-1"></i> Generar resumen
            </button>
        </form>
    </div>
    <p class="text-sm text-slate-400 mt-3"><i class="fas fa-info-circle mr-1"></i>
    El resumen diario consolida todas las boletas aceptadas del día seleccionado y se envía a SUNAT.</p>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden">
    <!-- 📱 VISTA MÓVIL (LISTA MINIMALISTA < md) -->
    <div class="md:hidden divide-y divide-slate-800">
        @forelse($resumenes as $r)
            @php $colors = ['pendiente'=>'yellow','enviado'=>'blue','aceptado'=>'green','rechazado'=>'red']; $c = $colors[$r->estado_sunat] ?? 'slate'; @endphp
            <div class="p-3 hover:bg-slate-800/40 transition">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-mono text-xs font-black text-slate-100">{{ $r->identificador }}</span>
                    <span class="px-2 py-0.5 bg-{{ $c }}-500/20 text-{{ $c }}-400 border border-{{ $c }}-500/30 rounded-md text-[10px] font-black uppercase">
                        {{ ucfirst($r->estado_sunat) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                    <span>Fecha: <strong class="text-slate-200">{{ $r->fecha_resumen->format('d/m/Y') }}</strong></span>
                    <span class="font-bold text-emerald-400 font-mono">{{ $moneda }}{{ number_format($r->total_general, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-800/60 font-mono">
                    <span>{{ $r->cantidad_comprobantes }} boletas</span>
                    <span>Ticket: {{ $r->ticket_sunat ?: '—' }}</span>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-500 text-xs">
                <i class="fas fa-calendar-day text-3xl mb-2 block text-slate-600"></i>
                No hay resúmenes diarios generados
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-800 text-xs uppercase text-slate-400">
                <tr>
                    <th class="text-left py-3 px-4">Identificador</th>
                    <th class="text-left py-3 px-4">Fecha resumen</th>
                    <th class="text-right py-3 px-4">Boletas</th>
                    <th class="text-right py-3 px-4">Total</th>
                    <th class="text-center py-3 px-4">Estado SUNAT</th>
                    <th class="text-left py-3 px-4">Ticket SUNAT</th>
                </tr>
            </thead>
            <tbody>
            @forelse($resumenes as $r)
                <tr class="border-b border-slate-800 hover:bg-slate-800">
                    <td class="py-3 px-4 font-mono text-xs font-bold">{{ $r->identificador }}</td>
                    <td class="py-3 px-4 text-sm">{{ $r->fecha_resumen->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-right font-semibold">{{ $r->cantidad_comprobantes }}</td>
                    <td class="py-3 px-4 text-right font-bold text-emerald-600">{{ $moneda }}{{ number_format($r->total_general, 2) }}</td>
                    <td class="py-3 px-4 text-center">
                        @php $colors = ['pendiente'=>'yellow','enviado'=>'blue','aceptado'=>'green','rechazado'=>'red']; $c = $colors[$r->estado_sunat] ?? 'slate'; @endphp
                        <span class="px-2 py-1 bg-{{ $c }}-100 text-{{ $c }}-700 rounded-full text-xs font-semibold">{{ ucfirst($r->estado_sunat) }}</span>
                    </td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $r->ticket_sunat ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-12 text-slate-400">
                    <i class="fas fa-calendar-day text-5xl mb-2"></i>
                    <p>No hay resúmenes diarios generados</p>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $resumenes->links() }}</div>
</div>
@endsection
