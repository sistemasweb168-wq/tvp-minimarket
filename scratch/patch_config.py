with open('resources/views/configuracion/index.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

whatsapp_html = """                      <div><label class="block text-sm font-semibold mb-1 text-amber-500"><i class="fab fa-whatsapp mr-1"></i>WhatsApp Alertas</label><input type="text" name="whatsapp_alertas" value="{{ $empresa->whatsapp_alertas }}" placeholder="Ej: 999888777" class="w-full px-3 py-2.5 border border-slate-600 rounded-lg"></div>
"""
s = s.replace('<div><label class="block text-sm font-semibold mb-1">Tel', whatsapp_html + '                      <div><label class="block text-sm font-semibold mb-1">Tel')

with open('resources/views/configuracion/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
