# Gunakan image PHP 8.2 dengan Apache
FROM php:8.2-apache

# 1. FIX ULTIMATE: Hapus paksa modul MPM yang bentrok
# Kita tidak hanya mematikan, tapi menghapus file konfigurasinya secara fisik 
# agar Apache tidak punya pilihan lain selain menggunakan mpm_prefork (wajib untuk PHP).
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf && \
    a2enmod mpm_prefork

# 2. Instal dependensi sistem yang diperlukan
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# 3. Instal ekstensi PHP (pdo_mysql sangat penting untuk Aiven)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. Aktifkan mod_rewrite Apache agar routing Lumen jalan
RUN a2enmod rewrite

# Tambahkan ServerName untuk menghilangkan warning di log
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 5. Ubah Document Root Apache ke folder /public milik Lumen
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Salin semua file kodingan ke dalam container
COPY . /var/www/html

# 7. Atur izin akses (Permission) folder agar Lumen bisa menulis log/cache
# Kita buat foldernya dulu agar tidak error jika folder tidak ada di GitHub
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/framework/views \
             /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Instal Composer dan jalankan install dependensi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 9. Pengaturan Port Dinamis (Sangat Penting untuk Railway)
# Kita paksa Apache untuk mendengarkan port yang diberikan oleh Railway lewat variabel $PORT.
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

# Default Port jika variabel tidak terbaca
ENV PORT 80
EXPOSE 80

# Jalankan Apache di foreground
CMD ["apache2-foreground"]