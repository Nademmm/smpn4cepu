# SMP Negeri 4 Cepu — "Satu Sekolah, Satu Ruang Digital"

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![Filament PHP v3](https://img.shields.io/badge/Filament-v3.2-F59E0B?style=flat-square&logo=filament)](https://filamentphp.com)
[![Livewire v3](https://img.shields.io/badge/Livewire-v3.5-FB70A9?style=flat-square&logo=livewire)](https://livewire.laravel.com)
[![Tailwind CSS v3](https://img.shields.io/badge/TailwindCSS-v3.4-38B2AC?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)
[![Chart.js v4](https://img.shields.io/badge/Chart.js-v4.4-FF6384?style=flat-square&logo=chart.js)](https://www.chartjs.org)
[![Hostinger Cloud](https://img.shields.io/badge/Hostinger-Cloud%20Startup-673AB7?style=flat-square&logo=hostinger)](https://hostinger.com)
[![Tests Passing](https://img.shields.io/badge/Tests-8%20Passed%20(47%20assertions)-brightgreen?style=flat-square)](#-pengujian-otomatis-automated-testing)

Sistem Informasi Terpadu dan Layanan Digital Resmi **SMP Negeri 4 Cepu** (NPSN: 20314928) yang berlandaskan konsep **"Satu Sekolah, Satu Ruang Digital"**. Proyek ini mentransformasi portal web sekolah konvensional menjadi ekosistem digital terpusat yang mencakup portal informasi sekolah, pemilihan ketua OSIS elektronik (Pilketos), perpustakaan digital, modul materi & latihan soal mandiri, serta panel administrasi terpadu berbasis kontrol akses peran (RBAC).

---

## 📌 Indeks Dokumentasi Proyek

Dokumentasi lengkap dan spesifikasi teknis mendalam tersedia pada direktori [`docs/`](docs/):

1. **[Product Requirements Document (PRD)](docs/PRD.md)**: Analisis latar belakang, visi sekolah, ruang lingkup fungsional/non-fungsional, user persona, dan peta jalan rilis V1 hingga V4.
2. **[Software Architecture Document (SAD)](docs/ARCHITECTURE.md)**: Cetak biru arsitektur modular monolith, diagram relasi entitas 3NF (ERD), mesin keamanan multi-tier Pilketos, zero client leakage kuis, serta integrasi LiteSpeed Web Server.
3. **[Panduan Deployment Hostinger Cloud](docs/DEPLOYMENT.md)**: Prosedur isolasi direktori privat (`laravel_app`), symlink `public_html`, otomatisasi Git hPanel, konfigurasi bypass LiteSpeed Cache, dan skrip `deploy.sh`.
4. **[Master Data Dictionary & Data Resmi](docs/DATA_DICTIONARY_AND_SEEDS.md)**: Profil lengkap sekolah, sejarah resmi 1979, 11 butir misi, direktori 33 GTK lengkap dengan NIP & Golongan, 11 fasilitas, dan 11 mata pelajaran Kurikulum Merdeka.

---

## 🚀 Fitur Utama Sistem (Tahap V1)

### 1. Portal Web Informasi & Profil Sekolah
- **Header & Navigasi Responsif**: Sticky navbar, topbar info sekolah (NPSN, tanggal, medsos), dan slide-over menu pada mobile.
- **Beranda Interaktif**: Hero banner ajakan aksi, grid akses cepat, statistik ringkas, cuplikan berita, pengumuman, dan agenda.
- **Profil Lengkap**: Sambutan kepala sekolah, sejarah resmi berdirinya sekolah (SK No. 0188/O/1979), serta visi dan 11 butir misi.
- **Direktori Guru & Tendik (GTK)**: Kartu profil guru dengan penyaring ganda reaktif (*Livewire*) berdasarkan jabatan dan status kepegawaian (PNS, PPPK, Honorer).
- **Statistik Siswa & Rombel**: Agregasi jumlah siswa per kelas (7, 8, 9) dan rasio gender per tahun ajaran.
- **Fasilitas & Galeri**: Dokumentasi 11 sarana prasarana sekolah dan galeri kegiatan.
- **Kontak & Peta Lokasi**: Informasi alamat resmi Cepu - Blora dan sematan peta interaktif.
- **Dukungan Tema Terang & Gelap**: Berpindah otomatis menyesuaikan preferensi perangkat tanpa FOUC (*Flash of Unstyled Content*).

### 2. Modul Pemilihan Ketua OSIS (Pilketos Terbuka & Aman)
- **Bilik Suara Digital (1 Suara per Perangkat)**: Pemungutan suara elektronik tanpa registrasi akun menggunakan `PilketosFingerprintService` (kombinasi HttpOnly Cookie, Hardware SHA-256 Hash, IP Subnet `/24` NAT-safe, dan User-Agent Hash).
- **Asas Luber Jurdil**: Data pemilih (`election_devices`) dipisahkan secara fisik dan anonim dari kotak suara (`election_votes`).
- **Quick Count Realtime Polling**: Grafik batang hasil suara terbuka berbasis Chart.js dengan polling Livewire berkala 5 detik (`wire:poll.5s`) tanpa memerlukan server WebSocket persisten.

### 3. Modul Perpustakaan Digital
- **Katalog Koleksi Buku**: Daftar buku dengan judul, pengarang, penerbit, tahun terbit, dan foto sampul.
- **Pencarian Instan**: Pencarian cepat berbasis judul dan penulis.
- **Lokasi Rak & Stok Sirkulasi**: Informasi nomor rak buku dan ketersediaan stok fisik yang dapat dipinjam.

### 4. Modul Belajar Mandiri (Materi & Bank Soal)
- **Materi Terstruktur**: Pengelompokan materi bahan ajar per mata pelajaran dan jenjang kelas (7, 8, 9) dengan unduhan berkas atau tautan eksternal.
- **Bank Soal & Latihan Siswa**: Paket soal pilihan ganda dengan durasi waktu pengerjaan.
- **Zero Client Leakage**: Kunci jawaban (`is_correct`) tidak pernah diekspos ke browser; evaluasi penilaian dilakukan sepenuhnya di server.
- **Skor Otomatis & Pembahasan**: Penilaian instan skala 100 dengan indikator kelulusan (*passing grade*) serta lembar pembahasan reflektif.

### 5. Panel Administrasi Terpusat (Filament PHP v3 + Filament Shield)
- Single Back-Office di `/admin` dengan 9 resource CRUD (Berita, GTK, Fasilitas, Statistik Siswa, Buku, Mapel, Materi, Bank Soal, dan Paslon Pilketos).
- Dashboard Widget pemantau metrik sekolah dan grafik perolehan suara Pilketos live polling.
- Role-Based Access Control (RBAC) granular membedakan hak akses Admin, Guru, Pustakawan, dan Panitia Pilketos.

---

## 🔐 Kredensial Pengujian Akun Default

Seluruh akun telah diisi melalui `DatabaseSeeder` dengan hak akses peran terverifikasi:

| Peran (Role) | Alamat Email | Kata Sandi | Akses Panel `/admin` |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@smpn4cepu.sch.id` | `AdminCepu2026!` | Hak penuh atas seluruh modul dan manajemen role |
| **Guru** | `guru@smpn4cepu.sch.id` | `GuruCepu2026!` | Pengelolaan materi ajar, bank soal, dan posting berita |
| **Staf Perpus** | `perpus@smpn4cepu.sch.id` | `PerpusCepu2026!` | Pengelolaan katalog dan stok buku perpustakaan |
| **Panitia Pilketos** | `pilketos@smpn4cepu.sch.id` | `PilketosCepu2026!` | Pengelolaan kandidat paslon dan pemantauan suara |

---

## 🛠️ Instalasi & Menjalankan di Lokal

### Prasyarat
- PHP 8.2 atau lebih baru (Ekstensi aktif: `pdo_mysql`, `intl`, `mbstring`, `fileinfo`, `curl`)
- Composer 2.x
- Node.js 18+ & NPM

### Langkah Menjalankan
```bash
# 1. Masuk ke direktori proyek
cd smpn4cepu

# 2. Pasang dependensi PHP & Node.js
composer install
npm install

# 3. Konfigurasi file .env dan generate key
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi basis data dan seeding data resmi
php artisan migrate:fresh --seed

# 5. Kompilasi aset frontend (Tailwind CSS & Chart.js)
npm run build

# 6. Jalankan server lokal
php artisan serve
