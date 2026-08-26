@extends('layouts.app')
@section('title', 'Copias de Seguridad')
@section('header', 'Copias de Seguridad y Restauración')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <!-- Crear Backup -->
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-md p-6 text-white">
        <i class="fas fa-cloud-download-alt text-4xl mb-3 opacity-80"></i>
        <h3 class="font-bold text-lg mb-2">Crear Copia de Seguridad</h3>
        <p class="text-sm opacity-90 mb-4">Genera una copia completa de la base de datos del sistema.</p>
        <form method="POST" action="{{ route('backup.crear') }}">
            @csrf
            <input type="text" name="nombre" placeholder="Nombre (opcional)" class="w-full px-3 py-2 mb-2 rounded-lg text-slate-700">
            <textarea name="observaciones" placeholder="Observaciones..." rows="2" class="w-full px-3 py-2 mb-3 rounded-lg text-slate-700"></textarea>
            <button class="w-full bg-white text-emerald-700 py-2.5 rounded-lg font-semibold hover:bg-emerald-50">
                <i class="fas fa-download mr-2"></i>Crear Backup Ahora
            </button>
        </form>
    </div>

    <!-- Restaurar archivo -->
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-md p-6 text-white">
        <i class="fas fa-cloud-upload-alt text-4xl mb-3 opacity-80"></i>
        <h3 class="font-bold text-lg mb-2">Restaurar desde Archivo</h3>
        <p class="text-sm opacity-90 mb-4">Sube un archivo SQL para restaurar el sistema.</p>
        <form method="POST" action="{{ route('backup.restaurar-archivo') }}" enctype="multipart/form-data" onsubmit="return confirm('⚠️ ATENCIÓN: Esta acción reemplazará TODOS los datos actuales. ¿Continuar?')">
            @csrf
            <input type="file" name="archivo" accept=".sql" required class="block w-full text-sm mb-3 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-white file:text-blue-700">
            <button class="w-full bg-white text-blue-700 py-2.5 rounded-lg font-semibold hover:bg-blue-50">
                <i class="fas fa-upload mr-2"></i>Restaurar Sistema
            </button>
        </form>
    </div>

    <!-- Resetear -->
    <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl shadow-md p-6 text-white">
        <i class="fas fa-exclamation-triangle text-4xl mb-3 opacity-80"></i>
        <h3 class="font-bold text-lg mb-2">Resetear Sistema</h3>
        <p class="text-sm opacity-90 mb-4">Borra todos los datos transaccionales para una empresa nueva. Conserva: usuarios, configuración y empresa.</p>
        <button onclick="document.getElementById('modal-reset').classList.remove('hidden')" class="w-full bg-white text-red-700 py-2.5 rounded-lg font-semibold hover:bg-red-50">
            <i class="fas fa-trash-alt mr-2"></i>Resetear Sistema
        </button>
    </div>
</div>

