# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Copiar los archivos del proyecto al contenedor
COPY . /var/www/html/

# Instalar extensiones comunes de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configurar el puerto dinámico para Render
ENV PORT=10000
EXPOSE 10000

# Configurar Apache para escuchar el puerto de Render
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Comando de inicio
CMD ["apache2-foreground"]
