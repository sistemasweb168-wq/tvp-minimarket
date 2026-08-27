with open('resources/views/productos/index.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

modal_html = '''
<!-- Modal Importar Excel -->
<div id="modal-importar" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/75 backdrop-blur-sm hidden">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-800">Importar Productos</h3>
            <button onclick="document.getElementById('modal-importar').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
        </div>
        <p class="text-sm text-slate-600 mb-4">Descarga la plantilla, llénala con tus licores y sube el archivo para actualizar tu catálogo masivamente.</p>
        <div class="mb-6 flex justify-center bg-blue-50 p-3 rounded-xl border border-blue-100">
            <a href="{{ route('productos.plantilla') }}" class="text-blue-700 hover:text-blue-800 font-bold text-sm flex items-center gap-2">
                <i class="fas fa-download"></i> Descargar Plantilla.xlsx
            </a>
        </div>
        <form action="{{ route('productos.importar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-5">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Seleccionar Archivo Excel</label>
                <input type="file" name="archivo_excel" accept=".xlsx,.xls,.csv" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer" required>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="document.getElementById('modal-importar').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-md">Subir Inventario</button>
            </div>
        </form>
    </div>
</div>
'''

s = s.replace('@endsection', modal_html + '\n@endsection')

with open('resources/views/productos/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
