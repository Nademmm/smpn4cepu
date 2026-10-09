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

        // 2. Statistik Siswa & Rombel Resmi
        $rombelData = [
            ['academic_year' => '2025/2026', 'grade_level' => 7, 'class_name' => '7A', 'male_count' => 16, 'female_count' => 16, 'total_count' => 32],
            ['academic_year' => '2025/2026', 'grade_level' => 7, 'class_name' => '7B', 'male_count' => 15, 'female_count' => 17, 'total_count' => 32],
            ['academic_year' => '2025/2026', 'grade_level' => 8, 'class_name' => '8A', 'male_count' => 14, 'female_count' => 18, 'total_count' => 32],
            ['academic_year' => '2025/2026', 'grade_level' => 8, 'class_name' => '8B', 'male_count' => 16, 'female_count' => 16, 'total_count' => 32],
            ['academic_year' => '2025/2026', 'grade_level' => 9, 'class_name' => '9A', 'male_count' => 15, 'female_count' => 17, 'total_count' => 32],
            ['academic_year' => '2025/2026', 'grade_level' => 9, 'class_name' => '9B', 'male_count' => 14, 'female_count' => 18, 'total_count' => 32],
        ];

        foreach ($rombelData as $r) {
            StudentStatistic::firstOrCreate(
                ['academic_year' => $r['academic_year'], 'class_name' => $r['class_name']],
                $r
            );
        }

        // 3. Staf Pengajar & Tenaga Kependidikan Resmi (Data Primer Sekolah Beserta Foto Riil)
        $staffData = [
            [
                'nip' => '197508121999031004',
                'name' => 'Drs. H. Hartono, M.Pd.',
                'position' => 'Kepala Sekolah',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 1,
            ],
            [
                'nip' => '196807181994121001',
                'name' => 'Prasetyo Cahyo Nugroho, S.Pd., M.M.',
                'position' => 'Guru Madya Tk. I / Kurikulum',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
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
                'nip' => '197207072005012016',
                'name' => 'Ellies Erina, S.S.',
                'position' => 'Guru Madya',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 5,
            ],
            [
                'nip' => '197911142005012025',
                'name' => 'HUSNUL KHOTIMAH, S.S.',
                'position' => 'Guru Madya',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/husnul-khotimah.jpeg',
                'display_order' => 6,
            ],
            [
                'nip' => '196902151998012001',
                'name' => 'Dra. Setyorini',
                'position' => 'Guru Muda',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => 'staff/setyorini.jpeg',
                'display_order' => 7,
            ],
            [
                'nip' => '196906302008011005',
                'name' => 'Muhamad Sjafei, S.Pd.',
                'position' => 'Guru Madya',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 8,
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
                'position' => 'Guru Pertama',
                'employment_status' => EmploymentStatus::PNS,
                'photo_path' => null,
                'display_order' => 19,
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
            StaffMember::updateOrCreate(
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
            ['name' => 'Lapangan Olahraga', 'description' => 'Lapangan multifungsi untuk bola voli, basket, dan futsal.', 'photo_path' => null],
            ['name' => 'Gedung Indoor / Aula Serbaguna', 'description' => 'Gedung pertemuan siswa, latihan bulu tangkis, dan pentas seni.', 'photo_path' => 'images/assets/IMG_6522.JPG'],
            ['name' => 'Lapangan Upacara', 'description' => 'Area luas dan asri untuk upacara bendera serta apel kedisiplinan.', 'photo_path' => 'images/assets/IMG_6518.JPG'],
            ['name' => 'Mushala Sekolah', 'description' => 'Sarana ibadah sholat dhuhur berjamaah dan Baca Tulis Alquran.', 'photo_path' => null],
            ['name' => 'Ruang Guru', 'description' => 'Ruang kerja kolaboratif pendidik yang representatif.', 'photo_path' => null],
            ['name' => 'Ruang Tata Usaha', 'description' => 'Pusat pelayanan administrasi dan kearsipan persuratan sekolah.', 'photo_path' => null],
            ['name' => 'Ruang Kesenian', 'description' => 'Studio berekspresi seni tari, kriya, dan musik gamelan.', 'photo_path' => null],
        ];

        foreach ($facilities as $idx => $fac) {
            SchoolFacility::updateOrCreate(
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
            Post::updateOrCreate(
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

            Post::updateOrCreate(
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

            Post::updateOrCreate(
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

        // 8. Bank Soal & Soal Latihan Demo
        $matematika = Subject::where('code', 'MAT-SMP')->first();

        if ($matematika) {
            $bank = QuestionBank::firstOrCreate(
                ['title' => 'Latihan Soal Aljabar & Teorema Pythagoras - Kelas 8'],
                [
                    'subject_id' => $matematika->id,
                    'grade_level' => 8,
                    'duration_minutes' => 45,
                    'passing_grade' => 75,
                    'is_active' => true,
                    'created_by' => $guru ? $guru->id : ($admin ? $admin->id : 1),
                ]
            );

            // Pertanyaan 1
            $q1 = Question::firstOrCreate(
                ['bank_id' => $bank->id, 'question_text' => 'Panjang hipotenusa segitiga siku-siku dengan panjang sisi tegak 6 cm dan 8 cm adalah...'],
                ['explanation' => 'Gunakan Teorema Pythagoras: c = √(6² + 8²) = √(36 + 64) = √100 = 10 cm.', 'points' => 50]
            );

            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '9 cm', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '10 cm', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '12 cm', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q1->id, 'option_text' => '14 cm', 'is_correct' => false]);

            // Pertanyaan 2
            $q2 = Question::firstOrCreate(
                ['bank_id' => $bank->id, 'question_text' => 'Sederhanakan bentuk aljabar berikut: 3(2x - 4) + 5x.'],
                ['explanation' => 'Distribusikan 3 ke dalam kurung: 6x - 12 + 5x = (6x + 5x) - 12 = 11x - 12.', 'points' => 50]
            );

            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '11x - 4', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '11x - 12', 'is_correct' => true]);
            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '6x - 12', 'is_correct' => false]);
            QuestionOption::firstOrCreate(['question_id' => $q2->id, 'option_text' => '10x - 12', 'is_correct' => false]);
        }
    }
}
