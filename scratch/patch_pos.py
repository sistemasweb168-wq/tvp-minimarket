import re

with open('resources/views/ventas/pos.blade.php', 'r', encoding='utf-8') as f:
    html = f.read()

# Mobile toggle
html = html.replace("bg-slate-200/80 p-1 rounded-2xl mb-3 shadow-inner", "bg-slate-800/80 p-1 rounded-2xl mb-3 shadow-inner border border-slate-700")
html = html.replace("bg-white text-emerald-700", "bg-slate-700 text-amber-400")
html = html.replace("'text-slate-600 font-semibold'", "'text-slate-400 font-semibold hover:text-slate-200'")

# Search Bar
html = html.replace("bg-white rounded-2xl shadow-md p-3 sm:p-4", "bg-slate-900 rounded-2xl shadow-md p-3 sm:p-4 border border-slate-800")
html = html.replace("bg-slate-100 text-slate-900", "bg-slate-800 text-slate-100 border border-slate-700 placeholder-slate-400")
html = html.replace("bg-slate-200 hover:bg-slate-300 text-slate-700", "bg-slate-700 hover:bg-slate-600 text-slate-200")
html = html.replace("bg-slate-900 hover:bg-slate-800 text-white", "bg-amber-500 hover:bg-amber-400 text-slate-900")

# Category Buttons
html = html.replace("'bg-emerald-500 text-white shadow-emerald-200' : 'bg-white text-slate-700 hover:bg-slate-50'", "'bg-amber-500 text-slate-900 shadow-amber-500/20' : 'bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700'")
html = html.replace("'text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-50'", "'text-slate-900 shadow-md' : 'bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700'")
html = html.replace("px-3.5 py-2", "px-4 py-3") # Make categories slightly taller

# Product Grid
html = html.replace("class=\"relative bg-white rounded-2xl shadow-sm hover:shadow-md transition active:scale-95 p-2.5 sm:p-3 text-left border border-slate-100", "class=\"relative bg-slate-900 rounded-2xl shadow-sm hover:shadow-md hover:border-amber-500 transition active:scale-95 p-2.5 sm:p-3 text-left border border-slate-800")
html = html.replace("text-slate-500 text-[10px]", "text-slate-400 text-[10px]")
html = html.replace("text-slate-800 font-black", "text-slate-100 font-black")
html = html.replace("text-emerald-600 font-bold", "text-amber-400 font-bold")
html = html.replace("bg-slate-100 rounded-xl", "bg-slate-800 rounded-xl")
html = html.replace("text-slate-400 text-2xl", "text-slate-600 text-2xl")

# Cart Section
html = html.replace("bg-white rounded-2xl shadow-md flex flex-col h-[calc(100vh-140px)] sticky top-24", "bg-slate-900 border border-slate-800 rounded-2xl shadow-md flex flex-col h-[calc(100vh-140px)] sticky top-24")
html = html.replace("border-b border-slate-100", "border-b border-slate-800")
html = html.replace("text-slate-800 font-bold", "text-slate-100 font-bold")
html = html.replace("text-slate-500 font-semibold", "text-slate-400 font-semibold")
html = html.replace("bg-red-50 text-red-500 hover:bg-red-100", "bg-red-500/10 text-red-400 hover:bg-red-500/20")
html = html.replace("text-slate-400 text-5xl", "text-slate-600 text-5xl")

# Cart Items
html = html.replace("bg-slate-50/50 rounded-xl p-2.5 border border-slate-100", "bg-slate-800 rounded-xl p-2.5 border border-slate-700")
html = html.replace("text-slate-700 font-bold", "text-slate-200 font-bold")
html = html.replace("text-slate-400 text-[10px]", "text-slate-400 text-[10px]")
html = html.replace("bg-white border border-slate-200 text-slate-600 hover:bg-slate-50", "bg-slate-700 border border-slate-600 text-slate-300 hover:bg-slate-600")
html = html.replace("text-emerald-700 font-black", "text-amber-400 font-black")
html = html.replace("text-red-400 hover:text-red-600 hover:bg-red-50", "text-red-400 hover:text-red-300 hover:bg-slate-700")

# Cart Summary / Footer
html = html.replace("bg-slate-50 p-4 border-t border-slate-100", "bg-slate-950 p-4 border-t border-slate-800 rounded-b-2xl")
html = html.replace("text-slate-500 font-bold", "text-slate-400 font-bold")
html = html.replace("text-emerald-600", "text-amber-400")

with open('resources/views/ventas/pos.blade.php', 'w', encoding='utf-8') as f:
    f.write(html)
