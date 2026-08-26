<?php
/**
 * Script TEMPORAL para poblar la BD con DATOS COMPLETOS DEMO.
 * Acceso: http://127.0.0.1:8023/seed-demo-completo.php
 * - 10+ registros por modulo distribuidos en 60 dias
 * - Datos realistas para que dashboard, reportes y graficos esten ricos
 * ELIMINAR después de usar.
 */
set_time_limit(300);
ini_set('memory_limit', '256M');

$db_host = 'localhost';
$db_name = 'tpv_minimarket';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

$resultados = [];
$warnings = [];
$now = new DateTime();

function r(&$arr, $msg) { $arr[] = $msg; }

// ============================================================
// 0) LIMPIAR DATOS TRANSACCIONALES
// ============================================================
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
foreach ([
    'puntos_fidelidad', 'venta_detalles', 'ventas',
    'compra_detalles', 'compras',
    'movimientos_inventario', 'movimientos_caja', 'turnos_caja',
    'promociones', 'clientes'
] as $t) {
    try {
        $pdo->exec("DELETE FROM $t");
        $pdo->exec("ALTER TABLE $t AUTO_INCREMENT = 1");
    } catch (\Exception $e) {
        $warnings[] = "Tabla $t no existe o no se pudo limpiar";
    }
}
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");
r($resultados, "✓ Datos transaccionales anteriores limpiados");

