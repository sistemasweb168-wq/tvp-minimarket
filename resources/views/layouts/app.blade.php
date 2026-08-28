<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TPV Minimarket') | {{ $empresaGlobal->nombre_comercial ?? 'TPV Minimarket' }}</title>

    <meta name="theme-color" content="#f59e0b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Bodega Valezka">
    <link rel="manifest" href="/manifest.json">

    @if($empresaGlobal && $empresaGlobal->logo_url)
        <link rel="icon" href="{{ $empresaGlobal->logo_url }}">
        <link rel="apple-touch-icon" href="{{ $empresaGlobal->logo_url }}">
    @endif

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        * { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        body { overflow-x: hidden; }
        ::-webkit-scrollbar { width: 0px; height: 0px; display: none; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        .sidebar-link.active { background: linear-gradient(90deg, rgba(245,158,11,.15), transparent); border-left: 3px solid #f59e0b; color: #f59e0b; }
        .gradient-primary { background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%); }
        .gradient-card-1 { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
        .gradient-card-2 { background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); }
        .gradient-card-3 { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        .gradient-card-4 { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
        .gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); }

        /* Animaciones para gráficos */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chart-card { animation: fadeInUp 0.5s ease-out; }

        /* Tablas responsivas: ocultar columnas menos críticas en pantallas pequeñas */
        @media (max-width: 640px) {
            .hide-mobile { display: none !important; }
            table { font-size: 12px; }
            .text-3xl { font-size: 1.5rem; }
        }

        /* Mejor scroll horizontal en móvil */
        .overflow-x-auto { -webkit-overflow-scrolling: touch; }

        /* Cerrar sidebar en móvil al hacer click en enlace */
        @media (max-width: 1023px) {
            .sidebar-mobile-open { transform: translateX(0); }
        }

        [x-cloak] { display: none !important; }

        /* Estilos modernos para SweetAlert2 */
        .swal2-popup {
            border-radius: 1.25rem !important;
            padding: 1.75rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }
        .swal2-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
        }
        .swal2-html-container {
            font-size: 0.95rem !important;
            color: #475569 !important;
        }
        .swal2-confirm, .swal2-cancel {
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.65rem 1.5rem !important;
            font-size: 0.875rem !important;
        }
    
        /* Estilos Globales para Inputs en Modo Oscuro */
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]), select, textarea {
            background-color: #1e293b !important; /* bg-slate-800 */
            border: 1px solid #334155 !important; /* border-slate-700 */
            color: #f8fafc !important; /* text-slate-50 */
            border-radius: 0.75rem !important;
            font-size: 0.9rem !important;
        }
        input:read-only, input:disabled {
            background-color: #0f172a !important; /* bg-slate-900 */
            color: #94a3b8 !important;
        }
        input::placeholder, textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }

        /* Ocultar scrollbar en el sidebar pero mantener funcionalidad de scroll */
        .sidebar-scroll::-webkit-scrollbar { display: none; }
        .sidebar-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
    @yield('head')
</head>
<body class="bg-slate-950 text-slate-200">
<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

    <!-- Sidebar -->
    <!-- Sidebar -->
    <aside x-data="{ isHovered: false }" 
           @mouseenter="isHovered = true" 
           @mouseleave="isHovered = false" 
           class="bg-black border-r border-slate-900 text-white fixed inset-y-0 left-0 z-40 transition-all duration-300 overflow-x-hidden w-64 lg:w-16"
           :class="[
               sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               isHovered ? 'lg:w-64 shadow-2xl' : ''
           ]">
        <div class="px-3 py-5 border-b border-slate-800 flex items-center">
            @if($empresaGlobal && $empresaGlobal->logo_url)
                <img src="{{ $empresaGlobal->logo_url }}" class="w-10 h-10 rounded-lg object-contain bg-white p-1" alt="logo">
            @else
                <div class="w-10 h-10 rounded-lg gradient-primary flex items-center justify-center">
                    <i class="fas fa-store text-white"></i>
                </div>
            @endif
            <div class="flex-1 min-w-0 ml-3" x-show="isHovered || sidebarOpen" style="display: none;">
                <h1 class="font-bold text-sm whitespace-nowrap">{{ $empresaGlobal->nombre_comercial ?? 'TPV Minimarket' }}</h1>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Sistema POS</p>
            </div>
        </div>

        <nav class="py-4 overflow-y-auto sidebar-scroll" style="max-height: calc(100vh - 80px);">
            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Dashboard</span>
            </a>
            @if(auth()->user()->hasPermission('pos') || auth()->user()->isAdmin())
