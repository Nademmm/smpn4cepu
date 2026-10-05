<x-layouts.app title="Latihan Mandiri & Bank Soal">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Asesmen Pembelajaran</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Bank Soal & Latihan Mandiri</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Uji kemampuan materi pelajaran secara mandiri. Dilengkapi durasi hitung mundur, evaluasi nilai instan, dan pembahasan soal.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($questionBanks as $bank)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                {{ $bank->subject->name ?? 'Mata Pelajaran' }}
                            </span>
                            <span class="text-xs font-bold text-slate-500">Kelas {{ $bank->grade_level }}</span>
                        </div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-lg mb-2 leading-snug">{{ $bank->title }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-6">{{ $bank->description }}</p>
                        
                        <div class="grid grid-cols-2 gap-3 mb-6 text-xs bg-slate-50 dark:bg-slate-800/60 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Durasi:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $bank->duration_minutes }} Menit</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">KKM / Lulus:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $bank->passing_grade }} Poin</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('quiz.play', $bank->id) }}" class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Mulai Mengerjakan Soal</span>
                    </a>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800">
                    <p class="text-slate-500 text-sm">Belum ada paket bank soal yang aktif saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
