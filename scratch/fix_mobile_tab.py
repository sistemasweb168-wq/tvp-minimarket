with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

old_func = """        cerrarPostVenta() {
            this.modalPostVenta = false;
            this.ultimaVenta = null;
        }"""
new_func = """        cerrarPostVenta() {
            this.modalPostVenta = false;
            this.ultimaVenta = null;
            this.vistaMovil = 'productos';
        }"""
s = s.replace(old_func, new_func)

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
