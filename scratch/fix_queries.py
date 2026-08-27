with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

s = s.replace("where('estado', 1)", "where('activo', 1)")

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)

with open('app/Console/Commands/EnviarAlertasWhatsApp.php', 'r', encoding='utf-8') as f:
    s2 = f.read()

s2 = s2.replace("where('estado', 1)", "where('activo', 1)")

with open('app/Console/Commands/EnviarAlertasWhatsApp.php', 'w', encoding='utf-8') as f:
    f.write(s2)
