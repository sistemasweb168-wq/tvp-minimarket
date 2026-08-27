import re

with open('resources/views/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    s = f.read()

php_logic = """
@php
    $alertasStock = \App\Models\Producto::where('controla_stock', 1)->where('estado', 1)->whereRaw('stock <= stock_minimo')->count();
    $alertasVencimiento = \App\Models\Producto::whereNotNull('fecha_vencimiento')->where('estado', 1)->whereDate('fecha_vencimiento', '<=', now()->addDays(30))->count();
    $totalAlertas = $alertasStock + $alertasVencimiento;
@endphp
"""

bell_html = """
                      <div class="relative" x-data="{ open: false }">
                          <button @click="open = !open" class="relative text-slate-400 hover:text-amber-500 transition px-2">
                              <i class="fas fa-bell text-xl"></i>
                              @if($totalAlertas > 0)
                                  <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full">{{ $totalAlertas }}</span>
                              @endif
                          </button>
                          <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-72 bg-slate-900 rounded-xl shadow-lg border border-slate-700 py-3 z-50" style="display:none;" x-cloak>
                              <div class="px-4 pb-2 border-b border-slate-800 mb-2">
                                  <h3 class="font-bold text-slate-200">Notificaciones</h3>
                              </div>
                              @if($totalAlertas > 0)
                                  @if($alertasStock > 0)
                                      <a href="{{ route('reportes.inventario') }}" class="block px-4 py-2 hover:bg-slate-800 text-sm">
                                          <div class="flex items-start gap-3">
                                              <div class="w-8 h-8 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle"></i></div>
                                              <div>
                                                  <p class="font-bold text-slate-200">Stock Bajo</p>
                                                  <p class="text-xs text-slate-400">Hay {{ $alertasStock }} productos en límite crítico.</p>
                                              </div>
                                          </div>
                                      </a>
                                  @endif
                                  @if($alertasVencimiento > 0)
                                      <a href="{{ route('reportes.vencimientos') }}" class="block px-4 py-2 hover:bg-slate-800 text-sm">
                                          <div class="flex items-start gap-3">
                                              <div class="w-8 h-8 rounded-full bg-orange-500/20 text-orange-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-clock"></i></div>
                                              <div>
                                                  <p class="font-bold text-slate-200">Por Vencer</p>
                                                  <p class="text-xs text-slate-400">Hay {{ $alertasVencimiento }} productos venciendo pronto.</p>
                                              </div>
                                          </div>
                                      </a>
                                  @endif
                              @else
                                  <div class="px-4 py-3 text-center text-slate-500 text-sm">
                                      No hay alertas pendientes.
                                  </div>
                              @endif
                          </div>
                      </div>
"""

# Insert PHP logic before header
s = s.replace('<header ', php_logic + '<header ')

# Insert bell before the user menu dropdown
# The user menu starts with: <div class="relative" x-data="{ open: false }">\n                          <button @click="open = !open" class="flex items-center gap-2
parts = re.split(r'(<div class="relative" x-data="\{ open: false \}">\s*<button @click="open = !open" class="flex items-center gap-2)', s)
if len(parts) == 3:
    s = parts[0] + bell_html + parts[1] + parts[2]
    
with open('resources/views/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(s)
