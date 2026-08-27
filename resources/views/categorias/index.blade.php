@extends('layouts.app')
@section('title', 'Categorías')
@section('header', 'Categorías de Productos')

@section('content')
<div x-data="{ open: false, edit: null }" class="space-y-5">

<div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 flex justify-between items-center">
    <h3 class="font-bold text-slate-100">Listado de Categorías</h3>
    <button @click="open = true; edit = null" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2">
        <i class="fas fa-plus"></i>Nueva Categoría
    </button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse($categorias as $c)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-5 hover:shadow-lg transition" style="border-top: 4px solid {{ $c->color }}">
            <div class="flex justify-between items-start mb-3">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:{{ $c->color }}20">
                    <i class="fas fa-{{ $c->icono }} text-xl" style="color:{{ $c->color }}"></i>
                </div>
                <div class="flex gap-1">
                    <button @click="edit = {{ $c->toJson() }}; open = true" class="p-2 hover:bg-yellow-50 text-yellow-600 rounded-lg"><i class="fas fa-edit"></i></button>
                    <form method="POST" action="{{ route('categorias.destroy', $c->id) }}" class="inline" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button class="p-2 hover:bg-red-50 text-red-600 rounded-lg"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <h3 class="font-bold text-slate-100">{{ $c->nombre }}</h3>
            <p class="text-sm text-slate-400 mb-2">{{ $c->descripcion ?? 'Sin descripción' }}</p>
            <div class="flex items-center gap-2 text-xs">
                <span class="bg-slate-900 px-2 py-1 rounded-full"><i class="fas fa-box mr-1"></i>{{ $c->productos_count }} productos</span>
                @if(!$c->activo)
                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded-full">Inactivo</span>
                @endif
            </div>
        </div>
    @empty
        <p class="col-span-full text-center text-slate-400 py-12">No hay categorías</p>
    @endforelse
</div>
{{ $categorias->links() }}

<!-- Modal -->
<div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="open = false">
        <h3 class="text-xl font-bold mb-4" x-text="edit ? 'Editar Categoría' : 'Nueva Categoría'"></h3>
        <form :action="edit ? `/categorias/${edit.id}` : '{{ route('categorias.store') }}'" method="POST" class="space-y-3">
            @csrf
            <template x-if="edit">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div>
                <label class="block text-sm font-semibold mb-1">Nombre</label>
                <input type="text" name="nombre" :value="edit?.nombre || ''" required class="w-full px-3 py-2 border border-slate-600 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Descripción</label>
                <input type="text" name="descripcion" :value="edit?.descripcion || ''" class="w-full px-3 py-2 border border-slate-600 rounded-lg">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-1">Color</label>
                    <input type="color" name="color" :value="edit?.color || '#10b981'" class="w-full h-10 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Ícono</label>
                    <select name="icono" class="w-full px-3 py-2 border border-slate-600 rounded-lg">
                        @foreach(['cube','apple-alt','bread-slice','wine-bottle','candy-cane','cookie','cheese','fish','drumstick-bite','ice-cream','pizza-slice','seedling','soap','tshirt','baby','paw'] as $i)
                            <option value="{{ $i }}" x-bind:selected="edit?.icono === '{{ $i }}'">{{ $i }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <template x-if="edit">
                <label class="flex items-center gap-2"><input type="checkbox" name="activo" value="1" :checked="edit?.activo"> Activo</label>
            </template>
            <div class="flex gap-2 pt-3">
                <button type="button" @click="open = false" class="flex-1 py-2.5 bg-slate-700 rounded-lg">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 gradient-primary text-white rounded-lg font-semibold">Guardar</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
