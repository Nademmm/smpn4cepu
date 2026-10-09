# ========================================================
# Dockerfile Produksi: SMP Negeri 4 Cepu
# Stack: PHP 8.2 + FrankenPHP (Caddy) + Node 20 (Vite)
# Kompatibel: Railway, Coolify, Fly.io, Render, VPS Docker
# ========================================================

# --------------------------------------------------------
# Tahap 1: Build Aset Frontend (Tailwind CSS, Vite, Chart.js)
# --------------------------------------------------------
FROM node:20-bookworm-slim AS frontend-builder
WORKDIR /app

# Salin package.json & lockfile
COPY package*.json ./
# Gunakan npm install agar dependensi biner Linux (seperti rollup/vite) terpasang dengan benar
RUN npm install --prefer-offline --no-audit

# Salin sumber daya aset frontend dan file yang dipindai Tailwind
COPY resources ./resources
COPY app ./app
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public ./public

# Kompilasi aset produksi ke public/build
RUN npm run build

# --------------------------------------------------------
# Tahap 2: Runtime Produksi (FrankenPHP + PHP 8.2 Bookworm)
# --------------------------------------------------------
FROM dunglas/frankenphp:1-php8.2-bookworm AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    PORT=80

# Pasang ekstensi PHP yang dibutuhkan Laravel, Livewire, dan Filament
RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    bcmath \
    exif \
    gd \
    opcache \
    pcntl

WORKDIR /app

# Salin composer.json & composer.lock untuk caching layer Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Salin seluruh kode sumber proyek
COPY . .

# Salin hasil kompilasi aset dari Tahap 1
COPY --from=frontend-builder /app/public/build ./public/build

# Optimasi autoloader Composer (--no-scripts mencegah artisan filament:upgrade dijalankan saat build tanpa DB)
RUN composer dump-autoload --optimize --no-dev --no-scripts --classmap-authoritative

# Salin konfigurasi Caddyfile & script entrypoint
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Normalisasi baris baru LF (mencegah error CRLF dari Windows) dan beri izin eksekusi
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Konfigurasi hak akses direktori storage dan cache
RUN mkdir -p /app/storage /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 80 443

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
