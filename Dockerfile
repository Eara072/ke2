# Gunakan image PHP 8.2 FPM (Lebih ringan dan stabil untuk Cloud)
FROM php:8.2-fpm

# 1. Instal Nginx dan dependensi sistem
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Instal ekstensi PHP untuk MySQL (Aiven)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 3. Konfigurasi Nginx untuk Lumen
# Kita gunakan placeholder PORT agar bisa diganti secara dinamis saat start
RUN printf "server {\n\
    listen %s;\n\
    listen [::]:%s;\n\
    server_name localhost;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
    location / {\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
    }\n\
}" "80" "80" > /etc/nginx/sites-available/default

# 4. Salin kodingan ke folder kerja
WORKDIR /var/www/html
COPY . /var/www/html

# 5. Atur izin akses folder (Penting untuk Lumen)
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/framework/views \
             /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Instal Composer dan dependensi PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 7. Expose Port
EXPOSE 80

# 8. Jalankan PHP-FPM dan Nginx secara bersamaan
# Kita gunakan script shell untuk memastikan Nginx memakai port dari Railway ($PORT)
CMD sh -c "sed -i 's/listen 80;/listen '\"${PORT:-80}\"';/g' /etc/nginx/sites-available/default && php-fpm -D && nginx -g 'daemon off;'"