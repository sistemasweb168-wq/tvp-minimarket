@extends('layouts.app')
@section('title', 'Configuración')
@section('header', 'Configuración del Sistema')

@section('content')
<div x-data="{ tab: 'empresa' }" class="grid grid-cols-1 lg:grid-cols-4 gap-5">
    <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-3 h-fit">
        <button @click="tab='empresa'" :class="tab=='empresa' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 transition flex items-center gap-3">
            <i class="fas fa-building w-5"></i><span>Datos de Empresa</span>
        </button>
        <button @click="tab='general'" :class="tab=='general' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 transition flex items-center gap-3">
            <i class="fas fa-cog w-5"></i><span>General</span>
        </button>
        <button @click="tab='facturacion'" :class="tab=='facturacion' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 transition flex items-center gap-3">
            <i class="fas fa-file-invoice w-5"></i><span>Facturación</span>
        </button>
        <button @click="tab='ticket'" :class="tab=='ticket' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 transition flex items-center gap-3">
            <i class="fas fa-receipt w-5"></i><span>Ticket</span>
        </button>
    </div>

    <div class="lg:col-span-3">
        <!-- Empresa -->
        <div x-show="tab=='empresa'" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-building mr-2 text-emerald-500"></i>Datos de la Empresa</h2>
            <form method="POST" action="{{ route('configuracion.empresa') }}" enctype="multipart/form-data">
                @csrf
                    <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 p-4 bg-slate-800 rounded-2xl border border-slate-700">
                        <!-- Logo -->
                        <div class="flex items-center gap-4">
                            @if($empresa->logo)
                                <img src="{{ $empresa->logo_url }}" class="w-16 h-16 rounded-xl object-contain bg-slate-900 border border-slate-800 p-2 border border-slate-700 shadow-xs">
                            @else
                                <div class="w-16 h-16 rounded-xl gradient-primary flex items-center justify-center shadow-xs">
                                    <i class="fas fa-store text-white text-2xl"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Logo de la Empresa</label>
                                <input type="file" name="logo" accept="image/*" class="block w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-100 file:text-emerald-800 file:font-semibold">
                            </div>
                        </div>

                        <!-- Imagen Portada Login -->
                        <div class="flex items-center gap-4">
                            @if($empresa->login_imagen)
                                <img src="{{ $empresa->login_imagen_url }}" class="w-16 h-16 rounded-xl object-cover bg-slate-900 border border-slate-800 border border-slate-700 shadow-xs">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-emerald-800 flex items-center justify-center shadow-xs">
                                    <i class="fas fa-image text-emerald-200 text-2xl"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <label class="block text-xs font-bold uppercase text-slate-300 mb-1">Imagen de Portada Login (Mitad Pantalla)</label>
                                <input type="file" name="login_imagen" accept="image/*" class="block w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-emerald-100 file:text-emerald-800 file:font-semibold">
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Razón Social *</label><input type="text" name="razon_social" value="{{ $empresa->razon_social }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Nombre Comercial</label><input type="text" name="nombre_comercial" value="{{ $empresa->nombre_comercial }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">RUC / NIT</label><input type="text" name="ruc_nit" value="{{ $empresa->ruc_nit }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div class="md:col-span-2"><label class="block text-sm font-semibold mb-1">Dirección</label><input type="text" name="direccion" value="{{ $empresa->direccion }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Ciudad</label><input type="text" name="ciudad" value="{{ $empresa->ciudad }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Teléfono</label><input type="text" name="telefono" value="{{ $empresa->telefono }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Email</label><input type="email" name="email" value="{{ $empresa->email }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Sitio Web</label><input type="text" name="sitio_web" value="{{ $empresa->sitio_web }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                </div>

                <h3 class="text-lg font-bold mt-6 mb-4 pb-2 border-b">Moneda e Impuestos</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Símbolo Moneda *</label>
                        <input type="text" name="moneda" value="{{ $empresa->moneda ?? 'S/' }}" maxlength="10" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg text-2xl font-bold text-center">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Código Moneda *</label>
                        <select name="codigo_moneda" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                            @foreach(['PEN'=>'Sol Peruano (PEN)','USD'=>'Dólar (USD)','EUR'=>'Euro (EUR)','MXN'=>'Peso Mexicano (MXN)','COP'=>'Peso Colombiano (COP)','ARS'=>'Peso Argentino (ARS)','CLP'=>'Peso Chileno (CLP)','BOB'=>'Boliviano (BOB)','VES'=>'Bolívar (VES)'] as $cod => $nom)
                                <option value="{{ $cod }}" {{ ($empresa->codigo_moneda ?? 'PEN') == $cod ? 'selected' : '' }}>{{ $nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Impuesto (%) *</label>
                        <input type="number" step="0.01" name="impuesto" value="{{ $empresa->impuesto ?? 18 }}" required class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                    </div>
                </div>
                <label class="flex items-center gap-2 mt-3"><input type="checkbox" name="impuesto_incluido" value="1" {{ ($empresa->impuesto_incluido ?? true) ? 'checked' : '' }}> Precios con impuesto incluido</label>

                <h3 class="text-lg font-bold mt-6 mb-4 pb-2 border-b">Mensajes</h3>
                <div class="space-y-3">
                    <div><label class="block text-sm font-semibold mb-1">Mensaje en Ticket</label><input type="text" name="mensaje_ticket" value="{{ $empresa->mensaje_ticket ?? '¡Gracias por su compra!' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Términos y Condiciones</label><textarea name="terminos_condiciones" rows="3" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">{{ $empresa->terminos_condiciones }}</textarea></div>
                </div>

                <button type="submit" class="mt-6 gradient-primary text-white px-6 py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Guardar Datos de Empresa</button>
            </form>
        </div>

        <!-- General -->
        <div x-show="tab=='general'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-cog mr-2 text-emerald-500"></i>Configuración General</h2>
            <form method="POST" action="{{ route('configuracion.general') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold mb-1">Puntos por unidad de moneda gastada</label>
                    <input type="number" step="0.01" name="config[puntos_por_moneda]" value="{{ $configs['puntos_por_moneda'] ?? '0.1' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                    <p class="text-xs text-slate-400 mt-1">Ej: 0.1 = el cliente recibe 1 punto por cada 10 unidades gastadas</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Días de aviso por vencimiento</label>
                    <input type="number" name="config[dias_aviso_vencimiento]" value="{{ $configs['dias_aviso_vencimiento'] ?? '30' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Stock mínimo predeterminado</label>
                    <input type="number" name="config[stock_minimo_default]" value="{{ $configs['stock_minimo_default'] ?? '5' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                </div>
                <button class="gradient-primary text-white px-6 py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Guardar</button>
            </form>
        </div>

        <!-- Facturación -->
        <div x-show="tab=='facturacion'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-file-invoice mr-2 text-emerald-500"></i>Facturación</h2>
            <form method="POST" action="{{ route('configuracion.general') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-semibold mb-1">Serie Ticket</label><input type="text" name="config[serie_ticket]" value="{{ $configs['serie_ticket'] ?? 'T001' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Serie Boleta</label><input type="text" name="config[serie_boleta]" value="{{ $configs['serie_boleta'] ?? 'B001' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                    <div><label class="block text-sm font-semibold mb-1">Serie Factura</label><input type="text" name="config[serie_factura]" value="{{ $configs['serie_factura'] ?? 'F001' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
                </div>
                <button class="gradient-primary text-white px-6 py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Guardar</button>
            </form>
        </div>

        <!-- Ticket -->
        <div x-show="tab=='ticket'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-receipt mr-2 text-emerald-500"></i>Configuración de Ticket</h2>
            <form method="POST" action="{{ route('configuracion.general') }}" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-semibold mb-1">Ancho papel (mm)</label>
                    <select name="config[ancho_ticket]" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                        @foreach([58,80] as $ancho)
                            <option value="{{ $ancho }}" {{ ($configs['ancho_ticket'] ?? 80) == $ancho ? 'selected' : '' }}>{{ $ancho }}mm</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2"><input type="checkbox" name="config[imprimir_auto]" value="1" {{ !empty($configs['imprimir_auto']) ? 'checked' : '' }}> Imprimir ticket automáticamente al cobrar</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="config[mostrar_logo_ticket]" value="1" {{ !empty($configs['mostrar_logo_ticket']) ? 'checked' : '' }}> Mostrar logo en ticket</label>
                <button class="gradient-primary text-white px-6 py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Guardar</button>
            </form>
        </div>
    </div>
</div>
@endsection
