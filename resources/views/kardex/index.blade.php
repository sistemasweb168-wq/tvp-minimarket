@extends('layouts.app')
@section('title', 'Kardex & Mermas')
@section('header', 'Kardex de Inventario & Registro de Mermas')

@section('content')
<div x-data="{ modalMerma: false, productoId: '', cantidad: 1, motivo: 'Rotura de botella en mostrador', observaciones: '' }">

    <!-- Tarjetas de Resumen Superior -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-4 mb-4 sm:mb-5">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 sm:p-5 shadow-md flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-base sm:text-xl font-bold border border-emerald-500/30 flex-shrink-0">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-slate-400 font-semibold uppercase tracking-wider truncate">Entradas (Compras)</p>
                <h3 class="text-lg sm:text-2xl font-black text-white">{{ number_format($totalEntradas, 0) }} <span class="text-xs font-normal text-slate-400">unids</span></h3>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 sm:p-5 shadow-md flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-base sm:text-xl font-bold border border-blue-500/30 flex-shrink-0">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-slate-400 font-semibold uppercase tracking-wider truncate">Salidas (Ventas POS)</p>
                <h3 class="text-lg sm:text-2xl font-black text-white">{{ number_format($totalSalidas, 0) }} <span class="text-xs font-normal text-slate-400">unids</span></h3>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 sm:p-5 shadow-md flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-base sm:text-xl font-bold border border-rose-500/30 flex-shrink-0">
                <i class="fas fa-wine-glass-crack"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs text-slate-400 font-semibold uppercase tracking-wider truncate">Mermas / Roturas</p>
                <h3 class="text-lg sm:text-2xl font-black text-rose-400">{{ number_format($totalMermas, 0) }} <span class="text-xs font-normal text-slate-400">unids</span></h3>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros & Acción (Collapsible en móvil) -->
    <div x-data="{ showFiltros: false }" class="bg-slate-900 border border-slate-800 rounded-2xl p-3 sm:p-5 mb-4 sm:mb-5 shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 sm:gap-4">
            <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-2 w-full">
                <!-- Botón Filtros (Solo Móvil) -->
                <button type="button" @click="showFiltros = !showFiltros" class="md:hidden w-full px-3 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                    <i class="fas fa-filter text-amber-400"></i>
                    <span x-text="showFiltros ? 'Ocultar' : 'Filtros'"></span>
                </button>
                <!-- Botón Registrar Merma -->
                <button type="button" @click="modalMerma = true" class="w-full sm:w-auto px-3 sm:px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-lg shadow-rose-600/30 transition">
                    <i class="fas fa-wine-glass-crack"></i>
                    <span>+ Merma</span>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('kardex.index') }}" :class="showFiltros ? 'block' : 'hidden md:grid'" class="grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mt-3 pt-3 border-t border-slate-800">
            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 block">Producto</label>
                <select name="producto_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
                    <option value="">Todos los productos</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id }}" {{ request('producto_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nombre }} (Stock: {{ $p->stock }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 block">Tipo de Movimiento</label>
                <select name="tipo" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
                    <option value="">Todos los tipos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entradas (Compras)</option>
                    <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salidas (Ventas)</option>
                    <option value="merma" {{ request('tipo') == 'merma' ? 'selected' : '' }}>Mermas / Roturas</option>
                    <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajustes Manuales</option>
                </select>
            </div>

            <div>
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 block">Fecha Desde</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1 block">Fecha Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
                </div>
                <button type="submit" class="p-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-sm transition" title="Filtrar">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Contenedor de Movimientos (Tarjetas en Móvil / Tabla en Desktop) -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md overflow-hidden">
        
        <!-- 📱 VISTA MÓVIL (LISTA MINIMALISTA CON LÍNEAS FINAS < md) -->
        <div class="md:hidden divide-y divide-slate-800">
            @forelse($movimientos as $m)
                @php
                    $esResta = in_array($m->tipo, ['salida', 'merma']);
                @endphp
                <div class="p-3 hover:bg-slate-800/40 transition">
                    <!-- Fila 1: Producto y Cantidad -->
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-slate-100 text-xs leading-snug line-clamp-1">
                                {{ $m->producto->nombre ?? 'Producto Eliminado' }}
                            </h4>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $m->producto->codigo ?? '' }}</span>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-black font-mono text-sm {{ $esResta ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $esResta ? '-' : '+' }}{{ number_format($m->cantidad, 0) }}
                            </span>
                        </div>
                    </div>

                    <!-- Fila 2: Badge de Tipo y Motivo -->
                    <div class="flex items-center gap-1.5 flex-wrap my-1">
                        @if($m->tipo === 'entrada')
                            <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-md text-[9px] font-black uppercase tracking-wider">
                                <i class="fas fa-arrow-down mr-0.5"></i> Entrada
                            </span>
                        @elseif($m->tipo === 'salida')
                            <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-md text-[9px] font-black uppercase tracking-wider">
                                <i class="fas fa-arrow-up mr-0.5"></i> Salida
                            </span>
                        @elseif($m->tipo === 'merma')
                            <span class="px-2 py-0.5 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-md text-[9px] font-black uppercase tracking-wider">
                                <i class="fas fa-wine-glass-crack mr-0.5"></i> Merma
                            </span>
                        @else
                            <span class="px-2 py-0.5 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-md text-[9px] font-black uppercase tracking-wider">
                                <i class="fas fa-sync-alt mr-0.5"></i> {{ ucfirst($m->tipo) }}
                            </span>
                        @endif

                        <span class="text-[11px] text-slate-300 font-medium truncate max-w-[200px]">{{ $m->motivo }}</span>
                    </div>

                    <!-- Fila 3: Fecha, Stock result y Usuario -->
                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1 pt-1 border-t border-slate-800/60">
                        <span><i class="far fa-clock mr-1"></i>{{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : $m->created_at->format('d/m/Y H:i') }}</span>
                        <span class="font-mono">Stock: <strong class="text-slate-300">{{ number_format($m->stock_anterior, 0) }}</strong> → <strong class="text-amber-400">{{ number_format($m->stock_nuevo, 0) }}</strong></span>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-500 text-xs">
                    <i class="fas fa-clipboard-list text-3xl mb-2 block text-slate-600"></i>
                    No hay movimientos registrados
                </div>
            @endforelse
        </div>

        <!-- 💻 VISTA ESCRITORIO (TABLA COMPLETA >= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 font-bold">Fecha / Hora</th>
                        <th class="py-3.5 px-4 font-bold">Producto</th>
                        <th class="py-3.5 px-4 font-bold">Tipo</th>
                        <th class="py-3.5 px-4 font-bold">Motivo / Ref</th>
                        <th class="py-3.5 px-4 font-bold text-center">Cant.</th>
                        <th class="py-3.5 px-4 font-bold text-center">Stock Ant.</th>
                        <th class="py-3.5 px-4 font-bold text-center">Stock Nuevo</th>
                        <th class="py-3.5 px-4 font-bold">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-200">
                    @forelse($movimientos as $m)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-400 font-mono text-xs">
                                {{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : $m->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 font-medium">
                                <p class="text-white font-bold">{{ $m->producto->nombre ?? 'Producto Eliminado' }}</p>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $m->producto->codigo ?? '' }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($m->tipo === 'entrada')
                                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-arrow-down mr-1"></i> Entrada
                                    </span>
                                @elseif($m->tipo === 'salida')
                                    <span class="px-2.5 py-1 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-arrow-up mr-1"></i> Salida
                                    </span>
                                @elseif($m->tipo === 'merma')
                                    <span class="px-2.5 py-1 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-wine-glass-crack mr-1"></i> Merma / Rotura
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-sync-alt mr-1"></i> {{ ucfirst($m->tipo) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <p class="text-slate-300 font-medium">{{ $m->motivo }}</p>
                                @if($m->observaciones)
                                    <p class="text-[11px] text-slate-500 italic">{{ $m->observaciones }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-bold font-mono {{ in_array($m->tipo, ['salida', 'merma']) ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ in_array($m->tipo, ['salida', 'merma']) ? '-' : '+' }}{{ number_format($m->cantidad, 0) }}
                            </td>
                            <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ number_format($m->stock_anterior, 0) }}</td>
                            <td class="py-3 px-4 text-center font-bold text-amber-400 font-mono">{{ number_format($m->stock_nuevo, 0) }}</td>
                            <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-400">
                                <i class="fas fa-user-circle mr-1 text-slate-500"></i>{{ $m->user->name ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-500">
                                <i class="fas fa-clipboard-list text-3xl mb-2 block text-slate-600"></i>
                                No se encontraron registros de movimientos en el Kardex.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movimientos->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL REGISTRO DE MERMA / ROTURA -->
    <div x-show="modalMerma" x-cloak class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-lg p-6 shadow-2xl" @click.outside="modalMerma = false">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-wine-glass-crack text-rose-500"></i>
                    <span>Registrar Merma / Rotura de Botella</span>
                </h3>
                <button type="button" @click="modalMerma = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('kardex.merma') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Producto Afectado</label>
                    <select name="producto_id" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-rose-500 outline-none">
                        <option value="">Selecciona el producto...</option>
                        @foreach($productos as $p)
                            @if($p->controla_stock)
                                <option value="{{ $p->id }}">{{ $p->nombre }} (Stock actual: {{ $p->stock }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Cantidad Dañada</label>
                        <input type="number" name="cantidad" step="1" min="1" value="1" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Motivo de Merma</label>
                        <select name="motivo" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-rose-500 outline-none">
                            <option value="Rotura de botella en mostrador">Rotura en mostrador</option>
                            <option value="Rotura en descarga de camión">Rotura en descarga</option>
                            <option value="Producto vencido / picado">Producto vencido</option>
                            <option value="Lata o chapa abollada/filtrada">Defecto de envase</option>
                            <option value="Consumo de personal / Degustación">Consumo/Degustación</option>
                            <option value="Ajuste por descuadre de inventario">Descuadre de stock</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Detalles u Observaciones (Opcional)</label>
                    <textarea name="observaciones" rows="2" placeholder="Ej. Se cayó del estante durante la limpieza..." class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-rose-500 outline-none resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="modalMerma = false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-sm transition">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-rose-600/30 transition">Confirmar Merma</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
