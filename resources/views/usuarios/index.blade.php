@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<div x-data="{
    open: false,
    edit: null,
    usuariosList: {{ Js::from($usuarios->items()) }},
    editarUsuario(id) {
        this.edit = this.usuariosList.find(u => u.id === id);
        this.open = true;
    }
}">
<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-4 sm:p-5 mb-4 sm:mb-5 border border-slate-800 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
    <form method="GET" class="flex-1 flex gap-2 w-full max-w-xl">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs sm:text-sm"></i>
            <input name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o usuario..." class="w-full pl-10 pr-3 py-2 sm:py-2.5 border border-slate-600 rounded-xl text-xs sm:text-sm focus:outline-none focus:border-amber-500">
        </div>
        <button class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold transition"><i class="fas fa-search"></i></button>
    </form>
    <div class="flex gap-2 w-full sm:w-auto">
        <a href="{{ route('usuarios.roles') }}" class="flex-1 sm:flex-none bg-blue-50 hover:bg-blue-100 text-blue-700 px-3.5 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition"><i class="fas fa-user-tag"></i><span>Roles</span></a>
        <button @click="open=true; edit=null" class="flex-1 sm:flex-none gradient-primary text-white px-4 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-xs hover:brightness-105 transition"><i class="fas fa-user-plus"></i><span>Nuevo Usuario</span></button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($usuarios as $u)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 hover:shadow-lg transition">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 gradient-primary rounded-full flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold truncate">{{ $u->name }}</h3>
                    <p class="text-sm text-slate-400">{{ '@' . $u->username }}</p>
                    @if($u->role)
                        <span class="inline-block mt-2 px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">{{ $u->role->nombre }}</span>
                    @endif
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-sm space-y-1">
                <p><i class="fas fa-envelope text-slate-400 w-4"></i> {{ $u->email }}</p>
                @if($u->telefono)<p><i class="fas fa-phone text-slate-400 w-4"></i> {{ $u->telefono }}</p>@endif
                <p class="text-xs text-slate-400">
                    {!! $u->activo ? '<span class="text-green-600"><i class="fas fa-circle text-[8px]"></i> Activo</span>' : '<span class="text-red-600"><i class="fas fa-circle text-[8px]"></i> Inactivo</span>' !!}
                </p>
            </div>
            <div class="flex gap-2 mt-3">
                <button @click="editarUsuario({{ $u->id }})" class="flex-1 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-sm font-semibold"><i class="fas fa-edit mr-1"></i>Editar</button>
                <form method="POST" action="{{ route('usuarios.destroy', $u->id) }}" class="flex-1" onsubmit="return confirm('¿Desactivar?')">
                    @csrf @method('DELETE')
                    <button class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm"><i class="fas fa-user-slash mr-1"></i>Desactivar</button>
                </form>
            </div>
        </div>
    @empty
        <p class="col-span-full text-center text-slate-400 py-12">Sin usuarios</p>
    @endforelse
</div>
<div class="mt-4">{{ $usuarios->withQueryString()->links() }}</div>

<!-- Modal -->
<div x-show="open" x-cloak class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-slate-900 border border-slate-700 rounded-3xl w-full max-w-md p-6 shadow-2xl" @click.outside="open=false">
        <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-user text-amber-500"></i>
                <span x-text="edit ? 'Editar Usuario' : 'Nuevo Usuario'"></span>
            </h3>
            <button type="button" @click="open=false" class="text-slate-400 hover:text-white p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form :action="edit ? `/usuarios/${edit.id}` : '{{ route('usuarios.store') }}'" method="POST" class="space-y-3.5">
            @csrf
            <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Nombre completo</label>
                <input type="text" name="name" :value="edit?.name || ''" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Usuario</label>
                    <input type="text" name="username" :value="edit?.username || ''" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Email</label>
                    <input name="email" type="email" :value="edit?.email || ''" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
                </div>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Teléfono</label>
                <input type="text" name="telefono" :value="edit?.telefono || ''" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Rol Asignado</label>
                <select name="role_id" required class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none font-semibold">
                    @foreach($roles as $r)<option value="{{ $r->id }}" x-bind:selected="edit?.role_id === {{ $r->id }}">{{ $r->nombre }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block" x-text="edit ? 'Nueva contraseña (opcional)' : 'Contraseña'"></label>
                <input type="password" name="password" :required="!edit" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 block">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="w-full px-3.5 py-2.5 bg-slate-800 border border-slate-700 text-white rounded-xl text-sm focus:border-amber-500 outline-none">
            </div>
            <template x-if="edit">
                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer pt-1">
                    <input type="checkbox" name="activo" value="1" :checked="edit?.activo" class="rounded text-amber-500 bg-slate-800 border-slate-600">
                    <span>Usuario Activo</span>
                </label>
            </template>
            <div class="flex gap-3 pt-3 border-t border-slate-800">
                <button type="button" @click="open=false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-sm transition">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 gradient-primary text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/20 hover:brightness-105 transition">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
