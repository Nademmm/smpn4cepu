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

        // 1. Buat Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $stafPerpusRole = Role::firstOrCreate(['name' => 'staf_perpus']);
        $panitiaPilketosRole = Role::firstOrCreate(['name' => 'panitia_pilketos']);

        // 2. Buat Default Permissions
        $permissions = [
            'view_admin_panel',
            'manage_posts',
            'manage_staff',
            'manage_library',
            'manage_academics',
            'manage_pilketos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Assign Permissions ke Roles
        $superAdminRole->syncPermissions(Permission::all());

        // Guru: Posts, Learning Materials, Question Banks, Subjects
        $guruPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', '%post%')
              ->orWhere('name', 'like', '%learning_material%')
              ->orWhere('name', 'like', '%question_bank%')
              ->orWhere('name', 'like', '%subject%');
        })->get();
        $guruRole->syncPermissions($guruPermissions);

        // Staf Perpus: Library Books
        $perpusPermissions = Permission::where('name', 'like', '%library_book%')->get();
        $stafPerpusRole->syncPermissions($perpusPermissions);

        // Panitia Pilketos: Election Candidates
        $pilketosPermissions = Permission::where('name', 'like', '%election_candidate%')->get();
        $panitiaPilketosRole->syncPermissions($pilketosPermissions);

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
