@extends('layouts.app')
@section('title', 'Control de Envases & Garantías')
@section('header', 'Control de Envases Retornables & Garantías')

@section('content')
<div x-data="{
    modalPrestamo: false,
    editando: false,
    editId: null,
    envasesList: {{ Js::from($envases->items()) }},
    clientesList: {{ Js::from($clientes) }},
    clienteId: '',
    clienteNombre: '',
    busquedaCliente: '',
    dropdownCliente: false,
    tipoEnvase: 'Caja de Cerveza 12u (620ml)',
    esPersonalizado: false,
    cantidad: 1,
    montoGarantia: 20.00,
    observaciones: '',

    abrirNuevo() {
        this.editando = false;
        this.editId = null;
        this.clienteId = '';
        this.clienteNombre = '';
        this.busquedaCliente = '';
        this.tipoEnvase = 'Caja de Cerveza 12u (620ml)';
        this.esPersonalizado = false;
        this.cantidad = 1;
        this.montoGarantia = 20.00;
        this.observaciones = '';
        this.modalPrestamo = true;
    },
    abrirEdicion(id) {
        const item = this.envasesList.find(e => e.id === id);
        if (!item) return;

        this.editando = true;
        this.editId = item.id;
        this.clienteId = item.cliente_id || '';
        this.clienteNombre = item.cliente_nombre || '';
        this.busquedaCliente = this.clienteNombre;
        this.tipoEnvase = item.tipo_envase || '';
        this.cantidad = item.cantidad || 1;
        this.montoGarantia = parseFloat(item.monto_garantia || 0).toFixed(2);
        this.observaciones = item.observaciones || '';
        this.esPersonalizado = !['Caja de Cerveza 12u (620ml)', 'Botella Suelta Retornable (620ml)', 'Caja Cerveza Personal (310ml)'].includes(this.tipoEnvase);
        this.modalPrestamo = true;
    },
    seleccionarCliente(c) {
        this.clienteId = c.id;
        this.clienteNombre = c.nombres + (c.apellidos ? ' ' + c.apellidos : '');
        this.busquedaCliente = this.clienteNombre;
        this.dropdownCliente = false;
    },
    limpiarCliente() {
        this.clienteId = '';
        this.clienteNombre = '';
        this.busquedaCliente = '';
        this.dropdownCliente = false;
    },
    setTipoEnvase(nombre, garantiaSug) {
        this.esPersonalizado = false;
        this.tipoEnvase = nombre;
        this.montoGarantia = (garantiaSug * this.cantidad).toFixed(2);
    },
    setPersonalizado() {
        this.esPersonalizado = true;
        this.tipoEnvase = '';
    },
    recalcularGarantia() {
        if (this.tipoEnvase.includes('Caja de Cerveza 12u')) {
            this.montoGarantia = (20.00 * this.cantidad).toFixed(2);
        } else if (this.tipoEnvase.includes('Botella Suelta')) {
            this.montoGarantia = (2.00 * this.cantidad).toFixed(2);
        } else if (this.tipoEnvase.includes('Personal')) {
            this.montoGarantia = (15.00 * this.cantidad).toFixed(2);
        }
    },
    get clientesFiltrados() {
        if (!this.busquedaCliente || this.clienteId) return [];
        const q = this.busquedaCliente.toLowerCase();
        return this.clientesList.filter(c => 
            (c.nombres && c.nombres.toLowerCase().includes(q)) || 
            (c.apellidos && c.apellidos.toLowerCase().includes(q)) || 
            (c.documento && c.documento.includes(q))
        ).slice(0, 5);
    }
}">

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
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por cliente o envase..." class="w-full pl-8 pr-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm outline-none focus:border-amber-500">
            </form>
            <button type="button" @click="abrirNuevo()" class="px-4 py-2.5 gradient-primary text-white font-bold rounded-xl text-xs sm:text-sm flex items-center gap-2 shadow-md hover:brightness-105 transition whitespace-nowrap cursor-pointer">
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
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Botón Editar -->
                                    <button type="button" @click="abrirEdicion({{ $e->id }})" title="Editar Registro"
                                            class="p-1.5 bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-400 rounded-lg text-xs font-bold transition cursor-pointer">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    @if($e->estado === 'prestado')
                                        <!-- Botón Recibir & Reembolsar -->
                                        <form action="{{ route('envases.devolver', $e->id) }}" method="POST" onsubmit="return confirm('¿Confirmas que el cliente devolvió los envases y se le reembolsará la garantía de S/ {{ number_format($e->monto_garantia, 2) }}?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow transition flex items-center gap-1 cursor-pointer">
                                                <i class="fas fa-undo"></i> Recibir
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-slate-500 font-mono">Devuelto</span>
                                    @endif

                                    <!-- Botón Eliminar -->
                                    <form action="{{ route('envases.destroy', $e->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro de envases?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar" class="p-1.5 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 rounded-lg text-xs transition cursor-pointer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
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

    <!-- ============================================================== -->
    <!-- 🚀 MODAL REGISTRO / EDICIÓN DE ENVASES Y GARANTÍAS            -->
    <!-- ============================================================== -->
    <div x-show="modalPrestamo" x-cloak class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-lg p-6 shadow-2xl" @click.outside="modalPrestamo = false">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-box-open text-amber-500"></i>
                    <span x-text="editando ? `Editar Registro de Envase #${editId}` : 'Registrar Salida de Envases / Cascos'"></span>
                </h3>
                <button type="button" @click="modalPrestamo = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="editando ? `/envases/${editId}` : '{{ route('envases.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editando">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <input type="hidden" name="cliente_id" :value="clienteId">
                <input type="hidden" name="cliente_nombre" :value="clienteNombre || busquedaCliente">

                <!-- 1. Buscador Autocomplete de Cliente -->
                <div class="relative">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">
                        Cliente (Buscar por Nombre / DNI o Escribir Manual)
                    </label>
                    
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               x-model="busquedaCliente"
                               @focus="dropdownCliente = true"
                               @input="clienteId = ''; clienteNombre = busquedaCliente; dropdownCliente = true"
                               placeholder="Escribe el nombre o DNI del cliente..."
                               required
                               class="w-full pl-9 pr-8 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none font-medium">
                        
                        <button type="button" x-show="busquedaCliente" @click="limpiarCliente()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs cursor-pointer">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>

                    <!-- Indicador de Cliente Seleccionado -->
                    <template x-if="clienteId">
                        <div class="mt-1.5 px-2.5 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300 text-xs flex items-center justify-between">
                            <span><i class="fas fa-check-circle mr-1"></i> Cliente registrado vinculado</span>
                            <button type="button" @click="limpiarCliente()" class="underline text-[10px] text-emerald-400 hover:text-white cursor-pointer">Cambiar</button>
                        </div>
                    </template>

                    <!-- Lista Desplegable de Coincidencias -->
                    <div x-show="dropdownCliente && clientesFiltrados.length > 0" 
                         @click.outside="dropdownCliente = false"
                         class="absolute left-0 right-0 top-full mt-1 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden max-h-48 overflow-y-auto divide-y divide-slate-700">
                        <template x-for="c in clientesFiltrados" :key="c.id">
                            <div @click="seleccionarCliente(c)" class="p-2.5 hover:bg-slate-700/80 cursor-pointer flex justify-between items-center transition">
                                <div>
                                    <p class="text-xs font-bold text-white" x-text="c.nombres + ' ' + (c.apellidos || '')"></p>
                                    <p class="text-[10px] text-slate-400" x-text="c.documento ? ('Doc: ' + c.documento) : 'Sin documento'"></p>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 bg-amber-500/20 text-amber-400 rounded-full font-bold">Seleccionar</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 2. Selector de Tipo de Envase con Botones Rápidos -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">
                        Tipo de Envase / Casco
                    </label>
                    <div class="grid grid-cols-2 gap-1.5 mb-2">
                        <button type="button" @click="setTipoEnvase('Caja de Cerveza 12u (620ml)', 20.00)"
                                :class="!esPersonalizado && tipoEnvase.includes('Caja de Cerveza 12u') ? 'bg-amber-500 text-slate-950 font-bold border-amber-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'"
                                class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-beer-mug-empty"></i>
                            <span class="truncate">Caja 12u (620ml)</span>
                        </button>
                        <button type="button" @click="setTipoEnvase('Botella Suelta Retornable (620ml)', 2.00)"
                                :class="!esPersonalizado && tipoEnvase.includes('Botella Suelta') ? 'bg-amber-500 text-slate-950 font-bold border-amber-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'"
                                class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-wine-bottle"></i>
                            <span class="truncate">Botella Suelta</span>
                        </button>
                        <button type="button" @click="setTipoEnvase('Caja Cerveza Personal (310ml)', 15.00)"
                                :class="!esPersonalizado && tipoEnvase.includes('Personal') ? 'bg-amber-500 text-slate-950 font-bold border-amber-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'"
                                class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-boxes-stacked"></i>
                            <span class="truncate">Caja Personal 310ml</span>
                        </button>
                        <button type="button" @click="setPersonalizado()"
                                :class="esPersonalizado ? 'bg-amber-500 text-slate-950 font-bold border-amber-400' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'"
                                class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-pen"></i>
                            <span class="truncate">Otro Envase</span>
                        </button>
                    </div>

                    <!-- Input si es personalizado o nombre final -->
                    <div x-show="esPersonalizado">
                        <input type="text" x-model="tipoEnvase" placeholder="Escribe el tipo de envase o marca..." required class="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm focus:border-amber-500 outline-none">
                    </div>
                    <input type="hidden" name="tipo_envase" :value="tipoEnvase">
                </div>

                <!-- 3. Cantidad y Garantía en Soles -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Cantidad</label>
                        <input type="number" name="cantidad" x-model.number="cantidad" @input="recalcularGarantia()" step="1" min="1" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none font-bold text-center">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Garantía en Soles (S/)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">S/</span>
                            <input type="number" name="monto_garantia" x-model.number="montoGarantia" step="0.50" min="0" required class="w-full pl-8 pr-3 py-2.5 bg-slate-800 border border-slate-700 text-amber-400 font-black rounded-xl text-sm focus:border-amber-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- 4. Observaciones -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Observaciones (Opcional)</label>
                    <input type="text" name="observaciones" x-model="observaciones" placeholder="Ej. Prometió devolver el fin de semana..." class="w-full px-3.5 py-2 bg-slate-800 border border-slate-700 text-white rounded-xl text-xs sm:text-sm focus:border-amber-500 outline-none">
                </div>

                <div class="flex gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="modalPrestamo = false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-sm transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 gradient-primary text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/20 hover:brightness-105 transition cursor-pointer"
                            x-text="editando ? 'Guardar Cambios' : 'Registrar Préstamo'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
