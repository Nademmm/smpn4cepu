<div x-data="{ 
    modalVisiMisi: false, 
    activeCandidate: null,
    confirmVoteModal: false,
    selectedId: @entangle('selectedCandidateId'),
    init() {
        // Hasilkan hardware hash sederhana dari screen dan platform
        let raw = navigator.userAgent + screen.width + 'x' + screen.height + navigator.language;
        let hash = 0;
        for (let i = 0; i < raw.length; i++) {
            hash = ((hash << 5) - hash) + raw.charCodeAt(i);
            hash |= 0;
        }
        @this.set('clientHardwareHash', 'HW-' + Math.abs(hash).toString(16));
    }
}">
    @if($errorMessage)
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 dark:bg-rose-950/40 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>{{ $errorMessage }}</span>
        </div>
    @endif

    @if($hasVoted)
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-12 text-center border border-slate-200 dark:border-slate-800 shadow-sm max-w-xl mx-auto">
            <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-2">Suara Anda Berhasil Disimpan</h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-8">
                Terima kasih telah berpartisipasi dalam Pemilihan Ketua OSIS SMP Negeri 4 Cepu. Hak suara Anda telah dicatat secara aman, rahasia, dan langsung terhitung pada sistem rekapitulasi.
            </p>
            <a href="{{ route('pilketos.live') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-colors">
                <span>Pantau Quick Count Real-Time</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    @else
        {{-- Banner Panduan Pilketos --}}
        <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-2xl p-5 mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                    !
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 dark:text-white text-sm">Prinsip Asas Luber & Jurdil</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Setiap perangkat hanya berhak memberikan 1 (satu) suara. Pilihan Anda bersifat rahasia dan tidak dapat diubah setelah dikonfirmasi.</p>
                </div>
            </div>
            <a href="{{ route('pilketos.live') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex-shrink-0">
                Lihat Hasil Sementara &rarr;
            </a>
        </div>

        {{-- Grid Pasangan Calon --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto mb-10">
            @foreach($candidates as $c)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border-2 transition-all duration-200 flex flex-col justify-between relative
                    {{ $selectedCandidateId === $c->id ? 'border-blue-600 shadow-xl ring-4 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 shadow-sm hover:border-slate-300 dark:hover:border-slate-700' }}">
                    
                    {{-- Badge Nomor Urut --}}
                    <div class="flex items-center justify-between mb-6">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-slate-900 text-white dark:bg-blue-600 font-extrabold text-xl shadow-md">
                            0{{ $c->candidate_number }}
                        </span>
                        @if($selectedCandidateId === $c->id)
                            <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Pilihan Anda
                            </span>
                        @endif
                    </div>

                    {{-- Nama Calon --}}
                    <div class="mb-6">
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white leading-tight">
                            {{ $c->candidate_name }}
                        </h3>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                            Wakil: {{ $c->vice_candidate_name }}
                        </p>
                    </div>

                    {{-- Cuplikan Visi --}}
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-4 mb-6 text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic border border-slate-100 dark:border-slate-800">
                        "{{ Str::limit($c->vision, 110) }}"
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="modalVisiMisi = true; activeCandidate = {{ json_encode($c) }}"
                                class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors">
                            Baca Visi & Misi Lengkap
                        </button>

                        <button type="button" wire:click="$set('selectedCandidateId', {{ $c->id }})"
                                class="w-full py-3.5 px-4 rounded-xl font-bold text-sm transition-all shadow-md flex items-center justify-center gap-2
                                {{ $selectedCandidateId === $c->id ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">
                            <span>{{ $selectedCandidateId === $c->id ? 'Paslon Terpilih' : 'Pilih Paslon Ini' }}</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Konfirmasi Kirim Suara --}}
        <div class="max-w-md mx-auto text-center">
            <button type="button" @click="confirmVoteModal = true" 
                    @disabled(!$selectedCandidateId)
                    class="w-full py-4 px-6 rounded-2xl font-extrabold text-base shadow-lg transition-all flex items-center justify-center gap-2
                    {{ $selectedCandidateId ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer hover:shadow-xl' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kirimkan Hak Suara Sekarang</span>
            </button>
            <p class="text-xs text-slate-400 mt-2">Pastikan pilihan Anda sudah mantap sebelum mengirimkan suara.</p>
        </div>

        {{-- Modal Visi & Misi --}}
        <div x-show="modalVisiMisi" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800" @click.away="modalVisiMisi = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <div>
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Visi & Misi Paslon</span>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white" x-text="activeCandidate?.candidate_name"></h2>
                    </div>
                    <button type="button" @click="modalVisiMisi = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    <div>
                        <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-1">Visi Utama:</h3>
                        <p class="text-sm text-slate-800 dark:text-slate-200 font-medium" x-text="activeCandidate?.vision"></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-1">Misi & Program Kerja:</h3>
                        <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed" x-text="activeCandidate?.mission"></div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="modalVisiMisi = false" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-slate-200 font-bold text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Konfirmasi Voting --}}
        <div x-show="confirmVoteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-200 dark:border-slate-800" @click.away="confirmVoteModal = false">
                <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Konfirmasi Pilihan</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-6 leading-relaxed">
                    Apakah Anda yakin ingin memberikan suara untuk pasangan calon terpilih? Pilihan ini tidak dapat diubah kembali.
                </p>
                <div class="flex items-center gap-3">
                    <button type="button" @click="confirmVoteModal = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold">
                        Batal
                    </button>
                    <button type="button" wire:click="submitVote" @click="confirmVoteModal = false" class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md">
                        Ya, Kirimkan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
