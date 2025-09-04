# Etapa 1: obtenemos Composer desde la imagen oficial
FROM composer:latest AS composer_stage

# Etapa 2: imagen de PHP con Apache
FROM php:8.2-apache

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip mysqli pdo pdo_mysql

# Habilita mod_rewrite
RUN a2enmod rewrite

# Copia Composer desde la etapa anterior
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia todos los archivos del proyecto (incluye vendor si ya lo subiste)
COPY . .

# 📌 Ejecuta composer install para instalar dependencias
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Otorga permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]
