# Gunakan image PHP 8.2 FPM (Standar industri untuk kestabilan)
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

# 3. Konfigurasi Nginx (Anti CORS & Anti MPM Error)
RUN printf "server {\n\
    listen 80;\n\
    server_name localhost;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
    location / {\n\
        add_header 'Access-Control-Allow-Origin' '*' always;\n\
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS, PUT, DELETE' always;\n\
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With' always;\n\
        if (\$request_method = 'OPTIONS') {\n\
            add_header 'Access-Control-Allow-Origin' '*' always;\n\
            add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS, PUT, DELETE' always;\n\
            add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-Requested-With' always;\n\
            return 204;\n\
        }\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
    }\n\
}" > /etc/nginx/sites-available/default

# 4. Salin kodingan
WORKDIR /var/www/html
COPY . /var/www/html

# 5. Atur Folder & Izin
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/framework/views \
             /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 7. Start Service (Nginx & PHP-FPM)
EXPOSE 80
CMD sh -c "sed -i 's/listen 80;/listen '\"${PORT:-80}\"';/g' /etc/nginx/sites-available/default && php-fpm -D && nginx -g 'daemon off;'"
