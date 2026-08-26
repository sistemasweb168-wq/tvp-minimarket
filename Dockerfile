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

# Instalar dependencias de Composer optimizadas para producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod +x /var/www/html/entrypoint.sh

# Puerto expuesto
EXPOSE 80

# Comando de inicio
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
