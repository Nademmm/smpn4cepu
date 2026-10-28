# Panduan Deployment Kontainer PaaS (Coolify & Docker)
## SMP Negeri 4 Cepu: Lingkungan Staging & Kontainer

Dokumentasi ini menjelaskan konfigurasi deployment berbasis kontainer (Docker) dan platform PaaS terkelola mandiri (Coolify) untuk pengujian staging atau alternatif deployment VPS.

> **Catatan Lingkungan Produksi**: Lingkungan produksi utama resmi SMP Negeri 4 Cepu menggunakan **Hostinger Cloud Startup (LiteSpeed Web Server Enterprise)**. Untuk panduan rilis produksi resmi, silakan merujuk ke [docs/DEPLOYMENT.md](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/DEPLOYMENT.md).

---

## 1. Berkas Kontainerisasi yang Disediakan

1. **`Dockerfile`**: *Multi-stage container* produksi berbasis Node.js 20 untuk build aset Vite dan FrankenPHP/PHP 8.2+ Debian Bookworm. Hemat memori dan otomatis menangani port dinamis (`$PORT`).
2. **`docker/entrypoint.sh`**: Skrip inisialisasi kontainer yang otomatis mengeksekusi:
   - Migrasi basis data (`php artisan migrate --force`).
   - Eksekusi seeder resmi jika `RUN_SEEDER=true`.
   - Symlink penyimpanan berkas publik (`php artisan storage:link`).
   - Optimalisasi cache Laravel dan Filament (`php artisan optimize`).
   - Penyesuaian hak akses direktori `storage/` dan `bootstrap/cache/`.
3. **`docker/Caddyfile`**: Konfigurasi server web Caddy (FrankenPHP) dengan kompresi zstd/gzip dan proteksi berkas sensitif.
4. **`docker-compose.yml`**: Konfigurasi siap pakai untuk **Coolify** (Self-hosted PaaS) atau pengujian lokal lengkap dengan MySQL 8.0 dan persistent storage volume.

---

## 2. Panduan Deployment dengan Coolify (Self-Hosted PaaS)

Coolify adalah platform PaaS open-source modern yang berjalan di atas VPS Linux (seperti IDCloudHost, Biznet Gio, atau DigitalOcean).

### Langkah Deployment di Coolify:
1. Masuk ke dashboard Coolify Anda.
2. Buat **New Project** -> **New Resource** -> pilih **Docker Compose**.
3. Hubungkan repositori GitHub `smpn4cepu`.
4. Coolify akan mendeteksi dan membaca berkas `docker-compose.yml`.
5. Buka tab **Environment Variables** dan lengkapi variabel berikut:
   ```dotenv
   APP_NAME="SMP Negeri 4 Cepu"
   APP_ENV=production
   APP_KEY=base64:68B9K+Vd8L2tF6zD216aPZ+qI3p2H3lG9X6m1oZ5P9U=
   APP_DEBUG=false
   APP_URL=https://staging.smpn4cepu.sch.id
   APP_TIMEZONE="Asia/Jakarta"

   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=smp4cepu
   DB_USERNAME=smp_user
   DB_PASSWORD=PasswordKuatStaging123!

   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   FILESYSTEM_DISK=public

   # Jalankan seeder pada deployment awal pertama kali
   RUN_SEEDER=true
   ```
6. Tambahkan **Persistent Volume** pada direktori `/app/storage/app/public` agar aset gambar berita dan berkas materi tidak hilang saat redeploy.
7. Klik tombol **Deploy**. Coolify akan membangun image Docker, menjalankan migrasi, dan menerbitkan sertifikat SSL Let's Encrypt otomatis.
8. Setelah deployment awal berhasil, ubah nilai `RUN_SEEDER` menjadi `false` untuk mencegah duplikasi seeder pada rilis berikutnya.

---

## 3. Kredensial Akun Default Setelah Deploy

| Peran (Role) | Alamat Email / NISN | Kata Sandi | Akses Panel / Portal |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@smpn4cepu.sch.id` | `AdminCepu2026!` | Hak penuh atas seluruh modul di `/admin` |
| **Guru** | `guru@smpn4cepu.sch.id` | `GuruCepu2026!` | Manajemen materi ajar, bank soal, dan hasil kuis di `/admin` |
| **Staf Perpus** | `perpus@smpn4cepu.sch.id` | `PerpusCepu2026!` | Pengelolaan katalog buku, stok, dan reservasi di `/admin` |
| **Panitia Pilketos**| `pilketos@smpn4cepu.sch.id` | `PilketosCepu2026!` | Pengelolaan paslon dan pemantauan perolehan suara di `/admin` |
| **609 Siswa Aktif** | `{nisn}@siswa.smpn4cepu.sch.id` | `password123` | Portal belajar siswa (`/login`), latihan soal CBT, dan reservasi buku |