// ============================================================
// 1) CATEGORIAS (verificar que haya al menos 10)
// ============================================================
$existCats = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
if ($existCats < 12) {
    $catsExtra = [
        ['Vinos y Licores', '#7c3aed', 'wine-glass'],
        ['Congelados', '#0ea5e9', 'snowflake'],
        ['Cuidado Personal Plus', '#ec4899', 'pump-soap'],
    ];
    $stmt = $pdo->prepare("INSERT IGNORE INTO categorias (nombre, color, icono, activo, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
    foreach ($catsExtra as $c) $stmt->execute($c);
}
r($resultados, "✓ Categorias: " . $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn() . " disponibles");

// ============================================================
// 2) PROVEEDORES (10)
// ============================================================
$proveedores = [
    ['PR00001', 'Distribuidora Alimentos S.A.', '20100200300', 'Juan Pérez', '987654321', 'ventas@dasa.com', 'Lima'],
    ['PR00002', 'Bebidas y Más SAC', '20200300400', 'María Gómez', '976543210', 'pedidos@bebidasymas.com', 'Lima'],
    ['PR00003', 'Lácteos del Norte', '20300400500', 'Carlos Ruiz', '965432109', 'lacteos@delnorte.com', 'Trujillo'],
    ['PR00004', 'Panificadora La Espiga', '20400500600', 'Ana Torres', '954321098', 'pedidos@laespiga.com', 'Lima'],
    ['PR00005', 'Snacks Premium S.A.', '20500600700', 'Pedro Salinas', '987111222', 'ventas@snackspremium.com', 'Lima'],
    ['PR00006', 'Distribuidora Gloria SAC', '20600700800', 'Lucía Pacheco', '987333444', 'pedidos@gloria.com.pe', 'Lima'],
    ['PR00007', 'Importadora Limpieza Total', '20700800900', 'Miguel Castro', '987555666', 'ventas@limpiezatotal.com', 'Callao'],
    ['PR00008', 'Cervecería Backus', '20100100100', 'Sandra Velarde', '987777888', 'ventas@backus.pe', 'Lima'],
    ['PR00009', 'Procter & Gamble Perú', '20800900100', 'Diego Reyes', '987999000', 'pedidos@pg.pe', 'Lima'],
    ['PR00010', 'Alicorp Distribución', '20900100200', 'Karina Yupanqui', '988111222', 'ventas@alicorp.com.pe', 'Lima'],
];

$pdo->exec("DELETE FROM proveedores");
$pdo->exec("ALTER TABLE proveedores AUTO_INCREMENT = 1");
$stmt = $pdo->prepare("INSERT INTO proveedores (codigo, razon_social, ruc_nit, contacto, telefono, email, ciudad, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
foreach ($proveedores as $p) $stmt->execute($p);
r($resultados, "✓ 10 proveedores creados");

// ============================================================
// 3) CLIENTES (15)
// ============================================================
$clientes = [
    ['CL00001', 'DNI', '12345678', 'Juan Carlos', 'Pérez García', '987654321', 'juan.perez@gmail.com', 'Av. Lima 123', 'Lima', 'M'],
    ['CL00002', 'DNI', '87654321', 'María Elena', 'González López', '976543210', 'maria.gonzalez@gmail.com', 'Jr. Cusco 456', 'Lima', 'F'],
    ['CL00003', 'DNI', '23456789', 'Carlos Alberto', 'Ramírez Soto', '965432109', 'carlos.ramirez@hotmail.com', 'Av. Brasil 789', 'Lima', 'M'],
    ['CL00004', 'DNI', '34567890', 'Ana Sofía', 'Torres Vargas', '954321098', 'ana.torres@gmail.com', 'Av. Arequipa 321', 'Lima', 'F'],
    ['CL00005', 'DNI', '45678901', 'Luis Fernando', 'Mendoza Castro', '943210987', 'luis.mendoza@yahoo.com', 'Jr. Tacna 654', 'Lima', 'M'],
    ['CL00006', 'DNI', '56789012', 'Patricia', 'Flores Quispe', '932109876', 'patricia.flores@gmail.com', 'Av. Javier Prado 987', 'Lima', 'F'],
    ['CL00007', 'RUC', '20100200300', 'Comercial El Sol', null, '012345678', 'ventas@elsol.com', 'Av. Industrial 100', 'Lima', null],
    ['CL00008', 'DNI', '67890123', 'Roberto', 'Silva Huamán', '921098765', 'roberto.silva@gmail.com', 'Jr. Junín 234', 'Lima', 'M'],
    ['CL00009', 'DNI', '78901234', 'Carmen', 'Vega Ramos', '910987654', 'carmen.vega@hotmail.com', 'Av. La Marina 567', 'Lima', 'F'],
    ['CL00010', 'DNI', '89012345', 'José Manuel', 'Díaz Aguirre', '900876543', 'jose.diaz@gmail.com', 'Jr. Puno 890', 'Lima', 'M'],
    ['CL00011', 'DNI', '11223344', 'Sofia', 'Mariategui Lujan', '987112233', 'sofia.m@gmail.com', 'Av. Salaverry 200', 'Lima', 'F'],
    ['CL00012', 'DNI', '22334455', 'Diego', 'Quiroz Espinoza', '987223344', 'diego.q@gmail.com', 'Jr. Huallaga 300', 'Lima', 'M'],
    ['CL00013', 'DNI', '33445566', 'Valeria', 'Cardenas Rojas', '987334455', 'valeria.c@yahoo.com', 'Av. Petit Thouars 400', 'Lima', 'F'],
    ['CL00014', 'DNI', '44556677', 'Alejandro', 'Marin Pereda', '987445566', 'alex.m@gmail.com', 'Jr. Lampa 500', 'Lima', 'M'],
    ['CL00015', 'DNI', '55667788', 'Lucia', 'Bermudez Castillo', '987556677', 'lucia.b@hotmail.com', 'Av. Republica 600', 'Lima', 'F'],
];

$stmt = $pdo->prepare("INSERT INTO clientes (codigo, tipo_documento, documento, nombres, apellidos, telefono, email, direccion, ciudad, genero, puntos_fidelidad, credito_limite, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
foreach ($clientes as $c) {
    $puntos = rand(0, 50);
    $credito = [0, 100, 200, 500, 1000][rand(0, 4)];
    $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8], $c[9], $puntos, $credito]);
}
r($resultados, "✓ 15 clientes creados");

// ============================================================
// 4) PRODUCTOS - resetear stock a inicial generoso
// ============================================================
$totalProductos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$pdo->exec("UPDATE productos SET stock = stock + 200 WHERE stock < 100");
r($resultados, "✓ $totalProductos productos disponibles (stock recargado)");

