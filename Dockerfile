FROM php:8.2-apache

# Habilitar mod_rewrite y mod_headers (cabeceras de seguridad)
RUN a2enmod rewrite headers

# Extensiones PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configuración de producción PHP (no exponer errores)
RUN { \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'expose_php = Off'; \
    echo 'upload_max_filesize = 5M'; \
    echo 'post_max_size = 6M'; \
} > /usr/local/etc/php/conf.d/nicosport.ini

# Copiar el código de la aplicación al directorio web de Apache
COPY . /var/www/html/

# Permisos seguros: el código es propiedad de root y solo lectura para www-data;
# únicamente uploads/ necesita escritura.
RUN chown -R root:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 750 {} \; \
    && find /var/www/html -type f -exec chmod 640 {} \; \
    && chown -R www-data:www-data /var/www/html/uploads \
    && find /var/www/html/uploads -type d -exec chmod 770 {} \; \
    && find /var/www/html/uploads -type f -exec chmod 660 {} \; \
    && chown www-data:www-data /var/www/html/core/.env 2>/dev/null || true \
    && chmod 600 /var/www/html/core/.env 2>/dev/null || true \
    && chown -R www-data:www-data /var/www/html/private /var/www/html/api \
    && chmod -R g+s /var/www/html/uploads

EXPOSE 80