<a href="{{ route('ventas.pos') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('ventas.pos') ? 'active' : '' }}">
                <i class="fas fa-cash-register w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Punto de Venta</span>
            </a>
@endif

            @if(auth()->user()->hasPermission('ventas') || auth()->user()->hasPermission('compras') || auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Operaciones</span></p>
@endif
            @if(auth()->user()->hasPermission('ventas') || auth()->user()->isAdmin())
<a href="{{ route('ventas.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('ventas.index') ? 'active' : '' }}">
                <i class="fas fa-receipt w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Ventas</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('compras') || auth()->user()->isAdmin())
<a href="{{ route('compras.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('compras.*') ? 'active' : '' }}">
                <i class="fas fa-truck w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Compras</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
<a href="{{ route('caja.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Caja</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('caja') || auth()->user()->isAdmin())
<a href="{{ route('envases.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('envases.*') ? 'active' : '' }}">
                <i class="fas fa-box-open w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Envases & Cascos</span>
            </a>
@endif

            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Inventario</span></p>
@endif
            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('productos.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                <i class="fas fa-box w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Productos</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('categorias.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                <i class="fas fa-tags w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Categorías</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('promociones.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('promociones.*') ? 'active' : '' }}">
                <i class="fas fa-percent w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Promociones</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('kardex.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('kardex.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Kardex & Mermas</span>
            </a>
@endif

            @if(auth()->user()->hasPermission('clientes') || auth()->user()->hasPermission('proveedores') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Contactos</span></p>
@endif
            @if(auth()->user()->hasPermission('clientes') || auth()->user()->isAdmin())
<a href="{{ route('clientes.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="fas fa-users w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Clientes</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('proveedores') || auth()->user()->isAdmin())
<a href="{{ route('proveedores.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
                <i class="fas fa-truck-loading w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Proveedores</span>
            </a>
@endif

            @if(auth()->user()->hasPermission('sunat') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">SUNAT</span></p>
@endif
            @if(auth()->user()->hasPermission('sunat') || auth()->user()->isAdmin())
<a href="{{ route('facturacion.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('facturacion.index') || request()->routeIs('facturacion.show') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Facturación Electrónica</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('sunat') || auth()->user()->isAdmin())
<a href="{{ route('facturacion.resumenes') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('facturacion.resumenes') ? 'active' : '' }}">
                <i class="fas fa-calendar-day w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Resúmenes Diarios</span>
            </a>
@endif

            @if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Análisis</span></p>
@endif
            @if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
<a href="{{ route('reportes.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('reportes.index') ? 'active' : '' }}">
                <i class="fas fa-chart-line w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Reportes</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('reportes') || auth()->user()->isAdmin())
<a href="{{ route('reportes.utilidades') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('reportes.utilidades') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-dollar w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Utilidad Neta Real</span>
            </a>
@endif

            @if(auth()->user()->isAdmin())
                <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Sistema</span></p>
                <a href="{{ route('usuarios.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Usuarios</span>
                </a>
                <a href="{{ route('configuracion.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Configuración</span>
                </a>
                <a href="{{ route('facturacion.configuracion') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('facturacion.configuracion') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Config. SUNAT</span>
                </a>
                <a href="{{ route('backup.index') }}" class="sidebar-link flex items-center px-5 py-3 text-slate-300 hover:bg-slate-800 transition {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                    <i class="fas fa-database w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Backup</span>
                </a>
            @endif

            <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold"><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Ayuda</span></p>
            <a href="{{ route('manual.index') }}" class="sidebar-link flex items-center px-5 py-3 text-amber-400 hover:bg-slate-800 transition {{ request()->routeIs('manual.*') ? 'active' : '' }}">
                <i class="fas fa-book-open w-5 text-amber-400"></i><span class="whitespace-nowrap ml-3 font-bold" x-show="isHovered || sidebarOpen" style="display: none;">Manual de Usuario</span>
            </a>

            <div class="px-5 mt-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full flex items-center px-5 py-3 bg-red-600/20 hover:bg-red-600/40 rounded-lg text-red-300 transition">
                        <i class="fas fa-sign-out-alt w-5"></i><span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    <!-- Overlay móvil -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 lg:hidden" style="display:none;"></div>

    <!-- Main -->
    <div class="flex-1 lg:ml-16 min-w-0 transition-all duration-300">
        <!-- Topbar -->
        
@php
    $alertasStock = \App\Models\Producto::where('controla_stock', 1)->where('activo', 1)->whereRaw('stock <= stock_minimo')->count();
    $alertasVencimiento = \App\Models\Producto::whereNotNull('fecha_vencimiento')->where('activo', 1)->whereDate('fecha_vencimiento', '<=', now()->addDays(30))->count();
    $totalAlertas = $alertasStock + $alertasVencimiento;
@endphp
<header class="bg-slate-900 shadow-md border-b border-slate-800 sticky top-0 z-20">
            <div class="flex items-center justify-between px-3 sm:px-6 py-3">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-400 hover:text-white flex-shrink-0">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-base sm:text-lg font-semibold text-slate-800 truncate">@yield('header', 'Dashboard')</h2>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                    <div class="hidden md:flex items-center gap-2 text-sm text-slate-600">
                        <i class="far fa-calendar"></i>
                        <span class="hidden lg:inline">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                        <span class="lg:hidden">{{ now()->format('d/m/Y') }}</span>
                    </div>
                    
                      <div class="relative" x-data="{ open: false }">
                          <button @click="open = !open" class="relative text-slate-400 hover:text-amber-500 transition px-2">
                              <i class="fas fa-bell text-xl"></i>
                              @if($totalAlertas > 0)
                                  <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full">{{ $totalAlertas }}</span>
                              @endif
                          </button>
                          <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-72 bg-slate-900 rounded-xl shadow-lg border border-slate-700 py-3 z-50" style="display:none;" x-cloak>
                              <div class="px-4 pb-2 border-b border-slate-800 mb-2">
                                  <h3 class="font-bold text-slate-200">Notificaciones</h3>
                              </div>
                              @if($totalAlertas > 0)
                                  @if($alertasStock > 0)
                                      <a href="{{ route('reportes.inventario') }}" class="block px-4 py-2 hover:bg-slate-800 text-sm">
                                          <div class="flex items-start gap-3">
                                              <div class="w-8 h-8 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle"></i></div>
                                              <div>
                                                  <p class="font-bold text-slate-200">Stock Bajo</p>
                                                  <p class="text-xs text-slate-400">Hay {{ $alertasStock }} productos en límite crítico.</p>
                                              </div>
                                          </div>
                                      </a>
                                  @endif
                                  @if($alertasVencimiento > 0)
                                      <a href="{{ route('reportes.vencimientos') }}" class="block px-4 py-2 hover:bg-slate-800 text-sm">
                                          <div class="flex items-start gap-3">
                                              <div class="w-8 h-8 rounded-full bg-orange-500/20 text-orange-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-clock"></i></div>
                                              <div>
                                                  <p class="font-bold text-slate-200">Por Vencer</p>
                                                  <p class="text-xs text-slate-400">Hay {{ $alertasVencimiento }} productos venciendo pronto.</p>
                                              </div>
                                          </div>
                                      </a>
                                  @endif
                              @else
                                  <div class="px-4 py-3 text-center text-slate-500 text-sm">
                                      No hay alertas pendientes.
                                  </div>
                              @endif
                          </div>
                      </div>
<div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 hover:bg-slate-100 px-2 sm:px-3 py-2 rounded-lg transition">
                            <div class="w-8 h-8 gradient-primary rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ auth()->user()->role->nombre ?? 'Usuario' }}</p>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-slate-400 hidden sm:inline"></i>
                        </button>
                        <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-lg border border-slate-700 text-slate-200 py-2" style="display:none;">
                            <p class="px-4 py-2 text-xs text-slate-500 border-b border-slate-100">Conectado como</p>
                            <p class="px-4 py-1 text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="px-4 pb-2 text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                                @csrf
                                <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido principal -->
        <main class="p-3 sm:p-6 pb-24 lg:pb-6 min-w-0">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center shadow-sm text-sm">
                    <i class="fas fa-check-circle text-emerald-500 text-base flex-shrink-0"></i>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center shadow-sm text-sm">
                    <i class="fas fa-exclamation-circle text-red-500 text-base flex-shrink-0"></i>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Barra de Navegación Móvil Inferior (Bottom Bar) -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-30 px-2 py-1.5 shadow-lg flex items-center justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fas fa-tachometer-alt text-lg mb-0.5"></i>
                <span>Inicio</span>
            </a>
            @if(auth()->user()->hasPermission('pos') || auth()->user()->isAdmin())
<a href="{{ route('ventas.pos') }}" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-xs font-bold transition {{ request()->routeIs('ventas.pos') ? 'text-emerald-600 bg-emerald-50' : 'text-emerald-700 hover:text-emerald-900' }}">
                <div class="w-8 h-8 gradient-primary text-white rounded-full flex items-center justify-center shadow-md mb-0.5">
                    <i class="fas fa-cash-register text-sm"></i>
                </div>
                <span>POS</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('ventas') || auth()->user()->isAdmin())
<a href="{{ route('ventas.index') }}" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-xs font-semibold transition {{ request()->routeIs('ventas.index') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fas fa-receipt text-lg mb-0.5"></i>
                <span>Ventas</span>
            </a>
@endif
            @if(auth()->user()->hasPermission('productos') || auth()->user()->isAdmin())
<a href="{{ route('productos.index') }}" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-xs font-semibold transition {{ request()->routeIs('productos.*') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-800' }}">
                <i class="fas fa-box text-lg mb-0.5"></i>
                <span>Stock</span>
            </a>
@endif
            <button type="button" @click="sidebarOpen = true" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800">
                <i class="fas fa-bars text-lg mb-0.5"></i>
                <span>Menú</span>
            </button>
        </nav>
    </div>
</div>

<!-- SweetAlert2 Toast & Helpers Globales -->
<script>
    // Toast global reutilizable
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    window.Toast = Toast;

    // Helper moderno de notificaciones
    window.Notify = {
        success: (msg, title = '¡Correcto!') => Toast.fire({ icon: 'success', title: msg }),
        error: (msg, title = 'Error') => Toast.fire({ icon: 'error', title: msg }),
        warning: (msg, title = 'Atención') => Toast.fire({ icon: 'warning', title: msg }),
        info: (msg, title = 'Aviso') => Toast.fire({ icon: 'info', title: msg }),
        modal: (opts) => Swal.fire({
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            ...opts
        }),
        confirm: (opts = {}) => {
            return Swal.fire({
                title: opts.title || '¿Estás seguro?',
                text: opts.text || 'Esta acción no se puede deshacer.',
                icon: opts.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: opts.confirmColor || '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: opts.confirmText || 'Sí, continuar',
                cancelButtonText: opts.cancelText || 'Cancelar',
                reverseButtons: true
            });
        }
    };

    // Reemplazo transparente y moderno para alert()
    window.alert = function(message) {
        if (typeof message === 'object') {
            try { message = JSON.stringify(message, null, 2); } catch(e){}
        }
        return Swal.fire({
            title: 'Información',
            text: message,
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#10b981'
        });
    };

    // Delegación global para interceptar formularios onsubmit="return confirm(...)"
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[onsubmit]').forEach(function(form) {
            const onsubmitAttr = form.getAttribute('onsubmit') || '';
            const match = onsubmitAttr.match(/return\s+confirm\(['"](.*)['"]\)/i);
            if (match) {
                const message = match[1];
                form.removeAttribute('onsubmit');
                form.addEventListener('submit', function(e) {
                    if (form.dataset.confirmed === 'true') return;
                    e.preventDefault();
                    const isDanger = form.action.includes('destroy') || form.action.includes('anular') || form.action.includes('eliminar') || form.action.includes('restaurar');
                    Swal.fire({
                        title: isDanger ? '¿Confirmar acción?' : '¿Continuar?',
                        text: message,
                        icon: isDanger ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: isDanger ? '#ef4444' : '#10b981',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                });
            }
        });
    });
</script>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        });
    </script>
@endif
@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ef4444'
            });
        });
    </script>
@endif
@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Toast.fire({ icon: 'warning', title: "{{ session('warning') }}" });
        });
    </script>
@endif
@if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Toast.fire({ icon: 'info', title: "{{ session('info') }}" });
        });
    </script>
@endif

@yield('scripts')
</body>
</html>
