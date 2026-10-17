<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StudentUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan role 'siswa' tersedia
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);

        // 2. Buat akun siswa per angkatan (Kelas 7, Kelas 8, Kelas 9) untuk uji coba akses kuis & materi
        $students = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@siswa.smpn4cepu.sch.id',
                'nisn' => '0081234567',
                'student_class' => 'Kelas 7',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@siswa.smpn4cepu.sch.id',
                'nisn' => '0087654321',
                'student_class' => 'Kelas 8',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Rian Pratama',
                'email' => 'rian.pratama@siswa.smpn4cepu.sch.id',
                'nisn' => '0091122334',
                'student_class' => 'Kelas 9',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@siswa.smpn4cepu.sch.id',
                'nisn' => '0099887766',
                'student_class' => 'Kelas 7',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($students as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
            $user->syncRoles([$siswaRole]);
        }
    }
}
