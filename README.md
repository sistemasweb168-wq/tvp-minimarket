# 🏪 TPV Minimarket - Sistema de Punto de Venta

Sistema completo de Punto de Venta (POS) desarrollado en **Laravel 11 + MySQL** especialmente diseñado para **minimercados, minisúper y tiendas de alimentación**.

---

## 📋 Características Principales

### 🛒 Módulo de Ventas (POS)
- Punto de venta rápido con búsqueda instantánea
- Soporte para código de barras
- Múltiples formas de pago (efectivo, tarjeta, transferencia)
- Cálculo automático de cambio
- Impresión de tickets
- Anulación de ventas (con devolución de stock)

### 📦 Inventario
- Gestión completa de productos
- Categorías personalizables con colores e iconos
- Control de stock con alertas de stock bajo
- Manejo de lotes y fechas de vencimiento
- Movimientos de inventario detallados (entradas, salidas, ajustes, mermas)
- Múltiples unidades de medida (UND, KG, LT, etc.)
- Precios mayoristas

### 👥 Clientes y Fidelización
- Base de clientes completa
- Sistema de puntos de fidelidad
- Crédito por cliente
- Historial de compras

### 🚚 Proveedores y Compras
- Gestión de proveedores
- Registro de compras
- Actualización automática de stock al recibir mercancía
- Historial por proveedor

### 💰 Caja
- Apertura y cierre de turnos
- Control de efectivo, tarjeta y otros
- Movimientos de caja (ingresos/egresos)
- Cuadre automático con cálculo de diferencias
- Reporte de cierre

### 📊 Reportes y Análisis
- Dashboard con gráficos en tiempo real
- Reportes de ventas por período
- Productos más vendidos
- Estado de inventario con valoración
- Productos por vencer
- Exportación e impresión

### 🎯 Promociones
- Descuentos por porcentaje o monto fijo
- Promociones 2x1, 3x2
- Precio especial
- Por producto o categoría
- Vigencia configurable

### ⚙️ Configuración
- Datos de empresa (logo, RUC, dirección)
- Símbolo y código de moneda configurable
- Impuestos configurables
- Mensajes personalizables en tickets
- Series de comprobantes

### 🔐 Usuarios y Roles
- Sistema multi-usuario
- Roles predefinidos (Administrador, Gerente, Cajero, Almacenero)
- Permisos granulares por módulo
- Auditoría de actividad

### 💾 Backup y Restauración
- Crear copias de seguridad manuales
- Restaurar desde backups previos
- Restaurar desde archivo SQL externo
- Resetear sistema (para empresa nueva) con backup automático previo

### 🇵🇪 Facturación Electrónica SUNAT (Perú)
- Emisión de **Boletas Electrónicas** (B001)
- Emisión de **Facturas Electrónicas** (F001) con validación de RUC
- **Notas de Crédito** (FC01/BC01) para anulaciones
- **Resúmenes diarios** de boletas
- **Comunicaciones de baja** de comprobantes
- Integración directa con SUNAT vía **Greenter**
- Modos Beta (homologación) y Producción
- Firma digital con certificado .pem/.pfx
- Descarga de XML firmado y CDR de SUNAT
- Series y correlativos configurables
- Importe en letras automático
- Datos para QR conformes a especificación SUNAT

---

## 🛠 Requisitos

- **PHP** 8.2 o superior
- **MySQL** 5.7+ / MariaDB 10.3+
- **Composer** 2.x
- **Apache** o **Nginx** con `mod_rewrite`
- **Extensiones PHP**: PDO, MySQL, OpenSSL, Mbstring, Tokenizer, JSON, GD, Fileinfo

---

## 🚀 Instalación

### Configuración de MySQL

```
Servidor : localhost
Usuario  : root
Password : (vacío o tu contraseña)
Base BD  : tpv_minimarket
Puerto   : 3306
```

### Opción A: Instalación con Composer (recomendada)

1. **Clonar / copiar el proyecto** a tu servidor:
   ```bash
   cd C:\xampp\htdocs   # o tu directorio de servidor
   ```

2. **Instalar dependencias:**
   ```bash
   cd tvp-minimarket
   composer install
   ```

