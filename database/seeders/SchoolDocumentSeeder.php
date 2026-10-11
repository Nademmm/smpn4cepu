<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\SchoolDocument;
use Illuminate\Database\Seeder;

class SchoolDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $docs = [
            [
                'title' => 'Kalender Akademik Tahun Ajaran 2026/2027',
                'category' => 'Akademik',
                'description' => 'Jadwal masuk sekolah, penilaian tengah semester, asesmen akhir semester, serta hari libur resmi.',
                'file_path' => 'documents/kalender_akademik_2026_2027.pdf',
                'file_size' => '1.2 MB',
                'file_extension' => 'pdf',
                'download_count' => 142,
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'title' => 'Buku Saku Tata Tertib & Kode Etik Peserta Didik',
                'category' => 'Kesiswaan',
                'description' => 'Pedoman kedisiplinan, aturan seragam, hak dan kewajiban siswa, serta norma perilaku di lingkungan sekolah.',
                'file_path' => 'documents/tata_tertib_peserta_didik.pdf',
                'file_size' => '850 KB',
                'file_extension' => 'pdf',
                'download_count' => 89,
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'title' => 'Formulir Permohonan Izin / Keterangan Tidak Masuk Sekolah',
                'category' => 'Formulir',
                'description' => 'Surat permohonan resmi dari orang tua / wali murid untuk izin sakit atau keperluan mendesak keluarga.',
                'file_path' => 'documents/formulir_izin_siswa.pdf',
                'file_size' => '320 KB',
                'file_extension' => 'pdf',
                'download_count' => 210,
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'title' => 'Panduan Peminjaman Buku Perpustakaan Sekolah',
                'category' => 'Perpustakaan',
                'description' => 'Alur dan tata cara peminjaman koleksi buku fisik di ruang perpustakaan SMP Negeri 4 Cepu.',
                'file_path' => 'documents/panduan_perpustakaan.pdf',
                'file_size' => '540 KB',
                'file_extension' => 'pdf',
                'download_count' => 65,
                'is_active' => true,
                'display_order' => 4,
            ],
        ];

        foreach ($docs as $doc) {
            SchoolDocument::firstOrCreate(['title' => $doc['title']], $doc);
        }

        // Pesan kontak awal sebagai contoh tindak lanjut Humas
        ContactMessage::firstOrCreate(
            ['email_or_phone' => 'budi.santoso@example.com'],
            [
                'name' => 'Budi Santoso (Wali Murid)',
                'email_or_phone' => 'budi.santoso@example.com',
                'subject' => 'Informasi Pelayanan Legalisir Ijazah',
                'message' => 'Selamat pagi, mohon informasi jadwal dan syarat berkas yang harus dibawa untuk legalisir ijazah kelulusan tahun 2024. Terima kasih.',
                'ip_address' => '127.0.0.1',
                'is_read' => false,
            ]
        );
    }
}
