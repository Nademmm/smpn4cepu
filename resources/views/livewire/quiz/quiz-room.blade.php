<div x-data="{
    activeQuestionIndex: 0,
    answers: @entangle('answers'),
    totalQuestions: {{ count($questions) }},
    submitConfirmation: false
}">
    @if(!$isFinished)
        {{-- Header Status Kuis & Navigasi --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Ruang Latihan Mandiri</span>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Asesmen Pilihan Ganda</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500">Soal Terjawab:</span>
                <span class="px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950 font-mono font-bold text-sm text-blue-600 dark:text-blue-400">
                    <span x-text="Object.keys(answers).length"></span> / {{ count($questions) }}
                </span>
            </div>
        </div>

        @if(count($questions) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                {{-- Lembar Soal Aktif --}}
                <div class="lg:col-span-3">
                    @foreach($questions as $idx => $q)
                        <div x-show="activeQuestionIndex === {{ $idx }}" x-cloak class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                                <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                                    Pertanyaan #{{ $idx + 1 }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">Bobot: {{ $q['points'] }} Poin</span>
                            </div>

                            {{-- Isi Teks Pertanyaan --}}
                            <div class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white mb-8 leading-relaxed">
                                {{ $q['question_text'] }}
                            </div>

                            {{-- Opsi Jawaban Pilihan Ganda --}}
                            <div class="space-y-3">
                                @foreach($q['options'] as $opt)
                                    <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all duration-150"
                                           :class="answers[{{ $q['id'] }}] == {{ $opt['id'] }} ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-950/30' : 'border-slate-200 dark:border-slate-700/80 hover:border-slate-300 dark:hover:border-slate-600'">
                                        <input type="radio" name="question_{{ $q['id'] }}" value="{{ $opt['id'] }}"
                                               x-model="answers[{{ $q['id'] }}]"
                                               class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                                        <div class="flex items-center gap-3 text-sm font-medium text-slate-800 dark:text-slate-200">
                                            <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-600 dark:text-slate-300">
                                                {{ chr(65 + $loop->index) }}
                                            </span>
                                            <span>{{ $opt['text'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Tombol Navigasi Next/Prev --}}
                            <div class="flex items-center justify-between pt-8 mt-8 border-t border-slate-100 dark:border-slate-800">
                                <button type="button" @click="activeQuestionIndex = Math.max(0, activeQuestionIndex - 1)"
                                        :disabled="activeQuestionIndex === 0"
                                        class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed">
                                    &larr; Sebelumnya
                                </button>
                                
                                <button type="button" @click="activeQuestionIndex = Math.min(totalQuestions - 1, activeQuestionIndex + 1)"
                                        x-show="activeQuestionIndex < totalQuestions - 1"
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-xs font-bold text-white shadow-sm">
                                    Berikutnya &rarr;
                                </button>

                                <button type="button" @click="submitConfirmation = true"
                                        x-show="activeQuestionIndex === totalQuestions - 1"
                                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-md">
                                    Kirimkan Jawaban
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Sidebar Grid Nomor Soal --}}
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm sticky top-28">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Navigasi Nomor</h3>
                        <div class="grid grid-cols-4 gap-2 mb-6">
                            @foreach($questions as $idx => $q)
                                <button type="button" @click="activeQuestionIndex = {{ $idx }}"
                                        class="h-10 rounded-xl font-bold text-xs transition-colors flex items-center justify-center border"
                                        :class="{
                                            'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-500/20': activeQuestionIndex === {{ $idx }},
                                            'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-400': answers[{{ $q['id'] }}] && activeQuestionIndex !== {{ $idx }},
                                            'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700': !answers[{{ $q['id'] }}] && activeQuestionIndex !== {{ $idx }}
                                        }">
                                    {{ $idx + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <button type="button" @click="submitConfirmation = true"
                                class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-colors">
                            Selesaikan Kuis
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-800">
                <p class="text-slate-500">Belum ada soal yang ditambahkan pada paket bank soal ini.</p>
            </div>
        @endif

        {{-- Modal Konfirmasi Kirim --}}
        <div x-show="submitConfirmation" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-200 dark:border-slate-800" @click.away="submitConfirmation = false">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Konfirmasi Pengumpulan</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">
                    Anda telah menjawab <strong x-text="Object.keys(answers).length"></strong> dari {{ count($questions) }} soal. Apakah Anda ingin mengakhiri sesi latihan ini?
                </p>
                <div class="flex items-center gap-3">
                    <button type="button" @click="submitConfirmation = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300">
                        Periksa Lagi
                    </button>
                    <button type="button" wire:click="submitQuiz" @click="submitConfirmation = false" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md">
                        Ya, Kumpulkan
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- Ringkasan Hasil Evaluasi Server-Side (Zero Client Leakage Selesai) --}}
        <div class="max-w-3xl mx-auto space-y-8">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-12 text-center border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-6 {{ $resultSummary['is_passed'] ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400' }}">
                    <span class="font-mono text-2xl font-black">{{ round($resultSummary['score']) }}</span>
                </div>
                
                <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">
                    {{ $resultSummary['is_passed'] ? 'Selamat, Anda Tuntas!' : 'Latihan Selesai, Tetap Semangat!' }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-8">
                    Standar KKM: {{ $resultSummary['passing_grade'] }} | Total Soal: {{ $resultSummary['total_questions'] }}
                </p>

                <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto mb-8">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="block text-xs text-slate-500">Skor Akhir</span>
                        <span class="font-mono text-xl font-black text-blue-600 dark:text-blue-400">{{ round($resultSummary['score']) }}/100</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="block text-xs text-slate-500">Status</span>
                        <span class="font-bold text-sm {{ $resultSummary['is_passed'] ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $resultSummary['is_passed'] ? 'Lulus KKM' : 'Perlu Remedial' }}
                        </span>
                    </div>
                </div>

                <a href="{{ route('quizzes') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">
                    <span>Coba Latihan Paket Lainnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            {{-- Lembar Pembahasan Reflektif --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Lembar Pembahasan Soal</h3>
                <div class="space-y-6">
                    @foreach($resultSummary['answers_review'] as $idx => $rev)
                        <div class="p-5 rounded-2xl border {{ $rev['is_correct'] ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-900 dark:bg-emerald-950/20' : 'border-rose-200 bg-rose-50/30 dark:border-rose-900 dark:bg-rose-950/20' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-500">Soal #{{ $idx + 1 }}</span>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $rev['is_correct'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400' }}">
                                    {{ $rev['is_correct'] ? 'Benar (+'.$rev['points_awarded'].' Poin)' : 'Salah (0 Poin)' }}
                                </span>
                            </div>
                            <p class="font-semibold text-slate-900 dark:text-white text-sm mb-3">{{ $rev['question_text'] }}</p>
                            @if($rev['explanation'])
                                <div class="text-xs text-slate-600 dark:text-slate-300 bg-white/70 dark:bg-slate-800/70 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                                    <strong class="text-blue-600 dark:text-blue-400">Pembahasan:</strong> {{ $rev['explanation'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
