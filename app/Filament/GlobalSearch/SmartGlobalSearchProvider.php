<?php

namespace App\Filament\GlobalSearch;

use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\Contracts\GlobalSearchProvider;
use Illuminate\Support\Str;

class SmartGlobalSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $builder = GlobalSearchResults::make();
        $cleanQuery = trim($query);

        if (empty($cleanQuery)) {
            return $builder;
        }

        $lowercaseQuery = Str::lower($cleanQuery);

        // 1. Pencarian Halaman & Menu Navigasi Admin
        $navigationResults = $this->searchNavigation($lowercaseQuery);
        if ($navigationResults->isNotEmpty()) {
            $builder->category('Menu & Halaman Cepat', $navigationResults);
        }

        // 2. Pencarian Data Model dari Semua Resources
        $resources = Filament::getResources();

        usort(
            $resources,
            fn (string $a, string $b): int => ($a::getGlobalSearchSort() ?? 0) <=> ($b::getGlobalSearchSort() ?? 0),
        );

        foreach ($resources as $resource) {
            if (! $resource::canGloballySearch()) {
                continue;
            }

            try {
                $resourceResults = $resource::getGlobalSearchResults($cleanQuery);

                if (! $resourceResults->count()) {
                    continue;
                }

                $builder->category($resource::getPluralModelLabel(), $resourceResults);
            } catch (\Throwable $e) {
                // Lewati resource jika terjadi kendala saat querying
                continue;
            }
        }

        return $builder;
    }

    protected function searchNavigation(string $query): \Illuminate\Support\Collection
    {
        $routes = [
            [
                'title' => 'Dashboard Utama',
                'group' => 'Panel Utama',
                'keywords' => ['dashboard', 'beranda', 'home', 'ringkasan', 'statistik', 'utama'],
                'routeName' => 'filament.admin.pages.dashboard',
            ],
            [
                'title' => 'Berita & Artikel',
                'group' => 'Profil & Informasi',
                'keywords' => ['berita', 'artikel', 'post', 'informasi', 'kegiatan', 'pengumuman', 'warta'],
                'routeName' => 'filament.admin.resources.posts.index',
            ],
            [
                'title' => 'Tulis Berita Baru',
                'group' => 'Profil & Informasi',
                'keywords' => ['buat berita', 'tambah berita', 'tulis berita', 'buat artikel', 'tambah artikel', 'post baru'],
                'routeName' => 'filament.admin.resources.posts.create',
            ],
            [
                'title' => 'Data Guru & Staf',
                'group' => 'Profil & Informasi',
                'keywords' => ['guru', 'staf', 'staff', 'tenaga pendidik', 'karyawan', 'pegawai', 'pengajar', 'wali kelas'],
                'routeName' => 'filament.admin.resources.staff-members.index',
            ],
            [
                'title' => 'Tambah Guru / Staf',
                'group' => 'Profil & Informasi',
                'keywords' => ['tambah guru', 'tambah staf', 'input guru', 'tambah pengajar', 'daftar guru'],
                'routeName' => 'filament.admin.resources.staff-members.create',
            ],
            [
                'title' => 'Fasilitas Sekolah',
                'group' => 'Profil & Informasi',
                'keywords' => ['fasilitas', 'sarana', 'prasarana', 'gedung', 'laboratorium', 'ruang', 'lapangan', 'kelas', 'lab'],
                'routeName' => 'filament.admin.resources.school-facilities.index',
            ],
            [
                'title' => 'Tambah Fasilitas',
                'group' => 'Profil & Informasi',
                'keywords' => ['tambah fasilitas', 'tambah sarana', 'input gedung', 'tambah lab'],
                'routeName' => 'filament.admin.resources.school-facilities.create',
            ],
            [
                'title' => 'Materi Pembelajaran',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['materi', 'modul', 'bahan ajar', 'pembelajaran', 'e-learning', 'dokumen materi', 'materi ajar'],
                'routeName' => 'filament.admin.resources.learning-materials.index',
            ],
            [
                'title' => 'Unggah Materi Baru',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['tambah materi', 'unggah materi', 'upload materi', 'buat modul', 'input materi'],
                'routeName' => 'filament.admin.resources.learning-materials.create',
            ],
            [
                'title' => 'Katalog Buku Perpustakaan',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['buku', 'perpustakaan', 'katalog', 'pustaka', 'literasi', 'bacaan', 'novel', 'pelajaran'],
                'routeName' => 'filament.admin.resources.library-books.index',
            ],
            [
                'title' => 'Tambah Buku Perpustakaan',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['tambah buku', 'input buku', 'katalog baru', 'buku baru'],
                'routeName' => 'filament.admin.resources.library-books.create',
            ],
            [
                'title' => 'Bank Soal & Ujian',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['soal', 'bank soal', 'ujian', 'cbt', 'asesmen', 'tes', 'evaluasi', 'kuis'],
                'routeName' => 'filament.admin.resources.question-banks.index',
            ],
            [
                'title' => 'Buat Bank Soal Baru',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['tambah soal', 'buat soal', 'tambah ujian', 'buat ujian', 'input soal'],
                'routeName' => 'filament.admin.resources.question-banks.create',
            ],
            [
                'title' => 'Mata Pelajaran',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['mapel', 'mata pelajaran', 'kurikulum', 'pelajaran', 'bidang studi'],
                'routeName' => 'filament.admin.resources.subjects.index',
            ],
            [
                'title' => 'Tambah Mata Pelajaran',
                'group' => 'Akademik & Pembelajaran',
                'keywords' => ['tambah mapel', 'tambah mata pelajaran', 'input mapel'],
                'routeName' => 'filament.admin.resources.subjects.create',
            ],
            [
                'title' => 'Kandidat Paslon Pilketos',
                'group' => 'Kesiswaan & Pilketos',
                'keywords' => ['pilketos', 'osis', 'paslon', 'kandidat', 'pemilihan', 'ketua osis', 'suara'],
                'routeName' => 'filament.admin.resources.election-candidates.index',
            ],
            [
                'title' => 'Tambah Paslon Pilketos',
                'group' => 'Kesiswaan & Pilketos',
                'keywords' => ['tambah paslon', 'tambah kandidat', 'daftar paslon', 'calon ketua'],
                'routeName' => 'filament.admin.resources.election-candidates.create',
            ],
            [
                'title' => 'Statistik Siswa',
                'group' => 'Profil & Informasi',
                'keywords' => ['statistik', 'siswa', 'demografi', 'data siswa', 'grafik siswa', 'rekap siswa', 'jumlah siswa'],
                'routeName' => 'filament.admin.resources.student-statistics.index',
            ],
            [
                'title' => 'Kelola Data Statistik Siswa',
                'group' => 'Profil & Informasi',
                'keywords' => ['tambah statistik', 'input siswa', 'tambah data siswa'],
                'routeName' => 'filament.admin.resources.student-statistics.create',
            ],
        ];

        $results = collect();

        foreach ($routes as $route) {
            $titleMatch = Str::contains(Str::lower($route['title']), $query);
            $groupMatch = Str::contains(Str::lower($route['group']), $query);
            $keywordMatch = false;

            foreach ($route['keywords'] as $keyword) {
                if (Str::contains($keyword, $query) || Str::contains($query, $keyword)) {
                    $keywordMatch = true;
                    break;
                }
            }

            if ($titleMatch || $groupMatch || $keywordMatch) {
                try {
                    $url = route($route['routeName']);
                    $isAction = str_starts_with($route['title'], 'Tambah') || str_starts_with($route['title'], 'Buat') || str_starts_with($route['title'], 'Tulis') || str_starts_with($route['title'], 'Unggah') || str_starts_with($route['title'], 'Kelola');

                    $results->push(new GlobalSearchResult(
                        title: $route['title'],
                        url: $url,
                        details: [
                            'Menu' => $route['group'],
                            'Tipe' => $isAction ? 'Aksi Cepat' : 'Halaman Navigasi',
                        ]
                    ));
                } catch (\Throwable $e) {
                    // Abaikan jika route belum terdaftar
                }
            }
        }

        return $results;
    }
}
