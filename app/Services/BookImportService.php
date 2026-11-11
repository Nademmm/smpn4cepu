<?php

namespace App\Services;

use App\Enums\BookCategory;
use App\Models\Book;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BookImportService
{
    /**
     * Parse berkas Excel (.xlsx) atau CSV dan simpan ke basis data.
     *
     * @return array{success: bool, imported: int, updated: int, failed: int, errors: array<string>}
     */
    public function import(string|UploadedFile $file): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => ['Berkas tidak ditemukan pada server.'],
            ];
        }

        $extension = strtolower(pathinfo($file instanceof UploadedFile ? $file->getClientOriginalName() : $filePath, PATHINFO_EXTENSION));

        $rows = [];
        if ($extension === 'xlsx') {
            $rows = $this->parseXlsx($filePath);
        } else {
            $rows = $this->parseCsv($filePath);
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'imported' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => ['Tidak ada data yang dapat dibaca dari berkas Excel/CSV. Pastikan format kolom sesuai template.'],
            ];
        }

        $imported = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        $disk = Storage::disk('public');
        if (!$disk->exists('books/covers')) {
            $disk->makeDirectory('books/covers');
        }

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Baris Excel (baris 1 adalah header)
            $title = trim($row['title'] ?? '');

            if (empty($title)) {
                $failed++;
                $errors[] = "Baris {$rowNum}: Judul buku kosong, dilewati.";
                continue;
            }

            $isbn = !empty($row['isbn']) ? preg_replace('/[^0-9X]/i', '', trim($row['isbn'])) : null;
            $author = !empty($row['author']) ? trim($row['author']) : 'Tim Penulis SMPN 4 Cepu';
            $publisher = !empty($row['publisher']) ? trim($row['publisher']) : 'SMP Negeri 4 Cepu';
            $year = !empty($row['year']) && is_numeric($row['year']) ? (int) $row['year'] : (int) date('Y');
            $category = $this->normalizeCategory($row['category'] ?? '');
            $stock = isset($row['stock']) && is_numeric($row['stock']) ? max(0, (int) $row['stock']) : 10;
            $description = !empty($row['description']) ? trim($row['description']) : "Koleksi resmi Perpustakaan SMP Negeri 4 Cepu kategori {$category}.";

            $slug = Str::slug($title);
            // Tambahkan sufiks jika slug sudah dipakai buku lain dengan ISBN berbeda
            if (Book::where('slug', $slug)->when($isbn, fn($q) => $q->where('isbn', '!=', $isbn))->exists()) {
                $slug .= '-' . Str::lower(Str::random(4));
            }

            // Generate cover SVG otomatis jika belum ada file cover
            $coverFileName = "books/covers/{$slug}.svg";
            if (!$disk->exists($coverFileName)) {
                $categoryEnum = BookCategory::tryFrom($category);
                $categoryLabel = $categoryEnum ? $categoryEnum->getLabel() : 'Koleksi Perpustakaan';
                $coverSvg = $this->generateCoverSvg($title, $author, $categoryLabel);
                $disk->put($coverFileName, $coverSvg);
            }

            // Simpan atau perbarui buku berdasarkan ISBN jika ada, atau berdasarkan judul
            $existing = null;
            if (!empty($isbn)) {
                $existing = Book::where('isbn', $isbn)->first();
            }
            if (!$existing) {
                $existing = Book::where('title', $title)->first();
            }

            if ($existing) {
                $existing->update([
                    'title' => $title,
                    'author' => $author,
                    'publisher' => $publisher,
                    'published_year' => $year,
                    'category' => $category,
                    'description' => $description,
                    'physical_stock' => $stock > 0 ? $stock : $existing->physical_stock,
                    'is_active' => true,
                ]);
                $updated++;
            } else {
                Book::create([
                    'title' => $title,
                    'slug' => $slug,
                    'isbn' => $isbn,
                    'author' => $author,
                    'publisher' => $publisher,
                    'published_year' => $year,
                    'category' => $category,
                    'description' => $description,
                    'cover_path' => $coverFileName,
                    'physical_stock' => $stock,
                    'views_count' => 0,
                    'is_featured' => false,
                    'is_active' => true,
                ]);
                $imported++;
            }
        }

        // Hapus cache statistik perpustakaan agar langsung sinkron di publik
        \Illuminate\Support\Facades\Cache::forget('public_books_stats_v1');
        \Illuminate\Support\Facades\Cache::forget('public_books_category_counts_v1');
        \Illuminate\Support\Facades\Cache::forget('admin_school_overview_stats');

        return [
            'success' => ($imported + $updated) > 0,
            'imported' => $imported,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Membaca berkas Excel .xlsx menggunakan ZipArchive & SimpleXML bawaan PHP.
     * Cepat, hemat memori, dan tanpa dependensi eksternal.
     *
     * @return array<int, array<string, string>>
     */
    private function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca shared strings
        $sharedStrings = [];
        $sstXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sstXml) {
            $xml = @simplexml_load_string($sstXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string) $si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // 2. Baca sheet1.xml
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetContent) {
            return [];
        }

        $xml = @simplexml_load_string($sheetContent);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        $headerMap = [];

        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            $cells = [];

            foreach ($row->c as $c) {
                $cellRef = (string) $c['r'];
                preg_match('/^([A-Z]+)/', $cellRef, $matches);
                $col = $matches[1] ?? '';
                $t = (string) $c['t'];
                $val = (string) $c->v;

                if ($t === 's') {
                    $val = $sharedStrings[(int) $val] ?? '';
                }

                $cells[$col] = trim($val);
            }

            // Baris 1: Header Mapping
            if ($rowNum === 1) {
                foreach ($cells as $col => $headerText) {
                    $cleanHeader = strtolower(trim($headerText));
                    $headerMap[$col] = $this->mapKeyToField($cleanHeader);
                }
                continue;
            }

            // Lewati baris kosong
            if (empty(array_filter($cells))) {
                continue;
            }

            $rowData = [];
            foreach ($cells as $col => $val) {
                $field = $headerMap[$col] ?? null;
                if ($field) {
                    $rowData[$field] = $val;
                }
            }

            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Membaca berkas CSV dengan pemisah koma atau titik-koma.
     *
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [];
        }

        // Cek delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        $header = fgetcsv($handle, 4096, $delimiter);
        if (!$header) {
            fclose($handle);
            return [];
        }

        $headerMap = [];
        foreach ($header as $idx => $h) {
            $cleanHeader = strtolower(trim((string)$h));
            $headerMap[$idx] = $this->mapKeyToField($cleanHeader);
        }

        $rows = [];
        while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            $rowData = [];
            foreach ($row as $idx => $val) {
                $field = $headerMap[$idx] ?? null;
                if ($field) {
                    $rowData[$field] = trim((string)$val);
                }
            }

            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Memetakan nama kolom Excel/CSV ke atribut Book.
     */
    private function mapKeyToField(string $key): ?string
    {
        if (str_contains($key, 'judul') || str_contains($key, 'title')) return 'title';
        if (str_contains($key, 'isbn')) return 'isbn';
        if (str_contains($key, 'penulis') || str_contains($key, 'pengarang') || str_contains($key, 'author')) return 'author';
        if (str_contains($key, 'penerbit') || str_contains($key, 'publisher')) return 'publisher';
        if (str_contains($key, 'tahun') || str_contains($key, 'year')) return 'year';
        if (str_contains($key, 'kategori') || str_contains($key, 'category')) return 'category';
        if (str_contains($key, 'stok') || str_contains($key, 'stock') || str_contains($key, 'jumlah')) return 'stock';
        if (str_contains($key, 'deskripsi') || str_contains($key, 'sinopsis') || str_contains($key, 'keterangan') || str_contains($key, 'rak')) return 'description';

        return null;
    }

    /**
     * Menormalisasi kategori input ke BookCategory enum value.
     */
    private function normalizeCategory(string $input): string
    {
        $input = strtolower(trim($input));

        if (str_contains($input, '7') || str_contains($input, 'vii')) return BookCategory::KELAS_7->value;
        if (str_contains($input, '8') || str_contains($input, 'viii')) return BookCategory::KELAS_8->value;
        if (str_contains($input, '9') || str_contains($input, 'ix')) return BookCategory::KELAS_9->value;
        if (str_contains($input, 'karya') || str_contains($input, 'sekolah')) return BookCategory::KARYA_SEKOLAH->value;

        return BookCategory::LITERASI_UMUM->value;
    }

    /**
     * Menghasilkan teks CSV template resmi yang siap diunduh pengelola perpustakaan.
     */
    public static function getTemplateCsv(): string
    {
        $headers = [
            'Judul Buku',
            'Nomor ISBN',
            'Penulis / Pengarang',
            'Penerbit',
            'Tahun Terbit',
            'Kategori (kelas-7 / kelas-8 / kelas-9 / literasi-umum / karya-sekolah)',
            'Stok Fisik',
            'Deskripsi / Sinopsis / Lokasi Rak',
        ];

        $sampleRows = [
            [
                'Ilmu Pengetahuan Alam untuk SMP Kelas VII',
                '9786022443834',
                'Budiyanti Dwi Hardanie, dkk.',
                'Pusat Perbukuan Kemendikbudristek',
                '2023',
                'kelas-7',
                '40',
                'Buku teks utama IPA Kurikulum Merdeka membahas klasifikasi makhluk hidup, zat, energi, dan bumi.',
            ],
            [
                'Laskar Pelangi',
                '9789793062792',
                'Andrea Hirata',
                'Bentang Pustaka',
                '2018',
                'literasi-umum',
                '15',
                'Novel inspiratif tentang perjuangan 10 siswa di Belitung dalam menggapai pendidikan. Rak B-02.',
            ],
            [
                'Antologi Cerpen & Puisi Pelajar Spenpat Cepu',
                '',
                'OSIS & Tim Jurnalistik SMPN 4 Cepu',
                'SMP Negeri 4 Cepu Press',
                '2025',
                'karya-sekolah',
                '25',
                'Kumpulan karya kreatif siswa dan guru SMP Negeri 4 Cepu bertema literasi dan kearifan lokal. Rak K-01.',
            ],
        ];

        $output = fopen('php://temp', 'r+');
        // Tambahkan UTF-8 BOM agar terbaca sempurna saat dibuka langsung di Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');

        foreach ($sampleRows as $row) {
            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Bikin Cover SVG elegan untuk buku yang di-import tanpa berkas sampul.
     */
    private function generateCoverSvg(string $title, string $author, string $categoryLabel): string
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
    <rect x="10" y="10" width="280" height="430" rx="12" stroke="#38BDF8" stroke-width="2" stroke-opacity="0.35"/>
    <circle cx="150" cy="110" r="50" fill="#38BDF8" fill-opacity="0.15"/>
    <circle cx="150" cy="110" r="36" stroke="#38BDF8" stroke-width="2" stroke-opacity="0.5"/>
    <path d="M130 120V98C130 94 140 92 150 92C160 92 170 94 170 98V120M130 120C130 116 140 114 150 114C160 114 170 116 170 120M130 120C140 122 150 122 150 122M170 120C160 122 150 122 150 122V114" stroke="#38BDF8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="35" y="172" width="230" height="26" rx="13" fill="#38BDF8" fill-opacity="0.25"/>
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
}
