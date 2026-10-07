<div
    x-data="{
        showModal: false,
        confirmCandidate: null,
        candidateName: '',
        init() {
            // Generate canvas-based hardware hash fingerprint
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('SMPN4CEPU-PILKETOS', 2, 2);
                const hash = btoa(canvas.toDataURL()).slice(0, 32);
                $wire.set('clientHardwareHash', hash);
            } catch(e) {
                $wire.set('clientHardwareHash', 'HW-' + Math.random().toString(36).substring(2, 15));
            }
        },
        openConfirm(id, name) {
            this.confirmCandidate = id;
            this.candidateName = name;
            this.showModal = true;
        },
        executeVote() {
            $wire.set('selectedCandidateId', this.confirmCandidate);
            $wire.submitVote();
            this.showModal = false;
        }
    }"
>
    {{-- Banner Sesuai Desain Figma --}}
    <section class="bg-gradient-to-br from-brand to-brand-dark text-white py-12 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-4xl mx-auto space-y-4">
            <span class="inline-block rounded-full bg-emerald-500 text-white px-5 py-1.5 text-xs font-extrabold shadow-xs">
                🟢 Pemilihan Sedang Berlangsung
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white">Pemilihan Ketua & Wakil OSIS</h1>
            <p class="text-sm sm:text-base text-white/85 max-w-2xl mx-auto">
                Gunakan hak suaramu secara cerdas, berintegritas, langsung, umum, bebas, rahasia, jujur, dan adil.
            </p>

            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('pilketos.live') }}" class="btn-primary text-xs !min-h-[38px]">
                    Pantau Quick Count Suara
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        {{-- Pesan Status / Error --}}
        @if ($hasVoted)
            <div class="rounded-2xl border-2 border-emerald-400 bg-emerald-50 p-5 text-center text-emerald-900 font-extrabold text-sm sm:text-base shadow-xs">
                ✅ Suara Anda telah berhasil dicatat ke dalam bilik suara digital. Terima kasih telah berpartisipasi!
            </div>
        @endif

        @if ($errorMessage)
            <div class="rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-center text-red-800 text-sm font-bold">
                ⚠️ {{ $errorMessage }}
            </div>
        @endif

        {{-- Grid Kartu Kandidat Paslon Figma --}}
        <div>
            <h2 class="text-2xl font-extrabold text-navy mb-6 text-center sm:text-left">
                Kandidat Calon Ketua & Wakil Ketua OSIS
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($candidates as $cand)
                    <x-card class="relative text-center p-6 flex flex-col justify-between hover:border-brand transition-colors">
                        {{-- Nomor Urut Bulat Kuning Pojok Kiri Atas Figma --}}
                        <div class="absolute top-4 left-4 w-9 h-9 rounded-full bg-sun text-navy font-black text-base flex items-center justify-center shadow-xs">
                            {{ $cand->candidate_number }}
                        </div>

                        <div>
                            {{-- Foto Paslon --}}
                            <div class="mt-4 mb-4 flex justify-center">
                                @if ($cand->photo_path && file_exists(public_path('storage/' . $cand->photo_path)))
                                    <img
                                        src="{{ asset('storage/' . $cand->photo_path) }}"
                                        alt="{{ $cand->candidate_name }}"
                                        class="w-28 h-28 rounded-full object-cover border-4 border-sun-soft shadow-xs"
                                    >
                                @else
                                    <div class="w-28 h-28 rounded-full bg-brand text-sun flex items-center justify-center font-extrabold text-3xl border-4 border-sun-soft shadow-xs">
                                        {{ $cand->candidate_number }}
                                    </div>
                                @endif
                            </div>

                            <h3 class="font-extrabold text-navy text-lg leading-tight mb-1">
                                {{ $cand->candidate_name }}
                            </h3>
                            <div class="text-sm font-bold text-ink-soft mb-3">
                                & {{ $cand->vice_candidate_name }}
                            </div>

                            <div class="mb-4">
                                <x-badge type="blue">Paslon {{ $cand->candidate_number }}</x-badge>
                            </div>

                            <div class="text-xs text-ink-soft italic leading-relaxed line-clamp-3 mb-6 bg-cream/70 rounded-xl p-3">
                                "{{ $cand->vision }}"
                            </div>
                        </div>

                        <div>
                            @if (! $hasVoted)
                                <button
                                    type="button"
                                    @click="openConfirm({{ $cand->id }}, '{{ addslashes($cand->candidate_name) }} & {{ addslashes($cand->vice_candidate_name) }}')"
                                    class="btn-primary w-full text-sm"
                                >
                                    Pilih Pasangan Ini
                                </button>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="btn-primary w-full text-sm opacity-50 cursor-not-allowed"
                                >
                                    Sudah Memberikan Suara
                                </button>
                            @endif
                        </div>
                    </x-card>
                @empty
                    <div class="col-span-full">
                        <x-empty-state
                            title="Belum Ada Kandidat Terdaftar"
                            text="Daftar pasangan calon ketua dan wakil ketua OSIS sedang dipersiapkan oleh tim panitia pemilihan."
                        />
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Modal Konfirmasi Pilihan Suara --}}
        <div
            x-show="showModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/60 backdrop-blur-xs"
        >
            <div class="card max-w-md w-full text-center space-y-4">
                <div class="w-14 h-14 rounded-full bg-sun text-navy flex items-center justify-center text-xl font-black mx-auto">
                    🗳️
                </div>
                <h3 class="text-xl font-extrabold text-navy">Konfirmasi Pilihan Anda</h3>
                <p class="text-xs text-ink-soft leading-relaxed">
                    Apakah Anda yakin ingin memberikan suara untuk pasangan:
                </p>
                <div class="card bg-sky-soft p-3 font-extrabold text-brand text-sm" x-text="candidateName"></div>
                <p class="text-[11px] text-ink-mute">
                    Satu perangkat hanya dapat memberikan satu suara. Pilihan tidak dapat dibatalkan setelah dikirim.
                </p>
                <div class="flex justify-center gap-3 pt-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="btn-ghost text-xs"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="executeVote()"
                        class="btn-primary text-xs"
                    >
                        Ya, Yakin & Kirim Suara
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
