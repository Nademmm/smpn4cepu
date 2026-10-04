# Product Requirements Document (PRD)
## SMP Negeri 4 Cepu — "Satu Sekolah, Satu Ruang Digital" (Tahap V1)

---

## 1. Ringkasan Eksekutif & Latar Belakang

### 1.1 Latar Belakang
SMP Negeri 4 Cepu (NPSN: 20314928) merupakan satuan pendidikan jenjang Sekolah Menengah Pertama yang berlokasi di Desa Mulyorejo, Kecamatan Cepu, Kabupaten Blora, Jawa Tengah. Sekolah yang berdiri sejak 4 September 1979 ini berada di wilayah strategis vokasi industri migas dan agrikultur.

Seiring transformasi era digital, portal sekolah dituntut tidak hanya menjadi media profil statis, melainkan bertransformasi menjadi **ekosistem pusat layanan digital terpadu** bagi warga sekolah (siswa, guru, tenaga kependidikan, wali murid, dan masyarakat).

### 1.2 Visi & Semboyan Proyek
- **Semboyan Utama**: *"Satu Sekolah, Satu Ruang Digital"*
- **Visi Sekolah**: *"TERWUJUDNYA PESERTA DIDIK YANG CERIA BERIMAN (CERDAS, INOVATIF, KREATIF, BERINTEGRITAS, MANDIRI DALAM PELESTARIAN LINGKUNGAN HIDUP)"*
- **Karakter Desain**: *Modern • Clean • Professional • User Friendly* (Mendukung preferensi tampilan Terang dan Gelap / Light & Dark Mode).

---

## 2. Tujuan & Sasaran Produk

1. **Sentralisasi Informasi Publik**: Menyajikan informasi resmi sekolah (profil, visi-misi, sejarah, direktori guru/staf, rekapitulasi rombel siswa, berita, pengumuman, agenda, fasilitas, galeri, dan kontak/peta lokasi).
2. **Digitalisasi Layanan Siswa (Tahap V1)**:
   - **Pemilihan Ketua OSIS (Pilketos)**: Pemungutan suara elektronik aman berbasis sidik jari perangkat tanpa registrasi akun yang rumit, dilengkapi rekapitulasi grafik real-time terbuka.
   - **Katalog Perpustakaan Digital**: Akses pencarian buku, klasifikasi rak, dan status ketersediaan stok fisik perpustakaan.
   - **Modul Belajar Mandiri**: Akses materi terstruktur per mata pelajaran dan kelas, serta latihan mandiri bank soal dengan evaluasi nilai otomatis instan.
3. **Pengelolaan Terpusat (Single Admin Panel)**: Memberikan kontrol penuh bagi Administrator, Guru, Staf Perpustakaan, dan Panitia Pilketos dengan pembagian hak akses terisolasi (RBAC).
4. **Kesiapan Menuju Ekosistem Bertahap (Roadmap V1 - V4)**.

---

## 3. Peta Jalan Produk (Product Roadmap)

| Tahapan | Nama Rilis | Ruang Lingkup Utama | Status Biaya |
| :--- | :--- | :--- | :--- |
| **V1** | **Core Digital School** *(Tahap Aktif)* | Portal Profil Web Publik + Pilketos Terbuka Aman + Perpustakaan Digital + Materi & Bank Soal Interaktif + Single Admin Panel RBAC | **Termasuk Anggaran W1–W4** |
| **V2** | **Student Experience** | Kanal Karya Siswa, Portofolio Digital, Rekap Prestasi Siswa, Ruang Kegiatan Ekstrakurikuler, Dokumentasi Event Interaktif | Anggaran Tahap Berikutnya |
| **V3** | **Advanced Digital School** | Akun Personal Siswa & Guru, Computer Based Test (CBT) skala penuh dengan token ujian, Single Sign-On (SSO), Dashboard Nilai Siswa | Anggaran Tahap Berikutnya |
| **V4** | **School Enterprise Integration** | Integrasi API Kemendikbudristek (Sinkronisasi Data Dapodik), Otomasi Pelaporan Rapor Digital, Sistem Presensi Realtime | Anggaran Tahap Berikutnya |

---

