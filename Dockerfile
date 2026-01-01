FROM php:8.2-apache

# 1. Configuración de Apache (Sin tocar los MPMs)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

# 2. Copiar el código fuente
COPY src/ /var/www/html/

# 3. Asegurar permisos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando estándar
CMD ["apache2-foreground"]