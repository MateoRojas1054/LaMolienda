FROM php:8.2-apache

# Copiar el código fuente
COPY src/ /var/www/html/

# Configurar Apache: deshabilitar MPMs extra y habilitar solo prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]