// ============================================================
// 5) TURNOS DE CAJA (10 turnos en 60 dias)
// ============================================================
$turnoIds = [];
for ($i = 0; $i < 10; $i++) {
    $diasAtras = $i * 6;
    $fechaApertura = (clone $now)->modify("-$diasAtras days")->setTime(8, 0, 0);
    $fechaCierre = (clone $fechaApertura)->setTime(22, 0, 0);

    $stmt = $pdo->prepare("INSERT INTO turnos_caja (caja_id, user_id, fecha_apertura, fecha_cierre, monto_apertura, monto_cierre, monto_calculado, total_ventas, total_efectivo, total_tarjeta, total_otros, cantidad_ventas, estado, created_at, updated_at) VALUES (1, 1, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0, 'cerrado', NOW(), NOW())");
    $stmt->execute([
        $fechaApertura->format('Y-m-d H:i:s'),
        $fechaCierre->format('Y-m-d H:i:s'),
        rand(100, 300),
    ]);
    $turnoIds[] = [
        'id' => $pdo->lastInsertId(),
        'fecha' => $fechaApertura,
    ];
}
r($resultados, "✓ 10 turnos de caja creados (cada 6 dias)");

// ============================================================
// 6) VENTAS (50 distribuidas en 60 dias)
// ============================================================
$productos = $pdo->query("SELECT id, codigo, nombre, precio_venta, stock, aplica_impuesto, controla_stock FROM productos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$clientesIds = $pdo->query("SELECT id FROM clientes")->fetchAll(PDO::FETCH_COLUMN);
$usersIds = $pdo->query("SELECT id FROM users WHERE activo = 1")->fetchAll(PDO::FETCH_COLUMN);

// Distribucion de fechas: mas ventas en dias recientes
$fechas = [];
// 8 hoy
for ($i = 0; $i < 8; $i++) {
    $h = rand(8, 21); $m = rand(0, 59);
    $fechas[] = (clone $now)->setTime($h, $m, 0);
}
// 14 distribuidas en ultimos 7 dias (2 por dia)
for ($d = 1; $d <= 7; $d++) {
    for ($v = 0; $v < 2; $v++) {
        $h = rand(8, 21); $m = rand(0, 59);
        $fechas[] = (clone $now)->modify("-$d days")->setTime($h, $m, 0);
    }
}
// 28 distribuidas en el resto del mes (dias 8-60)
for ($i = 0; $i < 28; $i++) {
    $d = rand(8, 60);
    $h = rand(8, 21); $m = rand(0, 59);
    $fechas[] = (clone $now)->modify("-$d days")->setTime($h, $m, 0);
}
usort($fechas, fn($a, $b) => $a <=> $b);

$tasaImpuesto = 0.18;
$formasPago = ['efectivo','efectivo','efectivo','efectivo','tarjeta','tarjeta','tarjeta','transferencia','transferencia'];

$turnoTotals = []; // [turno_id => [ventas, efectivo, tarjeta, otros, cantidad]]
foreach ($turnoIds as $t) {
    $turnoTotals[$t['id']] = ['ventas'=>0, 'efectivo'=>0, 'tarjeta'=>0, 'otros'=>0, 'cantidad'=>0];
}

$stmtVenta = $pdo->prepare("INSERT INTO ventas (numero_ticket, tipo_comprobante, serie, fecha_venta, cliente_id, user_id, turno_caja_id, subtotal, descuento, impuesto, total, monto_recibido, cambio, forma_pago, estado, created_at, updated_at) VALUES (?, 'TICKET', 'T001', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completada', ?, ?)");
$stmtDetalle = $pdo->prepare("INSERT INTO venta_detalles (venta_id, producto_id, codigo, descripcion, cantidad, precio_unitario, descuento, impuesto, subtotal, total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");
$stmtMov = $pdo->prepare("INSERT INTO movimientos_inventario (producto_id, user_id, tipo, motivo, cantidad, stock_anterior, stock_nuevo, referencia_tipo, referencia_id, fecha, created_at, updated_at) VALUES (?, ?, 'salida', ?, ?, ?, ?, 'venta', ?, ?, NOW(), NOW())");
$stmtPunto = $pdo->prepare("INSERT INTO puntos_fidelidad (cliente_id, venta_id, tipo, puntos, saldo_anterior, saldo_nuevo, descripcion, created_at, updated_at) VALUES (?, ?, 'ganado', ?, ?, ?, ?, NOW(), NOW())");

$contadorTicket = 1;
foreach ($fechas as $fecha) {
    // Encontrar turno apropiado
    $turnoId = $turnoIds[0]['id'];
    foreach ($turnoIds as $t) {
        if ($fecha >= $t['fecha']) {
            $turnoId = $t['id'];
            break;
        }
    }

    shuffle($productos);
    $items = array_slice($productos, 0, rand(2, 7));

    $subtotal = 0; $impuestoTotal = 0; $detalles = [];

    foreach ($items as $p) {
        $cantidad = rand(1, 4);
        $precio = (float) $p['precio_venta'];
        $itemSubtotal = $cantidad * $precio;
        $impuestoItem = $p['aplica_impuesto'] ? ($itemSubtotal - ($itemSubtotal / (1 + $tasaImpuesto))) : 0;

        $subtotal += $itemSubtotal - $impuestoItem;
        $impuestoTotal += $impuestoItem;

        $detalles[] = [
            'producto_id' => $p['id'],
            'codigo' => $p['codigo'],
            'nombre' => $p['nombre'],
            'cantidad' => $cantidad,
            'precio' => $precio,
            'impuesto' => $impuestoItem,
            'subtotal' => $itemSubtotal - $impuestoItem,
            'total' => $itemSubtotal,
            'controla_stock' => $p['controla_stock'],
        ];
    }

    $descuento = (rand(1, 10) <= 2) ? round(rand(1, 8), 2) : 0;
    $total = $subtotal + $impuestoTotal - $descuento;
    $formaPago = $formasPago[array_rand($formasPago)];
    $montoRecibido = $formaPago === 'efectivo' ? ceil($total / 5) * 5 : $total;
    $cambio = $montoRecibido - $total;

    $clienteId = (rand(1, 10) <= 7) ? $clientesIds[array_rand($clientesIds)] : null;
    $userId = $usersIds[array_rand($usersIds)];

    $numeroTicket = 'T001-' . str_pad($contadorTicket++, 8, '0', STR_PAD_LEFT);
    $fechaStr = $fecha->format('Y-m-d H:i:s');

    $stmtVenta->execute([
        $numeroTicket, $fechaStr, $clienteId, $userId, $turnoId,
        round($subtotal, 2), $descuento, round($impuestoTotal, 2), round($total, 2),
        round($montoRecibido, 2), round($cambio, 2), $formaPago,
        $fechaStr, $fechaStr
    ]);
    $ventaId = $pdo->lastInsertId();

    foreach ($detalles as $d) {
        $stmtDetalle->execute([
            $ventaId, $d['producto_id'], $d['codigo'], $d['nombre'],
            $d['cantidad'], $d['precio'], round($d['impuesto'], 2),
            round($d['subtotal'], 2), round($d['total'], 2),
            $fechaStr, $fechaStr
        ]);

        if ($d['controla_stock']) {
            $stockActual = $pdo->query("SELECT stock FROM productos WHERE id = " . $d['producto_id'])->fetchColumn();
            $pdo->prepare("UPDATE productos SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$d['cantidad'], $d['producto_id']]);
            $stmtMov->execute([
                $d['producto_id'], $userId, 'Venta ' . $numeroTicket, $d['cantidad'],
                $stockActual, max(0, $stockActual - $d['cantidad']),
                $ventaId, $fechaStr
            ]);
        }
    }

    // Acumular totales del turno
    $turnoTotals[$turnoId]['ventas'] += $total;
    $turnoTotals[$turnoId]['cantidad']++;
    if ($formaPago === 'efectivo') $turnoTotals[$turnoId]['efectivo'] += $total;
    elseif ($formaPago === 'tarjeta') $turnoTotals[$turnoId]['tarjeta'] += $total;
    else $turnoTotals[$turnoId]['otros'] += $total;

    // Puntos fidelidad
    if ($clienteId && $total >= 10) {
        $puntos = floor($total / 10);
        $saldoAnt = $pdo->query("SELECT puntos_fidelidad FROM clientes WHERE id = $clienteId")->fetchColumn();
        $pdo->prepare("UPDATE clientes SET puntos_fidelidad = puntos_fidelidad + ? WHERE id = ?")->execute([$puntos, $clienteId]);
        $stmtPunto->execute([$clienteId, $ventaId, $puntos, $saldoAnt, $saldoAnt + $puntos, "Venta $numeroTicket"]);
    }
}
r($resultados, "✓ " . count($fechas) . " ventas distribuidas en 60 dias");

// Actualizar totales de cada turno
foreach ($turnoTotals as $tid => $tot) {
    $pdo->prepare("UPDATE turnos_caja SET total_ventas = ?, total_efectivo = ?, total_tarjeta = ?, total_otros = ?, cantidad_ventas = ?, monto_calculado = monto_apertura + ?, monto_cierre = monto_apertura + ? WHERE id = ?")
        ->execute([
            round($tot['ventas'], 2), round($tot['efectivo'], 2),
            round($tot['tarjeta'], 2), round($tot['otros'], 2),
            $tot['cantidad'], round($tot['efectivo'], 2), round($tot['efectivo'], 2), $tid
        ]);
}

// ============================================================
// 7) COMPRAS (15 en 60 dias)
// ============================================================
$proveedoresIds = $pdo->query("SELECT id FROM proveedores")->fetchAll(PDO::FETCH_COLUMN);
$stmtCompra = $pdo->prepare("INSERT INTO compras (numero, numero_factura, fecha_compra, proveedor_id, user_id, subtotal, descuento, impuesto, total, forma_pago, estado, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, 0, 0, ?, ?, 'recibida', ?, ?)");
$stmtCompraDet = $pdo->prepare("INSERT INTO compra_detalles (compra_id, producto_id, codigo, descripcion, cantidad, precio_unitario, descuento, impuesto, subtotal, total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?)");

for ($i = 1; $i <= 15; $i++) {
    $diasAtras = rand(1, 60);
    $fechaCompra = (clone $now)->modify("-$diasAtras days")->setTime(rand(8, 17), 0, 0);
    $fechaStr = $fechaCompra->format('Y-m-d H:i:s');

    shuffle($productos);
    $items = array_slice($productos, 0, rand(3, 8));
    $subtotal = 0; $detallesC = [];

    foreach ($items as $p) {
        $cantidad = rand(10, 60);
        $precio = (float) $p['precio_venta'] * 0.7;
        $tot = $cantidad * $precio;
        $subtotal += $tot;
        $detallesC[] = [$p['id'], $p['codigo'], $p['nombre'], $cantidad, $precio, $tot];
    }

    $numeroCompra = 'C-' . str_pad($i, 8, '0', STR_PAD_LEFT);
    $factura = 'F' . rand(100, 999) . '-' . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT);
    $proveedorId = $proveedoresIds[array_rand($proveedoresIds)];
    $formaPago = ['efectivo','transferencia','credito'][rand(0, 2)];

    $stmtCompra->execute([$numeroCompra, $factura, $fechaStr, $proveedorId, round($subtotal, 2), round($subtotal, 2), $formaPago, $fechaStr, $fechaStr]);
    $compraId = $pdo->lastInsertId();

    foreach ($detallesC as $d) {
        $stmtCompraDet->execute([$compraId, $d[0], $d[1], $d[2], $d[3], round($d[4], 2), round($d[5], 2), round($d[5], 2), $fechaStr, $fechaStr]);
        $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?")->execute([$d[3], $d[0]]);
    }
}
r($resultados, "✓ 15 compras creadas distribuidas en 60 dias");

// ============================================================
// 8) PROMOCIONES (12)
// ============================================================
$promos = [
    ['Descuento Verano', '10% off bebidas', 'descuento_porcentaje', 10, null, 2, -10, 30],
    ['2x1 en Snacks', '2x1 en snacks seleccionados', '2x1', 0, null, 7, -5, 25],
    ['Promo Lácteos', '15% off en lácteos', 'descuento_porcentaje', 15, null, 3, 0, 30],
    ['Combo Familiar', '3x2 en abarrotes', '3x2', 0, null, 1, -3, 20],
    ['Liquidacion Limpieza', 'S/5 off productos limpieza', 'descuento_fijo', 5, null, 8, -7, 25],
    ['Frutas Frescas', '20% off frutas', 'descuento_porcentaje', 20, null, 5, 0, 14],
    ['Aceite Promo', 'Precio especial S/10', 'precio_especial', 10, 2, null, -15, 40],
    ['Pan Combo', '12% off panaderia', 'descuento_porcentaje', 12, null, 4, -10, 30],
    ['Cuidado Personal', '10% off shampoo y dental', 'descuento_porcentaje', 10, null, 9, 1, 30],
    ['Mega Descuento', '25% off productos seleccionados', 'descuento_porcentaje', 25, null, null, 7, 60],
    ['Cerveza Heladita', '15% off cervezas', 'descuento_porcentaje', 15, null, 2, -20, 5],
    ['Dia del Padre', 'Promo especial', 'descuento_porcentaje', 18, null, null, 15, 35],
];

$stmt = $pdo->prepare("INSERT INTO promociones (nombre, descripcion, tipo, valor, producto_id, categoria_id, fecha_inicio, fecha_fin, cantidad_minima, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW())");
foreach ($promos as $p) {
    $fechaInicio = (clone $now)->modify($p[6] . ' days')->format('Y-m-d');
    $fechaFin = (clone $now)->modify('+' . $p[7] . ' days')->format('Y-m-d');
    $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $fechaInicio, $fechaFin]);
}
r($resultados, "✓ 12 promociones creadas (mix vigentes, programadas, expiradas)");

