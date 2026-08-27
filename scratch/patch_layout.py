import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    html = f.read()

# CSS Variables/Classes for theme
html = html.replace('.sidebar-link.active { background: linear-gradient(90deg, rgba(16,185,129,.15), transparent); border-left: 3px solid #10b981; color: #10b981; }', '.sidebar-link.active { background: linear-gradient(90deg, rgba(245,158,11,.15), transparent); border-left: 3px solid #f59e0b; color: #f59e0b; }')
html = html.replace('.gradient-primary { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }', '.gradient-primary { background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%); }')
html = html.replace('::-webkit-scrollbar-track { background: #f1f5f9; }', '::-webkit-scrollbar-track { background: #0f172a; }')
html = html.replace('::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }', '::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }')
html = html.replace('::-webkit-scrollbar-thumb:hover { background: #94a3b8; }', '::-webkit-scrollbar-thumb:hover { background: #475569; }')
html = html.replace('content="#059669"', 'content="#f59e0b"')

# HTML tags
html = html.replace('<body class="bg-slate-100">', '<body class="bg-slate-950 text-slate-200">')
html = html.replace('<aside class="bg-slate-900', '<aside class="bg-black border-r border-slate-900')
html = html.replace('<header class="bg-white shadow-sm border-b border-slate-200', '<header class="bg-slate-900 shadow-md border-b border-slate-800')
html = html.replace('text-slate-600 hover:text-slate-900', 'text-slate-400 hover:text-white') # Sidebar toggle icon

# Header profile drop-down
html = html.replace('bg-white rounded-lg shadow-lg border border-slate-200', 'bg-slate-800 rounded-lg shadow-lg border border-slate-700 text-slate-200')
html = html.replace('text-slate-800 font-bold', 'text-slate-100 font-bold')

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(html)
