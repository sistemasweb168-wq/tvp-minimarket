@extends('layouts.app')
@section('title', 'Promociones')
@section('header', 'Promociones y Descuentos')

@section('content')
@php $moneda = $empresaGlobal->moneda ?? 'S/'; @endphp

<div x-data="{ open: false, edit: null }">
<div class="bg-white rounded-2xl shadow-md p-5 mb-5 flex justify-between items-center">
    <h3 class="font-bold">Promociones Activas y Programadas</h3>
    <button @click="open=true; edit=null" class="gradient-primary text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2"><i class="fas fa-plus"></i>Nueva Promoción</button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($promociones as $p)
        <div class="bg-white rounded-2xl shadow-md p-5 relative overflow-hidden hover:shadow-lg transition">
            @if($p->vigente)
                <span class="absolute top-3 right-3 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">VIGENTE</span>
            @else
                <span class="absolute top-3 right-3 bg-slate-100 text-slate-500 px-2 py-1 rounded-full text-xs">Programada/Vencida</span>
            @endif

            <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-rose-500 rounded-2xl flex items-center justify-center text-white mb-3">
                <i class="fas fa-percent text-2xl"></i>
            </div>
            <h3 class="font-bold text-lg">{{ $p->nombre }}</h3>
            <p class="text-sm text-slate-500 mb-3">{{ $p->descripcion ?: 'Sin descripción' }}</p>

            <div class="space-y-1 text-sm">
                <p><strong>Tipo:</strong> {{ ucwords(str_replace('_', ' ', $p->tipo)) }}</p>
                <p><strong>Valor:</strong>
                    @if($p->tipo == 'descuento_porcentaje') {{ $p->valor }}%
                    @else {{ $moneda }}{{ number_format($p->valor, 2) }}
                    @endif
                </p>
                @if($p->producto)<p><strong>Producto:</strong> {{ $p->producto->nombre }}</p>@endif
                @if($p->categoria)<p><strong>Categoría:</strong> {{ $p->categoria->nombre }}</p>@endif
                <p class="text-xs text-slate-500"><i class="far fa-calendar"></i> {{ $p->fecha_inicio->format('d/m/Y') }} - {{ $p->fecha_fin->format('d/m/Y') }}</p>
            </div>

            <div class="flex gap-2 mt-3 pt-3 border-t border-slate-100">
                <button @click="edit={{ $p->toJson() }}; open=true" class="flex-1 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-sm"><i class="fas fa-edit mr-1"></i>Editar</button>
                <form method="POST" action="{{ route('promociones.destroy', $p->id) }}" class="flex-1" onsubmit="return confirm('¿Eliminar?')">
                    @csrf @method('DELETE')
                    <button class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm"><i class="fas fa-trash mr-1"></i>Eliminar</button>
                </form>
            </div>
        </div>
    @empty
        <p class="col-span-full text-center text-slate-400 py-12">No hay promociones registradas</p>
    @endforelse
</div>
<div class="mt-4">{{ $promociones->links() }}</div>

<!-- Modal -->
<div x-show="open" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto" @click.outside="open=false">
        <h3 class="text-xl font-bold mb-4" x-text="edit ? 'Editar Promoción' : 'Nueva Promoción'"></h3>
        <form :action="edit ? `/promociones/${edit.id}` : '{{ route('promociones.store') }}'" method="POST" class="space-y-3">
            @csrf
            <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>
            <div><label class="text-sm font-semibold">Nombre</label><input name="nombre" :value="edit?.nombre || ''" required class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
            <div><label class="text-sm font-semibold">Descripción</label><textarea name="descripcion" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg" x-text="edit?.descripcion || ''"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-sm font-semibold">Tipo</label>
                    <select name="tipo" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        <option value="descuento_porcentaje">Descuento %</option>
                        <option value="descuento_fijo">Descuento fijo</option>
                        <option value="2x1">2x1</option>
                        <option value="3x2">3x2</option>
                        <option value="precio_especial">Precio especial</option>
                    </select>
                </div>
                <div><label class="text-sm font-semibold">Valor</label><input type="number" step="0.01" name="valor" :value="edit?.valor || 0" required class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-sm font-semibold">Producto (opcional)</label>
                    <select name="producto_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        <option value="">— Cualquiera —</option>
                        @foreach($productos as $p)<option value="{{ $p->id }}">{{ $p->nombre }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-sm font-semibold">Categoría (opcional)</label>
                    <select name="categoria_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        <option value="">— Cualquiera —</option>
                        @foreach($categorias as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div><label class="text-sm font-semibold">Desde</label><input type="date" name="fecha_inicio" :value="edit?.fecha_inicio || '{{ now()->toDateString() }}'" required class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
                <div><label class="text-sm font-semibold">Hasta</label><input type="date" name="fecha_fin" :value="edit?.fecha_fin || '{{ now()->addDays(30)->toDateString() }}'" required class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
                <div><label class="text-sm font-semibold">Cant. mín.</label><input type="number" name="cantidad_minima" :value="edit?.cantidad_minima || 1" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></div>
            </div>
            <template x-if="edit"><label class="flex gap-2"><input type="checkbox" name="activo" value="1" :checked="edit?.activo"> Activa</label></template>
            <div class="flex gap-2 pt-3">
                <button type="button" @click="open=false" class="flex-1 py-2.5 bg-slate-200 rounded-lg">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 gradient-primary text-white rounded-lg font-semibold">Guardar</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
