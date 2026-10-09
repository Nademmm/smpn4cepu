# 🌐 Panduan Deployment Otomatis PaaS (Railway & Coolify)

Dokumentasi ini menjelaskan konfigurasi otomatisasi deployment platform-as-a-service (PaaS) untuk proyek **SMP Negeri 4 Cepu** (Laravel 11 + Filament v3 + MySQL).

---

## ⚡ Berkas Otomatisasi yang Telah Disediakan

1. **`Dockerfile`**: *Multi-stage container* produksi (Node.js 20 untuk Vite + FrankenPHP/PHP 8.2 Bookworm). Sangat cepat, hemat RAM, dan otomatis menangani port dinamis (`$PORT`).
2. **`docker/entrypoint.sh`**: Skrip otomatisasi saat booting kontainer yang menjalankan:
   - Migrasi basis data (`php artisan migrate --force`).
   - Eksekusi *seeder* resmi jika variabel lingkungan `RUN_SEEDER=true`.
   - Symlink penyimpanan berkas (`php artisan storage:link`).
   - Caching rute, konfigurasi, tampilan, dan komponen Filament (`php artisan optimize`).
   - Pengaturan hak akses folder `storage/` dan `bootstrap/cache/`.
3. **`docker/Caddyfile`**: Konfigurasi server web Caddy (FrankenPHP) dengan kompresi zstd/gzip dan proteksi berkas sensitif.
4. **`railway.json`**: Konfigurasi otomatisasi jika menggunakan **Railway**.
5. **`docker-compose.yml`**: Konfigurasi siap pakai untuk **Coolify** (Self-hosted PaaS) atau pengujian lokal lengkap dengan MySQL 8.0 dan persistent volume.
6. **`.github/workflows/deploy.yml`**: Pipa CI/CD GitHub Actions untuk pengujian otomatis sebelum deploy.

---

## 🚀 Panduan Deploy Opsi 1: Railway (Paling Cepat & Praktis)

### Langkah 1: Hubungkan Repositori GitHub ke Railway
1. Buka [railway.app](https://railway.app) dan login dengan akun GitHub Anda.
2. Klik tombol **New Project** -> Pilih **Deploy from GitHub repo**.
3. Pilih repositori `smpn4cepu`.

### Langkah 2: Tambahkan Layanan Database MySQL
1. Di dalam Dashboard Canvas Railway, klik tombol **Create / + Add Service**.
2. Pilih **Database** -> **MySQL**.
3. Railway akan membuat instance MySQL dan menyediakan variabel koneksi internal secara otomatis.

### Langkah 3: Konfigurasi Environment Variables di Railway
Buka service aplikasi `smpn4cepu`, masuk ke tab **Variables**, lalu tambahkan:

```env
APP_NAME="SMP Negeri 4 Cepu"
APP_ENV=production
APP_KEY=base64:68B9K+Vd8L2tF6zD216aPZ+qI3p2H3lG9X6m1oZ5P9U=
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_TIMEZONE="Asia/Jakarta"

# Koneksi Database (Gunakan Reference bawaan Railway)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Inisialisasi Akun & Data Resmi Sekolah (Hanya aktifkan saat deploy pertama kali)
RUN_SEEDER=true
```

> **Tips:** Setelah deploy pertama berhasil dan akun default (Admin, Guru, Perpus, Pilketos) berhasil dibuat, ubah nilai `RUN_SEEDER` menjadi `false` agar seeder tidak dijalankan ulang pada deploy berikutnya.

### Langkah 4: Tambahkan Volume Penyimpanan (Persistent Storage)
Agar foto profil guru, sampul buku perpustakaan, dan berkas materi tidak hilang saat redeploy:
1. Di service `smpn4cepu`, buka tab **Settings** -> bagian **Volumes**.
2. Klik **Add Volume**.
3. Tentukan Mount Path: `/app/storage/app/public`.

Railway akan otomatis melakukan build via `Dockerfile` dan aplikasi langsung online dengan domain HTTPS gratis!

---

## 🛡️ Panduan Deploy Opsi 2: Coolify (Paling Hemat untuk Sekolah)

Coolify adalah open-source PaaS yang diinstal pada VPS (seperti IDCloudHost atau Biznet Gio).

### Langkah Deployment di Coolify:
1. Di dashboard Coolify, buat **New Project** -> **New Resource** -> **Docker Compose**.
2. Hubungkan repositori GitHub `smpn4cepu`.
3. Coolify akan otomatis membaca berkas `docker-compose.yml`.
4. Isi variabel lingkungan (`APP_KEY`, dll.) pada menu **Environment Variables**.
5. Klik **Deploy**.
6. Hubungkan domain sekolah Anda (misal `smpn4cepu.sch.id`), Coolify akan otomatis menerbitkan sertifikat SSL Let's Encrypt secara gratis.

---

## 🔐 Kredensial Akun Default Setelah Deploy:

| Peran (Role) | Alamat Email | Kata Sandi |
| :--- | :--- | :--- |
| **Super Admin** | `admin@smpn4cepu.sch.id` | `AdminCepu2026!` |
| **Guru** | `guru@smpn4cepu.sch.id` | `GuruCepu2026!` |
| **Staf Perpus** | `perpus@smpn4cepu.sch.id` | `PerpusCepu2026!` |
| **Panitia Pilketos**| `pilketos@smpn4cepu.sch.id` | `PilketosCepu2026!` |
