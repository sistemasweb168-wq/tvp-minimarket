with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Add this.recalcularPrecios(); to cambiarCantidad
s = s.replace("AudioPOS.beep(800, 'sine', 0.04, 1.0);\n            }", "AudioPOS.beep(800, 'sine', 0.04, 1.0);\n            }\n            this.recalcularPrecios();")

# Add this.recalcularPrecios(); to quitarItem (though actually vaciarCarrito/quitarItem might just need actualizarTotal, but recalcularPrecios calls actualizarTotal, so it's safe)
s = s.replace("this.carrito.splice(idx, 1);\n            AudioPOS.beep(600, 'triangle', 0.05);", "this.carrito.splice(idx, 1);\n            AudioPOS.beep(600, 'triangle', 0.05);\n            this.recalcularPrecios();")

# Add to vaciarCarrito
s = s.replace("this.carrito = [];", "this.carrito = [];\n            this.recalcularPrecios();")

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
