# Panduan Operasional & Deployment Hosting
## SMP Negeri 4 Cepu — Hostinger Cloud Startup (LiteSpeed)

---

## 1. Spesifikasi Infrastruktur Produksi

Aplikasi di-deploy ke paket **Hostinger Cloud Startup** dengan alokasi sumber daya terdedikasi:
- **Unit Pemrosesan**: 4 vCPU Cores Terdedikasi
- **Memori Utama**: 4 GB (4096 MB) RAM
- **Penyimpanan**: 100 GB NVMe Storage (High I/O speed hingga 20.480 KB/s)
- **Web Server**: LiteSpeed Web Server Enterprise (LSWS) + LSPHP SAPI
- **Zend OPcache**: Terpasang (Alokasi 384 MB memori)
- **Mesin Basis Data**: MariaDB 10.11+ / MySQL 8.0 (Batas koneksi: 500 global, 100 per user)
- **Batas PHP**: `memory_limit` 3072 MB, `max_execution_time` 480s, `upload_max_filesize` 3072 MB
- **Domain Resmi**: `https://smpn4cepu.sch.id`

---

## 2. Struktur Tata Kelola Folder (Security Isolation)

Di lingkungan hosting terkelola cPanel/hPanel, menempatkan seluruh root Laravel di dalam `public_html` berisiko membocorkan file kredensial `.env` dan kode sumber jika server mengalami galat parsing. 

Oleh karena itu, arsitektur yang diterapkan adalah **Isolasi Folder Privat**:

```text
/home/uXXXXX/domains/smpn4cepu.sch.id/
├── laravel_app/                     # Direktori Privat (Semua source code Laravel)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── storage/
│   ├── vendor/
│   ├── .env                         # Aman dari akses HTTP web publik
│   └── deploy.sh
│
└── public_html/                     # Symbolic Link ke /laravel_app/public
    ├── index.php
    ├── .htaccess                    # Direktif routing & bypass LSCache
    ├── build/                       # Aset CSS & JS Vite
    └── storage/                     # Symlink ke laravel_app/storage/app/public
```

---

## 3. Langkah Demi Langkah Deployment Awal (Initial Setup)

### Langkah 1: Akses SSH Hostinger
Buka terminal dan lakukan autentikasi SSH ke server Hostinger:
```bash
ssh -p 65002 uXXXXX@smpn4cepu.sch.id
```

### Langkah 2: Kloning Repositori ke Folder Privat
Arahkan direktori ke root domain dan kloning repositori GitHub ke folder `laravel_app`:
```bash
cd /home/uXXXXX/domains/smpn4cepu.sch.id/
git clone https://github.com/organisasi/smpn4cepu.git laravel_app
cd laravel_app
```

### Langkah 3: Konfigurasi File Lingkungan (.env)
Salin `.env.example` menjadi `.env` produksi:
```bash
cp .env.example .env
nano .env
```
Sesuaikan parameter database dan URL:
```dotenv
APP_NAME="SMP Negeri 4 Cepu"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://smpn4cepu.sch.id
APP_TIMEZONE="Asia/Jakarta"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uXXXXX_smp4cepu
DB_USERNAME=uXXXXX_dbuser
DB_PASSWORD="PasswordKuatMariaDB!"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

Generate application encryption key:
```bash
php artisan key:generate
```

### Langkah 4: Hubungkan Symlink `public_html` dan `storage`
Hapus folder `public_html` kosong bawaan hPanel, lalu ganti dengan tautan simbolik ke `laravel_app/public`:
```bash
cd /home/uXXXXX/domains/smpn4cepu.sch.id/
rm -rf public_html
ln -s /home/uXXXXX/domains/smpn4cepu.sch.id/laravel_app/public public_html

# Buat storage symlink
cd laravel_app
php artisan storage:link
```

### Langkah 5: Pasang Dependensi & Jalankan Migrasi
```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan db:seed --force
```

### Langkah 6: Kompilasi & Optimalisasi Cache Produksi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components
```

---

## 4. Konfigurasi Khusus LiteSpeed Server (`public/.htaccess`)

Pastikan file `public/.htaccess` memuat konfigurasi bypass caching untuk Filament Admin dan Livewire dynamic POST:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Bypass LiteSpeed Cache untuk rute dinamis (Admin, Livewire, Pilketos, Auth, Materi, Kuis, Reservasi)
<IfModule LiteSpeed>
    CacheLookup on
    RewriteCond %{REQUEST_URI} ^/(admin|livewire|pilketos|login|logout|materi|latihan-soal|perpustakaan/reservasi)
    RewriteRule .* - [E=Cache-Control:no-cache]
</IfModule>

# Header Keamanan HTTP Standar
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

---

## 5. Skrip Otomasi Deployment (`deploy.sh`)

Untuk rilis awal maupun pembaruan berkala via Git hook atau SSH hPanel, cukup jalankan skrip `deploy.sh`:

```bash
chmod +x deploy.sh
./deploy.sh
```

Skrip `deploy.sh` telah dilengkapi otomatisasi penuh setara PaaS/Docker:
- Memuat Node.js/NVM secara otomatis jika tersedia, atau menggunakan *pre-built* `public/build/manifest.json` dari repositori Git sehingga bebas galat `ViteManifestNotFoundException`.
- Menjalankan `php artisan migrate --force` dan **otomatis menjalankan `DatabaseSeeder`** apabila basis data baru dibuat (tabel GTK/berita masih kosong) atau jika `RUN_SEEDER=true`.
- Menyalin seluruh aset gambar bawaan dari `public/images/{staff,facilities,principal,assets}` ke `storage/app/public/` sebelum menautkan `php artisan storage:link`.
- Menghubungkan *symlink* `../public_html` ke `laravel_app/public` secara aman dan membersihkan cache LiteSpeed Web Server.

---

## 6. Prosedur Pencadangan (Backup) & Pemulihan (Disaster Recovery)

1. **Pencadangan Basis Data Otomatis**:
   Aktifkan fitur hPanel *Daily Automated Backups* yang mencakup snapshot basis data MariaDB dan file direktori.
2. **Pencadangan Manual via CLI**:
   ```bash
   mysqldump -u uXXXXX_dbuser -p uXXXXX_smp4cepu > backup_smp4cepu_$(date +%F).sql
   ```
3. **Penyimpanan Media**:
   Arsip berkas di `storage/app/public` (gambar berita, foto guru, sampul buku, berkas materi) dapat disinkronisasi ke Google Drive atau cold storage eksternal.
