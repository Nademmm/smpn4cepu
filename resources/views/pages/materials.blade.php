<x-layouts.app title="Materi Pembelajaran">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Ruang Belajar Mandiri</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Materi Ajar Kurikulum Merdeka</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Akses bahan ajar, modul digital, dan lembar kerja peserta didik untuk 11 mata pelajaran wajib dan muatan lokal SMP Negeri 4 Cepu.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($materials as $mat)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                {{ $mat->subject->name ?? 'Umum' }}
                            </span>
                            <span class="text-xs font-bold text-slate-500">Kelas {{ $mat->grade_level }}</span>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2 leading-snug">{{ $mat->title }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">{{ $mat->description }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Guru: {{ $mat->author->name ?? 'Tim Guru' }}</span>
                        @if($mat->external_url)
                            <a href="{{ $mat->external_url }}" target="_blank" rel="noopener" class="font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                <span>Buka Materi</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @elseif($mat->file_path)
                            <a href="{{ asset('storage/' . $mat->file_path) }}" download class="font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                <span>Unduh PDF</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800">
                    <p class="text-slate-500 text-sm">Belum ada materi pembelajaran yang dipublikasikan oleh bapak/ibu guru.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
