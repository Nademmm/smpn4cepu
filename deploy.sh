#!/bin/bash
# ========================================================
# Script Deployment Git SMP Negeri 4 Cepu (Hostinger Cloud)
# ========================================================
set -e

echo "==> [1/6] Memasang dependensi Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> [2/6] Menjalankan migrasi basis data..."
php artisan migrate --force

echo "==> [3/6] Mengoptimalkan caching konfigurasi, route, dan view..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components || true

echo "==> [4/6] Memperbarui tautan storage (Symlink)..."
php artisan storage:link || true

echo "==> [5/6] Memastikan direktori public_html terhubung..."
if [ ! -L "/home/$USER/public_html" ]; then
    echo "Symlink public_html belum ada, membuat symlink baru..."
    rm -rf "/home/$USER/public_html"
    ln -s "/home/$USER/laravel_app/public" "/home/$USER/public_html"
fi

echo "==> [6/6] Membersihkan cache LiteSpeed Web Server..."
touch /home/$USER/laravel_app/storage/framework/cache/.litespeed_purge || true

echo "==> Selesai! Proyek SMP Negeri 4 Cepu siap digunakan."
