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
    {{-- Banner Resmi Pilketos --}}
    <section class="bg-gradient-to-br from-brand to-brand-dark text-white py-12 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-4xl mx-auto space-y-4">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white">Pemilihan Ketua & Wakil OSIS</h1>
            <p class="text-sm sm:text-base text-white/85 max-w-2xl mx-auto font-medium">
                Gunakan hak suaramu secara cerdas, berintegritas, langsung, umum, bebas, rahasia, jujur, dan adil.
            </p>

            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('pilketos.live') }}" class="btn-primary text-xs !min-h-[38px] shadow-xs">
                    Pantau Quick Count Suara
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8">
        {{-- Pesan Error Validasi Jika Ada --}}
        @if ($errorMessage)
            <div class="rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-center text-red-800 text-sm font-bold">
                ⚠️ {{ $errorMessage }}
            </div>
        @endif

        @if ($hasVoted)
            {{-- Tanda Terima Digital Resmi (Digital Voting Receipt) --}}
            <x-card class="max-w-2xl mx-auto text-center p-8 sm:p-12 space-y-6 shadow-md border-2 border-emerald-300 bg-gradient-to-b from-white to-emerald-50/40">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto shadow-xs border-2 border-emerald-300">
                    <x-app-icon name="check-circle" class="w-10 h-10" />
                </div>

                <div class="space-y-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider">
                        ✓ Hak Suara Terverifikasi
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-navy">
                        Terima Kasih, Suara Anda Telah Tercatat!
                    </h2>
                    <p class="text-xs sm:text-sm text-ink-soft max-w-lg mx-auto leading-relaxed font-medium">
                        Partisipasi Anda dalam Pemilihan Ketua & Wakil Ketua OSIS SMP Negeri 4 Cepu telah berhasil diamankan ke dalam kotak suara digital secara sah dan anonim.
                    </p>
                </div>

                {{-- Kotak Detail Tanda Terima --}}
                <div class="bg-white rounded-2xl border-2 border-sun-soft p-5 max-w-md mx-auto text-left space-y-3 shadow-2xs">
                    <div class="flex justify-between items-center pb-2.5 border-b border-sun-soft">
                        <span class="text-xs font-bold text-ink-mute uppercase">Status Hak Suara</span>
                        <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                            Telah Digunakan (1 Suara)
                        </span>
                    </div>
                    <div class="flex justify-between items-center pb-2.5 border-b border-sun-soft">
                        <span class="text-xs font-bold text-ink-mute uppercase">Waktu Pencatatan</span>
                        <span class="text-xs font-extrabold text-navy">
                            {{ now()->translatedFormat('d F Y, H:i') }} WIB
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-ink-mute uppercase">Metode Verifikasi</span>
                        <span class="text-xs font-extrabold text-brand">
                            Kriptografi Sidik Jari & Token
                        </span>
                    </div>
                </div>

                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('pilketos.live') }}" class="btn-primary !min-h-[42px] px-6 text-xs shadow-xs flex items-center gap-2">
                        <span>Pantau Quick Count Real-Time</span>
                        <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                    <a href="{{ route('home') }}" class="btn-ghost !min-h-[42px] px-5 text-xs flex items-center gap-2">
                        <x-app-icon name="home" class="w-3.5 h-3.5" />
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </x-card>
        @else
            {{-- Grid Kartu Kandidat Paslon Figma (Hanya Muncul Jika Belum Memilih) --}}
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
                                    @if ($cand->photo_url)
                                        <img
                                            src="{{ $cand->photo_url }}"
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
                                <button
                                    type="button"
                                    @click="openConfirm({{ $cand->id }}, '{{ addslashes($cand->candidate_name) }} & {{ addslashes($cand->vice_candidate_name) }}')"
                                    class="btn-primary w-full text-sm"
                                >
                                    Pilih Pasangan Ini
                                </button>
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

            {{-- Modal Konfirmasi Pilihan Suara (Teleport ke Body) --}}
            <template x-teleport="body">
                <div
                    x-show="showModal"
                    x-cloak
                    @keydown.escape.window="showModal = false"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/70 backdrop-blur-xs"
                >
                    <div
                        @click.away="showModal = false"
                        class="card max-w-sm w-full p-6 text-center space-y-4 bg-white shadow-2xl"
                    >
                        <div class="w-12 h-12 rounded-full bg-sun text-navy flex items-center justify-center mx-auto font-black text-xl">
                            !
                        </div>
                        <h3 class="text-lg font-black text-navy">Konfirmasi Hak Suara</h3>
                        <p class="text-xs text-ink-soft leading-relaxed font-medium">
                            Anda akan memberikan hak suara kepada:
                            <strong class="block text-navy text-sm mt-1" x-text="candidateName"></strong>
                            Pilihan tidak dapat diubah setelah dikonfirmasi.
                        </p>
                        <div class="flex justify-center gap-3 pt-2">
                            <button
                                type="button"
                                @click="showModal = false"
                                class="btn-ghost text-xs"
                            >
                                Kembali
                            </button>
                            <button
                                type="button"
                                @click="executeVote()"
                                class="btn-primary text-xs"
                            >
                                Ya, Saya Yakin
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        @endif
    </div>
</div>
