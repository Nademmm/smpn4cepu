#!/bin/sh
set -e

echo "=========================================================="
echo "🚀 Memulai SMP Negeri 4 Cepu Web Service (PaaS / Docker)"
echo "=========================================================="

# 1. Jalankan package discovery
echo "==> [1/6] Menemukan paket Laravel & Filament..."
php artisan package:discover --ansi || true

# 2. Tunggu dan jalankan migrasi basis data jika koneksi DB telah dikonfigurasi
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "==> [2/6] Memeriksa koneksi basis data ke $DB_HOST:$DB_PORT..."
    
    # Jalankan migrasi basis data
    echo "==> Menjalankan migrasi basis data..."
    php artisan migrate --force || {
        echo "⚠️ Peringatan: Migrasi basis data gagal atau basis data belum siap."
    }

    # Jika RUN_SEEDER=true di env PaaS, jalankan seeding akun dan master data resmi
    if [ "$RUN_SEEDER" = "true" ]; then
        echo "==> Inisialisasi RUN_SEEDER terdeteksi: Menjalankan DatabaseSeeder..."
        php artisan db:seed --force || echo "⚠️ Seeder dilewati atau sudah terisi."
    fi
else
    echo "==> [2/6] DB_HOST belum diatur ke remote host. Melewati migrasi otomatis saat booting."
fi

# 3. Pastikan symlink storage terhubung dan salin aset staf jika belum ada
echo "==> [3/6] Memastikan symlink storage publik..."
mkdir -p /app/storage/app/public/staff
if [ -d "/app/public/images/staff" ]; then
    cp -rn /app/public/images/staff/* /app/storage/app/public/staff/ 2>/dev/null || true
fi
php artisan storage:link || true

# 4. Optimalkan caching Laravel & Filament untuk performa produksi
echo "==> [4/6] Mengoptimalkan cache konfigurasi, route, view, dan komponen..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true
php artisan filament:cache-components || true

# 5. Pastikan kepemilikan dan hak akses direktori storage & bootstrap/cache
echo "==> [5/6] Memperbarui hak akses direktori storage dan cache..."
chmod -R 775 /app/storage /app/bootstrap/cache || true
chown -R www-data:www-data /app/storage /app/bootstrap/cache || true

# 6. Eksekusi proses server web
echo "==> [6/6] Menjalankan server web pada PORT: ${PORT:-80}..."
exec "$@"
