import re

with open('routes/web.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Add permissions
s = s.replace("Route::get('pos', [VentaController::class, 'pos'])->name('ventas.pos');", "Route::get('pos', [VentaController::class, 'pos'])->name('ventas.pos')->middleware('permission:pos');")
s = s.replace("Route::resource('ventas', VentaController::class)->only(['index', 'store', 'show']);", "Route::resource('ventas', VentaController::class)->only(['index', 'store', 'show'])->middleware('permission:ventas');")
s = s.replace("Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');", "Route::post('ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular')->middleware('permission:ventas.anular');")

with open('routes/web.php', 'w', encoding='utf-8') as f:
    f.write(s)
