@extends('layouts.app')
@section('title', 'Roles')
@section('header', 'Roles y Permisos')

@section('content')
@php
$permisosDisponibles = [
    'productos' => 'Productos', 'ventas' => 'Ventas', 'compras' => 'Compras',
    'clientes' => 'Clientes', 'proveedores' => 'Proveedores', 'caja' => 'Caja',
    'reportes' => 'Reportes', 'configuracion' => 'Configuración',
    'usuarios' => 'Usuarios', 'backup' => 'Backup',
];
@endphp

<div x-data="{ open: false, edit: null, permisosDisp: @json($permisosDisponibles) }">
<div class="bg-white rounded-2xl shadow-md p-5 mb-5 flex justify-between items-center">
    <h3 class="font-bold">Roles del Sistema</h3>
    <button @click="open=true; edit=null" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2"><i class="fas fa-plus"></i>Nuevo Rol</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($roles as $r)
        <div class="bg-white rounded-2xl shadow-md p-5">
            <div class="flex justify-between items-start mb-3">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-tag text-purple-600 text-xl"></i>
                </div>
                <button @click="edit={{ $r->toJson() }}; open=true" class="p-2 hover:bg-yellow-50 text-yellow-600 rounded-lg"><i class="fas fa-edit"></i></button>
            </div>
            <h3 class="font-bold">{{ $r->nombre }}</h3>
            <p class="text-sm text-slate-500 mb-3">{{ $r->descripcion ?? 'Sin descripción' }}</p>
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($r->permisos ?? [] as $p)
                    <span class="bg-slate-100 px-2 py-1 rounded text-xs">{{ $p }}</span>
                @endforeach
            </div>
            <p class="text-xs text-slate-500"><i class="fas fa-users mr-1"></i>{{ $r->users_count }} usuario(s)</p>
        </div>
    @endforeach
</div>

<div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6" @click.outside="open=false">
        <h3 class="text-xl font-bold mb-4" x-text="edit ? 'Editar Rol' : 'Nuevo Rol'"></h3>
        <form :action="edit ? `/roles/${edit.id}` : '{{ route('roles.store') }}'" method="POST" class="space-y-3">
            @csrf
            <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
            <div><label class="text-sm font-semibold">Nombre del rol</label><input name="nombre" :value="edit?.nombre || ''" required class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
            <div><label class="text-sm font-semibold">Descripción</label><input name="descripcion" :value="edit?.descripcion || ''" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
            <div>
                <label class="text-sm font-semibold mb-2 block">Permisos</label>
                <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                    <template x-for="(label, key) in permisosDisp" :key="key">
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded">
                            <input type="checkbox" name="permisos[]" :value="key" x-bind:checked="edit?.permisos?.includes(key)" class="rounded text-emerald-500">
                            <span class="text-sm" x-text="label"></span>
                        </label>
                    </template>
                </div>
            </div>
            <template x-if="edit"><label class="flex gap-2"><input type="checkbox" name="activo" value="1" :checked="edit?.activo"> Activo</label></template>
            <div class="flex gap-2 pt-3">
                <button type="button" @click="open=false" class="flex-1 py-2.5 bg-slate-200 rounded-lg">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 gradient-primary text-white rounded-lg font-semibold">Guardar</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
