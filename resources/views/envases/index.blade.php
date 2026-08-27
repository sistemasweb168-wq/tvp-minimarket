@extends('layouts.app')
@section('title', 'Control de Envases & Garantías')
@section('header', 'Control de Envases Retornables & Garantías')

@section('content')
<div x-data="{ modalPrestamo: false, clienteId: '', clienteNombre: '', tipoEnvase: 'Caja de Cerveza 12u', cantidad: 1, montoGarantia: 20.00, observaciones: '' }">

    <!-- Resumen Superior -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl font-bold border border-amber-500/30">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Envases / Cascos en Calle</p>
                <h3 class="text-xl sm:text-2xl font-black text-amber-400">{{ number_format($totalPrestados, 0) }} <span class="text-xs font-normal text-slate-400">unids</span></h3>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl font-bold border border-emerald-500/30">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Garantía en Caja Retenida</p>
                <h3 class="text-xl sm:text-2xl font-black text-white">S/ {{ number_format($totalGarantiaRetenida, 2) }}</h3>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-md flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-xl font-bold border border-blue-500/30">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Envases Recuperados</p>
                <h3 class="text-xl sm:text-2xl font-black text-white">{{ number_format($totalDevueltos, 0) }} <span class="text-xs font-normal text-slate-400">unids</span></h3>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Pestañas -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 mb-5 shadow-md flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        
        <!-- Pestañas de Estado -->
        <div class="flex bg-slate-800/80 p-1 rounded-xl border border-slate-700">
            <a href="{{ route('envases.index', ['estado' => 'prestado']) }}" 
               class="px-3.5 py-2 rounded-lg text-xs sm:text-sm font-bold transition flex items-center gap-1.5 {{ $estado === 'prestado' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                <i class="fas fa-clock"></i> Pendientes / En Calle
            </a>
            <a href="{{ route('envases.index', ['estado' => 'devuelto']) }}" 
               class="px-3.5 py-2 rounded-lg text-xs sm:text-sm font-bold transition flex items-center gap-1.5 {{ $estado === 'devuelto' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                <i class="fas fa-check-double"></i> Devueltos
            </a>
            <a href="{{ route('envases.index', ['estado' => '']) }}" 
               class="px-3.5 py-2 rounded-lg text-xs sm:text-sm font-bold transition flex items-center gap-1.5 {{ empty($estado) ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                Todos
            </a>
        </div>

        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('envases.index') }}" class="relative flex-1 sm:w-64">
                <input type="hidden" name="estado" value="{{ $estado }}">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar cliente..." class="w-full pl-8 pr-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
            </form>
            <button type="button" @click="modalPrestamo = true" class="px-4 py-2.5 gradient-primary text-white font-bold rounded-xl text-xs sm:text-sm flex items-center gap-2 shadow-md hover:brightness-105 transition whitespace-nowrap">
                <i class="fas fa-plus-circle"></i>
                <span>Prestar Envases</span>
            </button>
        </div>
    </div>

    <!-- Tabla de Envases y Garantías -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 font-bold">Fecha</th>
                        <th class="py-3.5 px-4 font-bold">Cliente</th>
                        <th class="py-3.5 px-4 font-bold">Envase / Casco</th>
                        <th class="py-3.5 px-4 font-bold text-center">Cant.</th>
                        <th class="py-3.5 px-4 font-bold text-right">Garantía</th>
                        <th class="py-3.5 px-4 font-bold text-center">Estado</th>
                        <th class="py-3.5 px-4 font-bold text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-200">
                    @forelse($envases as $e)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-400 font-mono text-xs">
                                {{ $e->fecha_prestamo ? $e->fecha_prestamo->format('d/m/Y H:i') : '' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-white">
                                <p>{{ $e->cliente_nombre ?? ($e->cliente->nombres ?? 'Cliente Mostrador') }}</p>
                                @if($e->cliente && $e->cliente->telefono)
                                    <span class="text-[10px] text-slate-400 font-normal"><i class="fas fa-phone mr-0.5"></i>{{ $e->cliente->telefono }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-slate-300 font-medium">{{ $e->tipo_envase }}</span>
                                @if($e->observaciones)
                                    <p class="text-[10px] text-slate-500 italic">{{ $e->observaciones }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-amber-400 font-mono text-sm">
                                {{ $e->cantidad }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-white">
                                S/ {{ number_format($e->monto_garantia, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($e->estado === 'prestado')
                                    <span class="px-2.5 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-clock mr-1"></i> En Calle
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-check mr-1"></i> Devuelto
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($e->estado === 'prestado')
                                    <form action="{{ route('envases.devolver', $e->id) }}" method="POST" onsubmit="return confirm('¿Confirmas que el cliente devolvió los envases y se le reembolsará la garantía de S/ {{ number_format($e->monto_garantia, 2) }}?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow transition flex items-center gap-1.5 mx-auto">
                                            <i class="fas fa-undo"></i> Recibir & Reembolsar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500 font-mono">Devuelto el {{ $e->fecha_devolucion ? $e->fecha_devolucion->format('d/m/Y') : '' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-500">
                                <i class="fas fa-box-open text-3xl mb-2 block text-slate-600"></i>
                                No hay registros de envases en esta sección.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($envases->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $envases->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL PRESTAR ENVASES CON GARANTIA -->
    <div x-show="modalPrestamo" x-cloak class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-lg p-6 shadow-2xl" @click.outside="modalPrestamo = false">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-box-open text-amber-500"></i>
                    <span>Registrar Salida de Envases / Cascos</span>
                </h3>
                <button type="button" @click="modalPrestamo = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('envases.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Cliente Registrado</label>
                        <select name="cliente_id" x-model="clienteId" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                            <option value="">Cliente Ocasional / Manual...</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->nombres }} {{ $c->apellidos }} ({{ $c->documento }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="!clienteId">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Nombre del Cliente</label>
                        <input type="text" name="cliente_nombre" placeholder="Ej. Juan Pérez / Vecino" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Tipo de Envase</label>
                        <select name="tipo_envase" x-model="tipoEnvase" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                            <option value="Caja de Cerveza (12 botellas 620ml)">Caja Cerveza 12u (620ml)</option>
                            <option value="Botella Retornable 620ml (Casco)">Botella Suelta 620ml</option>
                            <option value="Caja Cerveza Personal (310ml)">Caja Personal (310ml)</option>
                            <option value="Botella Retornable 1 Litro">Botella 1 Litro</option>
                            <option value="Bidón de Agua / Otro">Bidón / Otro Envase</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Cantidad de Envases</label>
                        <input type="number" name="cantidad" x-model="cantidad" step="1" min="1" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Monto de Garantía Cobrado en Efectivo (S/)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">S/</span>
                        <input type="number" name="monto_garantia" x-model="montoGarantia" step="0.50" min="0" required class="w-full pl-10 pr-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none font-bold text-amber-400">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Este monto ingresará a tu caja del turno actual como garantía en custodia.</p>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Observaciones (Opcional)</label>
                    <input type="text" name="observaciones" placeholder="Ej. Prometió devolver el sábado..." class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                </div>

                <div class="flex gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="modalPrestamo = false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-sm transition">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 gradient-primary text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/20 hover:brightness-105 transition">Registrar Préstamo</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
