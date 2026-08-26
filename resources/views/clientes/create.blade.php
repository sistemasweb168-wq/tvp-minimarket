@extends('layouts.app')
@section('title', 'Nuevo Cliente')
@section('header', 'Nuevo Cliente')

@section('content')
<form method="POST" action="{{ route('clientes.store') }}" class="bg-white rounded-2xl shadow-md p-6 max-w-4xl">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold mb-1">Código</label><input type="text" name="codigo" value="{{ $codigo }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Tipo Documento</label>
            <select name="tipo_documento" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                <option value="DNI">DNI</option><option value="RUC">RUC</option><option value="CE">CE</option><option value="PASAPORTE">Pasaporte</option>
            </select>
        </div>
        <div><label class="block text-sm font-semibold mb-1">N° Documento</label><input type="text" name="documento" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Nombres *</label><input type="text" name="nombres" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Apellidos</label><input type="text" name="apellidos" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Razón Social</label><input type="text" name="razon_social" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Teléfono</label><input type="text" name="telefono" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Email</label><input type="email" name="email" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Dirección</label><input type="text" name="direccion" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Ciudad</label><input type="text" name="ciudad" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Fecha Nacimiento</label><input type="date" name="fecha_nacimiento" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div><label class="block text-sm font-semibold mb-1">Género</label>
            <select name="genero" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                <option value="">—</option><option value="M">Masculino</option><option value="F">Femenino</option><option value="O">Otro</option>
            </select>
        </div>
        <div><label class="block text-sm font-semibold mb-1">Crédito Límite</label><input type="number" step="0.01" name="credito_limite" value="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></div>
        <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Observaciones</label><textarea name="observaciones" rows="2" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg"></textarea></div>
    </div>
    <div class="flex gap-3 mt-6">
        <a href="{{ route('clientes.index') }}" class="flex-1 text-center py-3 bg-slate-200 rounded-lg">Cancelar</a>
        <button class="flex-1 gradient-primary text-white py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Guardar Cliente</button>
    </div>
</form>
@endsection
