FROM php:8.2-apache

# Instala extensiones necesarias como mysqli o pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copia tu proyecto al contenedor
COPY . /var/www/html/

# Habilita mod_rewrite si usas URLs amigables
RUN a2enmod rewrite

# Da permisos (opcional si tienes errores de permisos)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Comando para mantener apache corriendo
CMD ["apache2-foreground"]