3. **Configurar `.env`:** copia `.env.example` a `.env` y revisa:
   ```
   DB_DATABASE=tpv_minimarket
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generar APP_KEY:**
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Crear enlace simbólico de storage:**
   ```bash
   php artisan storage:link
   ```

7. **Iniciar servidor:**
   ```bash
   php artisan serve
   ```
   Abre en navegador: `http://localhost:8000`

### Opción B: Instalación rápida con SQL (sin Composer)

Si necesitas levantar el sistema rápidamente sin Composer:

1. **Crear la base de datos** importando el archivo `database/install.sql` desde phpMyAdmin o terminal:
   ```bash
   mysql -u root -p < database/install.sql
   ```

2. Aún así necesitarás Composer/Laravel para que la aplicación funcione (PHP). Para ello:
   ```bash
   composer install
   php artisan key:generate
   php artisan serve
   ```

---

## 🔑 Credenciales Iniciales

Después de instalar, ingresa con cualquiera de estas cuentas:

| Rol           | Usuario  | Contraseña   |
|---------------|----------|--------------|
| Administrador | admin    | admin123     |
| Gerente       | gerente  | gerente123   |
| Cajero        | cajero   | cajero123    |

> ⚠️ **Importante**: Cambia las contraseñas por defecto en producción desde el módulo de Usuarios.

---

## 📂 Estructura del Proyecto

```
tvp-minimarket/
├── app/
│   ├── Http/
│   │   ├── Controllers/   # Controladores (Auth, Dashboard, Productos, etc.)
│   │   └── Middleware/    # CheckRole, CheckPermission
│   ├── Models/            # Modelos Eloquent
│   └── Providers/
├── bootstrap/
├── config/                # Configuración Laravel
├── database/
│   ├── migrations/        # 14 migraciones (tablas)
│   ├── seeders/           # Seeders con datos iniciales
│   └── install.sql        # Script SQL alternativo
├── public/
│   ├── index.php
│   ├── uploads/           # Imágenes (productos, empresa)
│   └── backups/           # Copias de seguridad
├── resources/
│   └── views/             # Vistas Blade con Tailwind CSS
│       ├── auth/login.blade.php
│       ├── layouts/app.blade.php
│       ├── dashboard/
│       ├── ventas/  (incluye pos.blade.php - POS completo)
│       ├── productos/  clientes/  proveedores/  compras/
│       ├── caja/  reportes/  promociones/
│       ├── configuracion/  backup/  usuarios/
└── routes/
    └── web.php            # Todas las rutas del sistema
```

---

## 🎨 Stack Tecnológico

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Tailwind CSS (vía CDN), Alpine.js, Chart.js, Font Awesome
- **Base de datos**: MySQL 8 / MariaDB 10
- **Autenticación**: Laravel Auth nativa con sesiones
- **Plantillas**: Blade

---

## 🔧 Comandos Útiles

```bash
# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Reiniciar BD (¡cuidado, borra datos!)
php artisan migrate:fresh --seed

# Crear nuevo controlador / modelo
php artisan make:controller NombreController
php artisan make:model NombreModel
```

---

## 🗂 Módulos Implementados

| Módulo            | Vista listado | Crear/Editar | API/Acciones |
|-------------------|:-------------:|:------------:|:------------:|
| Dashboard         | ✅            | —            | ✅           |
| POS / Ventas      | ✅            | ✅ (POS)     | ✅           |
| Productos         | ✅            | ✅           | ✅           |
| Categorías        | ✅            | ✅           | ✅           |
| Clientes          | ✅            | ✅           | ✅           |
| Proveedores       | ✅            | ✅           | ✅           |
| Compras           | ✅            | ✅           | ✅           |
| Caja / Turnos     | ✅            | ✅           | ✅           |
| Promociones       | ✅            | ✅           | ✅           |
| Reportes          | ✅            | —            | ✅           |
| Configuración     | ✅            | ✅           | ✅           |
| Backup/Restore    | ✅            | ✅           | ✅           |
| Usuarios          | ✅            | ✅           | ✅           |
| Roles y Permisos  | ✅            | ✅           | ✅           |

