with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

css = """
        /* Estilos Globales para Inputs en Modo Oscuro */
        input[type="text"], input[type="number"], input[type="email"], input[type="password"], input[type="search"], input[type="date"], select, textarea {
            background-color: #1e293b !important; /* bg-slate-800 */
            border: 1px solid #334155 !important; /* border-slate-700 */
            color: #f8fafc !important; /* text-slate-50 */
            border-radius: 0.5rem;
        }
        input:read-only, input:disabled {
            background-color: #0f172a !important; /* bg-slate-900 */
            color: #94a3b8 !important;
        }
        input::placeholder, textarea::placeholder {
            color: #64748b !important;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 1px #f59e0b !important;
        }
"""

if "/* Estilos Globales para Inputs en Modo Oscuro */" not in s:
    s = s.replace('</style>', css + '</style>')
    with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
        f.write(s)
