#!/bin/sh
set -e

echo "=========================================================="
echo "🚀 Memulai SMP Negeri 4 Cepu Web Service (PaaS / Docker)"
echo "=========================================================="

# 1. Jalankan package discovery
echo "==> [1/6] Menemukan paket Laravel & Filament..."
php artisan package:discover --ansi || true

# 2. Jalankan migrasi basis data (dengan mekanisme retry hingga 5 percobaan)
echo "==> [2/6] Memeriksa dan menjalankan migrasi basis data..."
php artisan config:clear || true
MIGRATE_OK=0
for ATTEMPT in 1 2 3 4 5; do
    if php artisan migrate --force; then
        MIGRATE_OK=1
        break
    fi
    echo "⚠️ Percobaan migrasi ke-$ATTEMPT gagal, menunggu 2 detik sebelum mencoba kembali..."
    sleep 2
done
if [ "$MIGRATE_OK" -ne 1 ]; then
    echo "⚠️ Peringatan: Migrasi basis data gagal setelah 5 percobaan."
fi

# Jika RUN_SEEDER=true atau data utama masih kosong, jalankan seeding otomatis
echo "==> Memeriksa kelengkapan master data dan akun..."
if [ "$RUN_SEEDER" = "true" ]; then
    echo "==> Inisialisasi RUN_SEEDER=true: Menjalankan DatabaseSeeder..."
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
        echo "==> Basis data belum memiliki data lengkap. Menjalankan auto-seeder..."
        php artisan db:seed --force || echo "⚠️ Seeder selesai atau dilewati."
    else
        echo "==> Master data sudah lengkap."
    fi
fi


# 3. Pastikan symlink storage terhubung dan salin aset bawaan jika belum ada
echo "==> [3/6] Memastikan symlink storage publik..."
mkdir -p /app/storage/app/public/staff /app/storage/app/public/facilities /app/storage/app/public/principal /app/storage/app/public/assets
if [ -d "/app/public/images/staff" ]; then
    cp -rn /app/public/images/staff/* /app/storage/app/public/staff/ 2>/dev/null || true
fi
if [ -d "/app/public/images/facilities" ]; then
    cp -rn /app/public/images/facilities/* /app/storage/app/public/facilities/ 2>/dev/null || true
fi
if [ -d "/app/public/images/principal" ]; then
    cp -rn /app/public/images/principal/* /app/storage/app/public/principal/ 2>/dev/null || true
fi
if [ -d "/app/public/images/assets" ]; then
    cp -rn /app/public/images/assets/* /app/storage/app/public/assets/ 2>/dev/null || true
fi
php artisan storage:link || true

# 4. Optimalkan caching Laravel & Filament untuk performa produksi
echo "==> [4/6] Mengoptimalkan cache konfigurasi, route, view, ikon, dan komponen..."
php artisan cache:clear || true
php artisan filament:assets || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true
php artisan icons:cache || true
php artisan filament:cache-components || true

# 5. Pastikan kepemilikan dan hak akses direktori storage & bootstrap/cache
echo "==> [5/6] Memperbarui hak akses direktori storage dan cache..."
chmod -R 775 /app/storage /app/bootstrap/cache || true
chown -R www-data:www-data /app/storage /app/bootstrap/cache || true

# 6. Eksekusi proses server web
echo "==> [6/6] Menjalankan server web pada PORT: ${PORT:-80}..."
exec "$@"
