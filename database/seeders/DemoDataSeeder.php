<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\{
    Role, User, Categoria, Proveedor, Producto, Cliente, Caja,
    TurnoCaja, MovimientoCaja, Venta, VentaDetalle, Compra, CompraDetalle,
    MovimientoInventario, Promocion, PuntoFidelidad, Backup
};

/**
 * Seeder de datos demo para alimentar el Dashboard con información
 * en diferentes fechas a fin de visualizar paneles y gráficos.
 *
 * Ejecutar con:  php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Iniciando DemoDataSeeder ===');

        DB::transaction(function () {
            $this->seedCategorias();
            $this->seedProveedores();
            $this->seedProductos();
            $this->seedClientes();
            $this->seedUsuarios();
            $this->seedCajas();
            $this->seedTurnosCaja();
            $this->seedMovimientosCaja();
            $this->seedCompras();
            $this->seedVentas();
            $this->seedMovimientosInventario();
            $this->seedPromociones();
            $this->seedPuntosFidelidad();
            $this->seedBackups();
        });

        $this->command->info('=== DemoDataSeeder finalizado correctamente ===');
    }

    /* ============================================================
     * 1. CATEGORÍAS (10 nuevas)
     * ============================================================ */
    private function seedCategorias(): void
    {
        $nuevas = [
            ['Embutidos',          '#b91c1c', 'bacon',         'Jamones, salchichas y embutidos'],
            ['Congelados',         '#0ea5e9', 'snowflake',     'Productos congelados'],
            ['Confitería',         '#f97316', 'candy-cane',    'Caramelos y dulces'],
            ['Licores',            '#7c2d12', 'glass-cheers',  'Vinos y licores'],
            ['Cereales',           '#ca8a04', 'wheat-awn',     'Cereales para desayuno'],
            ['Conservas',          '#475569', 'jar',           'Productos enlatados'],
            ['Útiles Escolares',   '#2563eb', 'pencil-alt',    'Material escolar'],
            ['Ferretería Básica',  '#71717a', 'hammer',        'Artículos de ferretería'],
            ['Cuidado del Hogar',  '#65a30d', 'home',          'Productos para el hogar'],
            ['Salud',              '#059669', 'first-aid',     'Productos farmacéuticos OTC'],
        ];

        $i = 0;
        foreach ($nuevas as $c) {
            $fecha = Carbon::now()->subDays(55 - $i * 5);
            Categoria::create([
                'nombre'      => $c[0],
                'color'       => $c[1],
                'icono'       => $c[2],
                'descripcion' => $c[3],
                'activo'      => true,
                'created_at'  => $fecha,
                'updated_at'  => $fecha,
            ]);
            $i++;
        }
        $this->command->info('  ✓ 10 categorías agregadas');
    }

    /* ============================================================
     * 2. PROVEEDORES (10 nuevos)
     * ============================================================ */
    private function seedProveedores(): void
    {
        $existentes = Proveedor::count();
        $proveedores = [
            ['Frigoríficos del Sur SAC',     '20500600700', 'Pedro Salinas',     '987-111-222', 'ventas@frigosur.com',     'Arequipa'],
            ['Congelados Andinos EIRL',      '20500600701', 'Lucía Mendoza',     '987-111-223', 'pedidos@congandinos.pe',  'Cusco'],
            ['Dulcería Nacional SA',         '20500600702', 'Roberto Vega',      '987-111-224', 'rvega@dulcerianac.com',   'Lima'],
            ['Importadora de Licores Perú',  '20500600703', 'Sandra Quispe',     '987-111-225', 'sq@licoresperu.com',      'Lima'],
            ['Cereales y Más SAC',           '20500600704', 'Diego Apaza',       '987-111-226', 'diego@cerealesymas.com',  'Junín'],
            ['Distribuidora Limpia Hogar',   '20500600705', 'Patricia Lazo',     '987-111-227', 'plazo@limpiahogar.pe',    'Lima'],
            ['Útiles del Norte SA',          '20500600706', 'Jorge Castañeda',   '987-111-228', 'jcaste@utilesnorte.com',  'Trujillo'],
            ['Ferretería Mayorista Lima',    '20500600707', 'Ricardo Fonseca',   '987-111-229', 'rfonseca@fmlmlima.pe',    'Lima'],
            ['Farmadrogas Comercial SAC',    '20500600708', 'Elena Rodríguez',   '987-111-230', 'elena@farmadrogas.com',   'Lima'],
            ['Distribuidora Selva Verde',    '20500600709', 'Manuel Ipanaqué',   '987-111-231', 'manuel@selvaverde.pe',    'Iquitos'],
        ];

        $base = $existentes + 1;
        foreach ($proveedores as $idx => $p) {
            $fecha = Carbon::now()->subDays(50 - $idx * 4);
            Proveedor::create([
                'codigo'        => 'PR' . str_pad($base + $idx, 5, '0', STR_PAD_LEFT),
                'razon_social'  => $p[0],
                'ruc_nit'       => $p[1],
                'contacto'      => $p[2],
                'telefono'      => $p[3],
                'email'         => $p[4],
                'ciudad'        => $p[5],
                'direccion'     => 'Av. Comercial '. (100 + $idx) .', '. $p[5],
                'activo'        => true,
                'created_at'    => $fecha,
                'updated_at'    => $fecha,
            ]);
        }
        $this->command->info('  ✓ 10 proveedores agregados');
    }

    /* ============================================================
     * 3. PRODUCTOS (10 nuevos)
     * ============================================================ */
    private function seedProductos(): void
    {
        // Usar categorías y proveedores aleatorios
        $cats = Categoria::pluck('id')->all();
        $provs = Proveedor::pluck('id')->all();
        $existentes = Producto::count();

        $productos = [
            // [nombre, precio_compra, precio_venta, unidad, stock, stock_min]
            ['Jamón Inglés 200g',          8.50, 12.50, 'UND', 25,  5],
            ['Pollo Congelado 1kg',        9.20, 13.90, 'KG',  18,  4],
            ['Caramelos Surtidos x100',    4.50, 7.50,  'PAQ', 40,  8],
            ['Vino Tinto Tabernero 750ml', 18.50,26.90, 'UND', 12,  3],
            ['Cereal Ángel Choco 500g',    8.90, 12.50, 'UND', 22,  5],
            ['Atún Florida en Aceite',     4.20, 5.90,  'UND', 60, 12],
            ['Cuaderno Cuadriculado A4',   3.50, 5.50,  'UND', 80, 15],
            ['Foco LED 9W',                4.50, 7.90,  'UND', 35,  8],
            ['Pastillas Paracetamol x10',  2.80, 4.50,  'CAJA',50, 10],
            ['Escoba Plástica',            8.50, 13.50, 'UND', 20,  4],
        ];

        $i = 0;
        foreach ($productos as $p) {
            $num = $existentes + 1 + $i;
            $fecha = Carbon::now()->subDays(45 - $i * 4);
            // Stock bajo intencional para algunos
            $stock = ($i % 4 == 0) ? max(1, $p[5] - 2) : $p[4];

            Producto::create([
                'codigo'           => 'P' . str_pad($num, 6, '0', STR_PAD_LEFT),
                'codigo_barras'    => '7501' . str_pad($num, 9, '0', STR_PAD_LEFT),
                'nombre'           => $p[0],
                'categoria_id'     => $cats[array_rand($cats)],
                'proveedor_id'     => $provs[array_rand($provs)],
                'precio_compra'    => $p[1],
                'precio_venta'     => $p[2],
                'precio_mayoreo'   => round($p[2] * 0.92, 2),
                'cantidad_mayoreo' => 12,
                'unidad_medida'    => $p[3],
                'stock'            => $stock,
                'stock_minimo'     => $p[5],
                'stock_maximo'     => $p[4] * 2,
                'controla_stock'   => true,
                'aplica_impuesto'  => true,
                'activo'           => true,
                'destacado'        => ($i < 3),
                // Para gráfico productos por vencer: algunos vencen pronto
                'fecha_vencimiento'=> ($i % 3 == 0) ? Carbon::now()->addDays(15 + $i)->toDateString() : null,
                'created_at'       => $fecha,
                'updated_at'       => $fecha,
            ]);
            $i++;
        }
        $this->command->info('  ✓ 10 productos agregados');
    }

    /* ============================================================
     * 4. CLIENTES (10 nuevos)
     * ============================================================ */
    private function seedClientes(): void
    {
        $existentes = Cliente::count();
        $clientes = [
            ['DNI', '72345671', 'María',     'Quispe Huamán',   '987-001-001', 'maria.quispe@gmail.com',  'F', 'Lima'],
            ['DNI', '72345672', 'José',      'Ramírez Soto',    '987-001-002', 'jramirez@hotmail.com',    'M', 'Lima'],
            ['DNI', '72345673', 'Ana',       'Vargas Torres',   '987-001-003', 'ana.vargas@gmail.com',    'F', 'Callao'],
            ['DNI', '72345674', 'Luis',      'Mamani Apaza',    '987-001-004', 'luismamani@yahoo.com',    'M', 'Arequipa'],
            ['DNI', '72345675', 'Carmen',    'Flores Salinas',  '987-001-005', 'carmenfs@gmail.com',      'F', 'Lima'],
            ['DNI', '72345676', 'Roberto',   'Chávez Pinto',    '987-001-006', 'rchavez@outlook.com',     'M', 'Trujillo'],
            ['RUC', '20100777888', 'Empresa', 'Comercial Sol',  '01-222-3344', 'compras@solperu.com',     null, 'Lima'],
            ['DNI', '72345678', 'Patricia',  'Mendoza Ruiz',    '987-001-008', 'patyM@gmail.com',         'F', 'Cusco'],
            ['DNI', '72345679', 'Eduardo',   'Sánchez León',    '987-001-009', 'eduslv@gmail.com',        'M', 'Lima'],
            ['DNI', '72345680', 'Sofía',     'Paredes Rivas',   '987-001-010', 'sofiapr@gmail.com',       'F', 'Lima'],
        ];

        $i = 0;
        foreach ($clientes as $c) {
            $num = $existentes + 1 + $i;
            $fecha = Carbon::now()->subDays(50 - $i * 4);
            Cliente::create([
                'codigo'            => 'C' . str_pad($num, 6, '0', STR_PAD_LEFT),
                'tipo_documento'    => $c[0],
                'documento'         => $c[1],
                'nombres'           => $c[2],
                'apellidos'         => $c[3],
                'razon_social'      => $c[0] === 'RUC' ? $c[2] . ' ' . $c[3] : null,
                'telefono'          => $c[4],
                'email'             => $c[5],
                'genero'            => $c[6],
                'ciudad'            => $c[7],
                'direccion'         => 'Calle '. (10 + $i) .' #'. (100 + $i * 5),
                'fecha_nacimiento'  => Carbon::now()->subYears(20 + $i)->subDays(rand(0, 365))->toDateString(),
                'puntos_fidelidad'  => rand(10, 250),
                'credito_limite'    => ($i % 3 == 0) ? 500 : 0,
                'activo'            => true,
                'created_at'        => $fecha,
                'updated_at'        => $fecha,
            ]);
            $i++;
        }
        $this->command->info('  ✓ 10 clientes agregados');
    }

    /* ============================================================
     * 5. USUARIOS (10 nuevos)
     * ============================================================ */
    private function seedUsuarios(): void
    {
        $roles = Role::pluck('id', 'nombre')->all();
        if (empty($roles)) {
            $this->command->warn('  ! No hay roles, omitiendo usuarios');
            return;
        }
        $rolesArr = array_values($roles);

        $usuarios = [
            ['Carlos Méndez',  'cmendez',  'carlos.mendez@tpv.com'],
            ['Lucía Torres',   'ltorres',  'lucia.torres@tpv.com'],
            ['Miguel Salas',   'msalas',   'miguel.salas@tpv.com'],
            ['Rosa Huamán',    'rhuaman',  'rosa.huaman@tpv.com'],
            ['Jorge Quispe',   'jquispe',  'jorge.quispe@tpv.com'],
            ['Elena Rivas',    'erivas',   'elena.rivas@tpv.com'],
            ['Daniel Vásquez', 'dvasquez', 'daniel.vasquez@tpv.com'],
            ['Isabel Cárdenas','icardenas','isabel.cardenas@tpv.com'],
            ['Andrés Pérez',   'aperez',   'andres.perez@tpv.com'],
            ['Verónica Soto',  'vsoto',    'veronica.soto@tpv.com'],
        ];

        foreach ($usuarios as $i => $u) {
            $fecha = Carbon::now()->subDays(60 - $i * 5);
            User::create([
                'name'       => $u[0],
                'username'   => $u[1],
                'email'      => $u[2],
                'password'   => Hash::make('demo1234'),
                'role_id'    => $rolesArr[array_rand($rolesArr)],
                'telefono'   => '987-100-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'activo'     => true,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
        }
        $this->command->info('  ✓ 10 usuarios agregados');
    }

    /* ============================================================
     * 6. CAJAS (10 turnos, dejamos las cajas en mínimo)
     * ============================================================ */
    private function seedCajas(): void
    {
        // Asegurar al menos 3 cajas físicas para distribuir
        $count = Caja::count();
        for ($i = $count + 1; $i <= 4; $i++) {
            Caja::create([
                'nombre'      => "Caja {$i}",
                'descripcion' => "Caja secundaria N° {$i}",
                'activo'      => true,
            ]);
        }
        $this->command->info('  ✓ Cajas verificadas');
    }

    /* ============================================================
     * 7. TURNOS DE CAJA (10)
     * ============================================================ */
    private function seedTurnosCaja(): void
    {
        $cajas = Caja::pluck('id')->all();
        $users = User::pluck('id')->all();

        for ($i = 0; $i < 10; $i++) {
            $apertura = Carbon::now()->subDays(45 - $i * 4)->setTime(8, rand(0, 30));
            $cierre   = $apertura->copy()->setTime(20, rand(0, 59));
            $totalVentas = rand(150, 850);

            TurnoCaja::create([
                'caja_id'         => $cajas[array_rand($cajas)],
                'user_id'         => $users[array_rand($users)],
                'fecha_apertura'  => $apertura,
                'fecha_cierre'    => $cierre,
                'monto_apertura'  => 100,
                'monto_cierre'    => 100 + $totalVentas - rand(-5, 5),
                'monto_calculado' => 100 + $totalVentas,
                'diferencia'      => rand(-5, 5),
                'total_ventas'    => $totalVentas,
                'total_efectivo'  => $totalVentas * 0.7,
                'total_tarjeta'   => $totalVentas * 0.2,
                'total_otros'     => $totalVentas * 0.1,
                'cantidad_ventas' => rand(5, 25),
                'estado'          => 'cerrado',
                'created_at'      => $apertura,
                'updated_at'      => $cierre,
            ]);
        }
        $this->command->info('  ✓ 10 turnos de caja agregados');
    }

    /* ============================================================
     * 8. MOVIMIENTOS DE CAJA (10)
     * ============================================================ */
    private function seedMovimientosCaja(): void
    {
        $turnos = TurnoCaja::pluck('id')->all();
        $users  = User::pluck('id')->all();
        if (empty($turnos)) return;

        $conceptos = [
            ['ingreso', 'Fondo inicial adicional'],
            ['egreso',  'Compra de útiles oficina'],
            ['ingreso', 'Cobro extra contado'],
            ['egreso',  'Pago de delivery'],
            ['egreso',  'Compra de bolsas'],
            ['ingreso', 'Devolución de cliente'],
            ['egreso',  'Pago de servicio agua'],
            ['ingreso', 'Préstamo socio'],
            ['egreso',  'Combustible'],
            ['egreso',  'Pago publicidad volantes'],
        ];

        foreach ($conceptos as $i => $c) {
            $fecha = Carbon::now()->subDays(40 - $i * 4)->setTime(rand(9, 19), rand(0, 59));
            MovimientoCaja::create([
                'turno_caja_id' => $turnos[array_rand($turnos)],
                'user_id'       => $users[array_rand($users)],
                'tipo'          => $c[0],
                'concepto'      => $c[1],
                'monto'         => rand(20, 250),
                'observaciones' => 'Registro demo #' . ($i + 1),
                'created_at'    => $fecha,
                'updated_at'    => $fecha,
            ]);
        }
        $this->command->info('  ✓ 10 movimientos de caja agregados');
    }

    /* ============================================================
     * 9. COMPRAS (10)
     * ============================================================ */
    private function seedCompras(): void
    {
        $provs    = Proveedor::pluck('id')->all();
        $users    = User::pluck('id')->all();
        $productos = Producto::all();
        $existentes = Compra::count();

        for ($i = 0; $i < 10; $i++) {
            $fecha = Carbon::now()->subDays(50 - $i * 5)->setTime(rand(8, 18), rand(0, 59));
            $subtotal = 0;
            $items = [];
            $cantItems = rand(2, 5);
            $usados = [];
            for ($j = 0; $j < $cantItems; $j++) {
                $prod = $productos->random();
                if (in_array($prod->id, $usados)) continue;
                $usados[] = $prod->id;
                $cant  = rand(5, 20);
                $precio = (float) $prod->precio_compra;
                $total  = round($cant * $precio, 2);
                $subtotal += $total;
                $items[] = [
                    'producto_id'     => $prod->id,
                    'codigo'          => $prod->codigo,
                    'descripcion'     => $prod->nombre,
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'subtotal'        => $total,
                    'total'           => $total,
                ];
            }
            $impuesto = round($subtotal * 0.18, 2);
            $totalCompra = round($subtotal + $impuesto, 2);

            $compra = Compra::create([
                'numero'          => 'C' . str_pad($existentes + $i + 1, 6, '0', STR_PAD_LEFT),
                'numero_factura'  => 'F-2026-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'fecha_compra'    => $fecha,
                'proveedor_id'    => $provs[array_rand($provs)],
                'user_id'         => $users[array_rand($users)],
                'subtotal'        => $subtotal,
                'descuento'       => 0,
                'impuesto'        => $impuesto,
                'total'           => $totalCompra,
                'forma_pago'      => ['efectivo','transferencia','credito'][array_rand([0,1,2])],
                'estado'          => 'recibida',
                'observaciones'   => 'Compra demo registrada',
                'created_at'      => $fecha,
                'updated_at'      => $fecha,
            ]);

            foreach ($items as $it) {
                CompraDetalle::create(array_merge($it, [
                    'compra_id' => $compra->id,
                    'created_at'=> $fecha,
                    'updated_at'=> $fecha,
                ]));
            }
        }
        $this->command->info('  ✓ 10 compras agregadas');
    }

    /* ============================================================
     * 10. VENTAS (Más de 10, distribuidas, para gráficos)
     *    Se generan ventas en últimos 30 días para alimentar:
     *      - Ventas hoy
     *      - Ventas por día (últimos 7)
     *      - Ventas por día de la semana
     *      - Ventas por categoría
     *      - Formas de pago
     *      - Top clientes / Top productos
     * ============================================================ */
    private function seedVentas(): void
    {
        $users    = User::pluck('id')->all();
        $clientes = Cliente::pluck('id')->all();
        $turnos   = TurnoCaja::pluck('id')->all();
        $productos = Producto::all();
        $formas   = ['efectivo','tarjeta','transferencia','efectivo','efectivo','tarjeta'];
        $existentes = Venta::count();

        // 10 ventas explícitamente distribuidas (1 por día reciente) + extras
        // El requerimiento mínimo es 10, pero para que los gráficos sean ricos
        // generamos 10 distintas con diferentes fechas.
        $fechasDistribuidas = [
            Carbon::now()->setTime(10, 15),                  // hoy
            Carbon::now()->setTime(15, 40),                  // hoy
            Carbon::now()->subDay()->setTime(11, 25),        // ayer
            Carbon::now()->subDays(2)->setTime(17, 10),
            Carbon::now()->subDays(3)->setTime(9, 30),
            Carbon::now()->subDays(4)->setTime(13, 55),
            Carbon::now()->subDays(5)->setTime(18, 20),
            Carbon::now()->subDays(7)->setTime(10, 45),
            Carbon::now()->subDays(10)->setTime(14, 30),
            Carbon::now()->subDays(15)->setTime(16, 0),
            Carbon::now()->subDays(20)->setTime(12, 15),
            Carbon::now()->subDays(25)->setTime(11, 30),
        ];

        foreach ($fechasDistribuidas as $i => $fecha) {
            $num = $existentes + $i + 1;
            $cliente = $clientes[array_rand($clientes)];
            $user    = $users[array_rand($users)];
            $turno   = !empty($turnos) ? $turnos[array_rand($turnos)] : null;
            $forma   = $formas[array_rand($formas)];

            $cantItems = rand(2, 5);
            $items = [];
            $subtotal = 0;
            $usados = [];
            for ($j = 0; $j < $cantItems; $j++) {
                $prod = $productos->random();
                if (in_array($prod->id, $usados)) continue;
                $usados[] = $prod->id;
                $cant = rand(1, 4);
                $precio = (float) $prod->precio_venta;
                $tot = round($cant * $precio, 2);
                $subtotal += $tot;
                $items[] = [
                    'producto_id'    => $prod->id,
                    'codigo'         => $prod->codigo,
                    'descripcion'    => $prod->nombre,
                    'cantidad'       => $cant,
                    'precio_unitario'=> $precio,
                    'descuento'      => 0,
                    'impuesto'       => 0,
                    'subtotal'       => $tot,
                    'total'          => $tot,
                ];
            }
            $impuesto = round($subtotal * 0.18 / 1.18, 2); // impuesto incluido
            $total    = round($subtotal, 2);

            $venta = Venta::create([
                'numero_ticket'   => 'T001-' . str_pad($num, 6, '0', STR_PAD_LEFT),
                'tipo_comprobante'=> 'TICKET',
                'serie'           => 'T001',
                'fecha_venta'     => $fecha,
                'cliente_id'      => $cliente,
                'user_id'         => $user,
                'turno_caja_id'   => $turno,
                'subtotal'        => round($subtotal - $impuesto, 2),
                'descuento'       => 0,
                'impuesto'        => $impuesto,
                'total'           => $total,
                'monto_recibido'  => $forma === 'efectivo' ? ceil($total / 10) * 10 : $total,
                'cambio'          => $forma === 'efectivo' ? round(ceil($total / 10) * 10 - $total, 2) : 0,
                'forma_pago'      => $forma,
                'estado'          => 'completada',
                'created_at'      => $fecha,
                'updated_at'      => $fecha,
            ]);

            foreach ($items as $it) {
                VentaDetalle::create(array_merge($it, [
                    'venta_id'   => $venta->id,
                    'created_at' => $fecha,
                    'updated_at' => $fecha,
                ]));
            }
        }
        $this->command->info('  ✓ ' . count($fechasDistribuidas) . ' ventas agregadas');
    }

    /* ============================================================
     * 11. MOVIMIENTOS DE INVENTARIO (10)
     * ============================================================ */
    private function seedMovimientosInventario(): void
    {
        $productos = Producto::all();
        $users     = User::pluck('id')->all();
        $tipos     = ['entrada','salida','ajuste','merma','entrada','salida','ajuste','entrada','salida','merma'];
        $motivos   = [
            'Recepción de compra', 'Venta directa', 'Conteo físico',
            'Producto vencido', 'Reposición de stock', 'Salida por venta',
            'Ajuste manual', 'Devolución a proveedor', 'Transferencia interna',
            'Daño en producto'
        ];

        foreach ($tipos as $i => $tipo) {
            $prod = $productos->random();
            $cantidad = rand(2, 15);
            $stockAnt = (float) $prod->stock;
            $stockNuevo = in_array($tipo, ['entrada','ajuste']) ? $stockAnt + $cantidad : max(0, $stockAnt - $cantidad);
            $fecha = Carbon::now()->subDays(40 - $i * 4)->setTime(rand(8, 18), rand(0, 59));

            MovimientoInventario::create([
                'producto_id'   => $prod->id,
                'user_id'       => $users[array_rand($users)],
                'tipo'          => $tipo,
                'motivo'        => $motivos[$i],
                'cantidad'      => $cantidad,
                'stock_anterior'=> $stockAnt,
                'stock_nuevo'   => $stockNuevo,
                'referencia_tipo'=> null,
                'observaciones' => 'Movimiento demo #' . ($i + 1),
                'fecha'         => $fecha,
                'created_at'    => $fecha,
                'updated_at'    => $fecha,
            ]);
        }
        $this->command->info('  ✓ 10 movimientos de inventario agregados');
    }

    /* ============================================================
     * 12. PROMOCIONES (10)
     * ============================================================ */
    private function seedPromociones(): void
    {
        $productos = Producto::pluck('id')->all();
        $cats      = Categoria::pluck('id')->all();

        $promos = [
            ['Descuento 10% Abarrotes',    'descuento_porcentaje', 10, 'cat'],
            ['Combo Bebidas 2x1',          '2x1',                  0,  'cat'],
            ['Oferta Lácteos -15%',        'descuento_porcentaje', 15, 'cat'],
            ['Liquidación Snacks S/2',     'descuento_fijo',       2,  'prod'],
            ['Promo Limpieza 3x2',         '3x2',                  0,  'cat'],
            ['Cuidado Personal -20%',      'descuento_porcentaje', 20, 'cat'],
            ['Precio Especial Cerveza',    'precio_especial',      5.50, 'prod'],
            ['Frutas y Verduras -10%',     'descuento_porcentaje', 10, 'cat'],
            ['Pan a S/0.30',               'precio_especial',      0.30,'prod'],
            ['Mega Combo Mes Aniversario', 'descuento_porcentaje', 25, 'cat'],
        ];

        foreach ($promos as $i => $p) {
            $inicio = Carbon::now()->subDays(40 - $i * 4);
            $fin    = $inicio->copy()->addDays(rand(15, 40));
            Promocion::create([
                'nombre'         => $p[0],
                'descripcion'    => 'Promoción demo: ' . $p[0],
                'tipo'           => $p[1],
                'valor'          => $p[2],
                'producto_id'    => $p[3] === 'prod' ? $productos[array_rand($productos)] : null,
                'categoria_id'   => $p[3] === 'cat'  ? $cats[array_rand($cats)] : null,
                'fecha_inicio'   => $inicio->toDateString(),
                'fecha_fin'      => $fin->toDateString(),
                'cantidad_minima'=> 1,
                'activo'         => true,
                'created_at'     => $inicio,
                'updated_at'     => $inicio,
            ]);
        }
        $this->command->info('  ✓ 10 promociones agregadas');
    }

    /* ============================================================
     * 13. PUNTOS DE FIDELIDAD (10)
     * ============================================================ */
    private function seedPuntosFidelidad(): void
    {
        $clientes = Cliente::all();
        $ventas   = Venta::pluck('id')->all();
        if ($clientes->isEmpty()) return;

        $tipos = ['ganado','ganado','ganado','canjeado','ganado','canjeado','ganado','ajuste','ganado','ganado'];

        foreach ($tipos as $i => $tipo) {
            $cli = $clientes->random();
            $pts = rand(5, 50);
            $ant = (int) $cli->puntos_fidelidad;
            $nuevo = $tipo === 'ganado' ? $ant + $pts : max(0, $ant - $pts);
            $fecha = Carbon::now()->subDays(35 - $i * 3)->setTime(rand(9, 19), rand(0, 59));

            PuntoFidelidad::create([
                'cliente_id'    => $cli->id,
                'venta_id'      => !empty($ventas) ? $ventas[array_rand($ventas)] : null,
                'tipo'          => $tipo,
                'puntos'        => $pts,
                'saldo_anterior'=> $ant,
                'saldo_nuevo'   => $nuevo,
                'descripcion'   => $tipo === 'ganado' ? 'Puntos por compra' : ($tipo === 'canjeado' ? 'Canje de puntos' : 'Ajuste manual'),
                'created_at'    => $fecha,
                'updated_at'    => $fecha,
            ]);
        }
        $this->command->info('  ✓ 10 registros de fidelidad agregados');
    }

    /* ============================================================
     * 14. BACKUPS (10)
     * ============================================================ */
    private function seedBackups(): void
    {
        $users = User::pluck('id')->all();
        for ($i = 0; $i < 10; $i++) {
            $fecha = Carbon::now()->subDays(45 - $i * 4)->setTime(rand(0, 23), rand(0, 59));
            $nombre = 'backup_' . $fecha->format('Ymd_His');
            Backup::create([
                'nombre'        => $nombre,
                'archivo'       => $nombre . '.sql',
                'tamano'        => rand(500_000, 8_000_000),
                'tipo'          => $i % 3 == 0 ? 'manual' : 'automatico',
                'user_id'       => $users[array_rand($users)],
                'observaciones' => 'Backup demo del ' . $fecha->format('d/m/Y'),
                'created_at'    => $fecha,
                'updated_at'    => $fecha,
            ]);
        }
        $this->command->info('  ✓ 10 backups agregados');
    }
}