<!-- Lista de backups -->
<!-- Lista de backups (Tarjetas en Móvil / Tabla en Desktop) -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden border border-slate-100">
    <div class="p-4 sm:p-5 border-b border-slate-100">
        <h3 class="font-extrabold text-sm sm:text-base text-slate-800 flex items-center gap-2">
            <i class="fas fa-history text-emerald-500"></i><span>Historial de Copias de Seguridad</span>
        </h3>
    </div>

    <!-- 📱 VISTA MÓVIL (TARJETAS < md) -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($backups as $b)
            <div class="p-3.5 hover:bg-slate-50 transition">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-800 text-xs sm:text-sm">{{ $b->nombre }}</span>
                    <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-lg text-[10px] font-bold">{{ $b->tamano_formateado }}</span>
                </div>
                <p class="text-[11px] font-mono text-slate-400 truncate mb-1.5">{{ $b->archivo }}</p>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mb-2">
                    <span><i class="far fa-clock mr-1"></i>{{ $b->created_at->format('d/m/Y H:i') }}</span>
                    <span class="text-slate-600">{{ $b->user?->name ?? 'Sistema' }}</span>
                </div>

                <!-- Botones de Acción Móvil -->
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('backup.descargar', $b->id) }}" class="flex-1 py-1.5 bg-blue-50 text-blue-700 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1">
                        <i class="fas fa-download text-xs"></i><span>Descargar</span>
                    </a>
                    <form method="POST" action="{{ route('backup.restaurar') }}" class="flex-1" onsubmit="return confirm('⚠️ Restaurar reemplazará todos los datos actuales. ¿Continuar?')">
                        @csrf
                        <input type="hidden" name="backup_id" value="{{ $b->id }}">
                        <button class="w-full py-1.5 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1">
                            <i class="fas fa-undo text-xs"></i><span>Restaurar</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('backup.eliminar', $b->id) }}" class="flex-none" onsubmit="return confirm('¿Eliminar este backup?')">
                        @csrf @method('DELETE')
                        <button class="p-2 bg-red-50 text-red-600 rounded-xl text-xs font-bold" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-xs">Sin copias de seguridad generadas</div>
        @endforelse
    </div>

    <!-- 💻 VISTA ESCRITORIO (TABLA >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                <tr>
                    <th class="py-3 px-4">Nombre</th>
                    <th class="py-3 px-4">Archivo</th>
                    <th class="py-3 px-4">Tamaño</th>
                    <th class="py-3 px-4">Tipo</th>
                    <th class="py-3 px-4">Usuario</th>
                    <th class="py-3 px-4">Fecha</th>
                    <th class="py-3 px-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($backups as $b)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-3 px-4 font-bold text-slate-800">{{ $b->nombre }}</td>
                    <td class="py-3 px-4 text-xs font-mono text-slate-500">{{ $b->archivo }}</td>
                    <td class="py-3 px-4 text-sm font-semibold">{{ $b->tamano_formateado }}</td>
                    <td class="py-3 px-4"><span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-bold">{{ ucfirst($b->tipo) }}</span></td>
                    <td class="py-3 px-4 text-sm text-slate-600">{{ $b->user?->name ?? '—' }}</td>
                    <td class="py-3 px-4 text-xs text-slate-400">{{ $b->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        <div class="flex justify-end gap-1.5">
                            <a href="{{ route('backup.descargar', $b->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Descargar"><i class="fas fa-download"></i></a>
                            <form method="POST" action="{{ route('backup.restaurar') }}" class="inline" onsubmit="return confirm('⚠️ Restaurar reemplazará todos los datos actuales. ¿Continuar?')">
                                @csrf
                                <input type="hidden" name="backup_id" value="{{ $b->id }}">
                                <button class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg" title="Restaurar"><i class="fas fa-undo"></i></button>
                            </form>
                            <form method="POST" action="{{ route('backup.eliminar', $b->id) }}" class="inline" onsubmit="return confirm('¿Eliminar este backup?')">
                                @csrf @method('DELETE')
                                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-slate-400 text-sm">Sin copias de seguridad generadas</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3 sm:p-4 border-t border-slate-100">{{ $backups->links() }}</div>
</div>

<!-- Modal Reset -->
<div id="modal-reset" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <div class="text-center mb-4">
            <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-3"></i>
            <h3 class="text-xl font-bold text-red-700">⚠️ ACCIÓN PELIGROSA</h3>
            <p class="text-sm text-slate-600 mt-2">Esta acción eliminará todos los datos transaccionales (ventas, compras, productos, clientes, etc.). Se conservarán los usuarios y la configuración. Se generará un backup automático antes de proceder.</p>
        </div>
        <form method="POST" action="{{ route('backup.resetear') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">Para confirmar, escribe <code class="bg-red-100 text-red-700 px-2 py-1 rounded">RESETEAR</code></label>
                <input type="text" name="confirmacion" required class="w-full px-3 py-2.5 border border-red-300 rounded-lg text-center font-bold" placeholder="RESETEAR">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tu contraseña</label>
                <input type="password" name="password" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('modal-reset').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-200 rounded-lg">Cancelar</button>
                <button type="submit" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">Resetear Sistema</button>
            </div>
        </form>
    </div>
</div>
@endsection
