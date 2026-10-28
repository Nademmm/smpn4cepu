<div class="max-w-3xl mx-auto py-8 sm:py-12 pb-16 px-4 sm:px-6">
    @if (! $hasStarted && ! $isFinished)
        {{-- Kartu Asesmen Mandiri, Verifikasi Angkatan & Password Soal --}}
        <x-card class="space-y-6 p-6 sm:p-8 shadow-xs">
            <div class="border-b-2 border-sun-soft pb-4">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="chip-active text-xs !py-1 !px-3 font-extrabold">{{ $bank?->subject?->name ?? $subjectName }}</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-sky-soft text-brand border border-brand/20">
                            Angkatan Kelas {{ $bank?->grade_level ?? $gradeLevel }}
                        </span>
                    </div>
                    @if ($requiresPassword)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                            <x-app-icon name="shield" class="w-3.5 h-3.5 text-amber-700" />
                            <span>Terkunci Password Soal</span>
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-navy leading-tight">
                    {{ $bank?->title ?? $bankTitle }}
                </h1>
                <p class="text-xs sm:text-sm text-ink-soft mt-1.5 font-medium leading-relaxed">
                    {{ $bank?->description ?? 'Asesmen mandiri berbasis digital untuk mengukur pemahaman materi ajar sesuai tingkat angkatan kelas.' }}
                </p>
            </div>

            {{-- Ringkasan Parameter Ujian --}}
            <div class="grid grid-cols-3 gap-3 bg-sky-soft/60 p-4 rounded-2xl border border-brand/20 text-center">
                <div class="p-2">
                    <span class="text-[11px] font-bold text-ink-mute uppercase block">Jumlah Soal</span>
                    <strong class="text-base font-black text-navy">{{ $questionsCount }} Butir</strong>
                </div>
                <div class="p-2 border-x border-brand/15">
                    <span class="text-[11px] font-bold text-ink-mute uppercase block">Alokasi Waktu</span>
                    <strong class="text-base font-black text-navy">{{ round($durationSeconds / 60) }} Menit</strong>
                </div>
                <div class="p-2">
                    <span class="text-[11px] font-bold text-ink-mute uppercase block">KKM Kelulusan</span>
                    <strong class="text-base font-black text-navy">{{ $bank?->passing_grade ?? $passingGrade }} Poin</strong>
                </div>
            </div>

            {{-- Pesan Notifikasi Error --}}
            @if ($authErrorMessage)
                <div class="p-3.5 bg-rose-50 border border-rose-300 rounded-xl text-xs font-bold text-rose-800 flex items-center gap-2">
                    <x-app-icon name="x" class="w-4 h-4 text-rose-600 shrink-0" />
                    <span>{{ $authErrorMessage }}</span>
                </div>
            @endif

            {{-- Profil Peserta Didik & Form Verifikasi Masuk Kuis --}}
            <div class="bg-sky-soft/80 border border-brand/30 rounded-2xl p-5 sm:p-6 space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-brand/20">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-brand text-white flex items-center justify-center font-black text-base shadow-2xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-ink-mute uppercase block">Peserta Latihan Terverifikasi</span>
                            <h3 class="font-black text-navy text-base leading-snug">{{ Auth::user()->name }}</h3>
                            <p class="text-xs text-ink-soft font-medium">
                                {{ Auth::user()->nisn ? 'NISN: ' . Auth::user()->nisn . ' · ' : '' }}{{ Auth::user()->email }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="px-3.5 py-2 min-h-[40px] text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors border border-rose-200 inline-flex items-center gap-1.5"
                        >
                            <x-app-icon name="logout" class="w-3.5 h-3.5" />
                            <span>Ganti Akun</span>
                        </button>
                    </form>
                </div>

                <form wire:submit="startQuiz" class="space-y-5">
                    {{-- Pilihan Tingkat Angkatan (Kelas 7, 8, 9 - Tanpa Pecah Sub-Kelas) --}}
                    <div>
                        <label class="block text-xs font-extrabold text-navy mb-2 uppercase tracking-wider">
                            1. Pilih Tingkat Angkatan Kelas Anda <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ([
                                'Kelas 7' => ['roman' => 'Kelas VII', 'sub' => 'Angkatan 7'],
                                'Kelas 8' => ['roman' => 'Kelas VIII', 'sub' => 'Angkatan 8'],
                                'Kelas 9' => ['roman' => 'Kelas IX', 'sub' => 'Angkatan 9'],
                            ] as $cohortValue => $cohortMeta)
                                <label
                                    class="relative flex flex-col items-center justify-center p-3.5 rounded-2xl border-2 cursor-pointer transition-all text-center min-h-[68px] focus-within:ring-2 focus-within:ring-brand {{ $studentClass === $cohortValue ? 'bg-brand text-white border-brand shadow-sm' : 'bg-white text-navy border-sun-soft hover:border-brand' }}"
                                >
                                    <input
                                        type="radio"
                                        name="studentClass"
                                        value="{{ $cohortValue }}"
                                        wire:model.live="studentClass"
                                        class="sr-only"
                                    >
                                    <span class="text-sm sm:text-base font-black leading-tight">{{ $cohortMeta['roman'] }}</span>
                                    <span class="text-[11px] font-bold mt-0.5 {{ $studentClass === $cohortValue ? 'text-sun-soft' : 'text-ink-mute' }}">
                                        {{ $cohortMeta['sub'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('studentClass')
                            <span class="text-xs font-bold text-rose-600 block mt-1.5">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Input Password / Token Soal --}}
                    @if ($requiresPassword)
                        <div x-data="{ showToken: false }" class="p-4 rounded-2xl bg-white border-2 border-amber-300/90 space-y-2.5 shadow-2xs">
                            <div class="flex items-center justify-between gap-2">
                                <label for="quizPasswordInput" class="block text-xs font-extrabold text-navy uppercase tracking-wider">
                                    2. Masukkan Password / Token Soal <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full">
                                    Wajib Diisi
                                </span>
                            </div>
                            <p class="text-xs text-ink-soft leading-relaxed font-medium">
                                Soal ini dilindungi password agar hanya peserta yang mendapat izin guru pengampu yang dapat mengerjakan.
                            </p>
                            <div class="relative">
                                <input
                                    id="quizPasswordInput"
                                    :type="showToken ? 'text' : 'password'"
                                    wire:model="quizPassword"
                                    placeholder="Masukkan password / token soal dari guru (contoh: MTK8-2026)..."
                                    autocomplete="off"
                                    class="field pr-24 font-mono font-extrabold tracking-wider uppercase text-sm @error('quizPassword') !border-rose-400 !bg-rose-50/40 @enderror"
                                    required
                                >
                                <button
                                    type="button"
                                    @click="showToken = !showToken"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-lg text-xs font-bold text-brand hover:bg-sky-soft transition-colors"
                                    :aria-label="showToken ? 'Sembunyikan token' : 'Tampilkan token'"
                                >
                                    <span x-text="showToken ? 'Sembunyikan' : 'Lihat Token'"></span>
                                </button>
                            </div>
                            @error('quizPassword')
                                <div class="flex items-center gap-1.5 text-xs font-bold text-rose-600 pt-0.5">
                                    <x-app-icon name="x-circle" class="w-4 h-4 shrink-0" />
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    @endif

                    <div class="pt-1 flex flex-col sm:flex-row items-center gap-3">
                        <a href="{{ route('quizzes') }}" class="btn-ghost w-full sm:w-auto text-xs justify-center">
                            <x-app-icon name="arrow-left" class="w-3.5 h-3.5" />
                            <span>Kembali ke Daftar Soal</span>
                        </a>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="btn-primary w-full sm:flex-1 !min-h-[46px] text-sm font-extrabold shadow-xs flex items-center justify-center gap-2"
                        >
                            <x-app-icon name="shield" class="w-4 h-4" />
                            <span wire:loading.remove target="startQuiz">Buka Kunci & Mulai Kerjakan Soal</span>
                            <span wire:loading target="startQuiz">Memverifikasi Akses Soal...</span>
                            <x-app-icon name="arrow-right" class="w-4 h-4" />
                        </button>
                    </div>
                </form>
            </div>
        </x-card>
    @elseif (! $isFinished)
        @php
            $totalQuestions = count($questions);
        @endphp

        <div
            x-data="{
                currentIndex: 0,
                total: {{ $totalQuestions }},
                confirmFinish: false,
                selectedAnswers: @entangle('answers'),
                timeLeft: {{ $durationSeconds }},
                timer: null,
                init() {
                    this.timer = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.timer);
                            $wire.submitQuiz();
                        }
                    }, 1000);
                },
                formatTime(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                }
            }"
            class="space-y-6"
        >
            <x-card class="space-y-6">
                {{-- Header Status & Progress Bar dengan Countdown Timer --}}
                <div>
                    <div class="flex items-center justify-between text-xs sm:text-sm font-extrabold mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-brand font-black">{{ $participantIdentifier }}</span>
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black transition-colors"
                                :class="timeLeft < 180 ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-amber-100 text-amber-900 border border-amber-300'"
                            >
                                <x-app-icon name="clock" class="w-3.5 h-3.5" />
                                <span x-text="formatTime(timeLeft)"></span>
                            </span>
                        </div>
                        <span class="text-ink-mute">
                            Soal <span x-text="currentIndex + 1"></span> dari <span x-text="total"></span>
                        </span>
                    </div>

                    {{-- Track Kuning Muda & Bar Biru --}}
                    <div class="h-2.5 w-full bg-sun-soft rounded-full overflow-hidden">
                        <div
                            class="h-full bg-brand rounded-full transition-all duration-300"
                            :style="'width: ' + (((currentIndex + 1) / total) * 100) + '%'"
                        ></div>
                    </div>
                </div>

                {{-- Loop Butir Soal Berbasis Index Alpine --}}
                @foreach ($questions as $qIndex => $question)
                    <div
                        x-show="currentIndex === {{ $qIndex }}"
                        x-cloak
                        class="space-y-5"
                    >
                        <h2 class="text-lg sm:text-xl font-extrabold text-navy leading-relaxed">
                            {{ $qIndex + 1 }}. {{ $question['question_text'] ?? ($question['text'] ?? '') }}
                        </h2>

                        {{-- Opsi Jawaban Radio Card (Client-Side Alpine Entangle: 0ms Instan, 0 Server Request saat Klik) --}}
                        <div class="grid gap-3">
                            @foreach ($question['options'] as $optIndex => $option)
                                @php
                                    $optLetter = chr(65 + $optIndex);
                                @endphp
                                <label
                                    class="flex items-center gap-3.5 p-3.5 sm:p-4 rounded-2xl border-2 cursor-pointer transition-all focus-within:ring-2 focus-within:ring-brand"
                                    :class="selectedAnswers[{{ $question['id'] }}] == {{ $option['id'] }} ? 'bg-sky-soft border-brand text-brand font-extrabold' : 'border-sun-soft bg-white text-navy hover:border-brand font-bold'"
                                >
                                    <input
                                        type="radio"
                                        name="question_{{ $question['id'] }}"
                                        value="{{ $option['id'] }}"
                                        x-model="selectedAnswers[{{ $question['id'] }}]"
                                        class="sr-only"
                                    >
                                    <span
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shrink-0 border-2"
                                        :class="selectedAnswers[{{ $question['id'] }}] == {{ $option['id'] }} ? 'bg-brand text-white border-brand' : 'border-ink-mute text-ink-mute bg-white'"
                                    >
                                        {{ $optLetter }}
                                    </span>
                                    <span class="text-sm sm:text-base leading-snug">
                                        {{ $option['text'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Tombol Navigasi Bawah --}}
                <div class="flex items-center justify-between pt-4 border-t-2 border-sun-soft">
                    <button
                        type="button"
                        @click="currentIndex = Math.max(0, currentIndex - 1)"
                        :disabled="currentIndex === 0"
                        class="btn-ghost text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <x-app-icon name="arrow-left" class="w-3.5 h-3.5" />
                        Soal Sebelumnya
                    </button>

                    <div>
                        {{-- Tombol Selanjutnya --}}
                        <button
                            type="button"
                            x-show="currentIndex < total - 1"
                            @click="currentIndex = Math.min(total - 1, currentIndex + 1)"
                            class="btn-secondary text-xs"
                        >
                            Selanjutnya
                            <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                        </button>

                        {{-- Tombol Selesai & Kirim Jawaban (Hanya di Soal Terakhir) --}}
                        <button
                            type="button"
                            x-show="currentIndex === total - 1"
                            @click="confirmFinish = true"
                            class="btn-primary text-xs"
                        >
                            <x-app-icon name="check-circle" class="w-4 h-4" />
                            Selesai & Kumpulkan
                        </button>
                    </div>
                </div>
            </x-card>

            {{-- Grid Navigasi Nomor Soal --}}
            <x-card class="p-5">
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block mb-3">
                    Navigasi Cepat Soal:
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($questions as $qIndex => $question)
                        <button
                            type="button"
                            @click="currentIndex = {{ $qIndex }}"
                            aria-label="Soal nomor {{ $qIndex + 1 }}"
                            class="w-10 h-10 min-h-[40px] rounded-xl text-xs font-black transition-all flex items-center justify-center border-2"
                            :class="[
                                selectedAnswers[{{ $question['id'] }}] ? 'bg-sky-soft text-brand border-brand' : 'bg-white text-ink-mute border-sun-soft hover:border-brand',
                                currentIndex === {{ $qIndex }} ? 'ring-2 ring-brand ring-offset-2' : ''
                            ]"
                        >
                            {{ $qIndex + 1 }}
                        </button>
                    @endforeach
                </div>
            </x-card>

            {{-- Modal Konfirmasi Selesai Ujian (Teleport ke Body) --}}
            <template x-teleport="body">
                <div
                    x-show="confirmFinish"
                    x-cloak
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="confirm-finish-title"
                    @keydown.escape.window="confirmFinish = false"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/70 backdrop-blur-xs"
                >
                    <div
                        @click.away="confirmFinish = false"
                        class="card max-w-sm w-full p-6 text-center space-y-4 bg-white shadow-2xl"
                    >
                        <div class="w-12 h-12 rounded-full bg-brand/10 text-brand flex items-center justify-center mx-auto">
                            <x-app-icon name="check-circle" class="w-6 h-6" />
                        </div>
                        <h3 id="confirm-finish-title" class="text-lg font-black text-navy">Kumpulkan Jawaban?</h3>
                        <p class="text-xs text-ink-soft leading-relaxed font-medium">
                            Pastikan semua soal telah terjawab dengan teliti sebelum mengirimkan hasil simulasi ini.
                        </p>
                        <div class="flex justify-center gap-3 pt-2">
                            <button
                                type="button"
                                @click="confirmFinish = false"
                                class="btn-ghost text-xs"
                            >
                                Periksa Lagi
                            </button>
                            <button
                                type="button"
                                @click="clearInterval(timer)"
                                wire:click="submitQuiz"
                                wire:loading.attr="disabled"
                                class="btn-primary text-xs"
                            >
                                <span wire:loading.remove>Ya, Kirim Sekarang</span>
                                <span wire:loading>Menghitung Skor...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @else
        {{-- Layar Hasil / Skor --}}
        @php
            $score = round($resultSummary['final_score'] ?? $resultSummary['score'] ?? 0);
            $isPassed = $resultSummary['is_passed'] ?? false;
            $correctCount = $resultSummary['correct_answers'] ?? $resultSummary['correct_count'] ?? collect($resultSummary['reviews'] ?? [])->where('is_correct', true)->count();
            $totalCount = $resultSummary['total_questions'] ?? count($resultSummary['reviews'] ?? $questions);
            $spentSeconds = (int) ($resultSummary['duration_spent_seconds'] ?? 0);
            $spentFormatted = $spentSeconds >= 60
                ? floor($spentSeconds / 60) . ' menit ' . ($spentSeconds % 60) . ' detik'
                : $spentSeconds . ' detik';
        @endphp

        <div class="space-y-8">
            <x-card class="max-w-xl mx-auto text-center p-8 sm:p-10 space-y-5 shadow-xs">
                <span class="inline-block text-xs font-extrabold uppercase tracking-wider text-brand">
                    LATIHAN SELESAI
                </span>

                <h2 class="text-2xl font-extrabold text-navy">Hasil Simulasi Mandiri</h2>

                <div class="inline-block bg-sky-soft text-brand font-black text-xs px-4 py-1.5 rounded-full border border-brand/20">
                    Peserta: {{ $participantIdentifier }}
                </div>

                {{-- Angka Skor Besar --}}
                <div class="text-6xl sm:text-7xl font-extrabold {{ $isPassed ? 'text-emerald-600' : 'text-red-600' }} leading-none">
                    {{ $score }}<span class="text-2xl text-ink-mute font-bold">/100</span>
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-bold text-ink-soft">
                        Jawaban benar: {{ $correctCount }} dari {{ $totalCount }} butir soal
                    </p>
                    @if ($spentSeconds > 0)
                        <p class="text-xs font-semibold text-ink-mute">
                            Diselesaikan dalam waktu {{ $spentFormatted }}
                        </p>
                    @endif
                </div>

                <div class="pt-1">
                    @if ($isPassed)
                        <span class="inline-block rounded-full bg-emerald-100 px-4 py-1.5 text-xs font-extrabold text-emerald-800">
                            ✓ Tuntas · Memenuhi KKM ({{ $passingGrade }})
                        </span>
                    @else
                        <span class="inline-block rounded-full bg-red-100 px-4 py-1.5 text-xs font-extrabold text-red-800">
                            Belum Tuntas · KKM {{ $passingGrade }} · Tetap Semangat Belajar
                        </span>
                    @endif
                </div>

                <div class="p-3 bg-sky-soft/60 rounded-xl border border-brand/20 text-xs font-bold text-navy flex items-center justify-center gap-2">
                    <x-app-icon name="check-circle" class="w-4 h-4 text-brand shrink-0" />
                    <span>Hasil latihan ini telah tersimpan secara permanen pada portofolio akun belajar Anda.</span>
                </div>

                <div class="pt-4 border-t-2 border-sun-soft flex justify-center gap-3">
                    <a href="{{ route('quizzes') }}" class="btn-primary text-xs">
                        Pilih Latihan Lain
                    </a>
                </div>
            </x-card>

            {{-- Pembahasan & Review Jawaban Butir Soal --}}
            @if (!empty($resultSummary['reviews']))
                <x-card class="space-y-5 p-6 sm:p-8 shadow-xs">
                    <div class="border-b-2 border-sun-soft pb-4 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold text-brand uppercase tracking-wider block">Evaluasi Mandiri</span>
                            <h3 class="text-lg sm:text-xl font-extrabold text-navy">Pembahasan & Kunci Jawaban</h3>
                        </div>
                        <span class="text-xs font-bold text-ink-mute bg-cream px-3 py-1 rounded-full border border-sun-soft">
                            {{ count($resultSummary['reviews']) }} Butir Soal
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach ($resultSummary['reviews'] as $rIndex => $review)
                            <div class="p-4 sm:p-5 rounded-2xl border-2 {{ !empty($review['is_correct']) ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/30' }} space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="text-sm sm:text-base font-extrabold text-navy leading-snug">
                                        {{ $rIndex + 1 }}. {{ $review['question_text'] ?? '' }}
                                    </h4>
                                    <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase {{ !empty($review['is_correct']) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ !empty($review['is_correct']) ? 'Benar' : 'Kurang Tepat' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                                    <div class="p-3 rounded-xl bg-white border {{ !empty($review['is_correct']) ? 'border-emerald-200' : 'border-rose-200' }}">
                                        <span class="text-[11px] font-bold text-ink-mute block mb-0.5">Jawaban Anda:</span>
                                        <strong class="font-extrabold {{ !empty($review['is_correct']) ? 'text-emerald-800' : 'text-rose-800' }}">
                                            {{ $review['user_selected'] ?? 'Tidak Menjawab' }}
                                        </strong>
                                    </div>
                                    <div class="p-3 rounded-xl bg-white border border-emerald-200">
                                        <span class="text-[11px] font-bold text-ink-mute block mb-0.5">Kunci Jawaban Benar:</span>
                                        <strong class="font-extrabold text-emerald-800">
                                            {{ $review['correct_answer'] ?? '-' }}
                                        </strong>
                                    </div>
                                </div>

                                @if (!empty($review['explanation']))
                                    <div class="p-3 rounded-xl bg-sky-soft/70 border border-brand/20 text-xs text-navy leading-relaxed font-medium">
                                        <strong class="font-extrabold text-brand block mb-0.5">Pembahasan:</strong>
                                        {{ $review['explanation'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    @endif
</div>
