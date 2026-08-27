import re

with open('resources/views/ventas/index.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Wrap form for anular inside @if
s = re.sub(
    r'(<form action="\{\{\s*route\(\'ventas\.anular\',\s*\$venta\)\s*\}\}".*?</form>)',
    r'@if(auth()->user()->hasPermission(\'ventas.anular\') || auth()->user()->isAdmin())\n\1\n@endif',
    s,
    flags=re.DOTALL
)

with open('resources/views/ventas/index.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
