<x-layouts.app title="Latihan Soal Mandiri">
    <x-page-header
        title="Belajar"
        subtitle="Ukur pemahaman materi belajarmu secara mandiri melalui bank soal terpadu."
        crumb="Latihan Soal"
        variant="violet"
    >
        <x-slot:tabs>
            <a href="{{ route('materials') }}" class="tab">
                Materi Pelajaran
            </a>
            <a href="{{ route('quizzes') }}" class="tab tab-active">
                Latihan Soal
            </a>
        </x-slot:tabs>
    </x-page-header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8">
        {{-- Header Info Latihan --}}
        <div>
            <h2 class="text-2xl font-extrabold text-navy mb-1">Daftar Paket Latihan Soal</h2>
            <p class="text-sm text-ink-soft">Pilih paket soal sesuai mata pelajaran dan tingkat kelas untuk memulai simulasi mandiri.</p>
        </div>

        {{-- Grid Paket Soal --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($questionBanks as $bank)
                <x-card class="flex flex-col justify-between hover:border-brand transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="w-10 h-10 rounded-xl bg-sky-soft text-brand flex items-center justify-center font-extrabold">
                                <x-app-icon name="quiz" class="w-5 h-5" />
                            </span>
                            <x-badge type="blue">Kelas {{ $bank->grade_level }}</x-badge>
                        </div>

                        <div class="text-xs font-bold text-brand uppercase tracking-wider mb-1">
                            {{ $bank->subject?->name ?? 'Umum' }}
                        </div>
                        <h3 class="text-lg font-extrabold text-navy leading-snug mb-2">
                            {{ $bank->title }}
                        </h3>
                        <p class="text-xs text-ink-soft line-clamp-2 leading-relaxed mb-4">
                            {{ $bank->description ?? 'Latihan mandiri untuk mengukur pemahaman kompetensi dasar.' }}
                        </p>

                        {{-- Metadata Soal: Durasi, KKM, Jumlah Butir --}}
                        <div class="grid grid-cols-3 gap-2 py-3 border-y border-sun-soft text-center text-xs mb-4">
                            <div>
                                <span class="block font-extrabold text-navy text-sm">{{ $bank->questions_count ?? $bank->questions()->count() }}</span>
                                <span class="text-[11px] text-ink-mute">Soal</span>
                            </div>
                            <div>
                                <span class="block font-extrabold text-navy text-sm">{{ $bank->duration_minutes ?? 15 }}'</span>
                                <span class="text-[11px] text-ink-mute">Durasi</span>
                            </div>
                            <div>
                                <span class="block font-extrabold text-brand text-sm">{{ $bank->passing_score ?? 75 }}</span>
                                <span class="text-[11px] text-ink-mute">KKM</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('quiz.play', $bank->id) }}" class="btn-primary w-full text-center text-sm">
                        Mulai Latihan
                    </a>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Belum Ada Paket Soal"
                        text="Bank soal latihan mandiri sedang dipersiapkan oleh tim guru."
                    />
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
