with open('resources/views/productos/create.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

s = s.replace('<form method="POST"', '<form x-data="comboForm()" method="POST"')

combo_html = """
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6" x-show="tipo_producto === 'combo'" x-cloak>
            <h3 class="font-bold text-amber-500 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-boxes mr-2"></i>Componentes del Combo</h3>
            <p class="text-xs text-slate-400 mb-4">Añade los productos que componen este combo. El sistema descontará el stock de estos productos al vender el combo.</p>
            
            <div class="space-y-3">
                <template x-for="(comp, index) in componentes" :key="index">
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Producto</label>
                            <select :name="`componente_id[]`" x-model="comp.id" required class="w-full px-3 py-2 border border-slate-600 rounded-lg">
                                <option value="">Seleccione producto...</option>
                                @foreach($productosList as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }} (Stock: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Cant.</label>
                            <input type="number" step="0.01" :name="`componente_cantidad[]`" x-model="comp.cantidad" required class="w-full px-3 py-2 border border-slate-600 rounded-lg text-center">
                        </div>
                        <button type="button" @click="removeComponent(index)" class="px-3 py-2 bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </template>
            </div>
            
            <button type="button" @click="addComponent()" class="mt-4 px-4 py-2 border border-amber-500/50 text-amber-500 rounded-xl hover:bg-amber-500 hover:text-slate-900 transition text-sm font-semibold flex items-center gap-2">
                <i class="fas fa-plus"></i> Agregar Producto al Combo
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

# Insert the tipo_field inside Información General, right before Categoria
s = s.replace('                  <div>\n                      <label class="block text-sm font-semibold text-slate-200 mb-1">Categor', tipo_field + '                  <div>\n                      <label class="block text-sm font-semibold text-slate-200 mb-1">Categor')

# Insert combo_html after the first card (Información General)
s = s.replace('          <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">\n              <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-tags', combo_html + '\n          <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-md p-6">\n              <h3 class="font-bold text-slate-100 mb-4 pb-3 border-b border-slate-800"><i class="fas fa-tags')

# Append the Alpine component logic at the end
script = """
@section('scripts')
<script>
    function comboForm() {
        return {
            tipo_producto: 'estandar',
            componentes: [],
            addComponent() {
                this.componentes.push({ id: '', cantidad: 1 });
            },
            removeComponent(index) {
                this.componentes.splice(index, 1);
            }
        }
    }
</script>
@endsection
"""

s = s + script

with open('resources/views/productos/create.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
