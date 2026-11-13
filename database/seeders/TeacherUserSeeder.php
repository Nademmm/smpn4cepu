<?php

namespace Database\Seeders;

use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TeacherUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role resmi tersedia
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $stafPerpusRole = Role::firstOrCreate(['name' => 'staf_perpus']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        $staffList = StaffMember::orderBy('display_order')->get();

        foreach ($staffList as $s) {
            // Generate email unik yang rapi dan konsisten
            $cleanName = preg_replace('/,.*$/', '', $s->name); // Hapus gelar belakang
            $cleanName = preg_replace('/^(Dra\.|Drs\.|Dr\.)\s*/i', '', $cleanName); // Hapus gelar depan
            $cleanName = trim(preg_replace('/\s+S\.Pd$/i', '', $cleanName));
            $slug = Str::slug($cleanName, '.');
            
            $email = "{$slug}@guru.smpn4cepu.sch.id";

            // Bersihkan format NIP (hanya digit angka)
            $cleanNip = preg_replace('/[^0-9]/', '', (string)$s->nip);
            if (empty($cleanNip)) {
                $cleanNip = null;
            }

            // Cari akun user (jangan menimpa admin sistem)
            $user = User::where('email', $email)->first();

            if (!$user && $cleanNip && !in_array($cleanNip, ['198501012010011001', '199002022015021002', '199405052020012003', '199203032018011005'])) {
                $user = User::where('nip', $cleanNip)->where('email', '!=', 'admin@smpn4cepu.sch.id')->first();
            }

            if (!$user) {
                $user = User::where('name', $s->name)->where('email', '!=', 'admin@smpn4cepu.sch.id')->first();
            }

            $password = Hash::make('GuruCepu2026!');

            if (!$user) {
                $user = User::create([
                    'name' => $s->name,
                    'email' => $email,
                    'nip' => $cleanNip,
                    'password' => $password,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'name' => $s->name,
                    'email' => $email,
                    'nip' => $cleanNip ?? $user->nip,
                    'password' => $password,
                ]);
            }

            // Tentukan Roles sesuai jabatan di sekolah
            $roles = [];
            $pos = strtolower((string) $s->position);
            if (str_contains($pos, 'kepala sekolah')) {
                $roles[] = 'super_admin';
            }
            if (str_contains($pos, 'guru')) {
                $roles[] = 'guru';
            }
            if (str_contains($pos, 'perpustakaan') || str_contains($pos, 'pustakawan')) {
                $roles[] = 'staf_perpus';
            }

            $user->syncRoles(array_values(array_unique($roles)));
        }
    }
}

