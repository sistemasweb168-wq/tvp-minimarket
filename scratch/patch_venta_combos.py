import re

with open('app/Http/Controllers/VentaController.php', 'r', encoding='utf-8') as f:
    s = f.read()

# Logic to replace inside store()
store_stock = """                if ($producto->tipo_producto === 'combo') {
                    // Descontar componentes
                    foreach ($producto->componentesCombo as $componente) {
                        if ($componente->controla_stock) {
                            $stockAnterior = $componente->stock;
                            $cantADescontar = $item['cantidad'] * $componente->pivot->cantidad;
                            $stockNuevo = max(0, $stockAnterior - $cantADescontar);
                            $componente->update(['stock' => $stockNuevo]);
                            
                            MovimientoInventario::create([
                                'producto_id' => $componente->id,
                                'user_id' => auth()->id(),
                                'tipo' => 'salida',
                                'motivo' => 'Venta (Combo) #' . $numeroTicket,
                                'cantidad' => $cantADescontar,
                                'stock_anterior' => $stockAnterior,
                                'stock_nuevo' => $stockNuevo,
                                'referencia_tipo' => 'venta',
                                'referencia_id' => $venta->id,
                                'fecha' => now(),
                            ]);
                        }
                    }
                } elseif ($producto->controla_stock) {
                    $stockAnterior = $producto->stock;
                    $stockNuevo = max(0, $stockAnterior - $item['cantidad']);
                    $producto->update(['stock' => $stockNuevo]);

                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'salida',
                        'motivo' => 'Venta #' . $numeroTicket,
                        'cantidad' => $item['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'referencia_tipo' => 'venta',
                        'referencia_id' => $venta->id,
                        'fecha' => now(),
                    ]);
                }"""

s = re.sub(r'if \(\$producto->controla_stock\) \{.*?(?=// Actualizar totales del turno)', store_stock + '\n            }\n\n            ', s, flags=re.DOTALL)


# Logic to replace inside anular()
anular_stock = """                if ($producto) {
                    if ($producto->tipo_producto === 'combo') {
                        foreach ($producto->componentesCombo as $componente) {
                            if ($componente->controla_stock) {
                                $stockAnterior = $componente->stock;
                                $cantADevolver = $detalle->cantidad * $componente->pivot->cantidad;
                                $stockNuevo = $stockAnterior + $cantADevolver;
                                $componente->update(['stock' => $stockNuevo]);

                                MovimientoInventario::create([
                                    'producto_id' => $componente->id,
                                    'user_id' => auth()->id(),
                                    'tipo' => 'entrada',
                                    'motivo' => 'Anulación venta (Combo) #' . $venta->numero_ticket,
                                    'cantidad' => $cantADevolver,
                                    'stock_anterior' => $stockAnterior,
                                    'stock_nuevo' => $stockNuevo,
                                    'referencia_tipo' => 'venta_anulada',
                                    'referencia_id' => $venta->id,
                                    'fecha' => now(),
                                ]);
                            }
                        }
                    } elseif ($producto->controla_stock) {
                        $stockAnterior = $producto->stock;
                        $stockNuevo = $stockAnterior + $detalle->cantidad;
                        $producto->update(['stock' => $stockNuevo]);

                        MovimientoInventario::create([
                            'producto_id' => $producto->id,
                            'user_id' => auth()->id(),
                            'tipo' => 'entrada',
                            'motivo' => 'Anulación venta #' . $venta->numero_ticket,
                            'cantidad' => $detalle->cantidad,
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo' => $stockNuevo,
                            'referencia_tipo' => 'venta_anulada',
                            'referencia_id' => $venta->id,
                            'fecha' => now(),
                        ]);
                    }
                }"""

s = re.sub(r'if \(\$producto && \$producto->controla_stock\) \{.*?(\$venta->update\(\[\'estado\' => \'anulada\'\]\);)', anular_stock + '\n            }\n\n            ' + r'\1', s, flags=re.DOTALL)

with open('app/Http/Controllers/VentaController.php', 'w', encoding='utf-8') as f:
    f.write(s)
