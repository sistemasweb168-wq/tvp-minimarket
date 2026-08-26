FROM serversideup/php:8.3-apache

ENV AUTORUN_LARAVEL_MIGRATION=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=true
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html

# Copiar archivos con permisos adecuados
COPY --chown=www-data:www-data . /var/www/html

# Instalar dependencias de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs

EXPOSE 80
