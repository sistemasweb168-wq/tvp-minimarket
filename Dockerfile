FROM php:8.3-apache

# Instalar dependencias mínimas indispensables
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar Apache DocumentRoot a /public y activar mod_rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar proyecto
COPY . /var/www/html

# Crear directorios y permisos para Laravel
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Instalar paquetes de Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs

# Permisos de Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Inicio directo garantizado
CMD ["sh", "-c", "php artisan migrate --force || true; php artisan db:seed --force || true; apache2-foreground"]
