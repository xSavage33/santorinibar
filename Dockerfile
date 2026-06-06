FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite headers

# Configurar PHP para produccion
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configuraciones de seguridad PHP
RUN echo "expose_php = Off" >> "$PHP_INI_DIR/php.ini" && \
    echo "display_errors = Off" >> "$PHP_INI_DIR/php.ini" && \
    echo "log_errors = On" >> "$PHP_INI_DIR/php.ini" && \
    echo "error_log = /var/log/php_errors.log" >> "$PHP_INI_DIR/php.ini" && \
    echo "upload_max_filesize = 5M" >> "$PHP_INI_DIR/php.ini" && \
    echo "post_max_size = 6M" >> "$PHP_INI_DIR/php.ini" && \
    echo "max_execution_time = 30" >> "$PHP_INI_DIR/php.ini" && \
    echo "session.cookie_httponly = 1" >> "$PHP_INI_DIR/php.ini" && \
    echo "session.cookie_secure = 1" >> "$PHP_INI_DIR/php.ini" && \
    echo "session.use_strict_mode = 1" >> "$PHP_INI_DIR/php.ini"

# Configurar Apache para seguridad
RUN echo "ServerTokens Prod" >> /etc/apache2/conf-available/security.conf && \
    echo "ServerSignature Off" >> /etc/apache2/conf-available/security.conf

# Copiar archivos de la aplicacion
COPY . /var/www/html/

# Crear directorio de uploads y establecer permisos
RUN mkdir -p /var/www/html/uploads/productos && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/uploads

# Puerto
EXPOSE 80

CMD ["apache2-foreground"]
