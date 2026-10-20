#!/bin/bash
# ========================================================
# Script Deployment Produksi SMP Negeri 4 Cepu
# Target: Hostinger Cloud Startup (LiteSpeed + MariaDB/MySQL)
# Domain: https://smpn4cepu.sch.id
# ========================================================
set -e

echo "=========================================================="
echo "🚀 Memulai Deployment SMP Negeri 4 Cepu (Hostinger Cloud)"
echo "=========================================================="

# 0. Muat NVM / Node.js jika tersedia di environment SSH Hostinger
export NVM_DIR="$HOME/.nvm"
if [ -s "$NVM_DIR/nvm.sh" ]; then
    . "$NVM_DIR/nvm.sh"
fi

# 1. Pastikan file .env tersedia
if [ ! -f ".env" ]; then
    echo "==> [0/7] File .env belum ditemukan, menyalin dari .env.example..."
    cp .env.example .env
    php artisan key:generate --ansi
    echo "⚠️ PENTING: Silakan sesuaikan kredensial DB_CONNECTION=mysql di file .env!"
fi

echo "==> [1/7] Memasang dependensi PHP Composer & membangun aset Frontend..."
composer install --no-dev --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
    echo "    Node.js ($(node -v)) terdeteksi, mengompilasi aset Vite..."
    npm ci --no-audit && npm run build
elif [ -f "public/build/manifest.json" ]; then
    echo "    Node.js tidak ditemukan di server, menggunakan pre-built Vite manifest (public/build/manifest.json)."
else
    echo "❌ ERROR: Node.js tidak tersedia dan public/build/manifest.json belum ter-commit!"
    exit 1
fi

echo "==> [2/7] Menjalankan migrasi basis data produksi..."
php artisan config:clear || true
php artisan migrate --force

echo "==> [3/7] Memeriksa kelengkapan Master Data & Seeder..."
if grep -q "^RUN_SEEDER=true" .env 2>/dev/null || [ "$RUN_SEEDER" = "true" ]; then
    echo "    RUN_SEEDER=true aktif: Menjalankan DatabaseSeeder..."
    php artisan db:seed --force || echo "⚠️ Seeder selesai atau dilewati."
else
    php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();
        try {
            if (\App\Models\StaffMember::count() === 0 || \App\Models\Post::count() === 0) {
                exit(10);
            }
        } catch (\Throwable \$e) {
            exit(0);
        }
        exit(0);
    " 2>/dev/null && NEED_SEED=0 || NEED_SEED=1

    if [ "$NEED_SEED" -eq 1 ]; then
        echo "    Basis data masih kosong. Menjalankan auto-seeder data resmi SMPN 4 Cepu..."
        php artisan db:seed --force || echo "⚠️ Seeder selesai atau dilewati."
    else
        echo "    Master data sudah tersedia di basis data."
    fi
fi

echo "==> [4/7] Menyinkronkan aset media bawaan & memperbarui tautan storage (Symlink)..."
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
php artisan storage:link || true

echo "==> [5/7] Memastikan direktori public_html terhubung ke folder public Laravel..."
CURRENT_DIR="$(pwd)"
if [ "$(basename "$CURRENT_DIR")" = "laravel_app" ]; then
    if [ ! -L "../public_html" ]; then
        echo "    Membuat symlink ../public_html -> $CURRENT_DIR/public..."
        rm -rf "../public_html"
        ln -s "$CURRENT_DIR/public" "../public_html"
    else
        echo "    Symlink ../public_html sudah terhubung dengan benar."
    fi
fi

echo "==> [6/7] Mengoptimalkan caching konfigurasi, route, view, ikon, dan komponen Filament..."
php artisan cache:clear || true
php artisan filament:assets || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache || true
php artisan filament:cache-components || true

echo "==> [7/7] Mengatur hak akses direktori & membersihkan LiteSpeed Cache..."
chmod -R 775 storage bootstrap/cache || true
touch storage/framework/cache/.litespeed_purge || true

echo "=========================================================="
echo "✅ Selesai! Portal Resmi SMP Negeri 4 Cepu siap melayani."
echo "=========================================================="
