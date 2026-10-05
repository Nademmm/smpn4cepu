<x-layouts.app title="Statistik Rombongan Belajar">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Kesiswaan</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Statistik Rombongan Belajar (Rombel)</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Rekapitulasi resmi data siswa, rasio gender per kelas, dan rombongan belajar SMP Negeri 4 Cepu per tahun ajaran.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Tabel Rekapitulasi Rombel</h2>
                    <p class="text-xs text-slate-500">Tahun Ajaran 2025/2026</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400">Total Rombel:</span>
                    <span class="ml-2 font-mono font-bold text-slate-900 dark:text-white">{{ $stats->count() }} Kelas</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Tahun Ajaran</th>
                            <th class="py-3 px-4">Tingkat</th>
                            <th class="py-3 px-4">Nama Kelas</th>
                            <th class="py-3 px-4 text-center">Laki-Laki</th>
                            <th class="py-3 px-4 text-center">Perempuan</th>
                            <th class="py-3 px-4 text-right">Total Siswa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($stats as $s)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-400">{{ $s->academic_year }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">Kelas {{ $s->grade_level }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">{{ $s->class_name }}</td>
                                <td class="py-3 px-4 text-center text-slate-700 dark:text-slate-300">{{ $s->male_count }}</td>
                                <td class="py-3 px-4 text-center text-slate-700 dark:text-slate-300">{{ $s->female_count }}</td>
                                <td class="py-3 px-4 text-right font-mono font-black text-slate-900 dark:text-white">{{ $s->total_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 dark:border-slate-700 font-bold bg-slate-50 dark:bg-slate-800/40">
                            <td colspan="3" class="py-3 px-4 text-slate-900 dark:text-white">Total Keseluruhan Siswa</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $stats->sum('male_count') }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ $stats->sum('female_count') }}</td>
                            <td class="py-3 px-4 text-right font-mono text-blue-600 dark:text-blue-400 font-black text-base">{{ $stats->sum('total_count') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
