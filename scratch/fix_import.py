with open('app/Imports/ProductosImport.php', 'r', encoding='utf-8') as f:
    s = f.read()

s = s.replace("'estado' => 1", "'activo' => 1")

with open('app/Imports/ProductosImport.php', 'w', encoding='utf-8') as f:
    f.write(s)
