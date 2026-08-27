import re

with open('resources/views/usuarios/roles.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

old_array = """$permisosDisponibles = [
    'productos' => 'Productos', 'ventas' => 'Ventas', 'compras' => 'Compras',
    'clientes' => 'Clientes', 'proveedores' => 'Proveedores', 'caja' => 'Caja',
    'reportes' => 'Reportes', 'configuracion' => 'Configuración',
    'usuarios' => 'Usuarios', 'backup' => 'Backup',
];"""

new_array = """$permisosDisponibles = [
    'pos' => 'Acceso al POS (Caja Rápida)',
    'ventas' => 'Ver Historial de Ventas',
    'ventas.anular' => 'Anular Ventas',
    'productos' => 'Ver y Editar Productos',
    'compras' => 'Gestión de Compras',
    'clientes' => 'Gestión de Clientes',
    'proveedores' => 'Gestión de Proveedores',
    'caja' => 'Apertura y Cierre de Caja',
    'caja.movimientos' => 'Movimientos de Dinero',
    'reportes' => 'Acceso a Reportes',
    'sunat' => 'Facturación SUNAT',
    'configuracion' => 'Configuración',
    'usuarios' => 'Usuarios',
    'backup' => 'Backup',
];"""

s = s.replace(old_array, new_array)

with open('resources/views/usuarios/roles.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
