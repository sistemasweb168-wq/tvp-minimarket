with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

css_addition = """
        /* Ocultar scrollbar en el sidebar pero mantener funcionalidad de scroll */
        .sidebar-scroll::-webkit-scrollbar { display: none; }
        .sidebar-scroll { -ms-overflow-style: none; scrollbar-width: none; }
"""

# Insert CSS right before </style>
s = s.replace("</style>", css_addition + "</style>")

# Replace nav class
s = s.replace('<nav class="py-4 overflow-y-auto"', '<nav class="py-4 overflow-y-auto sidebar-scroll"')

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
