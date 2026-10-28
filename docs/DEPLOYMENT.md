# Panduan Operasional & Deployment Hosting
## SMP Negeri 4 Cepu: Hostinger Cloud Startup (LiteSpeed)

---

## 1. Spesifikasi Infrastruktur Produksi

Aplikasi di-deploy ke paket **Hostinger Cloud Startup** dengan alokasi sumber daya terdedikasi:
- **Unit Pemrosesan**: 4 vCPU Cores Terdedikasi
- **Memori Utama**: 4 GB (4096 MB) RAM
- **Penyimpanan**: 100 GB NVMe Storage (High I/O speed hingga 20.480 KB/s)
- **Web Server**: LiteSpeed Web Server Enterprise (LSWS) + LSPHP 8.3 SAPI
- **Zend OPcache**: Terpasang (Alokasi 384 MB memori)
- **Mesin Basis Data**: MariaDB 10.11+ / MySQL 8.0 (Batas koneksi: 500 global, 100 per user)
- **Batas PHP**: `memory_limit` 3072 MB, `max_execution_time` 480s, `upload_max_filesize` 3072 MB
- **Domain Resmi**: `https://smpn4cepu.sch.id`
- **Port SSH Standar**: `65002`

---

## 2. Struktur Tata Kelola Folder (Security Isolation)

Di lingkungan hosting terkelola cPanel/hPanel, menempatkan seluruh root Laravel di dalam `public_html` berisiko membocorkan file kredensial `.env` dan kode sumber jika server mengalami galat parsing. 

Oleh karena itu, arsitektur yang diterapkan adalah **Isolasi Folder Privat**:

```text
/home/u652241328/domains/smpn4cepu.sch.id/
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
    ├── .htaccess                    # Direktif routing, 301 Apex, & bypass LSCache
    ├── build/                       # Aset CSS & JS Vite
    └── storage/                     # Symlink ke laravel_app/storage/app/public
```

---

## 3. Langkah Demi Langkah Deployment Awal (Initial Setup)

### Langkah 1: Akses SSH Hostinger
Buka terminal dan lakukan autentikasi SSH ke IP Server Hostinger Anda:
```bash
ssh -p 65002 u652241328@<IP_SERVER_HOSTINGER>
```

### Langkah 2: Kloning Repositori ke Folder Privat
Arahkan direktori ke root domain dan kloning repositori GitHub ke folder `laravel_app`:
```bash
cd /home/u652241328/domains/smpn4cepu.sch.id/
git clone https://github.com/nadem-ai/smpn4cepu.git laravel_app
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
DB_DATABASE=u652241328_smp4cepu
DB_USERNAME=u652241328_admin
DB_PASSWORD="PasswordKuatMariaDB!"

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
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
cd /home/u652241328/domains/smpn4cepu.sch.id/
rm -rf public_html
ln -s /home/u652241328/domains/smpn4cepu.sch.id/laravel_app/public public_html

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

File `public/.htaccess` telah dikonfigurasi untuk menangani bypass caching untuk Filament Admin, Livewire dynamic POST, modul ujian kuis CBT, serta pengalihan kanonikal 301 dari `www` ke apex domain:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # 1. Canonical Redirect www ke Apex Domain (SEO Best Practice)
    RewriteCond %{HTTP_HOST} ^www\.smpn4cepu\.sch\.id$ [NC]
    RewriteRule ^(.*)$ https://smpn4cepu.sch.id/$1 [L,R=301]

    # 2. Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # 3. Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # 4. Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Bypass LiteSpeed Cache untuk rute dinamis (Admin, Livewire, Pilketos, Auth, Kuis, Reservasi)
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

## 5. Pipa Otomasi CI/CD GitHub Actions & Skrip `deploy.sh`

### 5.1 Alur Kerja GitHub Actions (`.github/workflows/deploy.yml`)

Setiap push ke branch `main` akan memicu pipeline otomatis:
1. **Automated Testing**: Menjalankan seluruh pengujian PHPUnit (24 tests, 216 assertions) di lingkungan PHP 8.3 & Node.js 20.
2. **Fresh Vite Build**: Mengompilasi aset CSS dan JS secara segar di runner GitHub Actions.
3. **SCP Asset Sync**: Mengunggah folder `public/build` terkompilasi langsung ke direktori aplikasi di Hostinger melalui port 65002.
4. **SSH Deployment Execution**: Menjalankan skrip `deploy.sh` di server secara otomatis.
5. **Health Check Verification**: Memverifikasi respons HTTP 200 dari portal resmi.

### 5.2 Rahasia GitHub Actions (GitHub Repository Secrets)

Pastikan rahasia berikut telah didaftarkan pada menu **Settings -> Secrets and variables -> Actions**:

| Nama Secret | Deskripsi & Nilai yang Wajib Diisi |
| :--- | :--- |
| `HOSTINGER_HOST` | **IP Server Asli Hostinger** (Contoh: `195.35.x.x`). **PENTING**: Jangan gunakan domain `smpn4cepu.sch.id` karena proxy CDN Hostinger menutup port 65002 dan menyebabkan galat koneksi *i/o timeout*. Gunakan IP dedicated yang tercantum di menu SSH Access hPanel. |
| `HOSTINGER_PORT` | `65002` (Port SSH resmi Hostinger Cloud). |
| `HOSTINGER_USER` | Username SSH hPanel (Contoh: `u652241328`). |
| `HOSTINGER_SSH_KEY` | Private SSH Key (format OpenSSH) yang pasangan Public Key-nya sudah diaktifkan di hPanel Hostinger. |
| `HOSTINGER_APP_DIR` | (Opsional) Path root aplikasi. Default: `/home/u652241328/domains/smpn4cepu.sch.id/laravel_app`. |

### 5.3 Skrip Otomasi Deployment (`deploy.sh`)

Skrip `deploy.sh` menangani pembaruan aplikasi secara mandiri:
- Memasang paket Composer produksi dengan `--no-dev --optimize-autoloader`.
- Menjalankan migrasi basis data (`php artisan migrate --force`).
- **Otomatisasi Database Seeding**: Mengecek kondisi basis data. Jika tabel GTK atau berita kosong, otomatis menjalankan `DatabaseSeeder` (termasuk 609 siswa dan 33 GTK).
- Sinkronisasi aset media: Menyalin foto bawaan dari `public/images/` ke `storage/app/public/` sebelum menjalankan `php artisan storage:link`.
- Membersihkan dan mengompilasi ulang seluruh cache sistem (config, route, view, event, Filament components).
- Mereset cache LiteSpeed Web Server.

---

## 6. Prosedur Pencadangan (Backup) & Pemulihan (Disaster Recovery)

1. **Pencadangan Basis Data Otomatis**:
   Aktifkan fitur hPanel *Daily Automated Backups* yang mencakup snapshot basis data MariaDB harian dan seluruh berkas direktori.
2. **Pencadangan Manual via CLI**:
   ```bash
   mysqldump -u u652241328_admin -p u652241328_smp4cepu > backup_smp4cepu_$(date +%F).sql
   ```
3. **Penyimpanan Media & Dokumen**:
   Folder `storage/app/public` (gambar berita, foto guru, arsip dokumen unduhan) dapat disinkronisasi ke Google Drive atau cold storage sekunder secara berkala.
