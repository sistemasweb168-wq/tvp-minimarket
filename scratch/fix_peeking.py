import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# 1. Fix the ugly global scrollbar that was still showing 
css_addition = """
        /* Ocultar scrollbar global feo */
        ::-webkit-scrollbar { width: 0px; height: 0px; display: none; }
"""
s = s.replace("::-webkit-scrollbar { width: 8px; height: 8px; }", "::-webkit-scrollbar { width: 0px; height: 0px; display: none; }")

# 2. Rewrite ALL sidebar text spans to use x-show instead of opacity (so they disappear completely and don't peek)
# For standard spans: <span class="whitespace-nowrap">Dashboard</span>
s = re.sub(
    r'<span class="whitespace-nowrap">([^<]+)</span>',
    r'<span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">\1</span>',
    s
)

# For spans with the old transition logic (like headers or logout):
s = re.sub(
    r'<span class="whitespace-nowrap transition-opacity[^>]+>([^<]+)</span>',
    r'<span class="whitespace-nowrap ml-3" x-show="isHovered || sidebarOpen" style="display: none;">\1</span>',
    s
)

# 3. Remove gap-3 from the links so they center perfectly when collapsed
s = s.replace('flex items-center gap-3', 'flex items-center')

# Fix Logo header
# Previous:
# <div class="flex-1 min-w-0 transition-opacity duration-200" :class="(isHovered || window.innerWidth < 1024) ? 'opacity-100' : 'opacity-0'">
#                 <h1 class="font-bold text-sm whitespace-nowrap">{{ $empresaGlobal->nombre_comercial ?? 'TPV Minimarket' }}</h1>
#                 <p class="text-[10px] text-slate-400 whitespace-nowrap">Sistema POS</p>
#             </div>
s = re.sub(
    r'<div class="flex-1 min-w-0 transition-opacity[^>]+>(.*?)</div>',
    r'<div class="flex-1 min-w-0 ml-3" x-show="isHovered || sidebarOpen" style="display: none;">\1</div>',
    s,
    flags=re.DOTALL
)


with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
