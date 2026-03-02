# Gunakan image PHP dengan Apache
FROM php:8.2-apache

# Instal ekstensi yang dibutuhkan (terutama untuk MySQL)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Aktifkan mod_rewrite Apache (PENTING untuk routing Lumen)
RUN a2enmod rewrite

# Atur Document Root ke folder public Lumen
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy kodingan ke dalam container
COPY . /var/www/html

# Set permission folder storage (Lumen butuh akses tulis)
RUN chown -R www-data:www-data /var/www/html/storage

# Instal dependency via composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader

# Expose port 80
EXPOSE 80