# Gunakan image resmi PHP 8.2 + Apache
FROM php:8.2-apache

# -------------------------------------------------
# 1. Install system dependencies
# -------------------------------------------------
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------
# 2. Install PHP extensions
# -------------------------------------------------
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# -------------------------------------------------
# 3. Enable Apache rewrite module (WAJIB untuk Lumen)
# -------------------------------------------------
RUN a2enmod rewrite

# Tambahkan ServerName supaya tidak ada warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# -------------------------------------------------
# 4. Ubah DocumentRoot ke folder /public
# -------------------------------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf || true

# -------------------------------------------------
# 5. Copy project ke container
# -------------------------------------------------
COPY . /var/www/html

# -------------------------------------------------
# 6. Install Composer
# -------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader --no-dev

# -------------------------------------------------
# 7. Permission untuk Lumen (storage & cache)
# -------------------------------------------------
RUN mkdir -p storage/logs \
             storage/framework/views \
             bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# -------------------------------------------------
# 8. Expose port (Railway pakai 80 untuk Apache image ini)
# -------------------------------------------------
EXPOSE 80

# Jalankan Apache
CMD ["apache2-foreground"]