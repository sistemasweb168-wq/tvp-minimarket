<?php
/**
 * Script TEMPORAL para poblar la BD con datos demo.
 * Acceso: http://127.0.0.1:8023/seed-demo.php
 * ELIMINAR después de usar.
 */

set_time_limit(120);

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
$now = new DateTime();

function r(&$arr, $msg) { $arr[] = $msg; }

// ============================================================
// 1) CLIENTES (10)
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
];

$pdo->exec("DELETE FROM clientes");
$pdo->exec("ALTER TABLE clientes AUTO_INCREMENT = 1");

$stmt = $pdo->prepare("INSERT INTO clientes (codigo, tipo_documento, documento, nombres, apellidos, telefono, email, direccion, ciudad, genero, puntos_fidelidad, credito_limite, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
foreach ($clientes as $c) {
    $puntos = rand(0, 250);
    $credito = [0, 100, 200, 500, 1000][rand(0, 4)];
    $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8], $c[9], $puntos, $credito]);
}
r($resultados, "✓ 10 clientes creados");

// ============================================================
// 2) PROVEEDORES (10) - completar hasta 10
// ============================================================
$proveedores = [
    ['PR00005', 'Snacks Premium S.A.', '20500600700', 'Pedro Salinas', '987111222', 'ventas@snackspremium.com', 'Lima'],
    ['PR00006', 'Distribuidora Gloria SAC', '20600700800', 'Lucía Pacheco', '987333444', 'pedidos@gloria.com.pe', 'Lima'],
    ['PR00007', 'Importadora Limpieza Total', '20700800900', 'Miguel Castro', '987555666', 'ventas@limpiezatotal.com', 'Callao'],
    ['PR00008', 'Cervecería Backus', '20100100100', 'Sandra Velarde', '987777888', 'ventas@backus.pe', 'Lima'],
    ['PR00009', 'Procter & Gamble Perú', '20800900100', 'Diego Reyes', '987999000', 'pedidos@pg.pe', 'Lima'],
    ['PR00010', 'Alicorp Distribución', '20900100200', 'Karina Yupanqui', '988111222', 'ventas@alicorp.com.pe', 'Lima'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO proveedores (codigo, razon_social, ruc_nit, contacto, telefono, email, ciudad, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
foreach ($proveedores as $p) {
    $stmt->execute($p);
}
r($resultados, "✓ Proveedores completados (total ahora: " . $pdo->query("SELECT COUNT(*) FROM proveedores")->fetchColumn() . ")");

// ============================================================
// 3) TURNO DE CAJA (cerrado) para asociar las ventas
// ============================================================
$pdo->exec("DELETE FROM movimientos_caja");
$pdo->exec("DELETE FROM turnos_caja");
$pdo->exec("ALTER TABLE turnos_caja AUTO_INCREMENT = 1");

$fechaTurno = (clone $now)->modify('-30 days');
$stmt = $pdo->prepare("INSERT INTO turnos_caja (caja_id, user_id, fecha_apertura, fecha_cierre, monto_apertura, monto_cierre, monto_calculado, total_ventas, total_efectivo, total_tarjeta, total_otros, cantidad_ventas, estado, created_at, updated_at) VALUES (1, 1, ?, ?, 100.00, 0, 0, 0, 0, 0, 0, 0, 'cerrado', NOW(), NOW())");
$stmt->execute([$fechaTurno->format('Y-m-d 08:00:00'), $fechaTurno->format('Y-m-d 22:00:00')]);
$turnoId = $pdo->lastInsertId();
r($resultados, "✓ Turno de caja demo creado");

// ============================================================
// 4) VENTAS (10 distribuidas en últimos 30 días)
// ============================================================
$pdo->exec("DELETE FROM venta_detalles");
$pdo->exec("DELETE FROM ventas");
$pdo->exec("ALTER TABLE ventas AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE venta_detalles AUTO_INCREMENT = 1");

// Obtener productos disponibles
$productos = $pdo->query("SELECT id, codigo, nombre, precio_venta, stock FROM productos WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);
$clientes_ids = $pdo->query("SELECT id FROM clientes")->fetchAll(PDO::FETCH_COLUMN);
$users_ids = $pdo->query("SELECT id FROM users WHERE activo = 1")->fetchAll(PDO::FETCH_COLUMN);

// Generar ventas en distintas fechas
// 4 ventas hoy, 6 distribuidas en los últimos 7 días, 10 más distribuidas en los últimos 30 días
$fechas = [];
// Hoy (4 ventas)
for ($i = 0; $i < 4; $i++) {
    $h = rand(9, 21);
    $m = rand(0, 59);
    $fechas[] = (clone $now)->setTime($h, $m, 0);
}
// Últimos 7 días (excluyendo hoy)
for ($i = 1; $i <= 6; $i++) {
    $f = (clone $now)->modify("-$i days");
    $h = rand(9, 21);
    $m = rand(0, 59);
    $f->setTime($h, $m, 0);
    $fechas[] = $f;
}
// Distribuidas en 30 días
for ($i = 8; $i <= 30; $i += 3) {
    $f = (clone $now)->modify("-$i days");
    $h = rand(9, 21);
    $m = rand(0, 59);
    $f->setTime($h, $m, 0);
    $fechas[] = $f;
}

usort($fechas, fn($a, $b) => $a <=> $b);

$tasaImpuesto = 0.18;
$formasPago = ['efectivo', 'efectivo', 'efectivo', 'tarjeta', 'tarjeta', 'transferencia']; // más efectivo

$totalVentasAcum = 0;
$totalEfectivo = 0;
$totalTarjeta = 0;
$totalOtros = 0;
$cantidadVentas = 0;

$stmtVenta = $pdo->prepare("INSERT INTO ventas (numero_ticket, tipo_comprobante, serie, fecha_venta, cliente_id, user_id, turno_caja_id, subtotal, descuento, impuesto, total, monto_recibido, cambio, forma_pago, estado, created_at, updated_at) VALUES (?, 'TICKET', 'T001', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completada', ?, ?)");
$stmtDetalle = $pdo->prepare("INSERT INTO venta_detalles (venta_id, producto_id, codigo, descripcion, cantidad, precio_unitario, descuento, impuesto, subtotal, total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");
$stmtMovInv = $pdo->prepare("INSERT INTO movimientos_inventario (producto_id, user_id, tipo, motivo, cantidad, stock_anterior, stock_nuevo, referencia_tipo, referencia_id, fecha, created_at, updated_at) VALUES (?, ?, 'salida', ?, ?, ?, ?, 'venta', ?, ?, NOW(), NOW())");
$stmtUpdStock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

$contadorTicket = 1;
foreach ($fechas as $idx => $fecha) {
    // Decidir cuántos productos lleva esta venta
    $cantItems = rand(2, 6);
    shuffle($productos);
    $items = array_slice($productos, 0, $cantItems);

    $subtotal = 0;
    $impuestoTotal = 0;
    $detalles = [];

    foreach ($items as $p) {
        $cantidad = rand(1, 4);
        $precio = (float) $p['precio_venta'];
        $itemSubtotal = $cantidad * $precio;

        // Impuesto incluido (18%)
        $impuestoItem = $itemSubtotal - ($itemSubtotal / (1 + $tasaImpuesto));

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
        ];
    }

    $descuento = (rand(1, 10) <= 2) ? round(rand(1, 5), 2) : 0; // 20% prob de descuento
    $total = $subtotal + $impuestoTotal - $descuento;

    $formaPago = $formasPago[array_rand($formasPago)];
    $montoRecibido = $formaPago === 'efectivo' ? ceil($total / 5) * 5 : $total;
    $cambio = $montoRecibido - $total;

    // Cliente: 60% chance de tener cliente, 40% genérico
    $clienteId = (rand(1, 10) <= 6) ? $clientes_ids[array_rand($clientes_ids)] : null;
    $userId = $users_ids[array_rand($users_ids)];

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

        // Actualizar stock
        $stockActual = $pdo->query("SELECT stock FROM productos WHERE id = " . $d['producto_id'])->fetchColumn();
        $stmtUpdStock->execute([$d['cantidad'], $d['producto_id']]);

        // Movimiento de inventario
        $stmtMovInv->execute([
            $d['producto_id'], $userId,
            'Venta ' . $numeroTicket, $d['cantidad'],
            $stockActual, $stockActual - $d['cantidad'],
            $ventaId, $fechaStr
        ]);
    }

    $totalVentasAcum += $total;
    if ($formaPago === 'efectivo') $totalEfectivo += $total;
    elseif ($formaPago === 'tarjeta') $totalTarjeta += $total;
    else $totalOtros += $total;
    $cantidadVentas++;

    // Asignar puntos al cliente si aplica
    if ($clienteId) {
        $puntos = floor($total / 10);
        if ($puntos > 0) {
            $pdo->prepare("UPDATE clientes SET puntos_fidelidad = puntos_fidelidad + ? WHERE id = ?")->execute([$puntos, $clienteId]);
        }
    }
}
r($resultados, "✓ " . count($fechas) . " ventas creadas distribuidas en últimos 30 días");

// Actualizar totales del turno
$pdo->prepare("UPDATE turnos_caja SET total_ventas = ?, total_efectivo = ?, total_tarjeta = ?, total_otros = ?, cantidad_ventas = ?, monto_calculado = monto_apertura + total_efectivo, monto_cierre = monto_apertura + total_efectivo WHERE id = ?")
    ->execute([round($totalVentasAcum, 2), round($totalEfectivo, 2), round($totalTarjeta, 2), round($totalOtros, 2), $cantidadVentas, $turnoId]);

// ============================================================
// 5) COMPRAS (10 distribuidas en últimos 30 días)
// ============================================================
$pdo->exec("DELETE FROM compra_detalles");
$pdo->exec("DELETE FROM compras");
$pdo->exec("ALTER TABLE compras AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE compra_detalles AUTO_INCREMENT = 1");

$proveedores_ids = $pdo->query("SELECT id FROM proveedores WHERE activo = 1")->fetchAll(PDO::FETCH_COLUMN);

$stmtCompra = $pdo->prepare("INSERT INTO compras (numero, numero_factura, fecha_compra, proveedor_id, user_id, subtotal, descuento, impuesto, total, forma_pago, estado, created_at, updated_at) VALUES (?, ?, ?, ?, 1, ?, 0, 0, ?, ?, 'recibida', ?, ?)");
$stmtCompraDet = $pdo->prepare("INSERT INTO compra_detalles (compra_id, producto_id, codigo, descripcion, cantidad, precio_unitario, descuento, impuesto, subtotal, total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?)");

for ($i = 1; $i <= 10; $i++) {
    $diasAtras = rand(1, 30);
    $fechaCompra = (clone $now)->modify("-$diasAtras days")->setTime(rand(8, 17), 0, 0);
    $fechaStr = $fechaCompra->format('Y-m-d H:i:s');

    shuffle($productos);
    $items = array_slice($productos, 0, rand(3, 7));
    $subtotal = 0;
    $detalles = [];

    foreach ($items as $p) {
        $cantidad = rand(20, 100);
        $precio = (float) $p['precio_venta'] * 0.7; // precio compra estimado
        $tot = $cantidad * $precio;
        $subtotal += $tot;
        $detalles[] = [$p['id'], $p['codigo'], $p['nombre'], $cantidad, $precio, $tot];
    }

    $numeroCompra = 'C-' . str_pad($i, 8, '0', STR_PAD_LEFT);
    $factura = 'F' . rand(100, 999) . '-' . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT);
    $proveedorId = $proveedores_ids[array_rand($proveedores_ids)];
    $formaPago = ['efectivo', 'transferencia', 'credito'][rand(0, 2)];

    $stmtCompra->execute([$numeroCompra, $factura, $fechaStr, $proveedorId, round($subtotal, 2), round($subtotal, 2), $formaPago, $fechaStr, $fechaStr]);
    $compraId = $pdo->lastInsertId();

    foreach ($detalles as $d) {
        $stmtCompraDet->execute([$compraId, $d[0], $d[1], $d[2], $d[3], round($d[4], 2), round($d[5], 2), round($d[5], 2), $fechaStr, $fechaStr]);
        // Aumentar stock
        $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?")->execute([$d[3], $d[0]]);
    }
}
r($resultados, "✓ 10 compras creadas distribuidas en últimos 30 días");

// ============================================================
// 6) PROMOCIONES (10)
// ============================================================
$pdo->exec("DELETE FROM promociones");
$pdo->exec("ALTER TABLE promociones AUTO_INCREMENT = 1");

$promos = [
    ['Descuento Verano',         'Descuento del 10% en bebidas',    'descuento_porcentaje', 10, null, 2,  -5,  25, 1],
    ['2x1 en Snacks',            '2x1 en todos los snacks',         '2x1',                  0,  null, 7,  -2,  30, 2],
    ['Promo Lácteos',            '15% off en lácteos',              'descuento_porcentaje', 15, null, 3,  0,   20, 1],
    ['Combo Familiar',           'Compra 3 unidades y paga 2',      '3x2',                  0,  null, 1,  -1,  15, 1],
    ['Liquidación Limpieza',     'S/ 5 de descuento en limpieza',   'descuento_fijo',       5,  null, 8,  -3,  20, 1],
    ['Frutas Frescas',           '20% off frutas y verduras',       'descuento_porcentaje', 20, null, 5,  0,   10, 1],
    ['Aceite Promocional',       'Precio especial S/ 10',           'precio_especial',      10, 2,    null, -10, 30, 1],
    ['Pan Combo',                'Descuento panadería',             'descuento_porcentaje', 12, null, 4,  -7,  30, 1],
    ['Cuidado Personal',         '10% off cuidado personal',        'descuento_porcentaje', 10, null, 9,  0,   25, 1],
    ['Mega Descuento',           '25% off productos seleccionados', 'descuento_porcentaje', 25, null, null, 5,   60, 1],
];

$stmt = $pdo->prepare("INSERT INTO promociones (nombre, descripcion, tipo, valor, producto_id, categoria_id, fecha_inicio, fecha_fin, cantidad_minima, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
foreach ($promos as $p) {
    $fechaInicio = (clone $now)->modify($p[6] . ' days')->format('Y-m-d');
    $fechaFin = (clone $now)->modify('+' . $p[7] . ' days')->format('Y-m-d');
    $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $fechaInicio, $fechaFin, 1, $p[8]]);
}
r($resultados, "✓ 10 promociones creadas (algunas vigentes, otras programadas)");

