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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-navy mb-1">Daftar Paket Latihan Soal</h2>
                <p class="text-sm text-ink-soft">Pilih paket soal sesuai mata pelajaran dan tingkat kelas untuk memulai simulasi mandiri.</p>
            </div>
            @auth
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>Masuk sebagai: {{ auth()->user()->name }}</span>
                </div>
            @else
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                    <span>* Login siswa dilakukan saat membuka paket soal untuk menyimpan riwayat nilai.</span>
                </div>
            @endauth
        </div>

        @auth
            @if(isset($myAttempts) && $myAttempts->isNotEmpty())
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-sand/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-sand/50 pb-3">
                        <div class="flex items-center gap-2.5">
                            <x-app-icon name="chart-bar" class="w-5 h-5 text-brand" />
                            <h3 class="font-extrabold text-navy text-base">Riwayat Latihan Saya</h3>
                        </div>
                        <span class="text-xs text-ink-soft font-semibold">{{ $myAttempts->count() }} Percobaan Terakhir</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        @foreach($myAttempts as $attempt)
                            <div class="p-3.5 rounded-xl border {{ $attempt->is_passed ? 'border-teal-200 bg-teal-50/40' : 'border-rose-200 bg-rose-50/40' }} flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-black text-navy line-clamp-1 mb-0.5">
                                        {{ $attempt->bank?->title ?? 'Latihan Soal' }}
                                    </div>
                                    <div class="text-[11px] text-ink-soft font-medium">
                                        {{ $attempt->created_at->translatedFormat('d M Y, H:i') }}
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-lg font-black {{ $attempt->is_passed ? 'text-teal-700' : 'text-rose-700' }}">
                                        {{ number_format($attempt->final_score, 0) }}
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $attempt->is_passed ? 'text-teal-600' : 'text-rose-600' }}">
                                        {{ $attempt->is_passed ? 'Lulus KKM' : 'Remedial' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endauth

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
                                <span>{{ $bank->questions_count ?? 0 }} Soal</span>
                            </div>
                            <span class="text-ink-mute">·</span>
                            <div>
                                <span>{{ $bank->duration_minutes ?? 15 }} Menit</span>
                            </div>
                            <span class="text-ink-mute">·</span>
                            <div class="text-brand font-black">
                                <span>KKM {{ $bank->passing_grade ?? 75 }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('quiz.play', $bank->id) }}" class="btn-primary w-full text-center text-xs shadow-2xs">
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
