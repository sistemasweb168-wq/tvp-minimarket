import re

with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Add logic for wholesale in AlpineJS functions
s = s.replace('@input="actualizarTotal()"', '@input="recalcularPrecios()"')

recalc_func = '''        recalcularPrecios() {
            this.carrito.forEach(item => {
                if (item.cantidad_mayoreo > 0 && item.cantidad >= item.cantidad_mayoreo && item.precio_mayoreo > 0) {
                    item.precio_unitario = item.precio_mayoreo;
                } else {
                    item.precio_unitario = item.precio_normal;
                }
            });
            this.actualizarTotal();
        },
'''

# Insert recalcularPrecios right before actualizarTotal
s = s.replace('        actualizarTotal() {', recalc_func + '        actualizarTotal() {')

# In agregarProducto, instead of this.actualizarTotal(), call this.recalcularPrecios()
s = s.replace('this.actualizarTotal();', 'this.recalcularPrecios();')

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
