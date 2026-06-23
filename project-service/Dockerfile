# 1. Gunakan image dasar PHP 8.4 versi CLI
FROM php:8.4-cli

# 2. Install dependensi sistem operasi yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Bersihkan cache instalasi untuk membuat ukuran container lebih ringan
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install ekstensi PHP inti yang diwajibkan oleh Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. Install Composer langsung dari image resminya
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Tentukan lokasi kerja kita di dalam container Docker
WORKDIR /var/www

# 6. Salin SELURUH file dari laptop Anda (folder service ini) ke dalam container
COPY . .

# 7. Jalankan instalasi package Laravel (Composer) di dalam container
RUN composer install --no-interaction --optimize-autoloader

# 8. Buka port 8000 di dalam container
EXPOSE 8000

# 9. Perintah mutlak untuk menyalakan aplikasi saat container hidup
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]