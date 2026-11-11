FROM php:8.4-apache

# Pastikan hanya satu MPM (mpm_prefork) yang aktif dengan aman
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# Install dependencies system dan PostgreSQL dev packages
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

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy seluruh file project
COPY . /var/www/html

# Ubah Document Root Apache ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Jalankan composer install
RUN composer install --no-dev --optimize-autoloader

# Berikan hak akses ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80