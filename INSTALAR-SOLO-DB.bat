@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
cls
echo ============================================================
echo   TPV MINIMARKET - INSTALACION RAPIDA (solo base de datos)
echo   No requiere Composer ni Artisan - solo MySQL
echo ============================================================
echo.

REM ====== DETECTAR LARAGON ======
set "LARAGON_PATH="
if exist "C:\laragon\bin\mysql" set "LARAGON_PATH=C:\laragon"
if exist "D:\laragon\bin\mysql" set "LARAGON_PATH=D:\laragon"

REM ====== BUSCAR MYSQL ======
set "MYSQL_BIN=mysql"
if defined LARAGON_PATH (
    for /d %%i in ("!LARAGON_PATH!\bin\mysql\mysql-*") do (
        if exist "%%i\bin\mysql.exe" set "MYSQL_BIN=%%i\bin\mysql.exe"
    )
    echo [OK] MySQL encontrado: !MYSQL_BIN!
)

echo.
echo Importando estructura y datos iniciales en base 'tpv_minimarket'...
echo (Asegurate que MySQL este iniciado en Laragon)
echo.
pause

"%MYSQL_BIN%" -u root < database\install.sql
if errorlevel 1 (
    echo.
    echo Intentando con contrasena...
    set /p MYSQL_PASS="Contrasena de root MySQL (vacio si no tiene): "
    if "!MYSQL_PASS!"=="" (
        "%MYSQL_BIN%" -u root < database\install.sql
    ) else (
        "%MYSQL_BIN%" -u root -p!MYSQL_PASS! < database\install.sql
    )
    if errorlevel 1 (
        echo.
        echo ERROR: No se pudo importar. Verifica que MySQL este corriendo.
        pause
        exit /b 1
    )
)

echo.
echo ============================================================
echo   BASE DE DATOS LISTA
echo ============================================================
echo.
echo La base 'tpv_minimarket' esta creada con todos los datos demo.
echo.
echo Credenciales:
echo   admin / admin123
echo   gerente / gerente123
echo   cajero / cajero123
echo.
echo PROXIMO PASO:
echo  Para que la app PHP funcione necesitas:
echo  1) Habilitar la extension mbstring en php.ini
echo  2) Ejecutar: composer install
echo  3) Ejecutar: php artisan key:generate
echo  4) Ejecutar: php artisan serve
echo.
echo Mientras tanto puedes verificar los datos en phpMyAdmin:
echo  http://localhost/phpmyadmin
echo.
pause
endlocal
