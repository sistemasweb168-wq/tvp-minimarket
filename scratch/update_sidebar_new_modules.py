with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Add Envases under Operaciones (after Caja)
caja_link = """@if(auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
<a href="{{ route('caja.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Caja</span>
            </a>
@endif"""

envases_link = """@if(auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
<a href="{{ route('caja.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Caja</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
<a href="{{ route('envases.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('envases.*') ? 'active' : '' }}">
                <i class="fas fa-box-open w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Envases & Cascos</span>
            </a>
@endif"""

s = s.replace(caja_link, envases_link)

# Add Kardex under Inventario (after Promociones)
promos_link = """@if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('promociones.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('promociones.*') ? 'active' : '' }}">
                <i class="fas fa-percent w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Promociones</span>
            </a>
@endif"""

kardex_link = """@if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('promociones.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('promociones.*') ? 'active' : '' }}">
                <i class="fas fa-percent w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Promociones</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('kardex.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('kardex.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Kardex & Mermas</span>
            </a>
@endif"""

s = s.replace(promos_link, kardex_link)

# Add Utilidades under Analisis (after Reportes)
reportes_link = """@if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
<a href="{{ route('reportes.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Reportes</span>
            </a>
@endif"""

utilidades_link = """@if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
<a href="{{ route('reportes.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('reportes.index') ? 'active' : '' }}">
                <i class="fas fa-chart-line w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Reportes</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
<a href="{{ route('reportes.utilidades') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('reportes.utilidades') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-dollar w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Utilidad Neta Real</span>
            </a>
@endif"""

s = s.replace(reportes_link, utilidades_link)

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
