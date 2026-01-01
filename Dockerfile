FROM php:8.2-apache

# Apache config segura
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite

# Copiar código
COPY src/ /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
