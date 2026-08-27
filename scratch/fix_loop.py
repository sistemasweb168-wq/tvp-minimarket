with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Fix the infinite loop and call to actualizarTotal
fixed_recalc = """        recalcularPrecios() {
            this.carrito.forEach(item => {
                if (item.cantidad_mayoreo > 0 && item.cantidad >= item.cantidad_mayoreo && item.precio_mayoreo > 0) {
                    item.precio_unitario = item.precio_mayoreo;
                } else {
                    item.precio_unitario = item.precio_normal;
                }
            });
            this.actualizarTotal();
        },"""

import re
s = re.sub(r'recalcularPrecios\(\) \{\s*this\.carrito\.forEach.*?\}\);\s*this\.recalcularPrecios\(\);\s*\},', fixed_recalc, s, flags=re.DOTALL)

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
