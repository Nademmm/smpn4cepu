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

        {{-- Statistik Resmi Guru & Tenaga Kependidikan (GTK) --}}
        <div class="space-y-6 pt-4">
            <div>
                <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-1">Data Pokok Pendidikan</span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-navy">Rekapitulasi Tenaga Pendidik & Kependidikan</h2>
                <p class="text-xs sm:text-sm text-ink-soft">Kondisi ketenagaan resmi SMP Negeri 4 Cepu per data pokok kelembagaan.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- 1. Tabel Guru / Tenaga Pendidik --}}
                <x-card class="p-0 overflow-hidden shadow-xs">
                    <div class="p-4 sm:p-5 bg-sky-soft border-b-2 border-sun-soft flex items-center justify-between">
                        <div>
                            <h3 class="font-extrabold text-navy text-base">Rekapitulasi Tenaga Pendidik (Guru)</h3>
                            <p class="text-xs text-ink-soft">Total 29 Pendidik Aktif</p>
                        </div>
                        <span class="text-xs font-black text-brand bg-white px-3 py-1 rounded-full border border-sun-soft">
                            29 Guru
                        </span>
                    </div>

                    <div class="p-5 space-y-4 text-xs sm:text-sm">
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="bg-cream/40 p-3 rounded-xl border border-sun-soft">
                                <span class="text-ink-mute text-[11px] font-bold block">Laki-Laki</span>
                                <strong class="text-xl font-black text-navy">8</strong>
                            </div>
                            <div class="bg-cream/40 p-3 rounded-xl border border-sun-soft">
                                <span class="text-ink-mute text-[11px] font-bold block">Perempuan</span>
                                <strong class="text-xl font-black text-navy">21</strong>
                            </div>
                            <div class="bg-sky-soft p-3 rounded-xl border border-brand/20">
                                <span class="text-brand text-[11px] font-black block">Jumlah Total</span>
                                <strong class="text-xl font-black text-brand">29</strong>
                            </div>
                        </div>

                        <div class="border-t border-sun-soft pt-3 space-y-2">
                            <span class="text-[11px] font-extrabold text-ink-mute uppercase tracking-wider block">Status Kepegawaian & Kualifikasi</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                                <div class="bg-white p-2.5 rounded-lg border border-sun-soft">
                                    <span class="text-ink-mute text-[11px] block">PNS:</span>
                                    <strong class="text-navy font-black text-sm">20 Orang</strong>
                                </div>
                                <div class="bg-white p-2.5 rounded-lg border border-sun-soft">
                                    <span class="text-ink-mute text-[11px] block">PPPK:</span>
                                    <strong class="text-navy font-black text-sm">8 Orang</strong>
                                </div>
                                <div class="bg-white p-2.5 rounded-lg border border-sun-soft">
                                    <span class="text-ink-mute text-[11px] block">PPPK PW:</span>
                                    <strong class="text-navy font-black text-sm">1 Orang</strong>
                                </div>
                                <div class="bg-white p-2.5 rounded-lg border border-sun-soft">
                                    <span class="text-ink-mute text-[11px] block">Pendidikan S1:</span>
                                    <strong class="text-navy font-black text-sm">28 Orang</strong>
                                </div>
                                <div class="bg-white p-2.5 rounded-lg border border-sun-soft">
                                    <span class="text-ink-mute text-[11px] block">Pendidikan S2:</span>
                                    <strong class="text-navy font-black text-sm">1 Orang</strong>
                                </div>
                                <div class="bg-emerald-50 p-2.5 rounded-lg border border-emerald-300 text-emerald-800">
                                    <span class="text-[11px] block">Kualifikasi:</span>
                                    <strong class="font-black text-xs">100% S1 / S2</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- 2. Tabel Tenaga Kependidikan (TU / Staf) --}}
                <x-card class="p-0 overflow-hidden shadow-xs">
                    <div class="p-4 sm:p-5 bg-amber-50 border-b-2 border-sun-soft flex items-center justify-between">
                        <div>
                            <h3 class="font-extrabold text-navy text-base">Rekapitulasi Tenaga Kependidikan</h3>
                            <p class="text-xs text-ink-soft">Total 9 Tenaga Administrasi & Perpustakaan</p>
                        </div>
                        <span class="text-xs font-black text-amber-800 bg-white px-3 py-1 rounded-full border border-sun-soft">
                            9 Tendik
                        </span>
                    </div>

                    <div class="p-5 space-y-4 text-xs sm:text-sm">
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="bg-cream/40 p-3 rounded-xl border border-sun-soft">
                                <span class="text-ink-mute text-[11px] font-bold block">Laki-Laki</span>
                                <strong class="text-xl font-black text-navy">5</strong>
                            </div>
                            <div class="bg-cream/40 p-3 rounded-xl border border-sun-soft">
                                <span class="text-ink-mute text-[11px] font-bold block">Perempuan</span>
                                <strong class="text-xl font-black text-navy">4</strong>
                            </div>
                            <div class="bg-amber-100/60 p-3 rounded-xl border border-amber-300">
                                <span class="text-amber-900 text-[11px] font-black block">Jumlah Total</span>
                                <strong class="text-xl font-black text-amber-900">9</strong>
                            </div>
                        </div>

                        <div class="border-t border-sun-soft pt-3 space-y-2">
                            <span class="text-[11px] font-extrabold text-ink-mute uppercase tracking-wider block">Sebaran Jenjang Pendidikan Terakhir</span>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="bg-white p-2 rounded-lg border border-sun-soft text-center">
                                    <span class="text-ink-mute text-[11px] block">SD:</span>
                                    <strong class="text-navy font-black">1</strong>
                                </div>
                                <div class="bg-white p-2 rounded-lg border border-sun-soft text-center">
                                    <span class="text-ink-mute text-[11px] block">SMP:</span>
                                    <strong class="text-navy font-black">1</strong>
                                </div>
                                <div class="bg-white p-2 rounded-lg border border-sun-soft text-center">
                                    <span class="text-ink-mute text-[11px] block">SMA:</span>
                                    <strong class="text-navy font-black">5</strong>
                                </div>
                                <div class="bg-white p-2 rounded-lg border border-sun-soft text-center">
                                    <span class="text-ink-mute text-[11px] block">Diploma 2:</span>
                                    <strong class="text-navy font-black">1</strong>
                                </div>
                                <div class="bg-white p-2 rounded-lg border border-sun-soft text-center">
                                    <span class="text-ink-mute text-[11px] block">Sarjana S1:</span>
                                    <strong class="text-navy font-black">1</strong>
                                </div>
                                <div class="bg-emerald-50 p-2 rounded-lg border border-emerald-300 text-emerald-800 text-center flex flex-col justify-center">
                                    <span class="text-[10px] block font-bold">Pustakawan:</span>
                                    <strong class="text-xs font-black">Tersedia</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
