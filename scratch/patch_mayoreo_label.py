with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

old_html = '<p class="font-bold text-xs sm:text-sm text-slate-800 flex-1 pr-2 leading-tight" x-text="item.nombre"></p>'
new_html = """<div class="flex-1 pr-2">
                                <p class="font-bold text-xs sm:text-sm text-slate-800 leading-tight" x-text="item.nombre"></p>
                                <span x-show="item.cantidad_mayoreo > 0 && item.cantidad >= item.cantidad_mayoreo && item.precio_mayoreo > 0" class="inline-block mt-0.5 bg-amber-100 text-amber-700 border border-amber-200 text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider shadow-sm" x-cloak>
                                    <i class="fas fa-star text-[8px]"></i> Promo Mayoreo
                                </span>
                            </div>"""

s = s.replace(old_html, new_html)

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
