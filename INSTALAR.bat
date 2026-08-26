@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
cls
echo ============================================================
echo   TPV MINIMARKET - INSTALADOR AUTOMATICO
echo   Compatible con Laragon / XAMPP / WAMP
echo ============================================================
echo.

REM ====== DETECTAR LARAGON ======
set "LARAGON_PATH="
if exist "C:\laragon\bin\mysql" (
    set "LARAGON_PATH=C:\laragon"
    echo [OK] Laragon detectado en C:\laragon
) else if exist "D:\laragon\bin\mysql" (
    set "LARAGON_PATH=D:\laragon"
    echo [OK] Laragon detectado en D:\laragon
)

REM ====== BUSCAR MYSQL ======
set "MYSQL_BIN=mysql"
if defined LARAGON_PATH (
    for /d %%i in ("!LARAGON_PATH!\bin\mysql\mysql-*") do (
        if exist "%%i\bin\mysql.exe" set "MYSQL_BIN=%%i\bin\mysql.exe"
    )
    echo [OK] MySQL: !MYSQL_BIN!
)

REM ====== BUSCAR PHP ======
set "PHP_BIN=php"
if defined LARAGON_PATH (
    for /d %%i in ("!LARAGON_PATH!\bin\php\php-*") do (
        if exist "%%i\php.exe" set "PHP_BIN=%%i\php.exe"
    )
    echo [OK] PHP:   !PHP_BIN!
)

REM ====== BUSCAR COMPOSER ======
set "COMPOSER_BIN=composer"
if defined LARAGON_PATH (
    if exist "!LARAGON_PATH!\bin\composer\composer.bat" (
        set "COMPOSER_BIN=!LARAGON_PATH!\bin\composer\composer.bat"
        echo [OK] Composer: !COMPOSER_BIN!
    )
)

echo.
echo ============================================================
echo Pasos a ejecutar:
echo  1) composer install
echo  2) Copiar .env si no existe
echo  3) Generar APP_KEY
echo  4) Crear base de datos tpv_minimarket
echo  5) Importar estructura y datos iniciales
echo  6) Crear enlace de storage
echo ============================================================
echo.
pause

echo.
echo [1/6] Instalando dependencias con Composer...
call "%COMPOSER_BIN%" install --no-interaction
if errorlevel 1 (
    echo.
    echo ERROR: Composer fallo. Verifica que Composer este instalado.
    echo  - Si usas Laragon: ejecuta este script desde la terminal de Laragon ^(menu Terminal^)
    echo  - O instala Composer desde https://getcomposer.org/
    pause
    exit /b 1
)

echo.
echo [2/6] Configurando archivo .env...
if not exist .env (
    copy .env.example .env >nul
    echo [OK] .env creado desde .env.example
) else (
    echo [INFO] .env ya existe, omitiendo
)

echo.
echo [3/6] Generando APP_KEY...
call "%PHP_BIN%" artisan key:generate --force
if errorlevel 1 (
    echo ERROR: No se pudo generar la clave. Verifica PHP.
    pause
    exit /b 1
)

echo.
echo [4/6] Creando base de datos tpv_minimarket en MySQL...
echo Ingresando con usuario root sin contrasena ^(estandar Laragon^)...
"%MYSQL_BIN%" -u root -e "CREATE DATABASE IF NOT EXISTS tpv_minimarket DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if errorlevel 1 (
    echo.
    echo No se pudo conectar sin contrasena. Intentando con contrasena...
    set /p MYSQL_PASS="Ingresa la contrasena de MySQL ^(deja vacio si no tiene^): "
    if "!MYSQL_PASS!"=="" (
        "%MYSQL_BIN%" -u root -e "CREATE DATABASE IF NOT EXISTS tpv_minimarket DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    ) else (
        "%MYSQL_BIN%" -u root -p!MYSQL_PASS! -e "CREATE DATABASE IF NOT EXISTS tpv_minimarket DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    )
    if errorlevel 1 (
        echo ERROR: No se pudo conectar a MySQL. Verifica que el servicio este iniciado.
        echo Si usas Laragon: abre Laragon y haz clic en "Iniciar Todo".
        pause
        exit /b 1
    )
)
echo [OK] Base de datos creada/verificada.

echo.
echo [5/6] Ejecutando migraciones y seeders...
call "%PHP_BIN%" artisan migrate:fresh --seed --force
if errorlevel 1 (
    echo.
    echo Las migraciones fallaron. Intentando importar SQL directo...
    "%MYSQL_BIN%" -u root tpv_minimarket < database\install.sql
    if errorlevel 1 (
        echo ERROR: Tampoco se pudo importar el SQL. Revisa la conexion a MySQL.
        pause
        exit /b 1
    )
    echo [OK] Datos importados via SQL.
)

echo.
echo [6/6] Creando enlace simbolico de storage...
call "%PHP_BIN%" artisan storage:link 2>nul

echo.
echo ============================================================
echo   INSTALACION COMPLETADA EXITOSAMENTE
echo ============================================================
echo.
echo Credenciales por defecto:
echo   Usuario: admin    Password: admin123
echo   Usuario: gerente  Password: gerente123
echo   Usuario: cajero   Password: cajero123
echo.
echo Para iniciar el servidor:
if defined LARAGON_PATH (
    echo   Opcion 1 ^(LARAGON^): Coloca el proyecto en C:\laragon\www\
    echo                       y abre http://tvp-minimarket.test
    echo   Opcion 2 ^(Artisan^): %PHP_BIN% artisan serve
    echo                       y abre http://localhost:8000
) else (
    echo   php artisan serve
    echo   y abre http://localhost:8000
)
echo.
pause
endlocal
