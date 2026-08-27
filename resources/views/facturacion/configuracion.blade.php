@extends('layouts.app')
@section('title', 'Configuración SUNAT')
@section('header', 'Configuración SUNAT')

@section('content')
<div x-data="{ tab: 'sunat' }" class="grid grid-cols-1 lg:grid-cols-4 gap-5">
    <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-3 h-fit">
        <button @click="tab='sunat'" :class="tab=='sunat' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 flex items-center gap-3">
            <i class="fas fa-shield-alt w-5"></i><span>Datos SUNAT</span>
        </button>
        <button @click="tab='certificado'" :class="tab=='certificado' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 flex items-center gap-3">
            <i class="fas fa-certificate w-5"></i><span>Certificado Digital</span>
        </button>
        <button @click="tab='series'" :class="tab=='series' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 flex items-center gap-3">
            <i class="fas fa-hashtag w-5"></i><span>Series y Correlativos</span>
        </button>
        <button @click="tab='ayuda'" :class="tab=='ayuda' ? 'gradient-primary text-white' : ''" class="w-full text-left px-4 py-3 rounded-lg mb-1 flex items-center gap-3">
            <i class="fas fa-question-circle w-5"></i><span>Ayuda</span>
        </button>
    </div>

    <div class="lg:col-span-3">
        <!-- Datos SUNAT -->
        <div x-show="tab=='sunat'" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-shield-alt mr-2 text-emerald-500"></i>Configuración SUNAT</h2>
            <form method="POST" action="{{ route('facturacion.configuracion.guardar') }}" enctype="multipart/form-data">
                @csrf
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-5">
                    <p class="text-sm text-blue-700"><i class="fas fa-info-circle mr-1"></i>
                    Para emitir comprobantes electrónicos primero debes tener un RUC habilitado por SUNAT y un certificado digital.</p>
                </div>

                <h3 class="font-extrabold text-sm text-slate-200 mb-2">Ambiente de emisión</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                    <label class="cursor-pointer block">
                        <input type="radio" name="sunat_modo" value="beta" {{ ($empresa->sunat_modo ?? 'beta') == 'beta' ? 'checked' : '' }} class="peer hidden">
                        <div class="border-2 border-slate-700 peer-checked:border-yellow-500 peer-checked:bg-yellow-50/80 rounded-2xl p-3.5 sm:p-4 transition shadow-xs">
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-8 h-8 rounded-xl bg-yellow-100 text-yellow-600 flex items-center justify-center text-base flex-shrink-0">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <span class="font-black text-slate-100 text-xs sm:text-sm">Beta / Homologación</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-tight">Ambiente de pruebas. Los comprobantes NO son válidos fiscalmente ante SUNAT.</p>
                        </div>
                    </label>
                    <label class="cursor-pointer block">
                        <input type="radio" name="sunat_modo" value="produccion" {{ ($empresa->sunat_modo ?? '') == 'produccion' ? 'checked' : '' }} class="peer hidden">
                        <div class="border-2 border-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/80 rounded-2xl p-3.5 sm:p-4 transition shadow-xs">
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base flex-shrink-0">
                                    <i class="fas fa-rocket"></i>
                                </div>
                                <span class="font-black text-slate-100 text-xs sm:text-sm">Producción Real</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-tight">Ambiente oficial. Los comprobantes son válidos y enviados a SUNAT.</p>
                        </div>
                    </label>
                </div>

                <h3 class="font-bold text-slate-200 mb-3">Credenciales SOL (Producción)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Usuario SOL</label>
                        <input type="text" name="sunat_usuario_sol" value="{{ $empresa->sunat_usuario_sol }}"
                               class="w-full px-3 py-2.5 border border-slate-600 rounded-lg" placeholder="MIRUC + MODDATOS">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Clave SOL</label>
                        <input type="password" name="sunat_clave_sol" value="{{ $empresa->sunat_clave_sol }}"
                               class="w-full px-3 py-2.5 border border-slate-600 rounded-lg" placeholder="••••••••">
                    </div>
                </div>

                <h3 class="font-bold text-slate-200 mb-3">Ubicación (Ubigeo SUNAT)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5" x-data="ubigeoSearch()">
                    <div class="md:col-span-2 relative">
                        <label class="block text-sm font-semibold mb-1">Buscar ubigeo</label>
                        <input type="text" x-model="query" @input.debounce.300ms="buscar()" @focus="open=true"
                               placeholder="Escribe distrito, provincia o departamento (ej: Miraflores)"
                               class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                        <div x-show="open && resultados.length > 0" x-cloak class="absolute z-10 w-full mt-1 bg-slate-900 border border-slate-800 border border-slate-700 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                            <template x-for="r in resultados" :key="r.codigo">
                                <button type="button" @click="seleccionar(r)" class="w-full text-left px-3 py-2 hover:bg-emerald-50 border-b border-slate-800 text-sm">
                                    <span class="font-mono text-xs text-slate-400" x-text="r.codigo"></span>
                                    <span x-text="' - ' + r.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Ubigeo (6 dígitos) *</label>
                        <input type="text" name="ubigeo" x-ref="ubigeoInput" value="{{ $empresa->ubigeo ?? '150101' }}" maxlength="6"
                               class="w-full px-3 py-2.5 border border-slate-600 rounded-lg font-mono font-bold" placeholder="150101">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Departamento</label>
                        <input type="text" name="departamento" x-ref="dptoInput" value="{{ $empresa->departamento ?? 'LIMA' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Provincia</label>
                        <input type="text" name="provincia" x-ref="provInput" value="{{ $empresa->provincia ?? 'LIMA' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Distrito</label>
                        <input type="text" name="distrito" x-ref="distInput" value="{{ $empresa->distrito ?? 'LIMA' }}" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                    </div>
                </div>

                <script>
                function ubigeoSearch() {
                    return {
                        query: '',
                        resultados: [],
                        open: false,
                        async buscar() {
                            if (this.query.length < 2) { this.resultados = []; return; }
                            try {
                                const res = await fetch('/api/sunat/ubigeos?q=' + encodeURIComponent(this.query));
                                this.resultados = await res.json();
                            } catch(e) { this.resultados = []; }
                        },
                        seleccionar(r) {
                            this.$refs.ubigeoInput.value = r.codigo;
                            this.$refs.dptoInput.value = r.departamento;
                            this.$refs.provInput.value = r.provincia;
                            this.$refs.distInput.value = r.distrito;
                            this.query = r.distrito + ', ' + r.provincia;
                            this.open = false;
                        }
                    }
                }
                </script>

                <label class="flex items-center gap-3 cursor-pointer p-4 bg-emerald-50 rounded-xl mb-5">
                    <input type="checkbox" name="facturacion_electronica_activa" value="1" {{ $empresa->facturacion_electronica_activa ? 'checked' : '' }} class="w-5 h-5 rounded text-emerald-500">
                    <div>
                        <p class="font-semibold text-emerald-800">Activar facturación electrónica</p>
                        <p class="text-xs text-emerald-700">Habilita la emisión de comprobantes electrónicos en el sistema</p>
                    </div>
                </label>

                <button class="gradient-primary text-white px-6 py-3 rounded-lg font-semibold"><i class="fas fa-save mr-2"></i>Guardar configuración</button>
            </form>
        </div>

        <!-- Certificado -->
        <div x-show="tab=='certificado'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-certificate mr-2 text-emerald-500"></i>Certificado Digital</h2>
            <form method="POST" action="{{ route('facturacion.configuracion.guardar') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="sunat_modo" value="{{ $empresa->sunat_modo ?? 'beta' }}">

                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded mb-5">
                    <p class="text-sm text-amber-800"><i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Importante:</strong> El certificado digital es un archivo .pem o .pfx emitido por una entidad certificadora autorizada (Reniec, Llama.pe, etc.). Sin él NO podrás emitir comprobantes electrónicos.</p>
                </div>

                @if($empresa->sunat_certificado_path)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5">
                        <p class="font-semibold text-green-800"><i class="fas fa-check-circle mr-1"></i>Certificado cargado</p>
                        <p class="text-xs text-green-700 mt-1 font-mono">{{ basename($empresa->sunat_certificado_path) }}</p>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2">Subir certificado (.pem o .pfx)</label>
                    <input type="file" name="sunat_certificado" accept=".pem,.pfx"
                           class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold mb-1">Contraseña del certificado</label>
                    <input type="password" name="sunat_certificado_password" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg" placeholder="••••••••">
                    <p class="text-xs text-slate-400 mt-1">Solo necesario si el certificado es .pfx</p>
                </div>

                <button class="gradient-primary text-white px-6 py-3 rounded-lg font-semibold"><i class="fas fa-upload mr-2"></i>Guardar certificado</button>
            </form>
        </div>

        <!-- Series -->
        <div x-show="tab=='series'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-hashtag mr-2 text-emerald-500"></i>Series y Correlativos</h2>

            <form method="POST" action="{{ route('facturacion.series.crear') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5 pb-5 border-b border-slate-700">
                @csrf
                <select name="tipo_documento" required class="px-3 py-2 border border-slate-600 rounded-lg">
                    <option value="01">01 - Factura</option>
                    <option value="03">03 - Boleta</option>
                    <option value="07">07 - Nota Crédito</option>
                    <option value="08">08 - Nota Débito</option>
                </select>
                <input name="serie" required maxlength="4" placeholder="Ej: F001" class="px-3 py-2 border border-slate-600 rounded-lg font-mono">
                <input name="descripcion" placeholder="Descripción" class="px-3 py-2 border border-slate-600 rounded-lg">
                <button class="gradient-primary text-white px-4 py-2 rounded-lg"><i class="fas fa-plus mr-1"></i>Agregar</button>
            </form>

            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-400 border-b">
                    <tr>
                        <th class="text-left py-2">Tipo</th>
                        <th class="text-left py-2">Serie</th>
                        <th class="text-right py-2">Correlativo actual</th>
                        <th class="text-left py-2">Descripción</th>
                        <th class="text-center py-2">Estado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($series as $s)
                    <tr class="border-b">
                        <td class="py-2">{{ \App\Models\ComprobanteElectronico::TIPOS[$s->tipo_documento] ?? $s->tipo_documento }}</td>
                        <td class="py-2 font-mono font-semibold">{{ $s->serie }}</td>
                        <td class="py-2 text-right font-mono">{{ str_pad($s->correlativo_actual, 8, '0', STR_PAD_LEFT) }}</td>
                        <td class="py-2 text-sm">{{ $s->descripcion ?: '—' }}</td>
                        <td class="py-2 text-center">
                            @if($s->activo)
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Activa</span>
                            @else
                                <span class="bg-slate-900 text-slate-400 px-2 py-1 rounded-full text-xs">Inactiva</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-slate-400">No hay series configuradas. Crea una nueva arriba.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Ayuda -->
        <div x-show="tab=='ayuda'" x-cloak class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-5"><i class="fas fa-question-circle mr-2 text-emerald-500"></i>Guía rápida</h2>

            <div class="space-y-4">
                <div class="border-l-4 border-emerald-500 bg-emerald-50 p-4 rounded">
                    <h3 class="font-bold text-emerald-800 mb-2">1. Modo Beta (Pruebas)</h3>
                    <p class="text-sm text-emerald-700">El sistema usa automáticamente las credenciales de prueba <code class="bg-slate-900 border border-slate-800 px-2 py-1 rounded">MODDATOS</code> con tu RUC. Útil para validar el flujo sin afectar producción.</p>
                </div>

                <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
                    <h3 class="font-bold text-blue-800 mb-2">2. Certificado digital</h3>
                    <p class="text-sm text-blue-700">En producción necesitas un certificado real .pem/.pfx con tu RUC. Los proveedores autorizados son: Reniec, Llama.pe, Camerfirma, entre otros.</p>
                </div>

                <div class="border-l-4 border-purple-500 bg-purple-50 p-4 rounded">
                    <h3 class="font-bold text-purple-800 mb-2">3. Series por defecto</h3>
                    <ul class="text-sm text-purple-700 list-disc list-inside mt-1">
                        <li><strong>F001</strong> - Facturas</li>
                        <li><strong>B001</strong> - Boletas de venta</li>
                        <li><strong>FC01</strong> - Notas de crédito de facturas</li>
                        <li><strong>BC01</strong> - Notas de crédito de boletas</li>
                    </ul>
                </div>

                <div class="border-l-4 border-orange-500 bg-orange-50 p-4 rounded">
                    <h3 class="font-bold text-orange-800 mb-2">4. Flujo de emisión</h3>
                    <ol class="text-sm text-orange-700 list-decimal list-inside space-y-1">
                        <li>Realizar venta en el POS</li>
                        <li>En la vista de la venta, click en "Emitir Boleta/Factura"</li>
                        <li>Click en "Enviar a SUNAT" para validarlo</li>
                        <li>SUNAT responde con CDR (aceptado/rechazado)</li>
                        <li>Descargar XML y CDR para tu contabilidad</li>
                    </ol>
                </div>

                <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                    <h3 class="font-bold text-red-800 mb-2">5. Anulaciones</h3>
                    <p class="text-sm text-red-700"><strong>Nota de Crédito:</strong> Para anular Facturas/Boletas dentro de los 7 días siguientes. <strong>Comunicación de Baja:</strong> Solo para Facturas del mismo día.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
