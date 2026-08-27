with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

s = s.replace(r"\'", "'")

with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