## 4. Analisis Pengguna & Persona

### Persona 1: Siswa SMPN 4 Cepu (Pengunjung Publik / Pemilih / Peserta Kuis)
- **Kebutuhan**: Melihat berita/agenda sekolah, membaca materi ajar, mencoba latihan soal mandiri di rumah atau gawai HP, dan memberikan 1 hak suara pada Pilketos secara adil tanpa ribet membuat akun.
- **Karakteristik**: Mengakses via smartphone Android/iOS, sering menggunakan Wi-Fi sekolah bersama di lab komputer.

### Persona 2: Guru / Tenaga Pendidik
- **Kebutuhan**: Mengunggah materi ajar (PDF/link), menyusun bank soal pilihan ganda lengkap dengan kunci dan pembahasan, serta mempublikasikan berita/agenda kegiatan sekolah.
- **Akses**: Login ke Panel `/admin` dengan role `guru`.

### Persona 3: Staf Perpustakaan
- **Kebutuhan**: Mendata koleksi buku fisik, menentukan posisi nomor rak, mencatat jumlah eksemplar total dan stok yang dapat dipinjam.
- **Akses**: Login ke Panel `/admin` dengan role `staf_perpus`.

### Persona 4: Administrator Sekolah & Panitia Pilketos
- **Kebutuhan**: Mengelola struktur akun pengguna, konfigurasi data sekolah, mendaftarkan nomor urut dan visi-misi kandidat paslon OSIS, memantau perolehan suara Pilketos via live chart widget, serta mencadangkan data.
- **Akses**: Login ke Panel `/admin` dengan role `super_admin` atau `panitia_pilketos`.

---

## 5. Spesifikasi Kebutuhan Fungsional (Functional Requirements)

### 5.1 Portal Profil & Informasi Sekolah (Front-Office)
- **FR-01 (Topbar & Header Sticky)**: Menampilkan NPSN (20314928), jam/tanggal sistem, link medsos resmi, logo sekolah, serta menu navigasi responsif (beralih ke slide-over menu pada mobile).
- **FR-02 (Beranda)**:
  - Hero banner utama dengan slogan "Satu Sekolah, Satu Ruang Digital".
  - Grid akses cepat (Pengumuman, Data Guru, Unduhan Berkas, Kontak).
  - Statistik Ringkas: Tahun Berdiri (1979), Total Siswa, Total Guru/Tendik, dan Fasilitas.
  - Sambutan Kepala Sekolah, cuplikan berita terbaru, pengumuman resmi, dan kalender agenda mendatang.
- **FR-03 (Profil Lengkap)**:
  - Halaman Sambutan Kepala Sekolah.
  - Sejarah Resmi (SK Pendirian No. 0188/O/1979 tgl 3 Sept 1979, kepemimpinan YB. Moertadji).
  - Visi Sekolah & 11 butir Misi.
- **FR-04 (Direktori Guru & Tendik - Filter Ganda)**:
  - Kartu profil GTK dilengkapi filter reaktif: berdasarkan Jabatan dan Status Kepegawaian (PNS, PPPK, Honorer) tanpa refresh halaman via Livewire.
- **FR-05 (Rekapitulasi Data Siswa)**:
  - Tabel statistik rombongan belajar per kelas (7, 8, 9), jumlah siswa laki-laki, perempuan, dan total rombel per tahun ajaran.
- **FR-06 (Fasilitas & Galeri)**:
  - Grid dokumentasi 11 sarana prasarana sekolah dan galeri foto kegiatan.
- **FR-07 (Kontak & Sematan Peta)**:
  - Informasi kontak resmi, alamat Jl. Raya Cepu Randu Km. 3,5, telp (0296) 421631, surel, dan sematan Google Maps koordinat `-7.1576081, 111.5662531`.

### 5.2 Modul Pemilihan Ketua OSIS (Pilketos Terbuka & Aman)
- **FR-08 (Bilik Suara Digital)**:
  - Menampilkan kartu pasangan calon ketua dan wakil ketua OSIS (nomor urut, foto, nama, visi, dan uraian misi).
  - Mekanisme **1 Suara per Perangkat** tanpa login menggunakan `PilketosFingerprintService` (kombinasi HttpOnly Cookie, Hardware SHA-256 Hash, IP Subnet `/24` NAT-safe, dan User-Agent Hash).
  - Mengisolasi rekaman suara (`election_votes`) secara anonim dari data voter (`election_devices`) untuk menjamin kerahasiaan hak pilih (Luber Jurdil).
