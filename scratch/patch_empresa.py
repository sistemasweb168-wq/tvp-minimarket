with open('app/Models/Empresa.php', 'r', encoding='utf-8') as f:
    s = f.read()

s = s.replace("'telefono',", "'telefono', 'whatsapp_alertas',")

with open('app/Models/Empresa.php', 'w', encoding='utf-8') as f:
    f.write(s)
