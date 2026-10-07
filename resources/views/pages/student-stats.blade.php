<x-layouts.app title="Statistik Rombongan Belajar Siswa">
    <x-page-header
        title="Statistik Rombongan Belajar"
        subtitle="Rekapitulasi persebaran jumlah peserta didik dan rombongan belajar per tingkat kelas SMP Negeri 4 Cepu."
        crumb="Statistik Siswa"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8">
        {{-- Total Rangkuman Card --}}
        @php
            $totalMale = $stats->sum('male_count');
            $totalFemale = $stats->sum('female_count');
            $grandTotal = $totalMale + $totalFemale;
            $classCount = $stats->count();
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <x-card class="p-4 bg-sky-soft">
                <span class="text-xs font-bold text-ink-mute block">Total Peserta Didik</span>
                <strong class="text-2xl sm:text-3xl font-extrabold text-brand">{{ $grandTotal }}</strong>
                <span class="text-[11px] text-ink-mute block mt-0.5">Siswa Aktif</span>
            </x-card>

            <x-card class="p-4 bg-sky-soft">
                <span class="text-xs font-bold text-ink-mute block">Siswa Laki-Laki</span>
                <strong class="text-2xl sm:text-3xl font-extrabold text-navy">{{ $totalMale }}</strong>
                <span class="text-[11px] text-ink-mute block mt-0.5">Orang</span>
            </x-card>

            <x-card class="p-4 bg-sky-soft">
                <span class="text-xs font-bold text-ink-mute block">Siswa Perempuan</span>
                <strong class="text-2xl sm:text-3xl font-extrabold text-navy">{{ $totalFemale }}</strong>
                <span class="text-[11px] text-ink-mute block mt-0.5">Orang</span>
            </x-card>

            <x-card class="p-4 bg-sky-soft">
                <span class="text-xs font-bold text-ink-mute block">Jumlah Rombel</span>
                <strong class="text-2xl sm:text-3xl font-extrabold text-sun">{{ $classCount }}</strong>
                <span class="text-[11px] text-ink-mute block mt-0.5">Kelas 7, 8, 9</span>
            </x-card>
        </div>

        {{-- Tabel Rekapitulasi Rombel --}}
        <x-card class="p-0 overflow-hidden">
            <div class="p-5 bg-sky-soft border-b-2 border-sun-soft flex items-center justify-between">
                <div>
                    <h2 class="font-extrabold text-navy text-base">Rincian Per Rombongan Belajar</h2>
                    <p class="text-xs text-ink-soft">Data persebaran peserta didik berdasarkan tahun ajaran aktif.</p>
                </div>
                <x-badge type="blue">Tahun Ajaran 2026/2027</x-badge>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-cream/60 border-b-2 border-sun-soft text-navy font-extrabold uppercase text-[11px]">
                        <tr>
                            <th class="p-4">Tingkat</th>
                            <th class="p-4">Nama Kelas</th>
                            <th class="p-4 text-center">Laki-Laki</th>
                            <th class="p-4 text-center">Perempuan</th>
                            <th class="p-4 text-center">Total Siswa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sun-soft font-semibold text-navy">
                        @forelse ($stats as $row)
                            <tr class="hover:bg-sky-soft/40 transition-colors">
                                <td class="p-4">Kelas {{ $row->grade_level }}</td>
                                <td class="p-4 font-extrabold text-brand">{{ $row->class_name }}</td>
                                <td class="p-4 text-center">{{ $row->male_count }}</td>
                                <td class="p-4 text-center">{{ $row->female_count }}</td>
                                <td class="p-4 text-center font-extrabold text-navy">
                                    {{ $row->male_count + $row->female_count }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-ink-soft">
                                    Data rekapitulasi rombongan belajar sedang diperbarui.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
