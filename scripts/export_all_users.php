<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

User::where('email', 'siswa.test@smpn4cepu.sch.id')->delete();

$users = User::with('roles')->orderByRaw("CASE WHEN nisn IS NULL THEN 0 ELSE 1 END, student_class ASC, name ASC")->get();

$csvFile = __DIR__ . '/../storage/app/data_semua_akun_smpn4cepu.csv';
$fp = fopen($csvFile, 'w');

// UTF-8 BOM untuk Excel
fputs($fp, "\xEF\xBB\xBF");

// Header
fputcsv($fp, [
    'No',
    'Kategori Akun',
    'Peran (Role)',
    'Nama Lengkap',
    'Username / Email Login',
    'Login Alternatif (NISN / NIP)',
    'Password Default',
    'NISN',
    'NIS',
    'Kelas / Rombel',
    'L/P',
    'Tempat Lahir',
    'Tanggal Lahir',
    'Status Akun',
]);

$no = 1;
$stats = [
    'super_admin' => 0,
    'guru' => 0,
    'staf_perpus' => 0,
    'panitia_pilketos' => 0,
    'siswa' => 0,
    'lainnya' => 0,
];

foreach ($users as $u) {
    $roles = $u->roles->pluck('name')->toArray();
    $roleName = !empty($roles) ? implode(', ', $roles) : 'user';

    $kategori = 'Peserta Didik (Siswa)';
    if (in_array('super_admin', $roles, true)) {
        $kategori = 'Super Administrator';
        $stats['super_admin']++;
    } elseif (in_array('guru', $roles, true)) {
        $kategori = 'Dewan Guru / Tenaga Pendidik';
        $stats['guru']++;
    } elseif (in_array('staf_perpus', $roles, true)) {
        $kategori = 'Staf Perpustakaan';
        $stats['staf_perpus']++;
    } elseif (in_array('panitia_pilketos', $roles, true)) {
        $kategori = 'Panitia Pemilihan Ketua OSIS';
        $stats['panitia_pilketos']++;
    } elseif (in_array('siswa', $roles, true) || !empty($u->nisn)) {
        $kategori = 'Peserta Didik (Siswa)';
        $stats['siswa']++;
    } else {
        $kategori = 'Pengguna Khusus';
        $stats['lainnya']++;
    }

    $rawNisn = $u->nisn ? trim((string)$u->nisn) : null;
    $rawNip = $u->nip ? trim((string)$u->nip) : null;
    $rawNis = $u->nis ? trim((string)$u->nis) : null;

    $altLogin = $rawNisn ? '="' . $rawNisn . '"' : ($rawNip ? '="' . $rawNip . '"' : '-');
    $nisnFormatted = $rawNisn ? '="' . $rawNisn . '"' : '-';
    $nisFormatted = $rawNis ? '="' . $rawNis . '"' : '-';
    $password = 'password123';

    fputcsv($fp, [
        $no++,
        $kategori,
        $roleName,
        $u->name,
        $u->email,
        $altLogin,
        $password,
        $nisnFormatted,
        $nisFormatted,
        $u->student_class ?? '-',
        $u->gender ?? '-',
        $u->birth_place ?? '-',
        $u->birth_date ? $u->birth_date->format('d/m/Y') : '-',
        'Aktif',
    ]);
}

fclose($fp);

echo "BERHASIL DIBUAT: {$csvFile}\n";
echo "Total Akun: " . count($users) . "\n";
print_r($stats);
