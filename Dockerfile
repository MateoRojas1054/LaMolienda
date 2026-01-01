FROM php:8.2-apache

# 1. Configuraciones de Apache
# IMPORTANTE: No añadas "service apache2 restart" aquí. 
# Los cambios se aplican al iniciar el contenedor.
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

# 2. Copiar el código fuente
COPY src/ /var/www/html/

# 3. Permisos de archivos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto (Railway lo mapeará automáticamente)
EXPOSE 80

# El CMD ya lo hereda de la imagen base, pero lo ponemos por seguridad
CMD ["apache2-foreground"]