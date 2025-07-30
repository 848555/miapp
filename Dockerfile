# Usa la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instala extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilita mod_rewrite si usas .htaccess
RUN a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia el proyecto
COPY . .

# Da permisos (opcional)
RUN chown -R www-data:www-data /var/www/html

# Instala dependencias PHP si tienes composer.json
RUN if [ -f composer.json ]; then composer install; fi

# Expone el puerto 80 (Apache)
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]
