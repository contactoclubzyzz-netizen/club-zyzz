# Imagen oficial de PHP con Apache
FROM php:8.2-apache

# Copiar los archivos del proyecto al contenedor
COPY . /var/www/html/

# Instalar extensiones comunes de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Exponer el puerto web
EXPOSE 80
