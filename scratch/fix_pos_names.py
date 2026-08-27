with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Fix product name text color
s = s.replace('text-slate-800 line-clamp-2', 'text-slate-100 line-clamp-2')
s = s.replace('border-slate-50', 'border-slate-700')

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