---

## 🎯 Atajos del POS

- **Enter**: Agregar primer producto encontrado
- **F2**: Abrir modal de cobro
- **Esc**: Cerrar modal

---

## 🇵🇪 Configuración Facturación Electrónica SUNAT

### Pasos para activar SUNAT en el sistema

1. **Iniciar en Modo Beta (Homologación):**
   - Configurar el RUC de tu empresa en Configuración → Empresa
   - Ir a **Config. SUNAT** → seleccionar modo **Beta**
   - En beta, las credenciales SOL son automáticas (MODDATOS)
   - Subir un certificado demo (Greenter incluye uno para pruebas)

2. **Probar emisión:**
   - Realizar una venta en el POS
   - En la vista de la venta, click en **"Emitir Boleta"** o **"Emitir Factura"**
   - Click en **"Enviar a SUNAT"** → recibirás respuesta de prueba

3. **Pasar a Producción:**
   - Adquirir certificado digital real (Reniec, Llama.pe, Camerfirma)
   - En Config. SUNAT cambiar modo a **Producción**
   - Ingresar usuario y clave SOL reales
   - Subir certificado .pem o .pfx con su contraseña
   - Habilitar la opción **Facturación electrónica activa**

### Series por defecto
| Tipo SUNAT | Serie | Descripción |
|-----------|-------|-------------|
| 01 | F001 | Factura electrónica |
| 03 | B001 | Boleta de venta electrónica |
| 07 | FC01 | Nota de crédito de facturas |
| 07 | BC01 | Nota de crédito de boletas |
| 08 | FD01 | Nota de débito de facturas |

### Estados SUNAT
- 🟡 **Pendiente**: Generado pero no enviado a SUNAT
- 🔵 **Enviado**: Enviado, esperando respuesta
- 🟢 **Aceptado**: SUNAT lo aceptó con CDR
- 🔴 **Rechazado**: SUNAT lo rechazó (ver mensaje)
- 🟠 **Observado**: Aceptado con observaciones
- ⚫ **Baja/Anulado**: Anulado posteriormente

### Anulaciones
- **Nota de Crédito**: Para anular dentro de los 7 días siguientes. Recomendado.
- **Comunicación de Baja**: Solo para facturas del mismo día.

### Resumen Diario
SUNAT requiere enviar diariamente un resumen consolidado de las boletas emitidas el día anterior. Ir a **Resúmenes Diarios** → seleccionar fecha → **Generar resumen**.

### Requisitos para producción real
1. RUC habilitado por SUNAT para emisión electrónica
2. Certificado digital firmado por entidad certificadora
3. Usuario SOL secundario (no el principal)
4. Clave SOL del usuario secundario

### Flujo completo de venta con CPE
1. Cajero ingresa al **POS** y agrega productos al carrito
2. Selecciona el cliente (con RUC para Factura, DNI para Boleta, o Genérico para Ticket)
3. Elige el tipo de comprobante: **🎫 Ticket / 📄 Boleta / 💼 Factura**
4. Pulsa **Cobrar** → completa el cobro → **Confirmar Venta**
5. El sistema:
   - Registra la venta en BD
   - Si es Boleta/Factura: genera CPE electrónico
   - Lo firma con el certificado digital
   - Lo envía a SUNAT automáticamente
   - Recibe CDR de aceptación
   - Abre la vista del comprobante con QR

### Endpoints API útiles
- `GET /api/sunat/ubigeos?q=miraflores` — Buscar ubigeos
- `GET /api/sunat/validar?documento=20100100100` — Validar RUC/DNI
- `GET /facturacion/{id}/pdf` — Descargar PDF A4
- `GET /facturacion/{id}/ticket` — Imprimir ticket 80mm
- `GET /facturacion/{id}/xml` — Descargar XML firmado
- `GET /facturacion/{id}/cdr` — Descargar CDR de SUNAT

---

## 🆘 Soporte

Para reportar errores o sugerencias, abre un issue en el repositorio del proyecto.

---

## 📄 Licencia

MIT License - Libre para uso comercial.

---

**TPV Minimarket** © 2026 - Sistema de gestión para minimercados.
