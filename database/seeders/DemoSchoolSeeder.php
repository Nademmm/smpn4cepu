<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\PostCategory;
use App\Models\ElectionCandidate;
use App\Models\LibraryBook;
use App\Models\Post;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\SchoolFacility;
use App\Models\StaffMember;
use App\Models\StudentStatistic;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSchoolSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@smpn4cepu.sch.id')->first();
        $guru = User::where('email', 'guru@smpn4cepu.sch.id')->first();

        // 1. Master 11 Mata Pelajaran Resmi SMPN 4 Cepu
        $subjects = [
            ['code' => 'PAI-SMP', 'name' => 'Pendidikan Agama Islam dan Budi Pekerti'],
            ['code' => 'PPKN-SMP', 'name' => 'Pendidikan Pancasila'],
            ['code' => 'IND-SMP', 'name' => 'Bahasa Indonesia'],
            ['code' => 'ING-SMP', 'name' => 'Bahasa Inggris'],
            ['code' => 'MAT-SMP', 'name' => 'Matematika'],
            ['code' => 'IPA-SMP', 'name' => 'Ilmu Pengetahuan Alam (IPA)'],
            ['code' => 'IPS-SMP', 'name' => 'Ilmu Pengetahuan Sosial (IPS)'],
            ['code' => 'PJOK-SMP', 'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            ['code' => 'INF-SMP', 'name' => 'Informatika'],
            ['code' => 'SENI-SMP', 'name' => 'Seni dan Prakarya'],
            ['code' => 'JAWA-SMP', 'name' => 'Bahasa Jawa'],
        ];

        foreach ($subjects as $sub) {
            Subject::firstOrCreate(['code' => $sub['code']], ['name' => $sub['name']]);
        }

        // 2. Staf Pengajar & Tenaga Kependidikan Resmi (Data Primer Sekolah Beserta Foto Riil)
        StaffMember::where('name', 'Drs. H. Hartono, M.Pd.')->delete();

        $staffData = [
            [
                'nip' => '196807181994121001',
                'name' => 'Prasetyo Cahyo Nugroho, S.Pd., M.M.',
                'position' => 'Kepala Sekolah',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'images/principal/FOTO_KS_1.jpg',
                'display_order' => 1,
            ],
            [
                'nip' => '197911142005012025',
                'name' => 'HUSNUL KHOTIMAH, S.S.',
                'position' => 'Waka Kurikulum',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/husnul-khotimah.jpeg',
                'display_order' => 2,
            ],
            [
                'nip' => '196608151989022001',
                'name' => 'Tri Wartuti, S.Pd.',
                'position' => 'Guru Madya Tk. I / Kesiswaan',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 3,
            ],
            [
                'nip' => '196710011994121004',
                'name' => 'Wahyudi, S.Pd.',
                'position' => 'Guru Madya Tk. I',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/wahyudi.jpeg',
                'display_order' => 4,
            ],
            [
                'nip' => '19690215 199801 2 001',
                'name' => 'Dra. Setyorini',
                'position' => 'Guru Madya',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'images/staff/setyorini.jpeg',
                'display_order' => 6,
            ],
            [
                'nip' => '197207072005012016',
                'name' => 'Ellies Erina, S.S.',
                'position' => 'Waka Sarana Prasarana',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 4,
            ],
            [
                'nip' => '196906302008011005',
                'name' => 'Muhamad Sjafei, S.Pd.',
                'position' => 'Waka Humas',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 5,
            ],
            [
                'nip' => '197101132008012003',
                'name' => 'Lucia Kristiyaningsih, S.Pd.',
                'position' => 'Guru Madya',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 9,
            ],
            [
                'nip' => '198204142010011025',
                'name' => 'Said Mubarok, S.Pd.',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 10,
            ],
            [
                'nip' => '197706132009011009',
                'name' => 'Mukhammad Syaidin, M.Pd.I.',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 11,
            ],
            [
                'nip' => '198703242015041001',
                'name' => 'Sulistiyo, S.Pd., Gr.',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 12,
            ],
            [
                'nip' => '197601062014062003',
                'name' => 'Indah Lestari, S.Pd.',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 13,
            ],
            [
                'nip' => '197904242014062004',
                'name' => 'Siti Masripah, S.Pd.',
                'position' => 'Guru Pertama',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/siti-masripah.jpeg',
                'display_order' => 14,
            ],
            [
                'nip' => '197902062014062003',
                'name' => 'Nurbaiti, S.Pd.',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/nurbaiti.jpeg',
                'display_order' => 15,
            ],
            [
                'nip' => '197906082014062002',
                'name' => 'Diana Puji Astuti, S.Pd.',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/diana-puji-astuti.jpeg',
                'display_order' => 16,
            ],
            [
                'nip' => '196703282008012005',
                'name' => 'Nurhayati, S.E.',
                'position' => 'Guru Pertama',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/nurhayati.jpeg',
                'display_order' => 17,
            ],
            [
                'nip' => '197111022014062001',
                'name' => 'Kun Ambarsasi, S.Pd.',
                'position' => 'Guru Pertama',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/kun-ambarsasi.jpeg',
                'display_order' => 18,
            ],
            [
                'nip' => '198705092017081001',
                'name' => 'Sampurno, S.Pd., Gr.',
                'position' => 'Waka Kesiswaan',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 3,
            ],
            [
                'nip' => '196708292014062002',
                'name' => 'Sri Pintaningtyastuti, S.Pd.',
                'position' => 'Guru Pertama',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/sri-pintaningtyastuti.jpeg',
                'display_order' => 20,
            ],
            [
                'nip' => '198610302020121004',
                'name' => 'Tri Sri Kuncoro, S.Pd.',
                'position' => 'Guru Pertama',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 21,
            ],
            [
                'nip' => '199208262022212007',
                'name' => 'Mifta Yustiningtyas Fauziah, S.Pd., Gr.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/mifta-yustiningtyas-fauziah.jpeg',
                'display_order' => 22,
            ],
            [
                'nip' => '197703262024212004',
                'name' => 'Laeli Hasanah, S.Pd.',
                'position' => 'Guru Ahli Pertama / Kepala Perpustakaan',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/laeli-hasanah.jpeg',
                'display_order' => 23,
            ],
            [
                'nip' => '199301192024211008',
                'name' => 'Freddy Ariestya Pradana, S.Pd.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => null,
                'display_order' => 24,
            ],
            [
                'nip' => '199607162024212036',
                'name' => 'Hardina Briliyani Gusman, S.Pd.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/hardina-briliyani-gusman.jpeg',
                'display_order' => 25,
            ],
            [
                'nip' => '199903162024212023',
                'name' => 'ZAHROTUNNIHAYAH, S.Pd.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/zahrotunnihayah.jpeg',
                'display_order' => 26,
            ],
            [
                'nip' => '200007052024212007',
                'name' => 'Hetik Wulandari, S.Pd.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/hetik-wulandari.jpeg',
                'display_order' => 27,
            ],
            [
                'nip' => '199705192025212011',
                'name' => 'Brilliani Dyah Sekartaji, S.Pd.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/brilliani-dyah-sekartaji.jpeg',
                'display_order' => 28,
            ],
            [
                'nip' => '199812252025212015',
                'name' => 'Dhea Eristania Dewi, S.Pd.',
                'position' => 'Guru Ahli Pertama',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => null,
                'display_order' => 29,
            ],
            [
                'nip' => '199106282023212024',
                'name' => 'Yaatun, A.Ma.Pust.',
                'position' => 'Teknis Pustakawan',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => null,
                'display_order' => 30,
            ],
            [
                'nip' => '196907262025212004',
                'name' => 'Ninik Yuliastutik',
                'position' => 'Staf Tenaga Kependidikan',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/ninik-yuliastutik.jpeg',
                'display_order' => 31,
            ],
            [
                'nip' => '197809132025212004',
                'name' => 'Hermayanti Sri P.',
                'position' => 'Staf Tenaga Kependidikan',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/hermayanti-sri-p.jpeg',
                'display_order' => 32,
            ],
            [
                'nip' => '198701302025212006',
                'name' => 'Dyah Dirta Sukmasari',
                'position' => 'Staf Tata Usaha',
                'employment_status' => EmploymentStatus::PPPK,
                'photo_path' => 'staff/dyah-dirta-sukmasari.jpeg',
                'display_order' => 33,
            ],
            [
                'nip' => null,
                'name' => 'Rakip',
                'position' => 'Staf Tenaga Kependidikan',
                'employment_status' => EmploymentStatus::HONORER,
                'photo_path' => 'staff/rakip.jpeg',
                'display_order' => 34,
            ],
            [
                'nip' => null,
                'name' => 'Sukir Anto',
                'position' => 'Staf Tenaga Kependidikan',
                'employment_status' => EmploymentStatus::HONORER,
                'photo_path' => 'staff/sukir-anto.jpeg',
                'display_order' => 35,
            ],
            [
                'nip' => null,
                'name' => 'Ahmad Yusron Auladi, S.Pd.',
                'position' => 'Guru Informatika & Media Pembelajaran',
                'employment_status' => EmploymentStatus::HONORER,
                'photo_path' => null,
                'display_order' => 36,
            ],
        ];

        foreach ($staffData as $s) {
            if (!empty($s['nip']) && StaffMember::where('nip', $s['nip'])->exists()) {
                continue;
            }
            StaffMember::firstOrCreate(
                ['name' => $s['name']],
                array_merge($s, ['is_active' => true])
            );
        }

        // 4. 11 Fasilitas Resmi Sekolah
        $facilities = [
            ['name' => 'Ruang Kelas Pembelajaran', 'description' => 'Ruang kelas bersih, nyaman, dan berorientasi student-centered learning.', 'photo_path' => 'images/assets/IMG_5797.JPG'],
            ['name' => 'Laboratorium IPA', 'description' => 'Peralatan praktikum sains fisika dan biologi terstandar.', 'photo_path' => null],
            ['name' => 'Laboratorium Komputer Multimedia', 'description' => '40 PC modern berkecepatan tinggi untuk TIK dan ujian digital.', 'photo_path' => null],
            ['name' => 'Perpustakaan Sekolah', 'description' => 'Pusat literasi dengan ribuan koleksi buku bacaan dan buku paket.', 'photo_path' => null],
            ['name' => 'Lapangan Olahraga', 'description' => 'Lapangan multifungsi untuk bola voli, basket, dan futsal.', 'photo_path' => 'images/facilities/UPACARA2.JPG'],
            ['name' => 'Gedung Indoor / Aula Serbaguna', 'description' => 'Gedung pertemuan siswa, latihan bulu tangkis, dan pentas seni.', 'photo_path' => 'images/assets/IMG_6522.JPG'],
            ['name' => 'Lapangan Upacara', 'description' => 'Area luas dan asri untuk upacara bendera serta apel kedisiplinan.', 'photo_path' => 'images/facilities/UPACARA1.JPG'],
            ['name' => 'Mushala Sekolah', 'description' => 'Sarana ibadah sholat dhuhur berjamaah dan Baca Tulis Alquran.', 'photo_path' => 'images/facilities/MUSHOLA3.jpeg'],
            ['name' => 'Ruang Guru', 'description' => 'Ruang kerja kolaboratif pendidik yang representatif.', 'photo_path' => null],
            ['name' => 'Ruang Tata Usaha', 'description' => 'Pusat pelayanan administrasi dan kearsipan persuratan sekolah.', 'photo_path' => null],
            ['name' => 'Ruang Kesenian', 'description' => 'Studio berekspresi seni tari, kriya, dan musik gamelan.', 'photo_path' => 'images/assets/IMG_8383.JPG'],
        ];

        foreach ($facilities as $idx => $fac) {
            SchoolFacility::firstOrCreate(
                ['name' => $fac['name']],
                ['description' => $fac['description'], 'photo_path' => $fac['photo_path'], 'display_order' => $idx + 1]
            );
        }

        // 5. Buku Perpustakaan
        $books = [
            [
                'isbn' => '978-602-244-325-4',
                'title' => 'Matematika untuk SMP Kelas VIII',
                'author' => 'Tim Kemdikbudristek',
                'publisher' => 'Pusat Perbukuan Kemendikbud',
                'category' => 'Buku Pelajaran',
                'publication_year' => 2022,
                'shelf_location' => 'Rak A-01',
                'total_stock' => 60,
                'available_stock' => 58,
            ],
            [
                'isbn' => '978-602-244-326-1',
                'title' => 'Ilmu Pengetahuan Alam untuk SMP Kelas VII',
                'author' => 'Victoriani Inabuy, dkk.',
                'publisher' => 'Pusat Perbukuan Kemendikbud',
                'category' => 'Buku Pelajaran',
                'publication_year' => 2021,
                'shelf_location' => 'Rak A-02',
                'total_stock' => 50,
                'available_stock' => 45,
            ],
            [
                'isbn' => '978-602-03-2478-4',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'category' => 'Fiksi & Sastra',
                'publication_year' => 2018,
                'shelf_location' => 'Rak F-03',
                'total_stock' => 10,
                'available_stock' => 8,
            ],
            [
                'isbn' => '978-602-06-3317-6',
                'title' => 'Informatika untuk Masa Depan',
                'author' => 'Irya Wisnubhadra',
                'publisher' => 'Gramedia Widiasarana',
                'category' => 'Teknologi',
                'publication_year' => 2023,
                'shelf_location' => 'Rak T-01',
                'total_stock' => 25,
                'available_stock' => 25,
            ],
        ];

        foreach ($books as $b) {
            LibraryBook::firstOrCreate(['isbn' => $b['isbn']], $b);
        }

        // 5b. Rincian 20 Rombongan Belajar (Rombel) Resmi
        $rombelData = [
            ['grade' => 7, 'class' => '7A', 'l' => 15, 'p' => 16, 'total' => 31],
            ['grade' => 7, 'class' => '7B', 'l' => 15, 'p' => 16, 'total' => 31],
            ['grade' => 7, 'class' => '7C', 'l' => 17, 'p' => 15, 'total' => 32],
            ['grade' => 7, 'class' => '7D', 'l' => 16, 'p' => 15, 'total' => 31],
            ['grade' => 7, 'class' => '7E', 'l' => 16, 'p' => 15, 'total' => 31],
            ['grade' => 7, 'class' => '7F', 'l' => 16, 'p' => 16, 'total' => 32],
            ['grade' => 7, 'class' => '7G', 'l' => 16, 'p' => 15, 'total' => 31],
            ['grade' => 8, 'class' => '8A', 'l' => 16, 'p' => 13, 'total' => 29],
            ['grade' => 8, 'class' => '8B', 'l' => 18, 'p' => 12, 'total' => 30],
            ['grade' => 8, 'class' => '8C', 'l' => 15, 'p' => 13, 'total' => 28],
            ['grade' => 8, 'class' => '8D', 'l' => 17, 'p' => 11, 'total' => 28],
            ['grade' => 8, 'class' => '8E', 'l' => 18, 'p' => 12, 'total' => 30],
            ['grade' => 8, 'class' => '8F', 'l' => 16, 'p' => 12, 'total' => 28],
            ['grade' => 8, 'class' => '8G', 'l' => 16, 'p' => 13, 'total' => 29],
            ['grade' => 9, 'class' => '9A', 'l' => 15, 'p' => 16, 'total' => 31],
            ['grade' => 9, 'class' => '9B', 'l' => 14, 'p' => 18, 'total' => 32],
            ['grade' => 9, 'class' => '9C', 'l' => 17, 'p' => 15, 'total' => 32],
            ['grade' => 9, 'class' => '9D', 'l' => 13, 'p' => 16, 'total' => 29],
            ['grade' => 9, 'class' => '9E', 'l' => 14, 'p' => 18, 'total' => 32],
            ['grade' => 9, 'class' => '9F', 'l' => 16, 'p' => 15, 'total' => 31],
        ];

        foreach ($rombelData as $r) {
            \App\Models\StudentStatistic::firstOrCreate(
                ['academic_year' => '2025/2026', 'class_name' => $r['class']],
                [
                    'grade_level' => $r['grade'],
                    'male_count' => $r['l'],
                    'female_count' => $r['p'],
                    'total_count' => $r['total'],
                ]
            );
        }

        // 6. Kandidat Pilketos Resmi
        ElectionCandidate::firstOrCreate(
            ['candidate_number' => 1],
            [
                'candidate_name' => 'Aditya Pratama (8A)',
                'vice_candidate_name' => 'Siti Nurhaliza (7B)',
                'vision' => 'Mewujudkan OSIS SMPN 4 Cepu yang tanggap teknologi, inklusif, dan berkarakter CERIA BERIMAN.',
                'mission' => "1. Meningkatkan literasi digital siswa melalui klub koding dan multimedia.\n2. Mengadakan bakti sosial rutin di wilayah Cepu.\n3. Mewadahi aspirasi siswa secara daring tanpa diskriminasi.",
                'total_votes_cached' => 0,
            ]
        );

        ElectionCandidate::firstOrCreate(
            ['candidate_number' => 2],
            [
                'candidate_name' => 'Rangga Bayu (8C)',
                'vice_candidate_name' => 'Anindya Putri (7D)',
                'vision' => 'Membangun generasi cerdas berbudaya, peduli lingkungan, dan unggul dalam prestasi.',
                'mission' => "1. Program Adiwiyata Digital berbasis bank sampah sekolah.\n2. Optimalisasi mading digital untuk kreasi seni siswa.\n3. Peningkatan prestasi olahraga dan seni tingkat kabupaten Blora.",
                'total_votes_cached' => 0,
            ]
        );

        // 7. Berita, Pengumuman, dan Agenda Resmi
        if ($admin) {
            Post::firstOrCreate(
                ['slug' => 'penerapan-konsep-satu-sekolah-satu-ruang-digital'],
                [
                    'author_id' => $admin->id,
                    'category' => PostCategory::BERITA,
                    'title' => 'Penerapan Konsep "Satu Sekolah, Satu Ruang Digital" di SMP Negeri 4 Cepu',
                    'excerpt' => 'SMPN 4 Cepu memulai transformasi digital terpadu melalui website resmi dan layanan digital terintegrasi.',
                    'content' => 'Dalam rangka meningkatkan pelayanan pendidikan dan keterbukaan informasi, SMP Negeri 4 Cepu resmi menginisiasi konsep Satu Sekolah, Satu Ruang Digital...',
                    'featured_image' => 'images/assets/20260717_092659.jpg',
                    'is_published' => true,
                    'published_at' => now()->subDays(2),
                ]
            );

            Post::firstOrCreate(
                ['slug' => 'pengumuman-pelaksanaan-pemilihan-ketua-osis-daring-2026'],
                [
                    'author_id' => $admin->id,
                    'category' => PostCategory::PENGUMUMAN,
                    'title' => 'Pengumuman Pelaksanaan Pemilihan Ketua OSIS Daring 2026',
                    'excerpt' => 'Seluruh siswa diundang untuk memberikan hak suaranya dalam Pilketos berbasis bilik digital terbuka.',
                    'content' => 'Pemilihan Ketua dan Wakil Ketua OSIS periode 2026/2027 akan diselenggarakan secara daring menggunakan sistem pemungutan suara aman...',
                    'featured_image' => 'images/assets/IMG_6518.JPG',
                    'is_published' => true,
                    'published_at' => now()->subDay(),
                ]
            );

            Post::firstOrCreate(
                ['slug' => 'agenda-penilaian-sumatif-tengah-semester-gasal'],
                [
                    'author_id' => $admin->id,
                    'category' => PostCategory::AGENDA,
                    'title' => 'Agenda Penilaian Sumatif Tengah Semester Gasal',
                    'excerpt' => 'Pelaksanaan Asesmen Tengah Semester bagi seluruh siswa kelas VII, VIII, dan IX.',
                    'content' => 'Diberitahukan kepada seluruh bapak/ibu guru dan peserta didik bahwa asesmen sumatif akan dilaksanakan mulai tanggal 12 Oktober 2026...',
                    'featured_image' => 'images/assets/IMG_5797.JPG',
                    'event_date' => now()->addDays(8),
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );
        }

        // 8. Bank Soal & Materi Latihan Per Angkatan (Kelas 7, Kelas 8, Kelas 9)
        $authorId = $guru ? $guru->id : ($admin ? $admin->id : 1);
        $ipa = Subject::where('code', 'IPA-SMP')->first();
        $matematika = Subject::where('code', 'MAT-SMP')->first();
        $bindo = Subject::where('code', 'IND-SMP')->first();

        // 8a. Paket Soal Angkatan Kelas 7 (IPA)
        if ($ipa) {
            \App\Models\LearningMaterial::firstOrCreate(
                ['slug' => 'modul-klasifikasi-makhluk-hidup-dan-ekosistem-kelas-7'],
                [
                    'subject_id' => $ipa->id,
                    'grade_level' => 7,
                    'title' => 'Modul Klasifikasi Makhluk Hidup & Ekosistem - Kelas 7',
                    'summary' => 'Panduan lengkap ciri-ciri makhluk hidup, taksonomi lima kingdom, dan interaksi komponen biotik-abiotik.',
                    'content' => "BAB 1: KLASIFIKASI MAKHLUK HIDUP\n\n1. Ciri-Ciri Makhluk Hidup:\n- Bernapas (respirasi)\n- Memerlukan nutrisi\n- Bergerak dan peka terhadap rangsang (iritabilitas)\n- Tumbuh dan berkembang\n\n2. Urutan Takson:\nKingdom - Filum/Divisi - Kelas - Ordo - Famili - Genus - Spesies.\n\n3. Komponen Ekosistem:\n- Komponen Biotik: Produsen, Konsumen, Dekomposer.\n- Komponen Abiotik: Cahaya matahari, air, tanah, udara, suhu.",
                    'is_active' => true,
                    'created_by' => $authorId,
                ]
            );

            $bank7 = QuestionBank::firstOrCreate(
                ['title' => 'Evaluasi Klasifikasi Makhluk Hidup & Ekosistem - Kelas 7'],
                [
                    'subject_id' => $ipa->id,
                    'grade_level' => 7,
                    'description' => 'Evaluasi pemahaman ciri-ciri makhluk hidup, urutan taksonomi, dan interaksi dalam ekosistem untuk siswa angkatan Kelas VII.',
                    'duration_minutes' => 30,
                    'passing_grade' => 75,
                    'access_code' => 'IPA7-2026',
                    'is_active' => true,
                    'created_by' => $authorId,
                ]
            );

            $q7_1 = Question::firstOrCreate(
                ['bank_id' => $bank7->id, 'question_text' => 'Kemampuan makhluk hidup untuk menanggapi rangsangan dari luar disebut...'],
                ['explanation' => 'Iritabilitas adalah kemampuan makhluk hidup untuk peka dan menanggapi rangsang dari lingkungannya.', 'points' => 50, 'order_index' => 1]
            );
            QuestionOption::firstOrCreate(['question_id' => $q7_1->id, 'option_text' => 'Adaptasi', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q7_1->id, 'option_text' => 'Iritabilitas', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q7_1->id, 'option_text' => 'Respirasi', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q7_1->id, 'option_text' => 'Ekskresi', 'is_correct' => false]);

            $q7_2 = Question::firstOrCreate(
                ['bank_id' => $bank7->id, 'question_text' => 'Organisme yang berperan menguraikan sisa-sisa makhluk hidup yang telah mati menjadi zat anorganik disebut...'],
                ['explanation' => 'Dekomposer (pengurai) seperti bakteri dan jamur menguraikan bahan organik menjadi zat anorganik yang menyuburkan tanah.', 'points' => 50, 'order_index' => 2]
            );
            QuestionOption::firstOrCreate(['question_id' => $q7_2->id, 'option_text' => 'Produsen', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q7_2->id, 'option_text' => 'Konsumen Puncak', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q7_2->id, 'option_text' => 'Dekomposer', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q7_2->id, 'option_text' => 'Herbivora', 'is_correct' => false]);
        }

        // 8b. Paket Soal Angkatan Kelas 8 (Matematika)
        if ($matematika) {
            \App\Models\LearningMaterial::firstOrCreate(
                ['slug' => 'modul-aljabar-dan-teorema-pythagoras-kelas-8'],
                [
                    'subject_id' => $matematika->id,
                    'grade_level' => 8,
                    'title' => 'Modul Bentuk Aljabar & Teorema Pythagoras - Kelas 8',
                    'summary' => 'Ringkasan rumus operasi bentuk aljabar linier serta penerapan Teorema Pythagoras pada bangun datar.',
                    'content' => "RINGKASAN MATERI MATEMATIKA KELAS VIII\n\n1. Operasi Bentuk Aljabar:\n- Sifat Distributif: a(b + c) = ab + ac\n- Penjumlahan dan pengurangan hanya dapat dilakukan pada suku-suku sejenis.\n\n2. Teorema Pythagoras:\nPada segitiga siku-siku dengan sisi siku-siku a dan b serta sisi miring (hipotenusa) c, berlaku:\nc² = a² + b²\n\nContoh Tripel Pythagoras: (3, 4, 5), (6, 8, 10), (5, 12, 13), (8, 15, 17).",
                    'is_active' => true,
                    'created_by' => $authorId,
                ]
            );

            $bank = QuestionBank::firstOrCreate(
                ['title' => 'Latihan Soal Aljabar & Teorema Pythagoras - Kelas 8'],
                [
                    'subject_id' => $matematika->id,
                    'grade_level' => 8,
                    'description' => 'Simulasi asesmen mandiri materi operasi bentuk aljabar dan penerapan Teorema Pythagoras untuk angkatan Kelas VIII.',
                    'duration_minutes' => 45,
                    'passing_grade' => 75,
                    'access_code' => 'MTK8-2026',
                    'is_active' => true,
                    'created_by' => $authorId,
                ]
            );

            $q1 = Question::firstOrCreate(
                ['bank_id' => $bank->id, 'question_text' => 'Panjang hipotenusa segitiga siku-siku dengan panjang sisi tegak 6 cm dan 8 cm adalah...'],
                ['explanation' => 'Gunakan Teorema Pythagoras: c = √(6² + 8²) = √(36 + 64) = √100 = 10 cm.', 'points' => 50, 'order_index' => 1]
            );

            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '9 cm', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '10 cm', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '12 cm', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '14 cm', 'is_correct' => false]);

            $q2 = Question::firstOrCreate(
                ['bank_id' => $bank->id, 'question_text' => 'Sederhanakan bentuk aljabar berikut: 3(2x - 4) + 5x.'],
                ['explanation' => 'Distribusikan 3 ke dalam kurung: 6x - 12 + 5x = (6x + 5x) - 12 = 11x - 12.', 'points' => 50, 'order_index' => 2]
            );

            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '11x - 4', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '11x - 12', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '6x - 12', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '10x - 12', 'is_correct' => false]);
        }

        // 8c. Paket Soal Angkatan Kelas 9 (Bahasa Indonesia)
        if ($bindo) {
            \App\Models\LearningMaterial::firstOrCreate(
                ['slug' => 'modul-teks-laporan-percobaan-dan-pidato-persuasif-kelas-9'],
                [
                    'subject_id' => $bindo->id,
                    'grade_level' => 9,
                    'title' => 'Modul Teks Laporan Percobaan & Pidato Persuasif - Kelas 9',
                    'summary' => 'Struktur kebahasaan teks laporan percobaan ilmiah serta teknik menyusun pidato persuasif yang efektif.',
                    'content' => "MATERI BAHASA INDONESIA KELAS IX\n\n1. Struktur Teks Laporan Percobaan:\n- Tujuan percobaan\n- Alat dan bahan\n- Langkah-langkah kerja\n- Hasil percobaan\n- Simpulan\n\n2. Struktur Pidato Persuasif:\n- Pembuka (Salam, sapaan, ucapan syukur)\n- Isi Pidato (Pernyataan posisi, rangkaian argumen logis, penguatan pernyataan)\n- Penutup (Simpulan, ajakan/harapan, permohonan maaf, salam penutup).",
                    'is_active' => true,
                    'created_by' => $authorId,
                ]
            );

            $bank9 = QuestionBank::firstOrCreate(
                ['title' => 'Asesmen Teks Laporan Percobaan & Pidato Persuasif - Kelas 9'],
                [
                    'subject_id' => $bindo->id,
                    'grade_level' => 9,
                    'description' => 'Latihan persiapan ujian akhir mengenai struktur teks laporan percobaan dan kaidah kebahasaan pidato persuasif untuk angkatan Kelas IX.',
                    'duration_minutes' => 40,
                    'passing_grade' => 75,
                    'access_code' => 'BINDO9-2026',
                    'is_active' => true,
                    'created_by' => $authorId,
                ]
            );

            $q9_1 = Question::firstOrCreate(
                ['bank_id' => $bank9->id, 'question_text' => 'Bagian dalam pidato persuasif yang berisi rangkuman argumen logis disertai bukti untuk meyakinkan pendengar adalah...'],
                ['explanation' => 'Tahap argumen dalam isi pidato persuasif berfungsi menyajikan alasan logis dan data pendukung agar pendengar yakin terhadap gagasan pembicara.', 'points' => 50, 'order_index' => 1]
            );
            QuestionOption::firstOrCreate(['question_id' => $q9_1->id, 'option_text' => 'Salam Pembuka', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q9_1->id, 'option_text' => 'Rangkaian Argumen', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q9_1->id, 'option_text' => 'Sapaan Kehormatan', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q9_1->id, 'option_text' => 'Ucapan Terima Kasih', 'is_correct' => false]);

            $q9_2 = Question::firstOrCreate(
                ['bank_id' => $bank9->id, 'question_text' => 'Urutan struktur teks laporan hasil percobaan yang tepat adalah...'],
                ['explanation' => 'Struktur baku laporan percobaan dimulai dari Tujuan, Alat dan Bahan, Langkah-langkah, Hasil Percobaan, lalu ditutup dengan Simpulan.', 'points' => 50, 'order_index' => 2]
            );
            QuestionOption::firstOrCreate(['question_id' => $q9_2->id, 'option_text' => 'Tujuan - Alat & Bahan - Langkah Kerja - Hasil - Simpulan', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q9_2->id, 'option_text' => 'Simpulan - Tujuan - Langkah Kerja - Alat & Bahan - Hasil', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q9_2->id, 'option_text' => 'Orientasi - Komplikasi - Resolusi - Koda', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q9_2->id, 'option_text' => 'Tesis - Argumentasi - Penegasan Ulang', 'is_correct' => false]);
        }
    }
}
