<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Models\StaffMember;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeacherSubjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'staf_perpus']);
        Role::firstOrCreate(['name' => 'super_admin']);

        $subjectsMeta = [
            'IPA-SMP' => 'Ilmu Pengetahuan Alam (IPA)',
            'ING-SMP' => 'Bahasa Inggris',
            'SENI-SMP' => 'Seni dan Prakarya',
            'IND-SMP' => 'Bahasa Indonesia',
            'MAT-SMP' => 'Matematika',
            'PPKN-SMP' => 'Pendidikan Pancasila',
            'PAI-SMP' => 'Pendidikan Agama Islam',
            'IPS-SMP' => 'Ilmu Pengetahuan Sosial (IPS)',
            'JAWA-SMP' => 'Bahasa Jawa',
            'BK-SMP' => 'Bimbingan Konseling (BK)',
            'INF-SMP' => 'Informatika',
            'PJOK-SMP' => 'Pendidikan Jasmani (PJOK)',
        ];

        $subjectsByCode = [];
        foreach ($subjectsMeta as $code => $name) {
            $subjectsByCode[$code] = Subject::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }

        // Non-teaching staff must not hold the 'guru' role or stray subject assignments.
        $nonTeachingStaffRules = [
            'yaatun@guru.smpn4cepu.sch.id' => ['staf_perpus'],
            'prasetyo.cahyo.nugroho@guru.smpn4cepu.sch.id' => ['super_admin'],
            'dyah.dirta.sukmasari@guru.smpn4cepu.sch.id' => [],
            'ninik.yuliastutik@guru.smpn4cepu.sch.id' => [],
            'hermayanti.sri.p@guru.smpn4cepu.sch.id' => [],
            'rakip@guru.smpn4cepu.sch.id' => [],
            'sukir.anto@guru.smpn4cepu.sch.id' => [],
        ];

        foreach ($nonTeachingStaffRules as $staffEmail => $expectedRoles) {
            $staffUser = User::where('email', $staffEmail)->first();
            if ($staffUser) {
                $staffUser->syncRoles($expectedRoles);
                $staffUser->subjects()->detach();
            }
        }

        $assignments = $this->teacherAssignments();
        $maxOrder = (int) (StaffMember::max('display_order') ?? 0);

        foreach ($assignments as $entry) {
            $user = User::whereIn('email', array_merge([$entry['email']], $entry['email_aliases'] ?? []))->first();

            if (! $user && ! empty($entry['name_aliases'])) {
                $user = User::whereIn('name', $entry['name_aliases'])->first();
            }

            if (! $user) {
                $user = User::create([
                    'name' => $entry['name'],
                    'email' => $entry['email'],
                    'password' => Hash::make('GuruCepu2026!'),
                    'email_verified_at' => now(),
                ]);
            }

            if (! empty($entry['create_staff_if_missing'])) {
                $staffExists = StaffMember::where('name', $user->name)
                    ->orWhereIn('name', $entry['name_aliases'] ?? [$entry['name']])
                    ->exists();

                if (! $staffExists) {
                    $maxOrder++;
                    StaffMember::create([
                        'name' => $entry['name'],
                        'position' => 'Guru Mata Pelajaran',
                        'employment_status' => EmploymentStatus::PPPK,
                        'display_order' => $maxOrder,
                        'is_active' => true,
                    ]);
                }
            }

            if (! empty($entry['extra_roles'])) {
                $user->syncRoles(array_values(array_unique(array_merge(['guru'], $entry['extra_roles']))));
            } elseif (! $user->hasRole('guru')) {
                $user->assignRole('guru');
            }

            $syncData = [];
            foreach ($entry['subjects'] as $subjectCode => $scope) {
                $subject = $subjectsByCode[$subjectCode] ?? null;
                if (! $subject) {
                    continue;
                }

                $syncData[$subject->id] = [
                    'allowed_grades' => json_encode(array_values($scope['grades'])),
                    'allowed_classes' => json_encode(array_values($scope['classes'])),
                ];
            }

            $user->subjects()->sync($syncData);
        }
    }

    /**
     * Exact teacher-to-subject-to-grade/rombel permissions matching Permission Materi Guru.xlsx (Sheet1).
     *
     * @return list<array<string, mixed>>
     */
    private function teacherAssignments(): array
    {
        return [
            [
                'name' => 'Wahyudi, S.Pd.',
                'email' => 'wahyudi@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IPA-SMP' => ['grades' => [8], 'classes' => ['8A', '8B', '8C', '8D', '8E']],
                ],
            ],
            [
                'name' => 'Ellies Erina, S.S.',
                'email' => 'ellies.erina@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'ING-SMP' => ['grades' => [9], 'classes' => ['9D', '9E', '9F']],
                    'SENI-SMP' => ['grades' => [8], 'classes' => ['8C', '8D']],
                ],
            ],
            [
                'name' => 'Husnul Khotimah, S.S.',
                'email' => 'husnul.khotimah@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IND-SMP' => ['grades' => [7], 'classes' => ['7C', '7D', '7E']],
                ],
            ],
            [
                'name' => 'Dra. Setyorini',
                'email' => 'setyorini@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IND-SMP' => ['grades' => [7, 8], 'classes' => ['7F', '7G', '8A', '8B', '8C']],
                ],
            ],
            [
                'name' => 'Muhamad Sjafei, S.Pd.',
                'email' => 'muhamad.sjafei@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'MAT-SMP' => ['grades' => [9], 'classes' => ['9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Lucia Kristiyaningsih, S.Pd.',
                'email' => 'lucia.kristiyaningsih@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'PPKN-SMP' => ['grades' => [7, 8], 'classes' => ['7A', '7B', '7C', '8A', '8B', '8C', '8D', '8E', '8F', '8G']],
                ],
            ],
            [
                'name' => 'Said Mubarok, S.Pd.',
                'email' => 'said.mubarok@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IPA-SMP' => ['grades' => [8, 9], 'classes' => ['8F', '8G', '9A', '9B', '9C']],
                ],
            ],
            [
                'name' => 'Mukhammad Syaidin, M.Pd.I.',
                'email' => 'mukhammad.syaidin@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'PAI-SMP' => ['grades' => [7, 8, 9], 'classes' => ['7A', '7B', '7C', '8A', '8B', '8C', '9A', '9B', '9C']],
                ],
            ],
            [
                'name' => 'Sulistiyo, S.Pd., Gr.',
                'email' => 'sulistiyo@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'PPKN-SMP' => ['grades' => [7, 9], 'classes' => ['7D', '7E', '7F', '7G', '9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Indah Lestari, S.Pd.',
                'email' => 'indah.lestari@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IND-SMP' => ['grades' => [8, 9], 'classes' => ['8G', '9A', '9B', '9C', '9D']],
                ],
            ],
            [
                'name' => 'Kun Ambarsasi, S.Pd.',
                'email' => 'kun.ambarsasi@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'ING-SMP' => ['grades' => [7, 8], 'classes' => ['7D', '7E', '7F', '8A', '8B', '8C']],
                    'SENI-SMP' => ['grades' => [7], 'classes' => ['7A', '7B']],
                ],
            ],
            [
                'name' => 'Siti Masripah, S.Pd.',
                'email' => 'siti.masripah@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'ING-SMP' => ['grades' => [7, 9], 'classes' => ['7A', '7B', '7C', '9A', '9B', '9C']],
                    'SENI-SMP' => ['grades' => [7], 'classes' => ['7F', '7G']],
                ],
            ],
            [
                'name' => 'Nurbaiti, S.Pd.',
                'email' => 'nurbaiti@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'MAT-SMP' => ['grades' => [8], 'classes' => ['8A', '8B', '8C', '8D', '8E']],
                ],
            ],
            [
                'name' => 'Diana Puji Astuti, S.Pd.',
                'email' => 'diana.puji.astuti@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IPS-SMP' => ['grades' => [8], 'classes' => ['8B', '8C', '8D', '8E', '8F', '8G']],
                    'JAWA-SMP' => ['grades' => [7], 'classes' => ['7C', '7D', '7E']],
                ],
            ],
            [
                'name' => 'Nurhayati, S.E.',
                'email' => 'nurhayati@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IPS-SMP' => ['grades' => [9], 'classes' => ['9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Sampurno, S.Pd., Gr.',
                'email' => 'sampurno@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'BK-SMP' => ['grades' => [7, 8], 'classes' => ['7D', '7E', '7F', '7G', '8A', '8B', '8C', '8D', '8E', '8F', '8G']],
                    'INF-SMP' => ['grades' => [8], 'classes' => ['8A', '8B', '8C', '8D']],
                ],
            ],
            [
                'name' => 'Sri Pintaningtyastuti, S.Pd.',
                'email' => 'sri.pintaningtyastuti@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'BK-SMP' => ['grades' => [7, 9], 'classes' => ['7A', '7B', '7C', '9A', '9B', '9C', '9D', '9E', '9F']],
                    'SENI-SMP' => ['grades' => [8], 'classes' => ['8E', '8F', '8G']],
                ],
            ],
            [
                'name' => 'Tri Sri Kuncoro, S.Pd.',
                'email' => 'tri.sri.kuncoro@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'PJOK-SMP' => ['grades' => [8, 9], 'classes' => ['8D', '8E', '8F', '8G', '9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Mifta Yustiningtyas Fauziah, S.Pd., Gr.',
                'email' => 'mifta.yustiningtyas.fauziah@guru.smpn4cepu.sch.id',
                'email_aliases' => ['mifta.yustiningtyas.fauzia@guru.smpn4cepu.sch.id'],
                'subjects' => [
                    'IPA-SMP' => ['grades' => [7], 'classes' => ['7D', '7E', '7F', '7G']],
                ],
            ],
            [
                'name' => 'Laeli Hasanah, S.Pd.',
                'email' => 'laeli.hasanah@guru.smpn4cepu.sch.id',
                'extra_roles' => ['staf_perpus'],
                'subjects' => [
                    'ING-SMP' => ['grades' => [7, 8], 'classes' => ['7G', '8D', '8E', '8F', '8G']],
                ],
            ],
            [
                'name' => 'Freddy Ariestya Pradana, S.Pd.',
                'email' => 'freddy.ariestya.pradana@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'PJOK-SMP' => ['grades' => [7, 8], 'classes' => ['7A', '7B', '7C', '7D', '7E', '7F', '7G', '8A', '8B', '8C']],
                ],
            ],
            [
                'name' => 'Hardina Briliyani Gusman, S.Pd.',
                'email' => 'hardina.briliyani.gusman@guru.smpn4cepu.sch.id',
                'email_aliases' => ['hardina.brilyani.gusman@guru.smpn4cepu.sch.id'],
                'subjects' => [
                    'IPA-SMP' => ['grades' => [7, 9], 'classes' => ['7A', '7B', '9D', '9E', '9F']],
                    'INF-SMP' => ['grades' => [8], 'classes' => ['8E', '8F', '8G']],
                ],
            ],
            [
                'name' => 'Zahrotun Nihayah, S.Pd.',
                'email' => 'zahrotunnihayah@guru.smpn4cepu.sch.id',
                'email_aliases' => ['zahrotun.nihayah@guru.smpn4cepu.sch.id'],
                'subjects' => [
                    'SENI-SMP' => ['grades' => [7, 8, 9], 'classes' => ['7C', '7D', '7E', '8A', '8B', '9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Hetik Wulandari, S.Pd.',
                'email' => 'hetik.wulandari@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IPS-SMP' => ['grades' => [7, 8], 'classes' => ['7A', '7B', '7C', '7D', '7E', '7F', '7G', '8A']],
                ],
            ],
            [
                'name' => 'Brilliani Dyah Sekartaji, S.Pd.',
                'email' => 'brilliani.dyah.sekartaji@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'JAWA-SMP' => ['grades' => [7, 8, 9], 'classes' => ['7F', '7G', '8A', '8B', '8C', '8D', '8E', '8F', '8G', '9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Dhea Eristania Dewi, S.Pd',
                'email' => 'dhea.eristania.dewi@guru.smpn4cepu.sch.id',
                'subjects' => [
                    'IND-SMP' => ['grades' => [8, 9], 'classes' => ['8D', '8E', '8F', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Rachmawati, S.Pd.I.',
                'email' => 'rachmawati@guru.smpn4cepu.sch.id',
                'create_staff_if_missing' => true,
                'subjects' => [
                    'PAI-SMP' => ['grades' => [7, 8, 9], 'classes' => ['7D', '7E', '7F', '7G', '8D', '8E', '8F', '8G', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Dwi Yuliandika Saputra, S.Pd.',
                'email' => 'dwi.yuliandika.saputra@guru.smpn4cepu.sch.id',
                'create_staff_if_missing' => true,
                'subjects' => [
                    'INF-SMP' => ['grades' => [7, 9], 'classes' => ['7A', '7B', '7C', '7D', '7E', '9A', '9B', '9C', '9D', '9E', '9F']],
                ],
            ],
            [
                'name' => 'Mega Linda Kurniawati, S.Pd.',
                'email' => 'mega.linda.kurniawati@guru.smpn4cepu.sch.id',
                'create_staff_if_missing' => true,
                'subjects' => [
                    'IPA-SMP' => ['grades' => [7], 'classes' => ['7C']],
                    'MAT-SMP' => ['grades' => [7], 'classes' => ['7A', '7B', '7C', '7D', '7E']],
                ],
            ],
            [
                'name' => 'Elis Nurhayati, S.Pd.',
                'email' => 'elis.nurhayati@guru.smpn4cepu.sch.id',
                'create_staff_if_missing' => true,
                'subjects' => [
                    'MAT-SMP' => ['grades' => [7, 8], 'classes' => ['7F', '7G', '8F', '8G']],
                ],
            ],
            [
                'name' => 'Supriyanto Indra Kartiko',
                'email' => 'supriyanto.indra.kartiko@guru.smpn4cepu.sch.id',
                'email_aliases' => [
                    'supriyanto.indr.kartiko@guru.smpn4cepu.sch.id',
                    'suprianto.indra.kartiko@guru.smpn4cepu.sch.id',
                ],
                'name_aliases' => [
                    'Supriyanto Indra Kartiko',
                    'Supriyanto Indr Kartiko',
                    'Suprianto Indra Kartiko',
                ],
                'create_staff_if_missing' => true,
                'subjects' => [
                    'INF-SMP' => ['grades' => [7], 'classes' => ['7F', '7G']],
                    'IND-SMP' => ['grades' => [7], 'classes' => ['7A', '7B']],
                    'JAWA-SMP' => ['grades' => [7], 'classes' => ['7A', '7B']],
                ],
            ],
        ];
    }
}

