#!/bin/bash
set -e

# Crear directorios de framework
mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache
touch storage/logs/laravel.log
chmod -R 777 storage bootstrap/cache

# Generar APP_KEY si no está seteado
if [ -z "$APP_KEY" ]; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Ejecutar migraciones automáticas si la BD está conectada
echo "Verificando base de datos..."
php artisan migrate --force || true

# Ejecutar seeders si la tabla users está vacía
php artisan db:seed --force || true

# Limpiar y optimizar cachés
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Iniciando servidor web Apache..."
exec apache2-foreground