// ============================================================
// 9) MOVIMIENTOS DE CAJA (15)
// ============================================================
$conceptosIngresos = ['Cobro pendiente', 'Recargo cliente VIP', 'Reembolso de proveedor', 'Venta pedido especial', 'Ingreso extra'];
$conceptosEgresos = ['Pago a proveedor', 'Compra de bolsas', 'Limpieza local', 'Pago servicio luz', 'Comida personal', 'Repuesto pos', 'Anticipo personal'];

$stmt = $pdo->prepare("INSERT INTO movimientos_caja (turno_caja_id, user_id, tipo, concepto, monto, created_at, updated_at) VALUES (?, 1, ?, ?, ?, ?, ?)");
for ($i = 0; $i < 15; $i++) {
    $turno = $turnoIds[array_rand($turnoIds)];
    $tipo = rand(0, 1) ? 'ingreso' : 'egreso';
    $concepto = $tipo === 'ingreso' ? $conceptosIngresos[array_rand($conceptosIngresos)] : $conceptosEgresos[array_rand($conceptosEgresos)];
    $monto = rand(20, 250);
    $fechaMov = (clone $turno['fecha'])->setTime(rand(9, 20), rand(0, 59), 0)->format('Y-m-d H:i:s');
    $stmt->execute([$turno['id'], $tipo, $concepto, $monto, $fechaMov, $fechaMov]);
}
r($resultados, "✓ 15 movimientos de caja registrados");

