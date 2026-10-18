FROM php:8.2-apache

# Habilitar mod_rewrite, mod_headers y mod_reqtimeout (Anti-Slowloris nativo)
RUN a2enmod rewrite headers reqtimeout

# Instalar extensiones de PHP necesarias (mysqli, pdo, pdo_mysql y gd con soporte WebP/JPEG/PNG)
RUN apt-get update && apt-get install -y \
    libwebp-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-webp --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && rm -rf /var/lib/apt/lists/*

# Configuración Anti-Slowloris y lectura estricta de .htaccess en Apache
RUN printf "TimeOut 20\nKeepAlive On\nMaxKeepAliveRequests 100\nKeepAliveTimeout 5\nServerTokens Prod\nServerSignature Off\n<IfModule mod_reqtimeout.c>\n  RequestReadTimeout header=5-15,MinRate=500 body=10-20,MinRate=500\n</IfModule>\n<Directory /var/www/html>\n  Options -Indexes +FollowSymLinks\n  AllowOverride All\n  Require all granted\n</Directory>\n" > /etc/apache2/conf-available/security-slowloris.conf \
    && a2enconf security-slowloris

# Límites seguros de subida en PHP para fotos/videos comprimidos
RUN printf "upload_max_filesize = 15M\npost_max_size = 16M\nmax_execution_time = 30\nmax_input_time = 20\nexpose_php = Off\n" > /usr/local/etc/php/conf.d/security-limits.ini

# Copiar el código de la aplicación al directorio web de Apache
COPY . /var/www/html/

# Ajustar permisos seguros
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

EXPOSE 80
