# Menggunakan PHP dengan FPM (FastCGI Process Manager)
FROM php:8.4-fpm

# Instalasi dependensi sistem dan ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Bersihkan cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalasi ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Optimasi runtime Laravel saat memakai `php artisan serve` di Docker.
# Artisan serve berjalan pada PHP CLI, jadi OPcache CLI perlu aktif.
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=1"; \
    echo "opcache.memory_consumption=192"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=1"; \
    echo "opcache.revalidate_freq=2"; \
    echo "realpath_cache_size=4096K"; \
    echo "realpath_cache_ttl=600"; \
  } > /usr/local/etc/php/conf.d/zz-laravel-performance.ini

# Ambil Composer terbaru dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy kode Laravel ke dalam container
COPY . .

# Instal dependensi PHP agar /var/www/vendor tersedia saat container start.
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 8000

# Perintah untuk menjalankan server development Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
