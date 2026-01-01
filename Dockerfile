FROM php:8.2-apache

# 1. Copiar el código fuente
COPY src/ /var/www/html/

# 2. Configuración de Apache
# Solo habilitamos rewrite y nos aseguramos de que ServerName esté configurado
RUN a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 3. Permisos (Opcional pero recomendado)
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]