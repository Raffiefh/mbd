FROM php:8.4-apache

# 1. Atasi konflik MPM Apache di awal sebelum konfigurasi site
RUN a2dismod -f mpm_event mpm_worker && a2enmod mpm_prefork

# 2. Install dependencies system dan PostgreSQL dev packages
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install PHP extensions (termasuk PDO PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# 4. Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 5. Copy seluruh file project ke dalam container
COPY . /var/www/html

# 6. Ubah Document Root Apache ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. Aktifkan mod_rewrite Apache untuk routing Laravel
RUN a2enmod rewrite

# 8. Jalankan composer install untuk production
RUN composer install --no-dev --optimize-autoloader

# 9. Berikan hak akses (permissions) ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80