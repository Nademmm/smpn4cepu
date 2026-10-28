#!/bin/bash
# ========================================================
# Script Deployment Otomatis Produksi SMP Negeri 4 Cepu
# Target: Hostinger Cloud (LiteSpeed + PHP 8.3 + MariaDB/SQLite)
# Domain: https://smpn4cepu.sch.id
# ========================================================
set -e

APP_DIR="/home/u652241328/domains/smpn4cepu.sch.id/laravel_app"
DOCROOT="/home/u652241328/domains/smpn4cepu.sch.id/public_html"

echo "=========================================================="
echo "🚀 Memulai Deployment Otomatis SMP Negeri 4 Cepu"
echo "Tanggal & Waktu: $(date)"
echo "=========================================================="

# Masuk ke direktori aplikasi Laravel
cd "$APP_DIR"

# 1. Aktifkan Maintenance Mode jika artisan tersedia
if [ -f artisan ]; then
    echo "==> [1/8] Mengaktifkan mode pemeliharaan (Maintenance Mode)..."
    php artisan down --retry=15 || true
fi

# 2. Ambil perubahan kode terbaru dari GitHub
echo "==> [2/8] Mengambil kode terbaru dari origin/main..."
git fetch origin main
git reset --hard origin/main

# 3. Pasang / Perbarui dependensi Composer (tanpa scripts untuk menghindari pembatasan proc_open di shared/cloud)
echo "==> [3/8] Menginstal dependensi Composer..."
composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader --no-interaction

# 4. Registrasi package & publikasikan aset Filament
echo "==> [4/8] Menjalankan package discover & aset Filament..."
php artisan package:discover --ansi
php artisan filament:assets --ansi

# 5. Sinkronisasi aset media bawaan ke storage
echo "==> [5/8] Memastikan struktur direktori media dan storage..."
mkdir -p storage/app/public/staff storage/app/public/facilities storage/app/public/principal storage/app/public/assets
if [ -d "public/images/staff" ]; then
    cp -rn public/images/staff/* storage/app/public/staff/ 2>/dev/null || true
fi
if [ -d "public/images/facilities" ]; then
    cp -rn public/images/facilities/* storage/app/public/facilities/ 2>/dev/null || true
fi
if [ -d "public/images/principal" ]; then
    cp -rn public/images/principal/* storage/app/public/principal/ 2>/dev/null || true
fi
if [ -d "public/images/assets" ]; then
    cp -rn public/images/assets/* storage/app/public/assets/ 2>/dev/null || true
fi

# 6. Sinkronisasi seluruh file public ke public_html
echo "==> [6/8] Menyinkronkan aset ke direktori web public_html..."
rsync -av --exclude='index.php' --exclude='.htaccess' --exclude='storage' --exclude='robots.txt' --exclude='sitemap.xml' "$APP_DIR/public/" "$DOCROOT/"

# robots.txt, sitemap.xml, dan feed.xml dilayani dinamis oleh Laravel (SeoController).
# Hapus salinan statis usang di docroot karena berkas statis menimpa rute dinamis.
rm -f "$DOCROOT/robots.txt" "$DOCROOT/sitemap.xml" "$DOCROOT/feed.xml"

# Pastikan symlink storage terhubung langsung via shell
ln -sfn "$APP_DIR/storage/app/public" "$DOCROOT/storage"

# 7. Eksekusi migrasi basis data
echo "==> [7/8] Menjalankan migrasi basis data..."
php artisan migrate --force

# 8. Rebuild cache produksi untuk performa maksimal
echo "==> [8/8] Membangun ulang cache konfigurasi, route, view, dan Filament..."
php artisan optimize:clear
php artisan optimize
php artisan icons:cache || true
php artisan filament:cache-components || true

# Nonaktifkan maintenance mode
echo "==> Mengaktifkan kembali aplikasi ke publik..."
php artisan up

# Health check
echo "==> Menjalankan Health Check..."
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" -k https://smpn4cepu.sch.id || echo "000")
echo ">> HTTP Status: $STATUS_CODE"

if [ "$STATUS_CODE" -eq 200 ]; then
    echo "=========================================================="
    echo "✅ DEPLOYMENT BERHASIL! (HTTP 200 OK)"
    echo "=========================================================="
    exit 0
else
    echo "=========================================================="
    echo "⚠️ PERINGATAN: Health check mengembalikan HTTP $STATUS_CODE"
    echo "=========================================================="
    exit 0
fi
