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
<div class="bg-white rounded-2xl shadow-md p-6">
    <h3 class="font-bold mb-4"><i class="fas fa-history mr-2 text-emerald-500"></i>Historial de Backups</h3>
    <table class="w-full">
        <thead class="text-xs uppercase text-slate-500 border-b">
            <tr>
                <th class="text-left py-2">Nombre</th>
                <th class="text-left py-2">Archivo</th>
                <th class="text-left py-2">Tamaño</th>
                <th class="text-left py-2">Tipo</th>
                <th class="text-left py-2">Usuario</th>
                <th class="text-left py-2">Fecha</th>
                <th class="text-right py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @forelse($backups as $b)
            <tr class="border-b hover:bg-slate-50">
                <td class="py-3 font-semibold">{{ $b->nombre }}</td>
                <td class="py-3 text-xs font-mono text-slate-500">{{ $b->archivo }}</td>
                <td class="py-3 text-sm">{{ $b->tamano_formateado }}</td>
                <td class="py-3"><span class="bg-{{ $b->tipo == 'manual' ? 'blue' : 'gray' }}-100 text-{{ $b->tipo == 'manual' ? 'blue' : 'gray' }}-700 px-2 py-1 rounded-full text-xs">{{ ucfirst($b->tipo) }}</span></td>
                <td class="py-3 text-sm">{{ $b->user?->name ?? '—' }}</td>
                <td class="py-3 text-xs">{{ $b->created_at->format('d/m/Y H:i') }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('backup.descargar', $b->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded" title="Descargar"><i class="fas fa-download"></i></a>
                    <form method="POST" action="{{ route('backup.restaurar') }}" class="inline" onsubmit="return confirm('⚠️ Restaurar reemplazará todos los datos actuales. ¿Continuar?')">
                        @csrf
                        <input type="hidden" name="backup_id" value="{{ $b->id }}">
                        <button class="p-2 text-emerald-600 hover:bg-emerald-50 rounded" title="Restaurar"><i class="fas fa-undo"></i></button>
                    </form>
                    <form method="POST" action="{{ route('backup.eliminar', $b->id) }}" class="inline" onsubmit="return confirm('¿Eliminar este backup?')">
                        @csrf @method('DELETE')
                        <button class="p-2 text-red-600 hover:bg-red-50 rounded" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-8 text-slate-400">Sin copias de seguridad</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $backups->links() }}</div>
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
