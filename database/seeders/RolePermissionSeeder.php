<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Roles Resmi
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $operatorRole = Role::firstOrCreate(['name' => 'operator_sekolah']);
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $waliKelasRole = Role::firstOrCreate(['name' => 'wali_kelas']);
        $stafPerpusRole = Role::firstOrCreate(['name' => 'staf_perpus']);
        $panitiaPilketosRole = Role::firstOrCreate(['name' => 'panitia_pilketos']);
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);

        // 2. Buat Shield Permissions untuk Semua Entity Filament & Widget secara langsung (bebas hang CLI)
        $entities = [
            'AcademicYear',
            'Achievement',
            'Book',
            'BookReservation',
            'ContactMessage',
            'ElectionCandidate',
            'Extracurricular',
            'GalleryPhoto',
            'LearningMaterial',
            'LibraryBook',
            'Post',
            'QuestionBank',
            'QuizAttempt',
            'Role',
            'SchoolDocument',
            'SchoolFacility',
            'SchoolSetting',
            'StaffMember',
            'StudentStatistic',
            'Subject',
            'User',
        ];

        $actions = [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'DeleteAny',
            'Restore',
            'RestoreAny',
            'ForceDelete',
            'ForceDeleteAny',
            'Replicate',
            'Reorder',
        ];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}:{$entity}"]);
            }
        }

        // Permissions untuk Widget Dashboard
        $widgetPermissions = [
            'View:PilketosLiveChartWidget',
            'View:SchoolOverviewWidget',
        ];

        foreach ($widgetPermissions as $widgetPermission) {
            Permission::firstOrCreate(['name' => $widgetPermission]);
        }

        // 3. Buat General/Legacy Permissions
        $generalPermissions = [
            'view_admin_panel',
            'manage_posts',
            'manage_staff',
            'manage_library',
            'manage_academics',
            'manage_pilketos',
        ];

        foreach ($generalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 4. Assign Permissions ke Roles Sesuai Peran & Tanggung Jawab

        // A. Super Admin: Seluruh Hak Akses Penuh
        $superAdminRole->syncPermissions(Permission::all());

        // B. Operator Sekolah: Mengelola siswa, rombel, tahun ajaran, profil guru & konten sekolah
        // Batasan: Tidak otomatis mengubah pengaturan sistem atau seluruh nilai
        $operatorPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%User%')
              ->orWhere('name', 'like', '%StudentStatistic%')
              ->orWhere('name', 'like', '%AcademicYear%')
              ->orWhere('name', 'like', '%StaffMember%')
              ->orWhere('name', 'like', '%Post%')
              ->orWhere('name', 'like', '%Achievement%')
              ->orWhere('name', 'like', '%SchoolDocument%')
              ->orWhere('name', 'like', '%SchoolFacility%')
              ->orWhere('name', 'like', '%Extracurricular%')
              ->orWhere('name', 'like', '%GalleryPhoto%')
              ->orWhere('name', 'like', '%ContactMessage%')
              ->orWhereIn('name', ['ViewAny:QuizAttempt', 'View:QuizAttempt', 'ViewAny:Subject', 'View:Subject'])
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->where(function ($q) {
            $q->where('name', 'not like', '%Role%')
              ->where('name', 'not like', '%SchoolSetting%')
              ->where('name', 'not like', '%Book%')
              ->where('name', 'not like', '%LibraryBook%')
              ->where('name', 'not like', '%BookReservation%')
              ->where('name', 'not like', '%ElectionCandidate%')
              ->where('name', 'not like', '%ElectionVote%');
        })->get();
        $operatorRole->syncPermissions($operatorPermissions);

        // C. Guru Mapel: Khusus kelola Materi Pembelajaran, Bank Soal, dan Rekap Nilai Kuis
        // Batasan: Tidak menghapus siswa atau mengelola perpustakaan tanpa tugas tambahan
        $guruPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%LearningMaterial%')
              ->orWhere('name', 'like', '%QuestionBank%')
              ->orWhereIn('name', [
                  'ViewAny:QuizAttempt', 'View:QuizAttempt',
              ])
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->get();
        $guruRole->syncPermissions($guruPermissions);

        // D. Wali Kelas: Melihat siswa dan rekap pembelajaran kelas binaannya
        // Batasan: Perubahan identitas utama siswa mengikuti izin khusus
        $waliKelasPermissions = Permission::where(function ($q) {
            $q->whereIn('name', [
                'ViewAny:User', 'View:User',
                'ViewAny:QuizAttempt', 'View:QuizAttempt',
                'ViewAny:StudentStatistic', 'View:StudentStatistic',
                'view_admin_panel',
                'View:SchoolOverviewWidget',
            ]);
        })->get();
        $waliKelasRole->syncPermissions($waliKelasPermissions);

        // E. Staf Perpustakaan: Khusus Buku & Katalog Perpustakaan serta Reservasi & Sirkulasi
        // Batasan: Tidak mengubah data akademik
        $stafPerpusPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%Book%')
              ->orWhere('name', 'like', '%LibraryBook%')
              ->orWhere('name', 'like', '%BookReservation%')
              ->orWhere('name', 'like', '%library%')
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->get();
        $stafPerpusRole->syncPermissions($stafPerpusPermissions);

        // F. Panitia Pilketos: Khusus Kandidat Pilketos & Live Chart Suara
        // Batasan: Tidak mengubah siswa, nilai, atau transaksi perpustakaan
        $panitiaPilketosPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%ElectionCandidate%')
              ->orWhere('name', 'like', '%pilketos%')
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:PilketosLiveChartWidget')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->get();
        $panitiaPilketosRole->syncPermissions($panitiaPilketosPermissions);

        // 5. Akun Default Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@smpn4cepu.sch.id'],
            [
                'name' => 'Administrator SMPN 4 Cepu',
                'password' => bcrypt('AdminCepu2026!'),
                'nip' => '198501012010011001',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles([$superAdminRole]);

        // 6. Akun Default Operator Sekolah
        $operator = User::firstOrCreate(
            ['email' => 'operator@smpn4cepu.sch.id'],
            [
                'name' => 'Operator Sekolah SMPN 4 Cepu',
                'password' => bcrypt('OperatorCepu2026!'),
                'nip' => '198804042012011004',
                'email_verified_at' => now(),
            ]
        );
        $operator->syncRoles([$operatorRole]);

        // 7. Akun Default Guru Mapel
        $guru = User::firstOrCreate(
            ['email' => 'guru@smpn4cepu.sch.id'],
            [
                'name' => 'Budi Santoso, S.Pd.',
                'password' => bcrypt('GuruCepu2026!'),
                'nip' => '199002022015021002',
                'email_verified_at' => now(),
            ]
        );
        $guru->syncRoles([$guruRole]);

        // 8. Akun Default Wali Kelas
        $waliKelas = User::firstOrCreate(
            ['email' => 'walikelas@smpn4cepu.sch.id'],
            [
                'name' => 'Sri Wahyuni, S.Pd. (Wali Kelas 7A)',
                'password' => bcrypt('WaliKelas2026!'),
                'nip' => '198903032014022003',
                'email_verified_at' => now(),
            ]
        );
        $waliKelas->syncRoles([$waliKelasRole]);

        // 9. Akun Default Staf Perpus
        $perpus = User::firstOrCreate(
            ['email' => 'perpus@smpn4cepu.sch.id'],
            [
                'name' => 'Dewi Lestari, A.Md.Pust.',
                'password' => bcrypt('PerpusCepu2026!'),
                'nip' => '199405052020012003',
                'email_verified_at' => now(),
            ]
        );
        $perpus->syncRoles([$stafPerpusRole]);

        // 10. Akun Default Panitia Pilketos
        $pilketos = User::firstOrCreate(
            ['email' => 'pilketos@smpn4cepu.sch.id'],
            [
                'name' => 'Panitia Pilketos 2026',
                'password' => bcrypt('PilketosCepu2026!'),
                'nip' => '199203032018011005',
                'email_verified_at' => now(),
            ]
        );
        $pilketos->syncRoles([$panitiaPilketosRole]);
    }
}
