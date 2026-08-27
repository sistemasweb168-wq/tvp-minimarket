import re

files = ['resources/views/productos/create.blade.php', 'resources/views/productos/edit.blade.php']

combo_html = """
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6" x-show="tipo_producto === 'combo'" x-cloak>
            <h3 class="font-bold text-amber-500 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-boxes mr-2"></i>Componentes del Combo</h3>
            <p class="text-xs text-slate-400 mb-4">Añade los productos que componen este pack.</p>
            <div class="space-y-3">
                <template x-for="(comp, index) in componentes" :key="index">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Producto</label>
                            <select :name="`componente_id[]`" x-model="comp.id" class="w-full px-3 py-2 border border-slate-600 rounded-lg">
                                <option value="">Seleccione producto...</option>
                                @foreach($productosList as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }} (Stock: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Cant.</label>
                            <input type="number" step="0.01" :name="`componente_cantidad[]`" x-model="comp.cantidad" class="w-full px-3 py-2 border border-slate-600 rounded-lg text-center">
                        </div>
                        <button type="button" @click="removeComponent(index)" class="px-3 py-2 bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition"><i class="fas fa-trash"></i></button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addComponent()" class="mt-4 px-4 py-2 border border-amber-500/50 text-amber-500 rounded-xl hover:bg-amber-500 hover:text-slate-900 transition text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
        </div>
"""

tipo_field = """                  <div>
                      <label class="block text-sm font-semibold text-slate-200 mb-1">Tipo de Producto <span class="text-red-500">*</span></label>
                      <select name="tipo_producto" x-model="tipo_producto" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg">
                          <option value="estandar">Estándar</option>
                          <option value="combo">Combo / Pack</option>
                      </select>
                  </div>
"""

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        s = f.read()

    # Prevent double insertion
    if 'x-model="tipo_producto"' not in s:
        s = s.replace('name="categoria_id"', 'name="categoria_id"') # no-op just to find it safely
        
        # Insert before the categoria_id block
        # The block starts with <div> then <label... then <select name="categoria_id"
        parts = re.split(r'(<div>\s*<label[^>]*>[^<]*</label>\s*<select name="categoria_id")', s)
        if len(parts) == 3:
            s = parts[0] + tipo_field + parts[1] + parts[2]
            
    if 'Componentes del Combo' not in s:
        # Insert before Información de Precios card
        # Find the card with fa-tags
        parts = re.split(r'(<div class="bg-slate-900[^>]*>\s*<h3[^>]*><i class="fas fa-tags)', s)
        if len(parts) == 3:
            s = parts[0] + combo_html + "\n" + parts[1] + parts[2]
            
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(s)
