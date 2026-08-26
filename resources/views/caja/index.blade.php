@extends('layouts.app')
@section('title', 'Caja')
@section('header', 'Gestión de Caja')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

@if($turnoActivo)
    <!-- Turno Activo -->
    <div class="bg-white rounded-2xl shadow-md p-4 sm:p-6 mb-5 border border-slate-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
            <div>
                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span>TURNO ACTIVO</span>
                </span>
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 mt-1.5">{{ $turnoActivo->caja->nombre }}</h2>
                <p class="text-xs sm:text-sm text-slate-500">Abierto el {{ $turnoActivo->fecha_apertura->format('d/m/Y H:i') }} • Cajero: <strong class="text-slate-700">{{ $turnoActivo->user->name }}</strong></p>
            </div>
            <button onclick="document.getElementById('modal-cerrar').classList.remove('hidden')" class="w-full sm:w-auto bg-red-500 hover:bg-red-600 active:scale-98 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-md shadow-red-500/20 flex items-center justify-center gap-2">
                <i class="fas fa-lock"></i><span>Cerrar Turno de Caja</span>
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-4 mb-6">
            <div class="bg-blue-50/80 border border-blue-100 rounded-2xl p-3 sm:p-4">
                <p class="text-[10px] sm:text-xs text-blue-600 font-extrabold uppercase tracking-wider">Apertura</p>
                <p class="text-lg sm:text-2xl font-black text-blue-900 mt-0.5">{{ $moneda }}{{ number_format($turnoActivo->monto_apertura, 2) }}</p>
            </div>
            <div class="bg-green-50/80 border border-green-100 rounded-2xl p-3 sm:p-4">
                <p class="text-[10px] sm:text-xs text-green-600 font-extrabold uppercase tracking-wider">Efectivo</p>
                <p class="text-lg sm:text-2xl font-black text-green-900 mt-0.5">{{ $moneda }}{{ number_format($turnoActivo->total_efectivo, 2) }}</p>
            </div>
            <div class="bg-purple-50/80 border border-purple-100 rounded-2xl p-3 sm:p-4">
                <p class="text-[10px] sm:text-xs text-purple-600 font-extrabold uppercase tracking-wider">Tarjeta/Digital</p>
                <p class="text-lg sm:text-2xl font-black text-purple-900 mt-0.5">{{ $moneda }}{{ number_format($turnoActivo->total_tarjeta, 2) }}</p>
            </div>
            <div class="bg-emerald-50/80 border border-emerald-100 rounded-2xl p-3 sm:p-4">
                <p class="text-[10px] sm:text-xs text-emerald-600 font-extrabold uppercase tracking-wider">Ventas ({{ $turnoActivo->cantidad_ventas }})</p>
                <p class="text-lg sm:text-2xl font-black text-emerald-900 mt-0.5">{{ $moneda }}{{ number_format($turnoActivo->total_ventas, 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-4 border-t border-slate-100">
            <!-- Registrar Movimiento -->
            <div class="bg-slate-50/80 border border-slate-200 rounded-2xl p-4">
                <h3 class="font-extrabold text-sm sm:text-base text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-arrows-alt-h text-blue-500"></i><span>Registrar Movimiento de Caja</span>
                </h3>
                <form method="POST" action="{{ route('caja.movimiento', $turnoActivo->id) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <select name="tipo" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold focus:outline-none focus:border-emerald-500">
                            <option value="ingreso">🟢 Ingreso (+)</option>
                            <option value="egreso">🔴 Egreso (-)</option>
                        </select>
                        <input type="number" step="0.01" name="monto" placeholder="Monto (0.00)" required 
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-black focus:outline-none focus:border-emerald-500">
                    </div>
                    <input type="text" name="concepto" placeholder="Concepto (ej: Pago a proveedor de pan)" required 
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs sm:text-sm font-medium focus:outline-none focus:border-emerald-500">
                    <button class="w-full gradient-primary text-white py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-md shadow-emerald-500/20 hover:brightness-105 transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-plus"></i><span>Registrar Movimiento</span>
                    </button>
                </form>
            </div>

            <!-- Lista de Movimientos -->
            <div class="bg-slate-50/80 border border-slate-200 rounded-2xl p-4 flex flex-col">
                <h3 class="font-extrabold text-sm sm:text-base text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-list text-emerald-500"></i><span>Movimientos del Turno</span>
                </h3>
                <div class="max-h-56 overflow-y-auto space-y-2 flex-1 pr-1">
                    @forelse($turnoActivo->movimientos as $m)
                        <div class="flex justify-between items-center bg-white border border-slate-200 rounded-xl p-2.5 shadow-xs">
                            <div class="min-w-0 pr-2">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $m->concepto }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $m->created_at->format('H:i') }} • {{ ucfirst($m->tipo) }}</p>
                            </div>
                            <span class="font-black text-xs sm:text-sm whitespace-nowrap {{ $m->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $m->tipo == 'ingreso' ? '+' : '-' }}{{ $moneda }}{{ number_format($m->monto, 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <i class="fas fa-receipt text-2xl text-slate-300 mb-1"></i>
                            <p>Sin movimientos en este turno</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cerrar Turno -->
    <div id="modal-cerrar" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 hidden flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-5 sm:p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 leading-tight">Cerrar Turno de Caja</h3>
                    <p class="text-xs text-slate-400">{{ $turnoActivo->caja->nombre }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('caja.cerrar', $turnoActivo->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Monto físico contado en caja *</label>
                    <input type="number" step="0.01" name="monto_cierre" required 
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-2xl text-2xl font-black text-center text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500"
                           placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" placeholder="Detalle de cierre o diferencias..." rows="2" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs text-slate-700 focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-cerrar').classList.add('hidden')" 
                            class="w-1/3 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="w-2/3 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-extrabold text-xs sm:text-sm transition shadow-md shadow-red-500/20 flex items-center justify-center gap-1.5">
                        <i class="fas fa-lock"></i><span>Cerrar Turno</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@else
    <!-- Sin turno activo -->
    <div class="bg-white rounded-2xl shadow-md p-6 sm:p-10 mb-5 text-center border border-slate-100">
        <div class="w-16 h-16 sm:w-20 sm:h-20 gradient-primary text-white rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/30">
            <i class="fas fa-cash-register text-2xl sm:text-4xl"></i>
        </div>
        <h2 class="text-lg sm:text-2xl font-black text-slate-800 mb-1">No tienes un turno de caja abierto</h2>
        <p class="text-xs sm:text-sm text-slate-500 mb-6 max-w-md mx-auto">Para realizar ventas y emitir comprobantes, debes iniciar y abrir un turno de caja con tu saldo inicial.</p>
        <button onclick="document.getElementById('modal-abrir').classList.remove('hidden')" 
                class="gradient-primary text-white px-8 py-3.5 rounded-2xl font-black text-sm sm:text-base shadow-lg shadow-emerald-500/30 hover:brightness-105 active:scale-98 transition inline-flex items-center gap-2">
            <i class="fas fa-key"></i><span>Abrir Turno de Caja</span>
        </button>
    </div>

    <!-- Modal Abrir Turno (100% Ajustado y Centrado en Móvil) -->
    <div id="modal-abrir" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 hidden flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-5 sm:p-6 shadow-2xl border border-slate-100 transform transition-all mx-2">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl gradient-primary text-white flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 leading-tight">Abrir Turno de Caja</h3>
                    <p class="text-xs text-slate-400">Ingresa tu fondo o saldo inicial</p>
                </div>
            </div>

            @if($cajas->count() == 0)
                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 p-3 rounded-2xl mb-3">No hay cajas registradas. Crea una primero para empezar.</p>
                <form method="POST" action="{{ route('caja.store') }}" class="space-y-3">
                    @csrf
                    <input type="text" name="nombre" placeholder="Nombre de la caja (ej: Caja Principal)" required 
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-xs sm:text-sm font-semibold">
                    <button class="w-full gradient-primary text-white py-2.5 rounded-xl font-bold text-xs sm:text-sm">Crear Caja</button>
                </form>
            @else
                <form method="POST" action="{{ route('caja.abrir') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Caja</label>
                        <select name="caja_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500">
                            @foreach($cajas as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Monto de Apertura (Saldo Inicial) *</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="monto_apertura" required autofocus
                                   class="w-full px-3 py-2.5 border border-slate-300 rounded-2xl text-2xl font-black text-center text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500" 
                                   placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Observaciones</label>
                        <textarea name="observaciones" placeholder="Observaciones o notas de apertura..." rows="2" 
                                  class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs text-slate-700 focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('modal-abrir').classList.add('hidden')" 
                                class="w-1/3 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="w-2/3 py-3 gradient-primary text-white rounded-xl font-extrabold text-xs sm:text-sm transition shadow-lg shadow-emerald-500/25 hover:brightness-105 flex items-center justify-center gap-1.5">
                            <i class="fas fa-check-circle"></i><span>Abrir Turno</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

<!-- Historial de Turnos (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100">
    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-extrabold text-slate-800 text-sm sm:text-base flex items-center gap-2">
            <i class="fas fa-history text-blue-500"></i><span>Últimos Turnos de Caja</span>
        </h3>
    </div>

    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($turnos as $t)
            <div class="p-3.5 hover:bg-slate-50 transition">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 text-xs">{{ $t->caja->nombre }}</span>
                        @if($t->estado == 'abierto')
                            <span class="bg-green-100 text-green-700 px-2 py-0.2 rounded-full text-[10px] font-bold">Abierto</span>
                        @else
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.2 rounded-full text-[10px] font-bold">Cerrado</span>
                        @endif
                    </div>
                    <span class="font-black text-emerald-600 text-sm">{{ $moneda }}{{ number_format($t->total_ventas, 2) }}</span>
                </div>

                <div class="text-[11px] text-slate-500 space-y-0.5 mb-2">
                    <p><i class="fas fa-user-circle mr-1 text-slate-400"></i>{{ $t->user->name }}</p>
                    <p><i class="far fa-clock mr-1 text-slate-400"></i>Apertura: {{ $t->fecha_apertura->format('d/m H:i') }} | Cierre: {{ $t->fecha_cierre?->format('d/m H:i') ?? 'En curso' }}</p>
                </div>

                @if($t->estado != 'abierto')
                    <div class="pt-2 border-t border-slate-100 flex gap-1.5 justify-end">
                        <a href="{{ route('caja.ticket', $t->id) }}" target="_blank" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <i class="fas fa-print text-xs"></i><span>Ticket Cierre</span>
                        </a>
                        <a href="{{ route('caja.cierre', $t->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <i class="fas fa-file-invoice text-xs"></i><span>Resumen</span>
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-xs">
                <i class="fas fa-cash-register text-3xl text-slate-300 mb-1"></i>
                <p>No hay turnos registrados</p>
            </div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3 px-4">Caja</th>
                    <th class="py-3 px-4">Cajero</th>
                    <th class="py-3 px-4">Apertura</th>
                    <th class="py-3 px-4">Cierre</th>
                    <th class="py-3 px-4 text-right">Ventas</th>
                    <th class="py-3 px-4 text-right">Diferencia</th>
                    <th class="py-3 px-4 text-center">Estado</th>
                    <th class="py-3 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($turnos as $t)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3 px-4 font-bold text-slate-800">{{ $t->caja->nombre }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $t->user->name }}</td>
                    <td class="py-3 px-4 text-xs">{{ $t->fecha_apertura->format('d/m H:i') }}</td>
                    <td class="py-3 px-4 text-xs">{{ $t->fecha_cierre?->format('d/m H:i') ?? '—' }}</td>
                    <td class="py-3 px-4 text-right font-extrabold text-emerald-600">{{ $moneda }}{{ number_format($t->total_ventas, 2) }}</td>
                    <td class="py-3 px-4 text-right font-semibold {{ $t->diferencia < 0 ? 'text-red-600' : ($t->diferencia > 0 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $moneda }}{{ number_format($t->diferencia, 2) }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($t->estado == 'abierto')
                            <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-bold">Abierto</span>
                        @else
                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-xs font-bold">Cerrado</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        @if($t->estado != 'abierto')
                            <a href="{{ route('caja.ticket', $t->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg inline-block text-xs font-bold" title="Imprimir Ticket de Cierre"><i class="fas fa-print"></i></a>
                            <a href="{{ route('caja.cierre', $t->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg inline-block text-xs font-bold" title="Ver Resumen Completo"><i class="fas fa-eye"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-12 text-slate-400 text-xs">No hay turnos registrados</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
