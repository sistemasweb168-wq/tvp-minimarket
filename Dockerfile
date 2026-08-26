FROM php:8.3-apache

# Instalar instalador oficial de extensiones PHP para evitar fallos de dependencias
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Instalar extensiones necesarias de forma garantizada
RUN install-php-extensions pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip soap

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache DocumentRoot a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html

# Crear directorios requeridos por Laravel
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Instalar dependencias de Composer sin ejecutar scripts durante el build
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod +x /var/www/html/entrypoint.sh

# Puerto expuesto
EXPOSE 80

# Comando de inicio
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
