<div class="max-w-3xl mx-auto py-8 sm:py-12 pb-16 px-4 sm:px-6">
    @if (! $hasStarted && ! $isFinished)
        {{-- Kartu Asesmen Mandiri & Autentikasi Peserta Didik --}}
        <x-card class="space-y-6 p-6 sm:p-8 shadow-xs">
            <div class="border-b-2 border-sun-soft pb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="chip-active text-xs !py-1 !px-3 font-extrabold">{{ $bank?->subject?->name ?? 'Mata Pelajaran' }}</span>
                    <span class="text-xs font-bold text-ink-mute">Kelas {{ $bank?->grade_level ?? 'VII' }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-navy leading-tight">
                    {{ $bank?->title ?? 'Simulasi Asesmen Mandiri' }}
                </h1>
                <p class="text-xs sm:text-sm text-ink-soft mt-1.5 font-medium">
                    Asesmen mandiri berbasis digital untuk mengukur pemahaman materi ajar secara komprehensif.
                </p>
            </div>

            {{-- Ringkasan Parameter Ujian --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-sky-soft/60 p-4 rounded-2xl border border-brand/20 text-center">
                <div class="p-2">
                    <span class="text-[11px] font-bold text-ink-mute uppercase block">Jumlah Soal</span>
                    <strong class="text-base font-black text-navy">{{ count($questions) }} Butir</strong>
                </div>
                <div class="p-2">
                    <span class="text-[11px] font-bold text-ink-mute uppercase block">Alokasi Waktu</span>
                    <strong class="text-base font-black text-navy">{{ round($durationSeconds / 60) }} Menit</strong>
                </div>
                <div class="p-2 col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold text-ink-mute uppercase block">KKM Kelulusan</span>
                    <strong class="text-base font-black text-navy">{{ $bank?->passing_grade ?? 75 }} Poin</strong>
                </div>
            </div>

            {{-- Pesan Notifikasi Sukses / Error --}}
            @if ($authSuccessMessage)
                <div class="p-3.5 bg-emerald-50 border border-emerald-300 rounded-xl text-xs font-bold text-emerald-800 flex items-center gap-2">
                    <x-app-icon name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>{{ $authSuccessMessage }}</span>
                </div>
            @endif

            @if ($authErrorMessage)
                <div class="p-3.5 bg-rose-50 border border-rose-300 rounded-xl text-xs font-bold text-rose-800 flex items-center gap-2">
                    <x-app-icon name="x" class="w-4 h-4 text-rose-600 shrink-0" />
                    <span>{{ $authErrorMessage }}</span>
                </div>
            @endif

            {{-- Profil Peserta Didik yang Sedang Aktif --}}
            <div class="bg-sky-soft/80 border border-brand/30 rounded-2xl p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-brand/20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center font-black text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-ink-mute uppercase block">Peserta Latihan Mandiri</span>
                            <h3 class="font-black text-navy text-base leading-snug">{{ Auth::user()->name }}</h3>
                            <p class="text-xs text-ink-soft">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-200"
                        >
                            Ganti Akun
                        </button>
                    </form>
                </div>

                <form wire:submit="startQuiz" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-navy mb-1.5">Pilih / Konfirmasi Kelas Anda <span class="text-rose-500">*</span></label>
                        <select
                            wire:model="studentClass"
                            class="field text-sm font-bold @error('studentClass') !border-rose-400 !bg-rose-50/30 @enderror"
                            required
                        >
                            <option value="">Pilih Rombongan Belajar (Kelas)...</option>
                            <optgroup label="Tingkat Kelas VII">
                                <option value="7A">Kelas 7A</option>
                                <option value="7B">Kelas 7B</option>
                                <option value="7C">Kelas 7C</option>
                                <option value="7D">Kelas 7D</option>
                                <option value="7E">Kelas 7E</option>
                                <option value="7F">Kelas 7F</option>
                                <option value="7G">Kelas 7G</option>
                            </optgroup>
                            <optgroup label="Tingkat Kelas VIII">
                                <option value="8A">Kelas 8A</option>
                                <option value="8B">Kelas 8B</option>
                                <option value="8C">Kelas 8C</option>
                                <option value="8D">Kelas 8D</option>
                                <option value="8E">Kelas 8E</option>
                                <option value="8F">Kelas 8F</option>
                                <option value="8G">Kelas 8G</option>
                            </optgroup>
                            <optgroup label="Tingkat Kelas IX">
                                <option value="9A">Kelas 9A</option>
                                <option value="9B">Kelas 9B</option>
                                <option value="9C">Kelas 9C</option>
                                <option value="9D">Kelas 9D</option>
                                <option value="9E">Kelas 9E</option>
                                <option value="9F">Kelas 9F</option>
                            </optgroup>
                        </select>
                        @error('studentClass')
                            <span class="text-[11px] font-bold text-rose-600 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                class="btn-primary w-full !min-h-[44px] text-sm font-extrabold shadow-xs flex items-center justify-center gap-2"
                            >
                                <span>Mulai Simulasi Kuis Sekarang</span>
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
                                <span>⏱️</span>
                                <span x-text="formatTime(timeLeft)"></span>
                            </span>
                        </div>
                        <span class="text-ink-mute">
                            Soal <span x-text="currentIndex + 1"></span> dari <span x-text="total"></span>
                        </span>
                    </div>

                    {{-- Track Kuning Muda & Bar Biru Figma --}}
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

                        {{-- Opsi Jawaban Radio Card Figma --}}
                        <div class="grid gap-3">
                            @foreach ($question['options'] as $optIndex => $option)
                                @php
                                    $optLetter = chr(65 + $optIndex);
                                @endphp
                                <label
                                    class="flex items-center gap-3.5 p-3.5 sm:p-4 rounded-2xl border-2 cursor-pointer transition-all {{ ($answers[$question['id']] ?? null) == $option['id'] ? 'bg-sky-soft border-brand text-brand font-extrabold' : 'border-sun-soft bg-white text-navy hover:border-brand font-bold' }}"
                                >
                                    <input
                                        type="radio"
                                        name="question_{{ $question['id'] }}"
                                        value="{{ $option['id'] }}"
                                        wire:model.live="answers.{{ $question['id'] }}"
                                        class="sr-only"
                                    >
                                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shrink-0 border-2 {{ ($answers[$question['id']] ?? null) == $option['id'] ? 'bg-brand text-white border-brand' : 'border-ink-mute text-ink-mute bg-white' }}">
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

                {{-- Tombol Navigasi Bawah Figma --}}
                <div class="flex items-center justify-between pt-4 border-t-2 border-sun-soft">
                    <button
                        type="button"
                        @click="currentIndex = Math.max(0, currentIndex - 1)"
                        :disabled="currentIndex === 0"
                        class="btn-ghost text-xs !min-h-[38px] disabled:opacity-40 disabled:cursor-not-allowed"
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
                            class="btn-secondary text-xs !min-h-[38px]"
                        >
                            Selanjutnya
                            <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                        </button>

                        {{-- Tombol Selesai & Kirim Jawaban (Hanya di Soal Terakhir) --}}
                        <button
                            type="button"
                            x-show="currentIndex === total - 1"
                            @click="confirmFinish = true"
                            class="btn-primary text-xs !min-h-[38px]"
                        >
                            <x-app-icon name="check-circle" class="w-4 h-4" />
                            Selesai & Kumpulkan
                        </button>
                    </div>
                </div>
            </x-card>

            {{-- Grid Navigasi Nomor Soal Figma --}}
            <x-card class="p-5">
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block mb-3">
                    Navigasi Cepat Soal:
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($questions as $qIndex => $question)
                        <button
                            type="button"
                            @click="currentIndex = {{ $qIndex }}"
                            class="w-9 h-9 rounded-xl text-xs font-black transition-all flex items-center justify-center border-2 {{ isset($answers[$question['id']]) ? 'bg-sky-soft text-brand border-brand' : 'bg-white text-ink-mute border-sun-soft hover:border-brand' }}"
                            :class="currentIndex === {{ $qIndex }} ? 'ring-2 ring-brand ring-offset-2' : ''"
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
                        <h3 class="text-lg font-black text-navy">Kumpulkan Jawaban?</h3>
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
        {{-- Layar Hasil / Skor Figma --}}
        @php
            $score = $resultSummary['score'] ?? 0;
            $isPassed = $resultSummary['is_passed'] ?? false;
            $correctCount = $resultSummary['correct_count'] ?? 0;
            $totalCount = $resultSummary['total_questions'] ?? count($questions);
        @endphp

        <x-card class="max-w-xl mx-auto text-center p-8 sm:p-10 space-y-5 shadow-xs">
            <span class="inline-block text-xs font-extrabold uppercase tracking-wider text-brand">
                LATIHAN SELESAI
            </span>

            <h2 class="text-2xl font-extrabold text-navy">Hasil Simulasi Mandiri</h2>

            <div class="inline-block bg-sky-soft text-brand font-black text-xs px-4 py-1.5 rounded-full border border-brand/20">
                Peserta: {{ $participantIdentifier }}
            </div>

            {{-- Angka Skor Besar Figma --}}
            <div class="text-6xl sm:text-7xl font-extrabold {{ $isPassed ? 'text-emerald-600' : 'text-red-600' }} leading-none">
                {{ $score }}<span class="text-2xl text-ink-mute font-bold">/100</span>
            </div>

            <p class="text-sm font-bold text-ink-soft">
                Jawaban benar: {{ $correctCount }} dari {{ $totalCount }} butir soal
            </p>

            <div class="pt-1">
                @if ($isPassed)
                    <span class="inline-block rounded-full bg-emerald-100 px-4 py-1.5 text-xs font-extrabold text-emerald-800">
                        ✓ Tuntas · Memenuhi KKM
                    </span>
                @else
                    <span class="inline-block rounded-full bg-red-100 px-4 py-1.5 text-xs font-extrabold text-red-800">
                        Belum Tuntas · Tetap Semangat Belajar
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
    @endif
</div>
