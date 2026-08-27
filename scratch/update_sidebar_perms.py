import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Helper function to wrap a link with a permission check
def wrap_link(route_name, permission_name, content):
    pattern = r'(<a href="\{\{\s*route\(\'' + route_name + r'[^>]+>.*?</a>)'
    replacement = r'@if(auth()->user()->hasPermission(\'' + permission_name + r'\') || auth()->user()->isAdmin())\n\1\n@endif'
    # We must be careful not to wrap multiple times if already wrapped, but we know it's not wrapped yet
    return re.sub(pattern, replacement, content, flags=re.DOTALL)

s = wrap_link('ventas.pos', 'pos', s)
s = wrap_link('ventas.index', 'ventas', s)
s = wrap_link('compras.index', 'compras', s)
s = wrap_link('caja.index', 'caja', s)
s = wrap_link('productos.index', 'productos', s)
s = wrap_link('categorias.index', 'productos', s)
s = wrap_link('promociones.index', 'productos', s)
s = wrap_link('clientes.index', 'clientes', s)
s = wrap_link('proveedores.index', 'proveedores', s)
s = wrap_link('facturacion.index', 'sunat', s)
s = wrap_link('facturacion.resumenes', 'sunat', s)
s = wrap_link('reportes.index', 'reportes', s)

# The section headers like "Operaciones", "Inventario" should only show if at least one permission is true
s = s.replace(
    '<p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Operaciones</span></p>',
    '''@if(auth()->user()->hasPermission('ventas') || auth()->user()->hasPermission('compras') || auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Operaciones</span></p>
@endif'''
)
s = s.replace(
    '<p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Inventario</span></p>',
    '''@if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Inventario</span></p>
@endif'''
)
s = s.replace(
    '<p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Contactos</span></p>',
    '''@if(auth()->user()->hasPermission('clientes') || auth()->user()->hasPermission('proveedores') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Contactos</span></p>
@endif'''
)
s = s.replace(
    '<p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">SUNAT</span></p>',
    '''@if(auth()->user()->hasPermission('sunat') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">SUNAT</span></p>
@endif'''
)
s = s.replace(
    '<p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Análisis</span></p>',
    '''@if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Análisis</span></p>
@endif'''
)

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