- **FR-09 (Quick Count Real-time)**:
  - Visualisasi grafik batang terbuka perolehan suara paslon berbasis Chart.js.
  - Pembaruan berkala via polling ringan Livewire (`wire:poll.5s`) tanpa server WebSocket persisten.

### 5.3 Modul Perpustakaan Digital
- **FR-10 (Katalog & Pencarian)**:
  - Daftar buku dengan judul, pengarang, penerbit, tahun terbit, kategori, dan foto sampul.
  - Pencarian instan berdasarkan judul atau nama penulis.
  - Informasi nomor lokasi rak buku dan status stok tersedia (`available_stock`).

### 5.4 Modul Belajar (Materi & Bank Soal)
- **FR-11 (Materi Pembelajaran)**:
  - Pengelompokan materi berdasarkan 11 Mata Pelajaran Kurikulum Merdeka SMP dan jenjang kelas (7, 8, 9).
  - Unduhan berkas materi pendukung atau tautan eksternal (Google Drive / YouTube edukatif).
- **FR-12 (Bank Soal & Latihan Mandiri)**:
  - Pemilihan paket soal berdasarkan mata pelajaran dan kelas.
  - Antarmuka pengerjaan soal pilihan ganda dengan durasi waktu mundur.
  - Arsitektur **Zero Client Leakage**: Kolom `is_correct` tidak pernah dikirim ke peramban siswa saat ujian berlangsung.
  - Evaluasi server-side instan dengan penghitungan skor skala 100, status ketuntasan berdasarkan *passing grade*, serta lembar pembahasan reflektif.

### 5.5 Panel Administrasi (Back-Office via Filament PHP v3)
- **FR-13 (Autentikasi & RBAC)**:
  - Single panel admin di `/admin` diproteksi Filament Shield berbasis `spatie/laravel-permission`.
  - Pemisahan hak akses: `super_admin`, `guru`, `staf_perpus`, `panitia_pilketos`.
- **FR-14 (Manajemen Konten Terpusat)**:
  - CRUD Berita, Pengumuman, Agenda, Direktori GTK, Fasilitas, Statistik Siswa, Katalog Buku, Master Mapel, Materi Belajar, Bank Soal & Butir Pertanyaan, serta Kandidat Pilketos.
- **FR-15 (Dashboard Monitoring)**:
  - Widget ringkasan metrik statistik sekolah.
  - Widget grafik perolehan suara Pilketos real-time polling 5 detik.

---

## 6. Spesifikasi Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Performa & Latensi**:
   - Waktu respons laman statis publik < 1.2 detik di bawah akselerasi LiteSpeed Cache (LSCache) dan OPcache.
   - Polling Pilketos mengonsumsi memori < 15MB per request di bawah kuota PHP memory limit 512MB.
2. **Keandalan Infrastruktur (Hostinger Cloud Startup)**:
   - Berjalan pada alokasi 4 vCPU, 4GB RAM, MariaDB tanpa dependensi daemon persisten (tanpa Node.js daemon atau WebSocket server).
   - Pemisahan direktori sumber kode privat (`laravel_app/`) dari document root publik (`public_html/`) via symlink.
3. **Keamanan (Security)**:
   - Enkripsi password menggunakan `bcrypt` cost factor 12.
   - Proteksi CSRF pada seluruh form transmisi data.
   - Sanitasi input dan proteksi SQL Injection via PDO parameterized query bawaan Eloquent.
   - Perlindungan anti vote-stuffing pada Pilketos dengan token HttpOnly dan hashing hardware.
4. **Aksesibilitas & Responsivitas**:
   - Desain responsif sempurna dari resolusi 360px (mobile) hingga 1920px (desktop).
   - Dukungan Dark Mode dan Light Mode bebas FOUC (*Flash of Unstyled Content*).
   - Kontras warna teks memenuhi standar WCAG AA.
