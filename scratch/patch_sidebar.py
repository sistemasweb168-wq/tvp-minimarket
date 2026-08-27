import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# 1. Update the sidebar classes to support hover expansion
s = s.replace(
    '''<aside class="bg-black border-r border-slate-900 text-white w-64 fixed inset-y-0 left-0 z-30 transition-transform duration-300 lg:translate-x-0"\n           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">''',
    '''<!-- Sidebar -->
    <aside x-data="{ isHovered: false }" 
           @mouseenter="isHovered = true" 
           @mouseleave="isHovered = false" 
           class="bg-black border-r border-slate-900 text-white fixed inset-y-0 left-0 z-40 transition-all duration-300 lg:translate-x-0 overflow-x-hidden"
           :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full w-64 lg:w-16 ' + (isHovered ? 'lg:w-64 shadow-2xl' : 'lg:w-16')">'''
)

# Fix the main wrapper margin to match the collapsed sidebar (w-16 = 4rem = 64px)
s = s.replace(
    '''<div class="flex-1 lg:ml-64 min-w-0">''',
    '''<div class="flex-1 lg:ml-16 min-w-0 transition-all duration-300">'''
)

# 2. Add whitespace-nowrap to all sidebar texts and adjust padding/layout
# We need to target the spans inside sidebar-link
# <a href="..." class="sidebar-link flex items-center gap-3 px-5 py-3...
# <i class="fas fa-users w-5"></i><span>Clientes</span>

s = re.sub(r'(<i class="[^"]+ w-5"></i>)<span>([^<]+)</span>', r'\1<span class="whitespace-nowrap">\2</span>', s)

# The section headers like <p class="px-5 mt-4 mb-2 text-xs uppercase text-slate-500 font-semibold">Contactos</p>
# Should fade out or hide when collapsed
s = re.sub(r'(<p class="px-5[^>]+>)([^<]+)(</p>)', r'\1<span class="whitespace-nowrap transition-opacity duration-200" :class="(sidebarOpen || isHovered) ? \'opacity-100\' : \'opacity-0 hidden\'">\2</span>\3', s)

# Adjust the logo section at the top of the sidebar
# It currently has: <div class="flex-1 min-w-0">
# We want it to hide when collapsed
s = s.replace(
    '''<div class="flex-1 min-w-0">\n                <h1 class="font-bold text-sm truncate">{{ $empresaGlobal->nombre_comercial ?? 'TPV Minimarket' }}</h1>\n                <p class="text-xs text-slate-400">Sistema POS</p>\n            </div>''',
    '''<div class="flex-1 min-w-0 transition-opacity duration-200" :class="(sidebarOpen || isHovered) ? 'opacity-100' : 'opacity-0 lg:hidden'">
                <h1 class="font-bold text-sm whitespace-nowrap">{{ $empresaGlobal->nombre_comercial ?? 'TPV Minimarket' }}</h1>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Sistema POS</p>
            </div>'''
)

# Also fix the form button for Logout
# <button class="w-full flex items-center gap-3 px-3 py-2.5 bg-red-600/20 hover:bg-red-600/40 rounded-lg text-red-300 transition">
# <i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span>
s = re.sub(
    r'(<i class="fas fa-sign-out-alt( w-5)?"></i>)<span>([^<]+)</span>', 
    r'\1<span class="whitespace-nowrap transition-opacity duration-200" :class="(sidebarOpen || isHovered) ? \'opacity-100\' : \'opacity-0 hidden\'">\3</span>', 
    s
)

# And in the logout button class, it should probably be px-5 to match the rest or just center the icon
s = s.replace(
    'px-3 py-2.5 bg-red-600/20',
    'px-5 py-3 bg-red-600/20'
)

# One more thing: The sidebar items use `gap-3` and `px-5`. 
# If it's w-16 (64px), px-5 is 20px padding left and right. The icon is w-5 (20px). 
# 20 + 20 + 20 = 60px. That fits perfectly in w-16!
# However, the logo is `p-5`, icon is `w-10 h-10`? Wait, logo is `w-10 h-10`.
# If sidebar is w-16 (64px), `p-5` is 20px, so 40px padding total + 40px logo = 80px! It will overflow w-16!
# Let's change `p-5` to `p-3` for the logo container, and `px-5` to `px-3 lg:px-5`? No, let's just make the left padding consistent.
s = s.replace('<div class="p-5 border-b border-slate-800 flex items-center gap-3">', '<div class="px-3 py-5 border-b border-slate-800 flex items-center gap-3">')
s = s.replace('px-5 py-3', 'px-5 py-3') # Links are px-5. Wait, 64px width - px-5 (1.25rem = 20px) = 44px left padding. Icon is 20px. 64 - 20(left) - 20(right) = 24px space for icon. So it fits. But the logo has p-5 which is p-20px.

# Let's ensure the toggle button on mobile works
# We should also ensure the Logout icon has w-5
s = s.replace('<i class="fas fa-sign-out-alt"></i>', '<i class="fas fa-sign-out-alt w-5"></i>')

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
