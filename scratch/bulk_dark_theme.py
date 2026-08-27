import os
import glob

exclude_files = [
    'app.blade.php',
    'pos.blade.php',
    'ticket.blade.php',
    'ticket_pdf.blade.php',
    'a4.blade.php'
]

# Find all blade files
blade_files = glob.glob('resources/views/**/*.blade.php', recursive=True)

for filepath in blade_files:
    basename = os.path.basename(filepath)
    if basename in exclude_files:
        continue

    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Apply dark mode replacements
    content = content.replace('bg-white', 'bg-slate-900 border border-slate-800')
    
    # Text colors
    content = content.replace('text-slate-900', 'text-slate-50')
    content = content.replace('text-slate-800', 'text-slate-100')
    content = content.replace('text-slate-700', 'text-slate-200')
    content = content.replace('text-slate-600', 'text-slate-300')
    content = content.replace('text-slate-500', 'text-slate-400')
    
    # Border colors
    content = content.replace('border-slate-100', 'border-slate-800')
    content = content.replace('border-slate-200', 'border-slate-700')
    content = content.replace('border-slate-300', 'border-slate-600')
    
    # Background colors
    content = content.replace('bg-slate-50', 'bg-slate-800')
    content = content.replace('bg-slate-100', 'bg-slate-900')
    content = content.replace('bg-slate-200', 'bg-slate-700')
    
    # Inputs
    content = content.replace('border border-slate-300', 'border border-slate-700 bg-slate-800 text-slate-100')
    content = content.replace('focus:border-emerald-500', 'focus:border-amber-500')
    content = content.replace('focus:ring-emerald-500', 'focus:ring-amber-500')

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

print(f"Patched {len(blade_files)} files (excluding {len(exclude_files)} files).")
