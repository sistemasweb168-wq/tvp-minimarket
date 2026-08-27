with open('app/Models/Producto.php', 'r', encoding='utf-8') as f:
    s = f.read()

if 'tipo_producto' not in s:
    s = s.replace("'codigo',", "'tipo_producto', 'codigo',")

rel = """
    public function componentesCombo()
    {
        return $this->belongsToMany(Producto::class, 'combo_productos', 'combo_id', 'producto_id')->withPivot('cantidad');
    }
"""

if 'componentesCombo' not in s:
    s = s[:s.rfind('}')] + rel + "}\n"

with open('app/Models/Producto.php', 'w', encoding='utf-8') as f:
    f.write(s)
