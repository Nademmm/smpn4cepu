<?php

namespace Database\Seeders;

use App\Enums\BookCategory;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan direktori penyimpanan lokal siap
        $disk = Storage::disk('public');
        if (!$disk->exists('books/covers')) {
            $disk->makeDirectory('books/covers');
        }
        if (!$disk->exists('books/ebooks')) {
            $disk->makeDirectory('books/ebooks');
        }

        $books = [
            // -----------------------------------------------------------------
            // 1 BUKU DUMMY CONTOH PERPUSTAKAAN DIGITAL SMPN 4 CEPU
            // -----------------------------------------------------------------
            [
                'title' => 'Matematika untuk SMP/MTs Kelas VII',
                'isbn' => '9786022448839',
                'author' => 'Hobri, Susanto, Mohammad Syaifuddin, dkk.',
                'publisher' => 'Pusat Perbukuan Kemendikbudristek',
                'published_year' => 2024,
                'category' => BookCategory::KELAS_7->value,
                'description' => 'Buku teks utama Kurikulum Merdeka (Contoh Koleksi Dummy) yang memuat materi bilangan bulat dan rasional, bentuk aljabar, persamaan linear satu variabel, perbandingan, kesebangunan, serta penyajian data statistik kontekstual untuk siswa kelas VII SMP.',
                'has_local_pdf' => true,
                'page_count' => 6,
                'language' => 'id',
                'physical_stock' => 35,
                'views_count' => 12,
                'is_featured' => true,
                'is_active' => true,
                'color' => '#1F5FBF',
            ],
        ];

        // Bersihkan buku seeder lama agar hanya tersisa 1 buku dummy contoh
        $allowedIsbns = array_column($books, 'isbn');
        Book::withTrashed()->whereNotIn('isbn', $allowedIsbns)->forceDelete();

        foreach ($books as $item) {
            $slug = Str::slug($item['title']);

            // 1. Generate cover SVG lokal di storage/app/public/books/covers/
            $coverFileName = "books/covers/{$slug}.svg";
            $categoryEnum = BookCategory::tryFrom($item['category']);
            $categoryLabel = $categoryEnum ? $categoryEnum->getLabel() : 'Koleksi Buku';
            $coverSvg = $this->createCoverSvg(
                $item['title'],
                $item['author'],
                $categoryLabel,
                $item['color'] ?? '#1F5FBF'
            );
            $disk->put($coverFileName, $coverSvg);

            // 2. Generate PDF lokal di storage/app/public/books/ebooks/ jika buku memiliki e-book
            $pdfFileName = null;
            if (!empty($item['has_local_pdf'])) {
                $pdfFileName = "books/ebooks/{$slug}.pdf";
                $pdfBinary = $this->createLocalPdf(
                    $item['title'],
                    $item['author'],
                    $item['publisher'] ?? 'Pusat Perbukuan Kemendikbudristek',
                    $categoryLabel,
                    $item['description']
                );
                $disk->put($pdfFileName, $pdfBinary);
            }

            // 3. Simpan data buku ke database dengan 100% path lokal, cover_url dan pdf_url null
            Book::updateOrCreate(
                ['isbn' => $item['isbn']],
                [
                    'title' => $item['title'],
                    'slug' => $slug,
                    'isbn' => $item['isbn'],
                    'author' => $item['author'],
                    'publisher' => $item['publisher'],
                    'published_year' => $item['published_year'],
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'cover_path' => $coverFileName,
                    'cover_url' => null,
                    'pdf_path' => $pdfFileName,
                    'pdf_url' => null,
                    'page_count' => $item['page_count'],
                    'language' => $item['language'],
                    'physical_stock' => $item['physical_stock'],
                    'views_count' => $item['views_count'],
                    'is_featured' => $item['is_featured'],
                    'is_active' => $item['is_active'],
                ]
            );
        }
    }

    /**
     * Membuat berkas sampul buku SVG yang rapi sesuai identitas SMPN 4 Cepu.
     */
    protected function createCoverSvg(string $title, string $author, string $categoryLabel, string $accentColor): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeAuthor = htmlspecialchars($author, ENT_QUOTES, 'UTF-8');
        $safeCategory = htmlspecialchars(strtoupper($categoryLabel), ENT_QUOTES, 'UTF-8');

        $titleLines = explode("\n", wordwrap($safeTitle, 22, "\n"));
        $titleTspans = '';
        $y = 225;
        foreach (array_slice($titleLines, 0, 4) as $line) {
            $titleTspans .= "<tspan x=\"150\" y=\"{$y}\">{$line}</tspan>";
            $y += 24;
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450" viewBox="0 0 300 450" fill="none">
    <rect width="300" height="450" rx="16" fill="#1B2A4A"/>
    <rect x="10" y="10" width="280" height="430" rx="12" stroke="{$accentColor}" stroke-width="2" stroke-opacity="0.35"/>
    <circle cx="150" cy="110" r="50" fill="{$accentColor}" fill-opacity="0.15"/>
    <circle cx="150" cy="110" r="36" stroke="{$accentColor}" stroke-width="2" stroke-opacity="0.5"/>
    <path d="M130 120V98C130 94 140 92 150 92C160 92 170 94 170 98V120M130 120C130 116 140 114 150 114C160 114 170 116 170 120M130 120C140 122 150 122 150 122M170 120C160 122 150 122 150 122V114" stroke="{$accentColor}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="35" y="172" width="230" height="26" rx="13" fill="{$accentColor}" fill-opacity="0.25"/>
    <text x="150" y="189" fill="#FFFFFF" font-family="Nunito, sans-serif" font-size="10" font-weight="800" text-anchor="middle" letter-spacing="1">{$safeCategory}</text>
    <text fill="#FFFFFF" font-family="Nunito, sans-serif" font-size="15" font-weight="800" text-anchor="middle">
        {$titleTspans}
    </text>
    <line x1="60" y1="345" x2="240" y2="345" stroke="#FFE3A0" stroke-width="1.5" stroke-opacity="0.3"/>
    <text x="150" y="365" fill="#FFE3A0" font-family="Nunito, sans-serif" font-size="11" font-weight="700" text-anchor="middle">{$safeAuthor}</text>
    <rect x="30" y="395" width="240" height="28" rx="8" fill="#0D2960"/>
    <text x="150" y="413" fill="#FFE3A0" font-family="Nunito, sans-serif" font-size="9" font-weight="800" text-anchor="middle" letter-spacing="1.5">SMP NEGERI 4 CEPU</text>
</svg>
SVG;
    }

    /**
     * Membuat berkas PDF lokal multi-halaman yang valid dan rapi untuk modul e-book sekolah.
     */
    protected function createLocalPdf(string $title, string $author, string $publisher, string $category, string $description): string
    {
        $pages = [];

        // Halaman 1: Cover Judul & Identitas
        $p1 = "BT /F1 11 Tf 50 780 Td (PERPUSTAKAAN DIGITAL SMP NEGERI 4 CEPU) Tj ET\n";
        $p1 .= "BT /F1 9 Tf 50 765 Td (Katalog Pembelajaran Kurikulum Merdeka dan Literasi Sekolah) Tj ET\n";
        $p1 .= "BT /F1 18 Tf 50 690 Td (" . addcslashes($title, "()\n\r\\") . ") Tj ET\n";
        $p1 .= "BT /F1 11 Tf 50 655 Td (" . addcslashes("Penyusun: " . $author, "()\n\r\\") . ") Tj ET\n";
        $p1 .= "BT /F1 11 Tf 50 635 Td (" . addcslashes("Penerbit: " . $publisher, "()\n\r\\") . ") Tj ET\n";
        $p1 .= "BT /F1 10 Tf 50 600 Td (" . addcslashes("Klasifikasi: " . $category, "()\n\r\\") . ") Tj ET\n";
        $p1 .= "BT /F1 10 Tf 50 540 Td (Ringkasan Sinopsis Materi:) Tj ET\n";
        $lines = explode("\n", wordwrap($description, 70, "\n"));
        $y = 520;
        foreach ($lines as $line) {
            $p1 .= "BT /F1 10 Tf 50 {$y} Td (" . addcslashes($line, "()\n\r\\") . ") Tj ET\n";
            $y -= 16;
        }
        $p1 .= "BT /F1 9 Tf 50 80 Td (SMP NEGERI 4 CEPU : SATU SEKOLAH, SATU RUANG DIGITAL) Tj ET\n";
        $p1 .= "BT /F1 8 Tf 50 65 Td (Dokumen digital resmi terdaftar pada pangkalan data sirkulasi sekolah.) Tj ET\n";
        $pages[] = $p1;

        // Halaman 2: Hak Cipta & Informasi Bibliografi
        $p2 = "BT /F1 14 Tf 50 780 Td (INFORMASI PENERBITAN DAN HAK CIPTA) Tj ET\n";
        $p2 .= "BT /F1 10 Tf 50 740 Td (Judul Koleksi: " . addcslashes($title, "()\n\r\\") . ") Tj ET\n";
        $p2 .= "BT /F1 10 Tf 50 720 Td (Penulis / Pengarang: " . addcslashes($author, "()\n\r\\") . ") Tj ET\n";
        $p2 .= "BT /F1 10 Tf 50 700 Td (Penerbit: " . addcslashes($publisher, "()\n\r\\") . ") Tj ET\n";
        $p2 .= "BT /F1 10 Tf 50 680 Td (Tahun Publikasi: 2024 / Edisi Kurikulum Merdeka) Tj ET\n";
        $p2 .= "BT /F1 10 Tf 50 660 Td (Bahasa Pengantar: Bahasa Indonesia) Tj ET\n";
        $p2 .= "BT /F1 10 Tf 50 620 Td (Katalog Dalam Terbitan (KDT):) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 595 Td (Unit Perpustakaan Digital SMP Negeri 4 Cepu) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 580 Td (Jl. Diponegoro No. 53 Cepu, Kabupaten Blora, Jawa Tengah 58312) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 565 Td (Surel: perpustakaan@smpn4cepu.sch.id | Laman: smpn4cepu.sch.id) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 510 Td (Hak Cipta dilindungi Undang-Undang Republik Indonesia.) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 495 Td (Dilarang mengumumkan atau memperbanyak sebagian maupun seluruh dokumen) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 480 Td (tanpa izin tertulis dari pemegang hak cipta dan pengelola perpustakaan.) Tj ET\n";
        $p2 .= "BT /F1 9 Tf 50 80 Td (Halaman 2 : Perpustakaan Digital SMPN 4 Cepu) Tj ET\n";
        $pages[] = $p2;

        // Halaman 3: Kata Pengantar
        $p3 = "BT /F1 14 Tf 50 780 Td (KATA PENGANTAR KEPALA SEKOLAH) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 735 Td (Puji syukur kita panjatkan ke hadirat Tuhan Yang Maha Esa atas hadirnya inovasi) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 715 Td (layanan Perpustakaan Digital di lingkungan SMP Negeri 4 Cepu.) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 685 Td (Pendidikan modern abad ke-21 menuntut kecakapan literasi digital, kemandirian) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 665 Td (dalam mencari referensi, serta ketajaman berpikir kritis. Portal ini dihadirkan) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 645 Td (agar setiap siswa dapat mengakses buku ajar resmi dan karya literasi kapan saja.) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 615 Td (Gunakan e-book ini sebagai sahabat belajar dalam menggapai cita-cita luhur.) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 550 Td (Cepu, Juli 2024) Tj ET\n";
        $p3 .= "BT /F1 10 Tf 50 530 Td (Kepala SMP Negeri 4 Cepu) Tj ET\n";
        $p3 .= "BT /F1 9 Tf 50 80 Td (Halaman 3 : Perpustakaan Digital SMPN 4 Cepu) Tj ET\n";
        $pages[] = $p3;

        // Halaman 4: Daftar Isi
        $p4 = "BT /F1 14 Tf 50 780 Td (DAFTAR ISI DAN PETA MATERI) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 50 730 Td (KATA PENGANTAR KEPALA SEKOLAH .................................... Halaman 3) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 50 705 Td (PETUNJUK PENGGUNAAN E-BOOK INTERAKTIF ................. Halaman 4) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 50 680 Td (BAB 1 : EKSPLORASI KONSEP UTAMA .................................. Halaman 5) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 70 660 Td (1.1 Capaian Pembelajaran dan Tujuan Materi) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 70 640 Td (1.2 Landasan Teori dan Ulasan Kontekstual) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 70 620 Td (1.3 Karakter Profil Pelajar Pancasila) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 50 590 Td (BAB 2 : LATIHAN SOAL DAN AKTIVITAS SISWA ................. Halaman 6) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 70 570 Td (2.1 Pertanyaan Pemantik Berpikir Kritis) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 70 550 Td (2.2 Lembar Refleksi Pembelajaran Mandiri) Tj ET\n";
        $p4 .= "BT /F1 10 Tf 50 520 Td (GLOSARIUM DAN INFORMASI KOLEKSI FISIK .................. Halaman 6) Tj ET\n";
        $p4 .= "BT /F1 9 Tf 50 80 Td (Halaman 4 : Perpustakaan Digital SMPN 4 Cepu) Tj ET\n";
        $pages[] = $p4;

        // Halaman 5: Bab 1 Materi Utama
        $p5 = "BT /F1 14 Tf 50 780 Td (BAB 1 : EKSPLORASI KONSEP DAN CAPAIAN MATERI) Tj ET\n";
        $p5 .= "BT /F1 11 Tf 50 745 Td (1.1 Capaian Pembelajaran) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 50 725 Td (Peserta didik mampu memahami konsep esensial, menghubungkannya dengan peristiwa) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 50 705 Td (di sekitar kehidupan sehari-hari, serta menyajikan argumen berbasis data faktual.) Tj ET\n";
        $p5 .= "BT /F1 11 Tf 50 665 Td (1.2 Pembahasan Materi Esensial) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 50 645 Td (" . addcslashes("Ulasan pada " . $title . " dirancang terstruktur guna membimbing siswa", "()\n\r\\") . ") Tj ET\n";
        $p5 .= "BT /F1 10 Tf 50 625 Td (melakukan penyelidikan ilmiah secara bertahap, mulai dari orientasi masalah,) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 50 605 Td (pengumpulan data terukur, hingga penarikan simpulan yang dapat dipertanggungjawabkan.) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 50 575 Td (Dimensi Profil Pelajar Pancasila yang dikuatkan:) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 70 555 Td (- Beriman, bertakwa kepada Tuhan Yang Maha Esa, dan berakhlak mulia) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 70 535 Td (- Mandiri serta berdaya juang dalam menyelesaikan tugas) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 70 515 Td (- Bernalar kritis dalam menanggapi informasi dan fenomena) Tj ET\n";
        $p5 .= "BT /F1 10 Tf 70 495 Td (- Bergotong royong dalam kerja kelompok dan eksperimen sains) Tj ET\n";
        $p5 .= "BT /F1 9 Tf 50 80 Td (Halaman 5 : Perpustakaan Digital SMPN 4 Cepu) Tj ET\n";
        $pages[] = $p5;

        // Halaman 6: Aktivitas, Soal & Refleksi
        $p6 = "BT /F1 14 Tf 50 780 Td (LATIHAN MANDIRI DAN REFLEKSI SISWA) Tj ET\n";
        $p6 .= "BT /F1 11 Tf 50 740 Td (A. Uji Pemahaman Mandiri) Tj ET\n";
        $p6 .= "BT /F1 10 Tf 50 715 Td (1. Tuliskan gagasan utama dari bab ini dengan menggunakan bahasa Anda sendiri!) Tj ET\n";
        $p6 .= "BT /F1 10 Tf 50 690 Td (2. Berikan satu contoh penerapan nyata dari konsep ini di sekolah atau rumah!) Tj ET\n";
        $p6 .= "BT /F1 10 Tf 50 665 Td (3. Kendala apa yang Anda temui saat belajar dan bagaimana cara mengatasinya?) Tj ET\n";
        $p6 .= "BT /F1 11 Tf 50 615 Td (B. Ketersediaan Koleksi Fisik Perpustakaan) Tj ET\n";
        $p6 .= "BT /F1 10 Tf 50 590 Td (Buku teks cetak dan modul referensi lengkap tersedia di Ruang Perpustakaan) Tj ET\n";
        $p6 .= "BT /F1 10 Tf 50 570 Td (SMP Negeri 4 Cepu. Silakan hubungi petugas sirkulasi untuk peminjaman.) Tj ET\n";
        $p6 .= "BT /F1 9 Tf 50 490 Td (Layanan Perpustakaan Digital SMP Negeri 4 Cepu | smpn4cepu.sch.id) Tj ET\n";
        $p6 .= "BT /F1 9 Tf 50 80 Td (Halaman 6 : Akhir Modul · Perpustakaan Digital SMPN 4 Cepu) Tj ET\n";
        $pages[] = $p6;

        $numPages = count($pages);
        $fontObjId = 2 + ($numPages * 2) + 1;
        $catalogObj = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $kidsStr = "";
        for ($i = 0; $i < $numPages; $i++) {
            $pId = 3 + ($i * 2);
            $kidsStr .= "{$pId} 0 R ";
        }
        $pagesObj = "2 0 obj\n<< /Type /Pages /Kids [{$kidsStr}] /Count {$numPages} >>\nendobj\n";

        $body = $catalogObj . $pagesObj;

        for ($i = 0; $i < $numPages; $i++) {
            $pId = 3 + ($i * 2);
            $cId = 4 + ($i * 2);
            $cStream = $pages[$i];
            $cLen = strlen($cStream);

            $pObj = "{$pId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents {$cId} 0 R /Resources << /Font << /F1 {$fontObjId} 0 R >> >> >>\nendobj\n";
            $cObj = "{$cId} 0 obj\n<< /Length {$cLen} >>\nstream\n{$cStream}\nendstream\nendobj\n";

            $body .= $pObj . $cObj;
        }

        $fontObj = "{$fontObjId} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $body .= $fontObj;

        $header = "%PDF-1.4\n";
        $fullContentWithoutXref = $header . $body;

        $totalObjects = $fontObjId;
        $xref = "xref\n0 " . ($totalObjects + 1) . "\n";
        $xref .= "0000000000 65535 f \n";

        $currPos = strlen($header);
        $xref .= sprintf("%010d 00000 n \n", $currPos); // Obj 1
        $currPos += strlen($catalogObj);
        $xref .= sprintf("%010d 00000 n \n", $currPos); // Obj 2
        $currPos += strlen($pagesObj);

        for ($i = 0; $i < $numPages; $i++) {
            $pId = 3 + ($i * 2);
            $cId = 4 + ($i * 2);
            $cStream = $pages[$i];
            $cLen = strlen($cStream);
            $pObj = "{$pId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents {$cId} 0 R /Resources << /Font << /F1 {$fontObjId} 0 R >> >> >>\nendobj\n";
            $cObj = "{$cId} 0 obj\n<< /Length {$cLen} >>\nstream\n{$cStream}\nendstream\nendobj\n";

            $xref .= sprintf("%010d 00000 n \n", $currPos);
            $currPos += strlen($pObj);
            $xref .= sprintf("%010d 00000 n \n", $currPos);
            $currPos += strlen($cObj);
        }

        $xref .= sprintf("%010d 00000 n \n", $currPos); // Font obj

        $xrefOffset = strlen($fullContentWithoutXref);
        $trailer = "trailer\n<< /Size " . ($totalObjects + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";

        return $fullContentWithoutXref . $xref . $trailer;
    }
}
