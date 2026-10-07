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

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-5">
            <x-card class="p-5 bg-white flex flex-col justify-between hover:border-brand transition-all shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider">Total Siswa</span>
                    <div class="w-8 h-8 rounded-lg bg-sky-soft text-brand flex items-center justify-center">
                        <x-app-icon name="users" class="w-4 h-4" />
                    </div>
                </div>
                <div>
                    <strong class="text-3xl sm:text-4xl font-black text-brand leading-none block">{{ $grandTotal }}</strong>
                    <span class="text-xs text-ink-soft font-semibold block mt-1.5">Peserta Didik Aktif</span>
                </div>
            </x-card>

            <x-card class="p-5 bg-white flex flex-col justify-between hover:border-brand transition-all shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider">Laki-Laki</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                        <x-app-icon name="user" class="w-4 h-4" />
                    </div>
                </div>
                <div>
                    <strong class="text-3xl sm:text-4xl font-black text-navy leading-none block">{{ $totalMale }}</strong>
                    <span class="text-xs text-ink-soft font-semibold block mt-1.5">Siswa Putra</span>
                </div>
            </x-card>

            <x-card class="p-5 bg-white flex flex-col justify-between hover:border-brand transition-all shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider">Perempuan</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <x-app-icon name="user" class="w-4 h-4" />
                    </div>
                </div>
                <div>
                    <strong class="text-3xl sm:text-4xl font-black text-navy leading-none block">{{ $totalFemale }}</strong>
                    <span class="text-xs text-ink-soft font-semibold block mt-1.5">Siswa Putri</span>
                </div>
            </x-card>

            <x-card class="p-5 bg-white flex flex-col justify-between hover:border-brand transition-all shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider">Rombel</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                        <x-app-icon name="building" class="w-4 h-4" />
                    </div>
                </div>
                <div>
                    <strong class="text-3xl sm:text-4xl font-black text-navy leading-none block">{{ $classCount }}</strong>
                    <span class="text-xs text-ink-soft font-semibold block mt-1.5">Kelas VII, VIII, IX</span>
                </div>
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
