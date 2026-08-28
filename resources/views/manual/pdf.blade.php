<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - Sistema Minimarket & Licorería</title>
    <style>
        @page {
            margin: 1.2cm;
            footer: page-footer;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Portada */
        .cover {
            text-align: center;
            padding-top: 4cm;
            padding-bottom: 2cm;
        }
        .cover-badge {
            display: inline-block;
            background-color: #f59e0b;
            color: #000;
            font-weight: bold;
            font-size: 10pt;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .cover-title {
            font-size: 26pt;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 10px 0;
            line-height: 1.1;
        }
        .cover-subtitle {
            font-size: 14pt;
            color: #64748b;
            margin-bottom: 40px;
        }
        .cover-box {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 40px auto;
            width: 80%;
            text-align: left;
        }

        /* Encabezados de Capítulo */
        h1 {
            font-size: 18pt;
            color: #0f172a;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 6px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        h2 {
            font-size: 13pt;
            color: #1e293b;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        h3 {
            font-size: 11pt;
            color: #334155;
            margin-top: 12px;
            margin-bottom: 5px;
        }
        p {
            margin-top: 0;
            margin-bottom: 10px;
            text-align: justify;
        }

        /* Cajas de Alerta y Destacados */
        .callout {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 10px 14px;
            border-radius: 4px;
            margin: 12px 0;
            font-size: 10pt;
        }
        .callout-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 10px 14px;
            border-radius: 4px;
            margin: 12px 0;
            font-size: 10pt;
        }
        .callout-info {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 10px 14px;
            border-radius: 4px;
            margin: 12px 0;
            font-size: 10pt;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Pasos numerados */
        .step-list {
            margin: 10px 0;
            padding-left: 0;
            list-style: none;
        }
        .step-item {
            margin-bottom: 10px;
            position: relative;
            padding-left: 28px;
        }
        .step-number {
            position: absolute;
            left: 0;
            top: 0;
            background-color: #f59e0b;
            color: #000;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 9pt;
        }

        .tag {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tag-green { background: #dcfce7; color: #166534; }
        .tag-yellow { background: #fef3c7; color: #92400e; }
        .tag-blue { background: #dbeafe; color: #1e40af; }
        .tag-purple { background: #f3e8ff; color: #6b21a8; }
    </style>
</head>
<body>

    <!-- PORTADA -->
    <div class="cover">
        <div class="cover-badge">Guía Oficial de Usuario v2.0</div>
        <h1 class="cover-title">{{ $empresa->nombre_comercial ?? $empresa->razon_social ?? 'SISTEMA MINIMARKET & LICORERÍA' }}</h1>
        <p class="cover-subtitle">Manual Práctico de Operaciones y Administración Comercial</p>
        
        <div class="cover-box">
            <table style="border: none; margin: 0;">
                <tr>
                    <td style="border: none; font-weight: bold; width: 35%;">Empresa:</td>
                    <td style="border: none;">{{ $empresa->razon_social ?? 'Bodega & Licorería' }}</td>
                </tr>
                <tr>
                    <td style="border: none; font-weight: bold;">RUC:</td>
                    <td style="border: none;">{{ $empresa->ruc ?? '10XXXXXXXXX' }}</td>
                </tr>
                <tr>
                    <td style="border: none; font-weight: bold;">Fecha de Emisión:</td>
                    <td style="border: none;">{{ date('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="border: none; font-weight: bold;">Versión del Sistema:</td>
                    <td style="border: none;">2.0 Pro Suite (Edición Licorería)</td>
                </tr>
            </table>
        </div>

        <p style="color: #94a3b8; font-size: 9pt; margin-top: 50px;">
            Este manual contiene las instrucciones detalladas paso a paso para el uso de todos los módulos del sistema: POS, Inventario, Six-Packs, Combos, Caja, Envases Retornables y SUNAT.
        </p>
    </div>

    <div class="page-break"></div>

    <!-- ÍNDICE GENERAL -->
    <h1>Índice de Contenidos</h1>
    <table style="margin-top: 15px;">
        <thead>
            <tr>
                <th style="width: 15%;">Módulo</th>
                <th>Descripción del Capítulo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Capítulo 1</strong></td>
                <td><strong>Acceso al Sistema, Roles y Permisos de Usuarios</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 2</strong></td>
                <td><strong>Punto de Venta (POS): Ventas, Pagos Mixtos y Emisión</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 3</strong></td>
                <td><strong>Productos: Estándar, Paquetes/Six-Packs y Combos Mixtos</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 4</strong></td>
                <td><strong>Control de Caja: Apertura, Gastos/Egresos y Arqueo de Cierre</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 5</strong></td>
                <td><strong>Control de Envases Retornables & Garantías (Cascos)</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 6</strong></td>
                <td><strong>Kardex de Inventario y Registro de Mermas / Botellas Rotas</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 7</strong></td>
                <td><strong>Reportes Financieros y Utilidad Neta Real</strong></td>
            </tr>
            <tr>
                <td><strong>Capítulo 8</strong></td>
                <td><strong>Facturación Electrónica SUNAT y Clientes</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- CAPÍTULO 1 -->
    <h1>Capítulo 1: Acceso al Sistema y Roles</h1>
    
    <h2>1.1 Inicio de Sesión</h2>
    <p>Para ingresar al sistema, acceda a la dirección web desde cualquier computadora, tablet o celular. Ingrese su correo electrónico y contraseña asignada.</p>
    
    <div class="callout-info">
        <strong>Redirección Inteligente para Cajeros:</strong> Si el usuario tiene el rol <em>Cajero</em>, el sistema lo llevará automáticamente a la pantalla del <strong>Punto de Venta (POS)</strong> sin pasar por el menú administrativo.
    </div>

    <h2>1.2 Gestión de Roles y Permisos</h2>
    <p>El administrador puede crear roles personalizados (ej. <em>Administrador, Cajero, Almacenero</em>) y asignar permisos específicos:</p>
    <ul>
        <li><span class="tag tag-green">POS</span> Acceso a la caja registradora rápida.</li>
        <li><span class="tag tag-blue">Ventas</span> Ver historial de tickets y anular ventas con clave.</li>
        <li><span class="tag tag-yellow">Productos</span> Crear productos, six-packs y modificar precios.</li>
        <li><span class="tag tag-purple">Caja</span> Abrir y cerrar turnos, registrar egresos y gastos.</li>
        <li><span class="tag tag-green">Reportes</span> Ver ganancias, utilidades netas y exportar datos.</li>
    </ul>

    <div class="page-break"></div>

    <!-- CAPÍTULO 2 -->
    <h1>Capítulo 2: Punto de Venta (POS)</h1>
    <p>El POS es el corazón de la venta rápida en mostrador. Está diseñado para operar con teclado, mouse o pantalla táctil.</p>

    <h2>2.1 Cómo realizar una Venta Rápida</h2>
    <div class="step-list">
        <div class="step-item">
            <div class="step-number">1</div>
            <strong>Buscar o Escanear Producto:</strong> Escriba el nombre en el buscador o dispare el lector de código de barras. El producto se añadirá al carrito automáticamente.
        </div>
        <div class="step-item">
            <div class="step-number">2</div>
            <strong>Ajustar Cantidades:</strong> Use los botones <code>+</code> y <code>-</code> en el carrito o presione directamente sobre el producto.
        </div>
        <div class="step-item">
            <div class="step-number">3</div>
            <strong>Cobrar:</strong> Presione el botón <strong>"Cobrar (F2)"</strong>.
        </div>
    </div>

    <h2>2.2 Modalidades de Pago Disponibles</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Forma de Pago</th>
                <th>Cómo Opera en Caja</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Efectivo</strong></td>
                <td>Ingrese el monto recibido (o use los botones rápidos S/10, S/20, S/50, S/100, S/200). El sistema calcula el vuelto exacto.</td>
            </tr>
            <tr>
                <td><strong>Yape / Plin</strong></td>
                <td>El cliente escanea el código QR desde el mostrador. Cobro exacto sin vuelto.</td>
            </tr>
            <tr>
                <td><strong>Tarjeta</strong></td>
                <td>Cobro mediante POS Izipay/Niubiz/Culqi.</td>
            </tr>
            <tr>
                <td><strong>Pago Mixto (Dividido)</strong></td>
                <td>Permite cobrar una parte en <strong>Efectivo</strong> y el saldo restante en <strong>Yape/Tarjeta</strong> (ej. Total S/ 50: S/ 30 efectivo + S/ 20 Yape).</td>
            </tr>
        </tbody>
    </table>

    <h2>2.3 Comprobantes y Compartir</h2>
    <p>Al confirmar la venta, se genera el comprobante al instante:</p>
    <ul>
        <li><strong>Ticketera Térmica (80mm / 58mm):</strong> Impresión rápida automática.</li>
        <li><strong>Formato PDF A4:</strong> Para facturas o clientes corporativos.</li>
        <li><strong>WhatsApp:</strong> Ingrese el número del cliente y envíe el ticket digital directo a su celular.</li>
    </ul>

    <div class="page-break"></div>

    <!-- CAPÍTULO 3 -->
    <h1>Capítulo 3: Catálogo de Productos y Modalidades</h1>
    <p>El sistema cuenta con 3 modos de producto para adaptarse a la venta de licores y abarrotes:</p>

    <h2>3.1 Modo 1: Producto Estándar (Individual)</h2>
    <p>Se utiliza para cualquier producto que se vende por unidad suelta (ej. <em>Cerveza Pilsen Lata 355ml, Whisky Red Label 750ml, Gaseosa Coca Cola 1.5L</em>). Se le define un stock físico directo.</p>

    <h2>3.2 Modo 2: Paquete / Six-Pack / Multipack (Estilo Abarrotes)</h2>
    <div class="callout">
        <strong>¿Cómo funciona el Six-Pack?:</strong> No necesita asignarle stock manual. Solo indica de qué lata proviene y que contiene <strong>6 unidades</strong>. Al vender 1 Six-pack, el sistema descuenta automáticamente <strong>6 latas</strong> de su inventario general.
    </div>
    <div class="step-list">
        <div class="step-item">
            <div class="step-number">1</div>
            Seleccione <strong>"2. Paquete / Six-Pack"</strong> en el formulario de producto.
        </div>
        <div class="step-item">
            <div class="step-number">2</div>
            Escanee el código de barras que viene impreso en el cartón del Six-pack.
        </div>
        <div class="step-item">
            <div class="step-number">3</div>
            Elija el producto base (ej. <em>Pilsen Lata</em>) e indique la cantidad (<code>6</code>).
        </div>
        <div class="step-item">
            <div class="step-number">4</div>
            Fije el precio del Six-pack (ej. S/ 26.00) y guarde.
        </div>
    </div>

    <h2>3.3 Modo 3: Combo Mixto Promocional</h2>
    <p>Para promociones compuestas por productos diferentes (ej. <em>Pack Fiestero: 1 Whisky Red Label + 2 Coca Colas + 1 Bolsa de Hielo</em>). El sistema calcula el costo total sumado de los ingredientes y descuenta cada uno al venderse.</p>

    <div class="page-break"></div>

    <!-- CAPÍTULO 4 -->
    <h1>Capítulo 4: Control de Caja y Gastos</h1>

    <h2>4.1 Apertura de Turno</h2>
    <p>Al iniciar la jornada, el cajero debe ingresar el <strong>Monto Inicial de Apertura</strong> (sencillo en monedas y billetes) para habilitar las ventas.</p>

    <h2>4.2 Registro de Gastos y Egresos de Caja</h2>
    <p>Desde el botón <strong>"Gasto"</strong> en la barra superior del POS o desde el menú Caja, se registran las salidas de dinero menores:</p>
    <ul>
        <li><strong>Insumos:</strong> Compra de bolsas de hielo, limones, servilletas.</li>
        <li><strong>Servicios:</strong> Pago de luz, agua, internet del local.</li>
        <li><strong>Personal:</strong> Almuerzo del personal, viáticos.</li>
        <li><strong>Proveedores:</strong> Pago menor a proveedores en efectivo.</li>
    </ul>

    <div class="callout-warning">
        <strong>Cuadre Automático:</strong> Todo gasto registrado se descuenta automáticamente del efectivo esperado al momento de cerrar la caja.
    </div>

    <h2>4.3 Cierre y Arqueo de Caja</h2>
    <p>Al terminar el turno, el cajero cuenta el dinero físico del cajón e ingresa el monto. El sistema compara:</p>
    <p style="text-align: center; font-weight: bold; background: #f1f5f9; padding: 8px; border-radius: 6px;">
        (Monto Apertura) + (Ventas Efectivo) + (Garantías Entrantes) - (Egresos/Gastos) - (Garantías Devueltas) = TOTAL CALCULADO
    </p>

    <!-- CAPÍTULO 5 -->
    <h1>Capítulo 5: Envases Retornables y Garantías (Cascos)</h1>
    <p>Diseñado especialmente para la venta de cerveza en botellas de 620ml y cajas plásticas con garantía en soles.</p>

    <h2>5.1 Prestar Envases con Cobro de Garantía</h2>
    <p>Si el cliente no trae botellas vacías, se registra la salida del envase y se cobra la garantía (ej. S/ 20.00 por caja). Este dinero entra a la caja como <em>Garantía en Custodia</em>.</p>

    <h2>5.2 Recepción y Devolución de Garantía</h2>
    <p>Cuando el cliente regresa los cascos vacíos, busque su nombre en <strong>Envases & Cascos</strong> y presione <strong>"Recibir & Reembolsar"</strong>. El sistema devolverá los S/ 20 de la caja y marcará el envase como devuelto.</p>

    <div class="page-break"></div>

    <!-- CAPÍTULO 6 -->
    <h1>Capítulo 6: Kardex y Registro de Mermas</h1>
    <p>El Kardex es la auditoría completa de cada movimiento de stock en su tienda.</p>

    <h2>6.1 Registro de Botellas Rotas / Mermas</h2>
    <p>Si una botella se rompe en el mostrador o durante la descarga del camión:</p>
    <ol>
        <li>Vaya a <strong>Inventario > Kardex & Mermas</strong>.</li>
        <li>Presione el botón rojo <strong>"Registrar Merma / Rotura"</strong>.</li>
        <li>Seleccione el producto, la cantidad de botellas rotas y el motivo (<em>Rotura en tienda, Vencimiento, etc.</em>).</li>
        <li>El sistema dará de baja el stock de inmediato y guardará el registro contable.</li>
    </ol>

    <!-- CAPÍTULO 7 -->
    <h1>Capítulo 7: Reportes y Utilidad Neta Real</h1>
    <p>El módulo de <strong>Utilidad Neta Real</strong> (`/reportes/utilidades`) le muestra su ganancia limpia en el bolsillo calculando:</p>
    
    <div class="callout">
        <strong>Fórmula Financiera Real:</strong><br>
        <strong>UTILIDAD NETA =</strong> Ventas Totales - Costo de Compra de Productos Vendidos - Gastos Operativos de Caja.
    </div>

    <p>Incluye el <strong>Top 10 de Licores Más Rentables</strong>, el porcentaje de margen neto y gráficos de evolución diaria.</p>

    <!-- CAPÍTULO 8 -->
    <h1>Capítulo 8: Facturación SUNAT</h1>
    <p>El sistema cuenta con integración a la API de Facturación Electrónica SUNAT:</p>
    <ul>
        <li><strong>Búsqueda Automática:</strong> Ingrese el DNI o RUC y el sistema autocompleta el nombre y dirección fiscal directamente desde RENIEC y SUNAT.</li>
        <li><strong>Boletas y Facturas Electrónicas:</strong> Envío directo y generación de código QR y hash de validación tributaria.</li>
    </ul>

    <div style="margin-top: 40px; text-align: center; border-top: 2px solid #e2e8f0; padding-top: 20px;">
        <p style="font-weight: bold; color: #0f172a; margin-bottom: 2px;">{{ $empresa->nombre_comercial ?? 'Sistema Minimarket & Licorería' }}</p>
        <p style="font-size: 9pt; color: #64748b;">Soporte Técnico y Consultas del Sistema</p>
    </div>

</body>
</html>
