<x-layouts.app title="Materi Pembelajaran">
    <x-page-header
        title="Belajar"
        subtitle="Baca materi, pelajari modul ajar, dan perluas wawasan belajarmu secara mandiri."
        crumb="Materi Pelajaran"
        variant="violet"
    >
        <x-slot:tabs>
            <a href="{{ route('materials') }}" class="tab tab-active">
                Materi Pelajaran
            </a>
            <a href="{{ route('quizzes') }}" class="tab">
                Latihan Soal
            </a>
        </x-slot:tabs>
    </x-page-header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ selectedGrade: '' }">
        {{-- Filter Tingkat Kelas --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-ink-mute block mb-2">PILIH TINGKAT KELAS</span>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="selectedGrade = ''"
                        :class="selectedGrade === '' ? 'chip-active' : ''"
                        class="chip"
                    >
                        Semua Kelas
                    </button>
                    <button
                        type="button"
                        @click="selectedGrade = '7'"
                        :class="selectedGrade === '7' ? 'chip-active' : ''"
                        class="chip"
                    >
                        Kelas VII
                    </button>
                    <button
                        type="button"
                        @click="selectedGrade = '8'"
                        :class="selectedGrade === '8' ? 'chip-active' : ''"
                        class="chip"
                    >
                        Kelas VIII
                    </button>
                    <button
                        type="button"
                        @click="selectedGrade = '9'"
                        :class="selectedGrade === '9' ? 'chip-active' : ''"
                        class="chip"
                    >
                        Kelas IX
                    </button>
                </div>
            </div>
        </div>

        {{-- Grid Materi Ajar --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($materials as $index => $mat)
                <div
                    x-show="selectedGrade === '' || selectedGrade === '{{ $mat->grade_level }}'"
                    x-transition
                    class="h-full"
                >
                    <x-card class="h-full flex flex-col justify-between hover:border-brand transition-colors">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="w-8 h-8 rounded-lg bg-sky-soft text-brand font-extrabold text-sm flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <x-badge type="blue">Kelas {{ $mat->grade_level }}</x-badge>
                            </div>
                            <div class="text-xs font-bold text-brand uppercase tracking-wider mb-1">
                                {{ $mat->subject?->name ?? 'Mata Pelajaran' }}
                            </div>
                            <h3 class="font-extrabold text-navy text-base mb-2 leading-snug">
                                {{ $mat->title }}
                            </h3>
                            <p class="text-xs text-ink-soft leading-relaxed line-clamp-3 mb-4">
                                {{ $mat->description ?? 'Modul pembelajaran Kurikulum Merdeka yang disusun oleh tenaga pendidik SMP Negeri 4 Cepu.' }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-sun-soft flex items-center justify-between gap-2">
                            <span class="text-[11px] font-semibold text-ink-mute">
                                Oleh {{ $mat->author?->name ?? 'Guru Pengampu' }}
                            </span>
                            @if ($mat->file_path)
                                <a
                                    href="{{ asset('storage/' . $mat->file_path) }}"
                                    target="_blank"
                                    class="btn-primary text-xs !py-1.5 !px-3.5 !min-h-[36px]"
                                >
                                    Unduh Modul
                                </a>
                            @else
                                <span class="text-xs font-bold text-brand">Baca Digital</span>
                            @endif
                        </div>
                    </x-card>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Materi Belum Tersedia"
                        text="Modul pembelajaran mandiri akan segera diunggah oleh guru pengampu mata pelajaran."
                    />
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
