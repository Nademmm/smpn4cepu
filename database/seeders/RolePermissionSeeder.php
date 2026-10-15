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
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $stafPerpusRole = Role::firstOrCreate(['name' => 'staf_perpus']);
        $panitiaPilketosRole = Role::firstOrCreate(['name' => 'panitia_pilketos']);

        // 2. Buat Shield Permissions untuk Semua Entity Filament & Widget secara langsung (bebas hang CLI)
        $entities = [
            'Achievement',
            'BookReservation',
            'ContactMessage',
            'ElectionCandidate',
            'Extracurricular',
            'GalleryPhoto',
            'LearningMaterial',
            'LibraryBook',
            'Post',
            'QuestionBank',
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

        // B. Guru: Materi Pembelajaran, Bank Soal/Kuis, Mata Pelajaran, Berita & Artikel
        $guruPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%Post%')
              ->orWhere('name', 'like', '%LearningMaterial%')
              ->orWhere('name', 'like', '%QuestionBank%')
              ->orWhere('name', 'like', '%Subject%')
              ->orWhere('name', 'like', '%post%')
              ->orWhere('name', 'like', '%academic%')
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->get();
        $guruRole->syncPermissions($guruPermissions);

        // C. Staf Perpustakaan: Khusus Buku & Katalog Perpustakaan serta Reservasi
        $stafPerpusPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%LibraryBook%')
              ->orWhere('name', 'like', '%BookReservation%')
              ->orWhere('name', 'like', '%library%')
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->get();
        $stafPerpusRole->syncPermissions($stafPerpusPermissions);

        // D. Panitia Pilketos: Khusus Kandidat Pilketos & Live Chart Suara
        $panitiaPilketosPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%ElectionCandidate%')
              ->orWhere('name', 'like', '%pilketos%')
              ->orWhere('name', 'view_admin_panel')
              ->orWhere('name', 'View:PilketosLiveChartWidget')
              ->orWhere('name', 'View:SchoolOverviewWidget');
        })->get();
        $panitiaPilketosRole->syncPermissions($panitiaPilketosPermissions);

        // 4. Akun Default Super Admin
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

        // 5. Akun Default Guru
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

        // 6. Akun Default Staf Perpus
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

        // 7. Akun Default Panitia Pilketos
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
