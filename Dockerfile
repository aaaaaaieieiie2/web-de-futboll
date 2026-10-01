FROM php:8.2-apache

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Instalar extensiones de PHP necesarias (mysqli para la conexión a BD)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar el código de la aplicación al directorio web de Apache
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

EXPOSE 80

