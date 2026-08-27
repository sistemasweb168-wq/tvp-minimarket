import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

# 1. Update global x-data for sidebarOpen
s = s.replace(
    '''<div class="flex min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">''',
    '''<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">'''
)

# 2. Update the aside element
# Look for <aside ...> up to >
s = re.sub(
    r'<aside x-data="\{ isHovered: false \}".*?:class="[^"]+">',
    '''<aside x-data="{ isHovered: false }" 
           @mouseenter="isHovered = true" 
           @mouseleave="isHovered = false" 
           class="bg-black border-r border-slate-900 text-white fixed inset-y-0 left-0 z-40 transition-all duration-300 overflow-x-hidden w-64 lg:w-16"
           :class="[
               sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               isHovered ? 'lg:w-64 shadow-2xl' : ''
           ]">''',
    s,
    flags=re.DOTALL
)

# 3. Fix the spans inside the sidebar
# Remove `hidden` and `lg:hidden` from the opacity class strings so it transitions smoothly width-wise
# Wait, for the logo block:
# :class="(sidebarOpen || isHovered) ? 'opacity-100' : 'opacity-0 lg:hidden'"
s = s.replace("? 'opacity-100' : 'opacity-0 lg:hidden'", "? 'opacity-100' : 'opacity-0'")
s = s.replace("? 'opacity-100' : 'opacity-0 hidden'", "? 'opacity-100' : 'opacity-0'")

# The text visibility condition should ideally just be `(sidebarOpen || isHovered || window.innerWidth < 1024)` but since it's inside mobile it will just show if `opacity-100` isn't forced.
# Let's change the condition to: `(isHovered || window.innerWidth < 1024)` ? 
# Actually, if we just use CSS: `group-hover:opacity-100 opacity-0 lg:opacity-0` and remove Alpine conditionals for text entirely! 
# Alpine is fine, let's just make it: `:class="(isHovered) ? 'opacity-100' : 'opacity-0 lg:opacity-0'"` wait, on mobile we want it ALWAYS 100%. 
# So `:class="(isHovered || window.innerWidth < 1024) ? 'opacity-100' : 'opacity-0'"`

s = s.replace("sidebarOpen || isHovered", "isHovered || window.innerWidth < 1024")

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
