@extends('layouts.app')
@section('title', 'Roles')
@section('header', 'Roles y Permisos')

@section('content')
@php
$permisosDisponibles = [
    'pos' => 'Acceso al POS (Caja Rápida)',
    'ventas' => 'Ver Historial de Ventas',
    'ventas.anular' => 'Anular Ventas',
    'productos' => 'Ver y Editar Productos',
    'kardex' => 'Kardex y Registro de Mermas',
    'compras' => 'Gestión de Compras',
    'clientes' => 'Gestión de Clientes',
    'proveedores' => 'Gestión de Proveedores',
    'caja' => 'Apertura y Cierre de Caja',
    'caja.movimientos' => 'Movimientos de Dinero',
    'envases' => 'Control de Envases & Garantías',
    'reportes' => 'Acceso a Reportes Generales',
    'utilidades' => 'Reporte de Utilidad Neta Real',
    'sunat' => 'Facturación SUNAT',
    'configuracion' => 'Configuración del Sistema',
    'usuarios' => 'Gestión de Usuarios y Roles',
    'backup' => 'Backup del Sistema',
];
@endphp

<div x-data="{
    open: false,
    edit: null,
    rolesList: {{ Js::from($roles) }},
    permisosDisp: {{ Js::from($permisosDisponibles) }},
    editarRol(id) {
        this.edit = this.rolesList.find(r => r.id === id);
        this.open = true;
    }
}">
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 mb-5 flex justify-between items-center">
    <h3 class="font-bold">Roles del Sistema</h3>
    <button @click="open=true; edit=null" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2"><i class="fas fa-plus"></i>Nuevo Rol</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($roles as $r)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5">
            <div class="flex justify-between items-start mb-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-tag text-purple-600 text-xl"></i>
                </div>
                <button @click="editarRol({{ $r->id }})" class="p-2 hover:bg-yellow-500/20 text-yellow-500 rounded-lg"><i class="fas fa-edit"></i></button>
            </div>
            <h3 class="font-bold">{{ $r->nombre }}</h3>
            <p class="text-sm text-slate-400 mb-3">{{ $r->descripcion ?? 'Sin descripción' }}</p>
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($r->permisos ?? [] as $p)
                    <span class="bg-slate-900 px-2 py-1 rounded text-xs">{{ $p }}</span>
                @endforeach
            </div>
            <p class="text-xs text-slate-400"><i class="fas fa-users mr-1"></i>{{ $r->users_count }} usuario(s)</p>
        </div>
    @endforeach
</div>

<div x-show="open" x-cloak class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-lg p-6 shadow-2xl" @click.outside="open=false">
        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-shield-alt text-amber-500"></i>
                <span x-text="edit ? 'Editar Rol' : 'Nuevo Rol'"></span>
            </h3>
            <button type="button" @click="open=false" class="text-slate-400 hover:text-white p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form :action="edit ? `/roles/${edit.id}` : '{{ route('roles.store') }}'" method="POST" class="space-y-4">
            @csrf
            <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Nombre del rol</label>
                <input type="text" name="nombre" :value="edit?.nombre || ''" required placeholder="Ej. Cajero Turno Noche" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5 block">Descripción</label>
                <input type="text" name="descripcion" :value="edit?.descripcion || ''" placeholder="Breve resumen de funciones..." class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-2 block">Permisos Permitidos</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-1 bg-slate-950/50 rounded-xl border border-slate-800">
                    <template x-for="(label, key) in permisosDisp" :key="key">
                        <label class="flex items-center gap-2.5 p-2.5 hover:bg-slate-800/80 rounded-lg cursor-pointer transition border border-transparent hover:border-slate-700">
                            <input type="checkbox" name="permisos[]" :value="key" x-bind:checked="edit?.permisos?.includes(key)" class="rounded text-amber-500 focus:ring-amber-500 bg-slate-800 border-slate-600">
                            <span class="text-xs font-medium text-slate-200" x-text="label"></span>
                        </label>
                    </template>
                </div>
            </div>
            <template x-if="edit">
                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer pt-1">
                    <input type="checkbox" name="activo" value="1" :checked="edit?.activo" class="rounded text-amber-500 bg-slate-800 border-slate-600">
                    <span>Rol Activo</span>
                </label>
            </template>
            <div class="flex gap-3 pt-3 border-t border-slate-800">
                <button type="button" @click="open=false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-sm transition">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 gradient-primary text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/20 hover:brightness-105 transition">Guardar Rol</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
