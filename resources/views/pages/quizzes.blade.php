<x-layouts.app title="Latihan Soal Mandiri - SMP Negeri 4 Cepu" metaDescription="Ukur pemahaman materi belajarmu secara mandiri melalui bank soal terpadu per angkatan Kelas VII, VIII, dan IX SMP Negeri 4 Cepu.">
    <x-page-header
        title="Belajar"
        subtitle="Ukur pemahaman materi belajarmu secara mandiri melalui bank soal terpadu per tingkat angkatan."
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

    @php
        $userGrade = 'all';
        if (auth()->check() && !empty(auth()->user()->student_class)) {
            if (preg_match('/7|VII/i', auth()->user()->student_class)) {
                $userGrade = '7';
            } elseif (preg_match('/8|VIII/i', auth()->user()->student_class)) {
                $userGrade = '8';
            } elseif (preg_match('/9|IX/i', auth()->user()->student_class)) {
                $userGrade = '9';
            }
        }
        $countAll = $questionBanks->count();
        $count7 = $questionBanks->where('grade_level', 7)->count();
        $count8 = $questionBanks->where('grade_level', 8)->count();
        $count9 = $questionBanks->where('grade_level', 9)->count();
    @endphp

    <div
        x-data="{
            selectedGrade: 'all',
            searchQuery: '',
            matchesFilter(grade, title, subject) {
                const gradeOk = this.selectedGrade === 'all' || String(grade) === String(this.selectedGrade);
                if (!gradeOk) return false;
                if (!this.searchQuery.trim()) return true;
                const q = this.searchQuery.toLowerCase();
                return title.toLowerCase().includes(q) || subject.toLowerCase().includes(q);
            }
        }"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8"
    >
        {{-- Header Info Latihan & Identitas Angkatan Siswa --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-navy mb-1">Daftar Paket Latihan Soal Per Angkatan</h2>
                <p class="text-sm text-ink-soft">Pilih paket soal sesuai angkatan kelas (Kelas VII, VIII, atau IX) dan masukkan password/token soal dari guru untuk memulai.</p>
            </div>
            @auth
                <div class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs font-bold shrink-0">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>{{ auth()->user()->name }}</span>
                    @if (!empty(auth()->user()->student_class))
                        <span class="px-2 py-0.5 rounded-full bg-teal-600 text-white text-[11px] font-extrabold">
                            {{ preg_replace('/^([789])[A-Z]$/i', 'Kelas $1', auth()->user()->student_class) }}
                        </span>
                    @endif
                </div>
            @endauth
        </div>

        {{-- Filter Bar: Tab Per Angkatan (Kelas 7, 8, 9) + Pencarian --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-sand/80 shadow-xs">
            <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter Angkatan Kelas">
                <button
                    type="button"
                    @click="selectedGrade = 'all'"
                    :class="selectedGrade === 'all' ? 'chip-active' : ''"
                    class="chip transition-all"
                >
                    <span>Semua Angkatan</span>
                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === 'all' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                        {{ $countAll }}
                    </span>
                </button>
                <button
                    type="button"
                    @click="selectedGrade = '7'"
                    :class="selectedGrade === '7' ? 'chip-active' : ''"
                    class="chip transition-all"
                >
                    <span>Kelas VII (7)</span>
                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === '7' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                        {{ $count7 }}
                    </span>
                </button>
                <button
                    type="button"
                    @click="selectedGrade = '8'"
                    :class="selectedGrade === '8' ? 'chip-active' : ''"
                    class="chip transition-all"
                >
                    <span>Kelas VIII (8)</span>
                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === '8' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                        {{ $count8 }}
                    </span>
                </button>
                <button
                    type="button"
                    @click="selectedGrade = '9'"
                    :class="selectedGrade === '9' ? 'chip-active' : ''"
                    class="chip transition-all"
                >
                    <span>Kelas IX (9)</span>
                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[11px] font-black" :class="selectedGrade === '9' ? 'bg-white/20 text-white' : 'bg-cream text-navy'">
                        {{ $count9 }}
                    </span>
                </button>
            </div>

            <div class="relative w-full md:w-72">
                <input
                    type="search"
                    x-model="searchQuery"
                    placeholder="Cari mata pelajaran atau judul soal..."
                    aria-label="Cari paket latihan soal"
                    class="field !py-2.5 pl-9 pr-4 text-xs font-bold"
                >
                <x-app-icon name="search" class="w-4 h-4 text-ink-mute absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
        </div>

        @auth
            @if(isset($myAttempts) && $myAttempts->isNotEmpty())
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-sand/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-sand/50 pb-3">
                        <div class="flex items-center gap-2.5">
                            <x-app-icon name="chart-bar" class="w-5 h-5 text-brand" />
                            <h3 class="font-extrabold text-navy text-base">Riwayat Nilai Latihan Saya</h3>
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
                @php
                    $romanGrade = match ((int) $bank->grade_level) {
                        7 => 'VII (7)',
                        8 => 'VIII (8)',
                        9 => 'IX (9)',
                        default => $bank->grade_level,
                    };
                @endphp
                <div
                    x-show="matchesFilter(@js((string) $bank->grade_level), @js($bank->title), @js($bank->subject?->name ?? 'Umum'))"
                    x-transition
                    class="flex"
                >
                    <x-card class="w-full flex flex-col justify-between hover:border-brand transition-all p-5 sm:p-6 group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-black text-brand uppercase tracking-wider">
                                    {{ $bank->subject?->name ?? 'Umum' }}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    @if ($bank->requiresPassword())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300" title="Memerlukan Password / Token Soal dari Guru">
                                            <x-app-icon name="shield" class="w-3 h-3 text-amber-700" />
                                            <span>Token</span>
                                        </span>
                                    @endif
                                    <x-badge type="blue">Kelas {{ $romanGrade }}</x-badge>
                                </div>
                            </div>

                            <h3 class="text-lg font-extrabold text-navy leading-snug mb-2 group-hover:text-brand transition-colors">
                                {{ $bank->title }}
                            </h3>
                            <p class="text-xs text-ink-soft line-clamp-2 leading-relaxed mb-4 font-medium">
                                {{ $bank->description ?? 'Latihan mandiri untuk mengukur pemahaman kompetensi dasar peserta didik sesuai tingkat angkatan kelas.' }}
                            </p>

                            {{-- Metadata Ringkas --}}
                            <div class="flex items-center justify-between gap-2 py-3 px-3.5 rounded-xl bg-cream/70 border border-sun-soft text-xs mb-5 font-semibold text-navy">
                                <div class="flex items-center gap-1.5">
                                    <x-app-icon name="quiz" class="w-4 h-4 text-brand" />
                                    <span>{{ $bank->questions_count ?? 0 }} Soal</span>
                                </div>
                                <span class="text-ink-mute">·</span>
                                <div>
                                    <span>{{ $bank->duration_minutes ?? 30 }} Menit</span>
                                </div>
                                <span class="text-ink-mute">·</span>
                                <div class="text-brand font-black">
                                    <span>KKM {{ $bank->passing_grade ?? 75 }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('quiz.play', $bank->id) }}" class="btn-primary w-full text-center text-xs shadow-2xs flex items-center justify-center gap-2">
                            <x-app-icon name="shield" class="w-3.5 h-3.5" />
                            <span>Masuk Ruang Soal</span>
                        </a>
                    </x-card>
                </div>
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
