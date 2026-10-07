<div class="max-w-3xl mx-auto py-8 sm:py-12 pb-16 px-4 sm:px-6">
    @if (! $isFinished)
        @php
            $totalQuestions = count($questions);
        @endphp

        <div
            x-data="{
                currentIndex: 0,
                total: {{ $totalQuestions }},
                confirmFinish: false
            }"
            class="space-y-6"
        >
            <x-card class="space-y-6">
                {{-- Header Status & Progress Bar (Persis Figma) --}}
                <div>
                    <div class="flex items-center justify-between text-xs sm:text-sm font-extrabold mb-2">
                        <span class="text-brand">Simulasi Mandiri</span>
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
                        Sebelumnya
                    </button>

                    <template x-if="currentIndex < total - 1">
                        <button
                            type="button"
                            @click="currentIndex = currentIndex + 1"
                            class="btn-primary text-xs !min-h-[38px]"
                        >
                            Selanjutnya
                            <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                        </button>
                    </template>

                    <template x-if="currentIndex === total - 1">
                        <button
                            type="button"
                            @click="confirmFinish = true"
                            class="btn-primary text-xs !min-h-[38px] bg-emerald-500 text-white hover:bg-emerald-600"
                        >
                            <x-app-icon name="check" class="w-4 h-4" />
                            Kirim Jawaban
                        </button>
                    </template>
                </div>
            </x-card>

            {{-- Modal Konfirmasi Kirim Jawaban --}}
            <div
                x-show="confirmFinish"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/60 backdrop-blur-xs"
            >
                <div class="card max-w-md w-full text-center space-y-4">
                    <div class="w-14 h-14 rounded-full bg-sun-soft flex items-center justify-center text-brand mx-auto">
                        <x-app-icon name="quiz" class="w-7 h-7" />
                    </div>
                    <h3 class="text-xl font-extrabold text-navy">Selesaikan Latihan?</h3>
                    <p class="text-xs text-ink-soft leading-relaxed">
                        Periksa kembali apakah seluruh butir soal sudah terjawab. Skor akan dihitung otomatis oleh sistem setelah dikirim.
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
        </div>
    @else
        {{-- Layar Hasil / Skor Figma --}}
        @php
            $score = $resultSummary['score'] ?? 0;
            $isPassed = $resultSummary['is_passed'] ?? false;
            $correctCount = $resultSummary['correct_count'] ?? 0;
            $totalCount = $resultSummary['total_questions'] ?? count($questions);
        @endphp

        <x-card class="max-w-xl mx-auto text-center p-8 sm:p-10 space-y-5">
            <span class="inline-block text-xs font-extrabold uppercase tracking-wider text-brand">
                LATIHAN SELESAI
            </span>

            <h2 class="text-2xl font-extrabold text-navy">Skor Perolehan</h2>

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

            <div class="pt-6 border-t-2 border-sun-soft flex justify-center gap-3">
                <a href="{{ route('quizzes') }}" class="btn-primary text-xs">
                    Pilih Latihan Lain
                </a>
            </div>
        </x-card>
    @endif
</div>
