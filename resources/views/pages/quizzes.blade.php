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
                <x-card class="flex flex-col justify-between hover:border-brand transition-all p-5 sm:p-6 group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-black text-brand uppercase tracking-wider">
                                {{ $bank->subject?->name ?? 'Umum' }}
                            </span>
                            <x-badge type="blue">Kelas {{ $bank->grade_level }}</x-badge>
                        </div>

                        <h3 class="text-lg font-extrabold text-navy leading-snug mb-2 group-hover:text-brand transition-colors">
                            {{ $bank->title }}
                        </h3>
                        <p class="text-xs text-ink-soft line-clamp-2 leading-relaxed mb-4 font-medium">
                            {{ $bank->description ?? 'Latihan mandiri untuk mengukur pemahaman kompetensi dasar peserta didik.' }}
                        </p>

                        {{-- Metadata Ringkas --}}
                        <div class="flex items-center justify-between gap-2 py-3 px-3.5 rounded-xl bg-cream/70 border border-sun-soft text-xs mb-5 font-semibold text-navy">
                            <div class="flex items-center gap-1.5">
                                <x-app-icon name="quiz" class="w-4 h-4 text-brand" />
                                <span>{{ $bank->questions_count ?? $bank->questions()->count() }} Soal</span>
                            </div>
                            <span class="text-ink-mute">·</span>
                            <div>
                                <span>{{ $bank->duration_minutes ?? 15 }} Menit</span>
                            </div>
                            <span class="text-ink-mute">·</span>
                            <div class="text-brand font-black">
                                <span>KKM {{ $bank->passing_score ?? 75 }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('quiz.play', $bank->id) }}" class="btn-primary w-full text-center text-xs !min-h-[40px] shadow-2xs">
                        Mulai Latihan Soal
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
