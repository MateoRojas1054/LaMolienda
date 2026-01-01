FROM php:8.2-apache

# Limpieza total de MPMs conflictivos
RUN rm -f \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_worker.load \
    /etc/apache2/mods-enabled/mpm_worker.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod mpm_prefork rewrite

COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

CMD ["apache2-foreground"]
