FROM php:8.2-apache

# ✅ Extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libjpeg62-turbo-dev \
    libpng-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

# ✅ Habilitar mod_rewrite (lo que ya tenías)
RUN a2enmod rewrite

# ✅ Cambiar DocumentRoot a /var/www/public
ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

EXPOSE 80