// ============================================================
// 7) Movimientos de caja
// ============================================================
$conceptosIngresos = ['Cobro pendiente', 'Recargo cliente VIP', 'Reembolso de proveedor'];
$conceptosEgresos = ['Pago a proveedor', 'Compra de bolsas', 'Limpieza local', 'Pago servicio luz', 'Comida personal'];

$stmt = $pdo->prepare("INSERT INTO movimientos_caja (turno_caja_id, user_id, tipo, concepto, monto, created_at, updated_at) VALUES (?, 1, ?, ?, ?, ?, ?)");

for ($i = 0; $i < 5; $i++) {
    $tipo = rand(0, 1) ? 'ingreso' : 'egreso';
    $concepto = $tipo === 'ingreso' ? $conceptosIngresos[array_rand($conceptosIngresos)] : $conceptosEgresos[array_rand($conceptosEgresos)];
    $monto = rand(20, 200);
    $fecha = (clone $now)->modify('-' . rand(1, 30) . ' days')->format('Y-m-d H:i:s');
    $stmt->execute([$turnoId, $tipo, $concepto, $monto, $fecha, $fecha]);
}
r($resultados, "✓ 5 movimientos de caja registrados");

// ============================================================
// 8) Algunas fechas de vencimiento en productos
// ============================================================
$prodsAleatorios = $pdo->query("SELECT id FROM productos ORDER BY RAND() LIMIT 8")->fetchAll(PDO::FETCH_COLUMN);
$diasVenc = [3, 10, 15, 20, 35, 45, 90, 180]; // algunos críticos, otros lejos
$stmt = $pdo->prepare("UPDATE productos SET fecha_vencimiento = ?, lote = ? WHERE id = ?");
foreach ($prodsAleatorios as $i => $pid) {
    $fechaVenc = (clone $now)->modify('+' . $diasVenc[$i] . ' days')->format('Y-m-d');
    $stmt->execute([$fechaVenc, 'L' . str_pad($i + 1, 4, '0', STR_PAD_LEFT), $pid]);
}
r($resultados, "✓ Fechas de vencimiento agregadas a 8 productos");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Datos Demo Cargados</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #064e3b, #10b981); min-height: 100vh; margin: 0; padding: 20px; display: flex; align-items: center; justify-content: center; }
.box { background: white; padding: 40px; border-radius: 20px; max-width: 600px; width: 100%; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }
h1 { color: #059669; margin-top: 0; }
.row { padding: 12px 16px; background: #ecfdf5; border-radius: 10px; margin-bottom: 8px; color: #065f46; font-weight: 500; border-left: 4px solid #10b981; }
.btn { display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 14px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-right: 10px; }
.btn-blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
.warn { background: #fef3c7; color: #92400e; padding: 15px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #f59e0b; }
.stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
.stat { background: #f0fdf4; padding: 15px; border-radius: 10px; }
.stat strong { color: #059669; font-size: 24px; display: block; }
</style>
</head>
<body>
<div class="box">
<h1>🎉 Datos Demo Cargados</h1>
<p style="color:#64748b;">La base de datos ha sido poblada con datos realistas distribuidos en los últimos 30 días.</p>

<?php foreach ($resultados as $r): ?>
    <div class="row"><?= htmlspecialchars($r) ?></div>
<?php endforeach; ?>

<div class="stats">
    <?php
    $totalVentas = $pdo->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    $totalCompras = $pdo->query("SELECT COUNT(*) FROM compras")->fetchColumn();
    $sumaVentas = $pdo->query("SELECT SUM(total) FROM ventas")->fetchColumn() ?: 0;
    $totalProductos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    ?>
    <div class="stat"><strong><?= $totalVentas ?></strong>Ventas registradas</div>
    <div class="stat"><strong>S/ <?= number_format($sumaVentas, 2) ?></strong>Total facturado</div>
    <div class="stat"><strong><?= $totalCompras ?></strong>Compras registradas</div>
    <div class="stat"><strong><?= $totalProductos ?></strong>Productos en stock</div>
</div>

<div style="margin-top:25px;">
<a href="/dashboard" class="btn">→ Ver Dashboard</a>
<a href="/reportes/ventas" class="btn btn-blue">→ Ver Reportes</a>
</div>

<div class="warn">
<strong>⚠️ Importante:</strong> Por seguridad, elimina este archivo después de usarlo:<br>
<code>public/seed-demo.php</code>
</div>
</div>
</body>
</html>
