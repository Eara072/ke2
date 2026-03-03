# Gunakan image PHP 8.2 dengan Apache
FROM php:8.2-apache

# 1. FIX MPM CONFLICT: Disable event/worker, enable prefork secara bersih
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork && \
    # Hapus symlink yang mungkin tersisa
    rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf

# 2. Instal dependensi sistem
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 3. Instal ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. Aktifkan mod_rewrite
RUN a2enmod rewrite

# 5. ServerName untuk hilangkan warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 6. Set Document Root ke /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

# 7. Instal Composer LEBIH AWAL (sebelum COPY semua file)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 8. Copy composer files dulu (layer caching lebih efisien)
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 9. Baru copy semua file (perubahan kode tidak invalidate layer composer)
COPY . /var/www/html

# 10. Atur izin akses
RUN mkdir -p storage/logs \
             storage/framework/views \
             storage/framework/cache \
             storage/framework/sessions \
             bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# 11. PORT dinamis Railway — ENV dulu, BARU sed
ENV PORT=80
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g' \
        /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]