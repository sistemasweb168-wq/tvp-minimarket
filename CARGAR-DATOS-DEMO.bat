@echo off
chcp 65001 >nul
title TPV Minimarket - Cargar Datos Demo
color 0B

echo.
echo ============================================================
echo   TPV MINIMARKET - CARGA DE DATOS DEMO PARA DASHBOARD
echo ============================================================
echo.
echo   Este script ejecutara el seeder "DemoDataSeeder" que
echo   agrega 10 registros a cada modulo del sistema con
echo   diferentes fechas, para alimentar los paneles y
echo   graficos estadisticos del dashboard.
echo.
echo   Modulos que se poblaran:
echo     - Categorias (10)
echo     - Proveedores (10)
echo     - Productos (10)
echo     - Clientes (10)
echo     - Usuarios (10)
echo     - Turnos de Caja (10)
echo     - Movimientos de Caja (10)
echo     - Compras (10) con sus detalles
echo     - Ventas (12) con sus detalles
echo     - Movimientos de Inventario (10)
echo     - Promociones (10)
echo     - Puntos de Fidelidad (10)
echo     - Backups (10)
echo.
echo ============================================================
echo.

cd /d "%~dp0"

echo [1/2] Verificando entorno Laravel...
if not exist artisan (
    echo  ERROR: No se encontro el archivo artisan en este directorio.
    echo  Asegurate de ejecutar este script desde la raiz del proyecto.
    pause
    exit /b 1
)

echo [2/2] Ejecutando seeder de datos demo...
echo.
php artisan db:seed --class=DemoDataSeeder

if errorlevel 1 (
    echo.
    echo ============================================================
    echo   ERROR AL EJECUTAR EL SEEDER
    echo ============================================================
    echo   Revisa que:
    echo    - MySQL este corriendo
    echo    - La BD "tpv_minimarket" exista
    echo    - Las migraciones esten ejecutadas (php artisan migrate)
    echo ============================================================
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   DATOS DEMO CARGADOS EXITOSAMENTE
echo ============================================================
echo.
echo   Ahora puedes ingresar al sistema:
echo     URL:      http://localhost:8000
echo     Usuario:  admin
echo     Clave:    admin123
echo.
echo   Ingresa al Dashboard para ver los graficos poblados.
echo ============================================================
echo.
pause
