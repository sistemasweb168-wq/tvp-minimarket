@extends('layouts.app')
@section('title', 'Editar Cliente')
@section('header', 'Editar: ' . $cliente->nombre_completo)

@section('content')
<form method="POST" action="{{ route('clientes.update', $cliente->id) }}" class="bg-white rounded-2xl shadow-md p-6 max-w-4xl">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold mb-1">Código</label><input type="text" name="codigo" value="{{ $cliente->codigo }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Tipo Documento</label>
            <select name="tipo_documento" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                @foreach(['DNI','RUC','CE','PASAPORTE'] as $td)
                    <option value="{{ $td }}" {{ $cliente->tipo_documento == $td ? 'selected' : '' }}>{{ $td }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-semibold mb-1">N° Documento</label><input type="text" name="documento" value="{{ $cliente->documento }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Nombres</label><input type="text" name="nombres" value="{{ $cliente->nombres }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Apellidos</label><input type="text" name="apellidos" value="{{ $cliente->apellidos }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Razón Social</label><input type="text" name="razon_social" value="{{ $cliente->razon_social }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Teléfono</label><input type="text" name="telefono" value="{{ $cliente->telefono }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Email</label><input type="email" name="email" value="{{ $cliente->email }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Dirección</label><input type="text" name="direccion" value="{{ $cliente->direccion }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Ciudad</label><input type="text" name="ciudad" value="{{ $cliente->ciudad }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Crédito Límite</label><input type="number" step="0.01" name="credito_limite" value="{{ $cliente->credito_limite }}" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Observaciones</label><textarea name="observaciones" rows="2" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">{{ $cliente->observaciones }}</textarea></div>
        <label class="md:col-span-2 flex items-center gap-2"><input type="checkbox" name="activo" value="1" {{ $cliente->activo ? 'checked' : '' }}> Activo</label>
    </div>
    <div class="flex gap-3 mt-6">
        <a href="{{ route('clientes.index') }}" class="flex-1 text-center py-3 bg-slate-200 rounded-lg">Cancelar</a>
        <button class="flex-1 gradient-primary text-white py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Actualizar</button>
    </div>
</form>
@endsection