// ============================================================
// 10) Fechas de vencimiento variadas
// ============================================================
$prodsAleatorios = $pdo->query("SELECT id FROM productos ORDER BY RAND() LIMIT 12")->fetchAll(PDO::FETCH_COLUMN);
$diasVenc = [2, 5, 8, 15, 22, 35, 60, 90, 120, 180, 270, 365];
$stmt = $pdo->prepare("UPDATE productos SET fecha_vencimiento = ?, lote = ? WHERE id = ?");
foreach ($prodsAleatorios as $i => $pid) {
    $fechaVenc = (clone $now)->modify('+' . $diasVenc[$i] . ' days')->format('Y-m-d');
    $stmt->execute([$fechaVenc, 'L' . str_pad($i + 1, 4, '0', STR_PAD_LEFT), $pid]);
}
r($resultados, "✓ 12 productos con fechas de vencimiento (algunos criticos)");

// ============================================================
// 11) MOVIMIENTOS DE INVENTARIO extra (ajustes)
// ============================================================
$stmt = $pdo->prepare("INSERT INTO movimientos_inventario (producto_id, user_id, tipo, motivo, cantidad, stock_anterior, stock_nuevo, fecha, created_at, updated_at) VALUES (?, 1, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
for ($i = 0; $i < 10; $i++) {
    $p = $productos[array_rand($productos)];
    $stockActual = $pdo->query("SELECT stock FROM productos WHERE id = " . $p['id'])->fetchColumn();
    $tipo = ['ajuste','merma'][rand(0,1)];
    $cant = rand(1, 5);
    $motivos = ['Producto vencido', 'Mermado', 'Roto en bodega', 'Ajuste de inventario', 'Devolucion'];
    $motivo = $motivos[array_rand($motivos)];
    $stockNuevo = max(0, $stockActual - $cant);
    $fechaMov = (clone $now)->modify('-' . rand(1, 50) . ' days')->format('Y-m-d H:i:s');
    $stmt->execute([$p['id'], $tipo, $motivo, $cant, $stockActual, $stockNuevo, $fechaMov]);
    $pdo->prepare("UPDATE productos SET stock = ? WHERE id = ?")->execute([$stockNuevo, $p['id']]);
}
r($resultados, "✓ 10 movimientos de inventario extras (ajustes/mermas)");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Datos Demo Completos Cargados</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #064e3b, #10b981); min-height: 100vh; margin: 0; padding: 20px; display: flex; align-items: center; justify-content: center; }
.box { background: white; padding: 40px; border-radius: 20px; max-width: 700px; width: 100%; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }
h1 { color: #059669; margin-top: 0; }
.row { padding: 12px 16px; background: #ecfdf5; border-radius: 10px; margin-bottom: 8px; color: #065f46; font-weight: 500; border-left: 4px solid #10b981; font-size: 14px; }
.btn { display: inline-block; margin-top: 10px; background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 14px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-right: 10px; }
.btn-blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
.btn-pink { background: linear-gradient(135deg, #ec4899, #f472b6); }
.warn { background: #fef3c7; color: #92400e; padding: 15px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #f59e0b; }
.stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 20px; }
.stat { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); padding: 15px; border-radius: 10px; text-align: center; }
.stat strong { color: #059669; font-size: 22px; display: block; margin-bottom: 4px; }
</style>
</head>
<body>
<div class="box">
<h1>🎉 Datos Demo Completos Cargados</h1>
<p style="color:#64748b;">Base de datos poblada con datos realistas distribuidos en los últimos 60 días.</p>

<?php foreach ($resultados as $rr): ?>
    <div class="row"><?= htmlspecialchars($rr) ?></div>
<?php endforeach; ?>

<div class="stats">
    <?php
    $totalVentas = $pdo->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    $totalCompras = $pdo->query("SELECT COUNT(*) FROM compras")->fetchColumn();
    $sumaVentas = $pdo->query("SELECT SUM(total) FROM ventas")->fetchColumn() ?: 0;
    $totalClientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $totalProds = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    $totalProveedores = $pdo->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
    $totalPromos = $pdo->query("SELECT COUNT(*) FROM promociones")->fetchColumn();
    $totalTurnos = $pdo->query("SELECT COUNT(*) FROM turnos_caja")->fetchColumn();
    $totalMovs = $pdo->query("SELECT COUNT(*) FROM movimientos_inventario")->fetchColumn();
    ?>
    <div class="stat"><strong><?= $totalVentas ?></strong>Ventas</div>
    <div class="stat"><strong>S/ <?= number_format($sumaVentas, 0) ?></strong>Facturado</div>
    <div class="stat"><strong><?= $totalCompras ?></strong>Compras</div>
    <div class="stat"><strong><?= $totalClientes ?></strong>Clientes</div>
    <div class="stat"><strong><?= $totalProveedores ?></strong>Proveedores</div>
    <div class="stat"><strong><?= $totalProds ?></strong>Productos</div>
    <div class="stat"><strong><?= $totalPromos ?></strong>Promociones</div>
    <div class="stat"><strong><?= $totalTurnos ?></strong>Turnos caja</div>
    <div class="stat"><strong><?= $totalMovs ?></strong>Mov. inventario</div>
</div>

<div style="margin-top:25px;">
<a href="/dashboard" class="btn">→ Ver Dashboard</a>
<a href="/reportes/ventas" class="btn btn-blue">→ Reportes Ventas</a>
<a href="/ventas" class="btn btn-pink">→ Historial Ventas</a>
</div>

<div class="warn">
<strong>⚠️ Importante:</strong> Por seguridad, elimina este archivo después de usarlo:<br>
<code>public/seed-demo-completo.php</code>
</div>
</div>
</body>
</html>
