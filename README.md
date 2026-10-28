# SMP Negeri 4 Cepu: "Satu Sekolah, Satu Ruang Digital"

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![Filament PHP](https://img.shields.io/badge/Filament-v5.x-F59E0B?style=flat-square&logo=filament)](https://filamentphp.com)
[![Livewire v3](https://img.shields.io/badge/Livewire-v3.5-FB70A9?style=flat-square&logo=livewire)](https://livewire.laravel.com)
[![Tailwind CSS v3](https://img.shields.io/badge/TailwindCSS-v3.4-38B2AC?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)
[![Chart.js v4](https://img.shields.io/badge/Chart.js-v4.4-FF6384?style=flat-square&logo=chart.js)](https://www.chartjs.org)
[![Hostinger Cloud](https://img.shields.io/badge/Hostinger-Cloud%20Startup-673AB7?style=flat-square&logo=hostinger)](https://hostinger.com)
[![Tests Passing](https://img.shields.io/badge/Tests-24%20Passed%20(216%20assertions)-brightgreen?style=flat-square)](#-pengujian-otomatis-automated-testing)

Sistem Informasi Terpadu dan Layanan Digital Resmi **SMP Negeri 4 Cepu** (NPSN: 20314928) yang berlandaskan konsep **"Satu Sekolah, Satu Ruang Digital"**. Proyek ini mentransformasi portal web sekolah konvensional menjadi ekosistem digital terpusat yang mencakup portal informasi publik, pemilihan ketua OSIS elektronik (Pilketos), perpustakaan digital, modul materi & latihan soal CBT mandiri, direktori 609 siswa aktif, serta panel administrasi terpadu berbasis kontrol akses peran (RBAC) dengan 20 modul CRUD lengkap.

---

## 📌 Indeks Dokumentasi Proyek

Dokumentasi lengkap dan spesifikasi teknis mendalam tersedia pada direktori [`docs/`](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs):

1. **[Product Requirements Document (PRD)](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/PRD.md)**: Analisis latar belakang, visi sekolah, ruang lingkup fungsional/non-fungsional, user persona, dan status pencapaian fitur V1.
2. **[Software Architecture Document (SAD)](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/ARCHITECTURE.md)**: Cetak biru arsitektur modular monolith, diagram relasi entitas 3NF (ERD), mesin keamanan multi-tier Pilketos, zero client leakage kuis CBT, arsitektur SEO, serta integrasi LiteSpeed Web Server.
3. **[Panduan Deployment Hostinger Cloud & CI/CD](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/DEPLOYMENT.md)**: Prosedur isolasi direktori privat (`laravel_app`), symlink `public_html`, pipeline otomatisasi GitHub Actions (`deploy.yml`), konfigurasi SSH port 65002, bypass LiteSpeed Cache, dan skrip `deploy.sh`.
4. **[Master Data Dictionary & Data Resmi](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/DATA_DICTIONARY_AND_SEEDS.md)**: Profil lengkap sekolah, sejarah resmi 1979, 11 butir misi, direktori 33 GTK lengkap dengan NIP & Golongan, 11 fasilitas, 11 mata pelajaran Kurikulum Merdeka, serta direktori resmi 609 siswa aktif (20 rombel).
5. **[Berkas Persyaratan Domain .sch.id (PANDI) & Checklist Hostinger](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/PANDI_DAN_CHECKLIST_HOSTINGER.md)**: Draf Surat Permohonan Kepala Sekolah, Surat Kuasa PANDI, daftar dokumen legalitas SMPN 4 Cepu, serta panduan verifikasi domain resmi.
6. **[Panduan Deployment Kontainer PaaS / Coolify](file:///c:/Users/nadem/OneDrive/Documents/smpn4cepu/docs/PAAS_DEPLOYMENT_GUIDE.md)**: Panduan penyiapan kontainer Docker dan Docker Compose untuk staging atau deployment alternatif berbasis VPS.

---

## 🚀 Fitur Utama Sistem

### 1. Portal Web Informasi & Profil Sekolah
- **Header & Navigasi Responsif**: Sticky navbar, topbar info sekolah (NPSN, tanggal, medsos), dan slide-over menu pada mobile.
- **Beranda Interaktif**: Hero banner ajakan aksi, grid akses cepat, statistik ringkas, cuplikan berita, pengumuman, dan kalender agenda.
- **Profil Lengkap**: Sambutan kepala sekolah, sejarah resmi berdirinya sekolah (SK No. 0188/O/1979), serta visi dan 11 butir misi resmi.
- **Direktori Guru & Tendik (GTK)**: Kartu profil guru dengan penyaring ganda reaktif (*Livewire*) berdasarkan jabatan dan status kepegawaian (PNS, PPPK, Honorer).
- **Statistik Siswa & Rombel**: Agregasi resmi 609 siswa per kelas (7, 8, 9) dan rasio gender (318 L / 291 P) pada 20 rombel tahun ajaran 2026/2027.
- **Fasilitas & Galeri**: Dokumentasi 11 sarana prasarana sekolah dan galeri kegiatan dengan viewer foto responsif.
- **Pusat Unduhan & Kontak**: Layanan unduh dokumen resmi sekolah, informasi alamat Jl. Raya Cepu Randu Km. 3,5, serta sematan peta interaktif.
- **Dukungan Tema Terang & Gelap**: Berpindah otomatis menyesuaikan preferensi perangkat tanpa FOUC (*Flash of Unstyled Content*).

### 2. Modul Pemilihan Ketua OSIS (Pilketos Terbuka & Aman)
- **Bilik Suara Digital (1 Suara per Perangkat)**: Pemungutan suara elektronik tanpa registrasi akun menggunakan `PilketosFingerprintService` (kombinasi HttpOnly Cookie, Hardware SHA-256 Hash, IP Subnet `/24` NAT-safe, dan User-Agent Hash).
- **Asas Luber Jurdil**: Data perangkat pemilih (`election_devices`) dipisahkan secara fisik dan anonim dari kotak suara (`election_votes`).
- **Quick Count Realtime Polling**: Grafik batang hasil suara terbuka berbasis Chart.js dengan polling Livewire berkala 5 detik (`wire:poll.5s`) tanpa memerlukan server WebSocket persisten.

### 3. Modul Perpustakaan Digital
- **Katalog Koleksi Buku**: Daftar koleksi buku lengkap dengan judul, pengarang, penerbit, nomor rak, stok eksemplar fisik, dan foto sampul.
- **Pencarian Instan**: Pencarian cepat berbasis judul, penulis, dan kategori buku.
- **Reservasi Peminjaman Buku**: Siswa dapat mengajukan reservasi peminjaman buku langsung dari portal untuk diverifikasi oleh staf perpustakaan di admin panel.

### 4. Modul Belajar Mandiri (Materi & Bank Soal CBT)
- **Materi Terstruktur**: Pengelompokan bahan ajar per mata pelajaran dan jenjang kelas (7, 8, 9) dengan unduhan berkas atau tautan materi eksternal.
- **Bank Soal & Latihan Siswa**: Paket soal pilihan ganda interaktif dilindungi token/password pengerjaan serta batas waktu mundur.
- **Zero Client Leakage**: Kunci jawaban (`is_correct`) tidak pernah diekspos ke browser; evaluasi penilaian dilakukan sepenuhnya di sisi server.
- **Skor Otomatis & Pembahasan**: Penilaian instan skala 100 dengan indikator kelulusan (*passing grade*) serta lembar pembahasan reflektif pasca-ujian.

### 5. Mesin Optimasi Mesin Pencari (SEO Maksimal)
- **Metadata Otomatis & Canonical**: Terintegrasi pada setiap halaman publik (`App\Support\Seo`) dengan tag judul unik, deskripsi relevan, OpenGraph Facebook, dan Twitter Card.
- **Dynamic Cookie-Free Sitemap**: Endpoint `/sitemap.xml` dinamis dengan prioritas halaman, frekuensi perubahan, dan integrasi tag gambar Google Image Search.
- **Robots.txt & RSS Feed**: Endpoint dinamis `/robots.txt` dan sindikasi warta sekolah `/feed.xml` untuk percepatan pengindeksan Googlebot.
- **Data Terstruktur (Schema.org)**: Markup JSON-LD untuk `EducationalOrganization`, `WebSite`, `WebPage`, dan `NewsArticle`.
- **Pengalihan Kanonikal Apex**: Aturan 301 redirect otomatis di `.htaccess` dari `www.smpn4cepu.sch.id` ke domain utama `https://smpn4cepu.sch.id`.

### 6. Panel Administrasi Terpusat (Filament + Shield RBAC)
Satu pintu pengawasan di `/admin` dengan **20 Modul CRUD Lengkap**:
1. **Berita & Artikel** (`PostResource`): Pengelolaan berita, pengumuman resmi, dan agenda kegiatan sekolah.
2. **Materi Pembelajaran** (`LearningMaterialResource`): Pengunggahan materi per mapel dan jenjang kelas.
3. **Bank Soal CBT** (`QuestionBankResource`): Pembuatan paket soal, token ujian, butir soal, dan opsi jawaban.
4. **Hasil Kuis Siswa** (`QuizAttemptResource`): Rekapitulasi nilai latihan soal, status kelulusan, dan detail jawaban siswa.
5. **Mata Pelajaran** (`SubjectResource`): Master 11 mata pelajaran Kurikulum Merdeka.
6. **Data Siswa** (`StudentResource`): Pengelolaan lengkap 609 peserta didik dengan filter rombel `7A` s.d. `9F`, NISN, NIS, tempat/tanggal lahir, dan jenis kelamin.
7. **Statistik Siswa** (`StudentStatisticResource`): Rekapitulasi agregat rombel dengan tombol sinkronisasi otomatis (*Sync Now*).
8. **Guru & Tendik** (`StaffMemberResource`): Direktori 33 staf GTK lengkap dengan NIP, golongan, dan jabatan.
9. **Fasilitas Sekolah** (`SchoolFacilityResource`): Sarana dan prasarana penunjang kegiatan belajar.
10. **Prestasi Siswa** (`AchievementResource`): Rekam jejak prestasi akademik dan non-akademik siswa.
11. **Ekstrakurikuler** (`ExtracurricularResource`): Kegiatan pengembangan bakat siswa.
12. **Katalog Perpustakaan** (`LibraryBookResource`): Data buku, nomor rak, dan stok sirkulasi.
13. **Reservasi Buku** (`BookReservationResource`): Pengelolaan permohonan peminjaman buku siswa.
14. **Pusat Unduhan** (`SchoolDocumentResource`): Publikasi dokumen resmi dan formulir sekolah.
15. **Galeri Kegiatan** (`GalleryPhotoResource`): Dokumentasi foto dokumentasi kegiatan sekolah.
16. **Kandidat Paslon Pilketos** (`ElectionCandidateResource`): Pendaftaran paslon ketua dan wakil ketua OSIS.
17. **Kotak Suara Pilketos** (`ElectionVoteResource`): Log perolehan suara masuk dan audit integritas voting.
18. **Pesan & Pengaduan** (`ContactMessageResource`): Kotak masuk aspirasi, pertanyaan, dan pengaduan masyarakat.
19. **Profil & Pengaturan Sekolah** (`SchoolSettingResource`): Konfigurasi identitas sekolah, sambutan kepala sekolah, dan kontak.
20. **Manajemen Akun Pengguna** (`UserResource`): Pengelolaan seluruh akun dengan tab terpisah untuk Staf & Guru serta Akun Siswa.
- **Smart Global Search**: Pintasan `Ctrl + K` untuk pencarian instan lintas 8 entitas data utama dari mana saja di panel admin.

---

## 🔐 Kredensial Pengujian Akun Default

Seluruh akun telah diisi melalui `DatabaseSeeder` dengan hak akses peran terverifikasi:

| Peran (Role) | Alamat Email / NISN | Kata Sandi | Akses Panel / Portal |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@smpn4cepu.sch.id` | `AdminCepu2026!` | Hak penuh atas seluruh modul dan manajemen role di `/admin` |
| **Guru** | `guru@smpn4cepu.sch.id` | `GuruCepu2026!` | Pengelolaan materi ajar, bank soal, hasil kuis, dan berita di `/admin` |
| **Staf Perpus** | `perpus@smpn4cepu.sch.id` | `PerpusCepu2026!` | Pengelolaan katalog buku, nomor rak, stok, dan reservasi di `/admin` |
| **Panitia Pilketos** | `pilketos@smpn4cepu.sch.id` | `PilketosCepu2026!` | Pengelolaan paslon OSIS, kotak suara, dan live polling di `/admin` |
| **609 Akun Siswa** | `{nisn}@siswa.smpn4cepu.sch.id` (Contoh: `0081234567`) | `password123` | Portal belajar siswa (`/login`), latihan soal CBT, Pilketos, dan reservasi buku |

> **Catatan Akun Siswa**: Seluruh 609 data akun siswa beserta username, email, dan kata sandi awal telah terdokumentasi rapi di Google Sheets resmi dan berkas arsip `data_semua_akun_smpn4cepu.xlsx`.

### 🔑 Token / Password Default Latihan Soal (Per Angkatan)

Setiap paket soal dilindungi oleh **Password / Token Soal** yang dapat dikelola oleh Guru/Admin di `/admin/question-banks`:

| Angkatan | Mata Pelajaran | Judul Paket Soal | Password / Token Soal |
| :--- | :--- | :--- | :--- |
| **Kelas VII (7)** | Ilmu Pengetahuan Alam (IPA) | Evaluasi Klasifikasi Makhluk Hidup & Ekosistem: Kelas 7 | `IPA7-2026` |
| **Kelas VIII (8)** | Matematika | Latihan Soal Aljabar & Teorema Pythagoras: Kelas 8 | `MTK8-2026` |
| **Kelas IX (9)** | Bahasa Indonesia | Asesmen Teks Laporan Percobaan & Pidato Persuasif: Kelas 9 | `BINDO9-2026` |

---

## 🛠️ Instalasi & Menjalankan di Lokal

### Prasyarat
- PHP 8.2 atau PHP 8.3 (Ekstensi aktif: `pdo_mysql`, `pdo_sqlite`, `intl`, `mbstring`, `fileinfo`, `curl`, `gd`, `exif`, `bcmath`, `zip`)
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

# 4. Jalankan migrasi basis data, seeding data resmi, dan tautkan storage publik
php artisan migrate:fresh --seed
php artisan storage:link

# 5. Kompilasi aset frontend (Tailwind CSS, Alpine.js, & Chart.js)
npm run build

# 6. Jalankan server lokal
php artisan serve
```

---

## 🧪 Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan suite pengujian otomatis komprehensif menggunakan **PHPUnit** untuk memastikan keandalan fitur portal publik, otentikasi siswa dan staf, keamanan kuis CBT (*Zero Client Leakage*), reservasi perpustakaan, pencarian global pintar, serta pemungutan suara Pilketos:

```bash
# Menjalankan seluruh pengujian otomatis (24 tests, 216 assertions)
php artisan test

# Memeriksa standar format kode (Laravel Pint)
composer lint
```
