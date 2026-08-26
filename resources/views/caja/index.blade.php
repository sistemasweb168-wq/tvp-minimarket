@extends('layouts.app')
@section('title', 'Caja')
@section('header', 'Gestión de Caja')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

@if($turnoActivo)
    <!-- Turno Activo -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-5">
        <div class="flex justify-between items-start mb-5">
            <div>
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold"><i class="fas fa-circle text-[8px] mr-1"></i>TURNO ACTIVO</span>
                <h2 class="text-2xl font-bold mt-2">{{ $turnoActivo->caja->nombre }}</h2>
                <p class="text-slate-500">Abierto el {{ $turnoActivo->fecha_apertura->format('d/m/Y H:i') }}</p>
            </div>
            <button onclick="document.getElementById('modal-cerrar').classList.remove('hidden')" class="bg-red-500 hover:bg-red-600 text-white px-5 py-2.5 rounded-lg font-semibold">
                <i class="fas fa-lock mr-2"></i>Cerrar Turno
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-xs text-blue-600 font-semibold">APERTURA</p>
                <p class="text-2xl font-bold text-blue-800">{{ $moneda }}{{ number_format($turnoActivo->monto_apertura, 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4">
                <p class="text-xs text-green-600 font-semibold">EFECTIVO</p>
                <p class="text-2xl font-bold text-green-800">{{ $moneda }}{{ number_format($turnoActivo->total_efectivo, 2) }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-xs text-purple-600 font-semibold">TARJETA</p>
                <p class="text-2xl font-bold text-purple-800">{{ $moneda }}{{ number_format($turnoActivo->total_tarjeta, 2) }}</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4">
                <p class="text-xs text-emerald-600 font-semibold">TOTAL VENTAS ({{ $turnoActivo->cantidad_ventas }})</p>
                <p class="text-2xl font-bold text-emerald-800">{{ $moneda }}{{ number_format($turnoActivo->total_ventas, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <h3 class="font-bold mb-3"><i class="fas fa-arrows-alt-h mr-2 text-blue-500"></i>Registrar movimiento</h3>
                <form method="POST" action="{{ route('caja.movimiento', $turnoActivo->id) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <select name="tipo" class="px-3 py-2 border border-slate-300 rounded-lg">
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                        <input type="number" step="0.01" name="monto" placeholder="Monto" required class="px-3 py-2 border border-slate-300 rounded-lg">
                    </div>
                    <input type="text" name="concepto" placeholder="Concepto (ej: pago a proveedor)" required class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                    <button class="w-full gradient-primary text-white py-2 rounded-lg"><i class="fas fa-plus mr-1"></i>Registrar</button>
                </form>
            </div>

            <div>
                <h3 class="font-bold mb-3"><i class="fas fa-list mr-2 text-emerald-500"></i>Movimientos</h3>
                <div class="max-h-64 overflow-y-auto space-y-2">
                    @forelse($turnoActivo->movimientos as $m)
                        <div class="flex justify-between bg-slate-50 rounded-lg p-3">
                            <div>
                                <p class="text-sm font-semibold">{{ $m->concepto }}</p>
                                <p class="text-xs text-slate-500">{{ $m->created_at->format('H:i') }}</p>
                            </div>
                            <p class="font-bold {{ $m->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $m->tipo == 'ingreso' ? '+' : '-' }}{{ $moneda }}{{ number_format($m->monto, 2) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-center py-4 text-slate-400 text-sm">Sin movimientos</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cerrar Turno -->
    <div id="modal-cerrar" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold mb-4">Cerrar Turno de Caja</h3>
            <form method="POST" action="{{ route('caja.cerrar', $turnoActivo->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Monto físico contado en caja</label>
                    <input type="number" step="0.01" name="monto_cierre" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-2xl font-bold text-center">
                </div>
                <textarea name="observaciones" placeholder="Observaciones..." rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('modal-cerrar').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-200 rounded-lg">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-red-500 text-white rounded-lg font-semibold"><i class="fas fa-lock mr-1"></i>Cerrar Turno</button>
                </div>
            </form>
        </div>
    </div>
@else
    <!-- Sin turno activo -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-5 text-center">
        <i class="fas fa-cash-register text-6xl text-slate-300 mb-4"></i>
        <h2 class="text-xl font-bold text-slate-700 mb-2">No tienes un turno de caja abierto</h2>
        <p class="text-slate-500 mb-6">Para realizar ventas, debes abrir un turno de caja primero.</p>
        <button onclick="document.getElementById('modal-abrir').classList.remove('hidden')" class="gradient-primary text-white px-8 py-3 rounded-xl font-semibold">
            <i class="fas fa-key mr-2"></i>Abrir Turno
        </button>
    </div>

    <!-- Modal Abrir -->
    <div id="modal-abrir" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold mb-4">Abrir Turno de Caja</h3>
            @if($cajas->count() == 0)
                <p class="text-sm text-amber-600 bg-amber-50 p-3 rounded-lg mb-3">No hay cajas registradas. Crea una primero.</p>
                <form method="POST" action="{{ route('caja.store') }}" class="space-y-3">
                    @csrf
                    <input type="text" name="nombre" placeholder="Nombre de la caja (ej: Caja 1)" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                    <button class="w-full gradient-primary text-white py-2 rounded-lg">Crear Caja</button>
                </form>
            @else
                <form method="POST" action="{{ route('caja.abrir') }}" class="space-y-3">
                    @csrf
                    <select name="caja_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        @foreach($cajas as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach
                    </select>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Monto de apertura</label>
                        <input type="number" step="0.01" name="monto_apertura" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-2xl font-bold text-center" placeholder="0.00">
                    </div>
                    <textarea name="observaciones" placeholder="Observaciones..." rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('modal-abrir').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-200 rounded-lg">Cancelar</button>
                        <button class="flex-1 gradient-primary text-white py-2.5 rounded-lg font-semibold">Abrir</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

<!-- Historial -->
<div class="bg-white rounded-2xl shadow-md p-6">
    <h3 class="font-bold mb-4"><i class="fas fa-history mr-2 text-blue-500"></i>Últimos Turnos</h3>
    <table class="w-full text-sm">
        <thead class="text-xs uppercase text-slate-500 border-b">
            <tr>
                <th class="text-left py-2">Caja</th>
                <th class="text-left py-2">Cajero</th>
                <th class="text-left py-2">Apertura</th>
                <th class="text-left py-2">Cierre</th>
                <th class="text-right py-2">Ventas</th>
                <th class="text-right py-2">Diferencia</th>
                <th class="text-center py-2">Estado</th>
            </tr>
        </thead>
        <tbody>
        @foreach($turnos as $t)
            <tr class="border-b hover:bg-slate-50">
                <td class="py-3">{{ $t->caja->nombre }}</td>
                <td class="py-3">{{ $t->user->name }}</td>
                <td class="py-3">{{ $t->fecha_apertura->format('d/m H:i') }}</td>
                <td class="py-3">{{ $t->fecha_cierre?->format('d/m H:i') ?? '—' }}</td>
                <td class="py-3 text-right font-semibold">{{ $moneda }}{{ number_format($t->total_ventas, 2) }}</td>
                <td class="py-3 text-right {{ $t->diferencia < 0 ? 'text-red-600' : ($t->diferencia > 0 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ $moneda }}{{ number_format($t->diferencia, 2) }}
                </td>
                <td class="py-3 text-center">
                    @if($t->estado == 'abierto')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Abierto</span>
                    @else
                        <a href="{{ route('caja.cierre', $t->id) }}" class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full text-xs hover:bg-slate-200">Ver Cierre</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
