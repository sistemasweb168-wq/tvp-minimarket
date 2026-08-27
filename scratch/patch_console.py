with open('routes/console.php', 'r', encoding='utf-8') as f:
    s = f.read()

sched = """
use Illuminate\Support\Facades\Schedule;

Schedule::command('alertas:whatsapp')->dailyAt('08:00');
"""

s = s + sched

with open('routes/console.php', 'w', encoding='utf-8') as f:
    f.write(s)
