<div wire:poll.8s.visible class="space-y-6">
    {{-- Header Status Live Count --}}
    <div class="card p-5 sm:p-6 bg-white border border-sand/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-black uppercase tracking-wider text-emerald-700">SIARAN REAL-TIME LANGSUNG</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-navy leading-snug">Hasil Sementara Perolehan Suara</h2>
            <p class="text-xs text-ink-soft font-medium">Data diperbarui otomatis setiap 5 detik langsung dari kotak suara digital.</p>
        </div>

        <div class="flex items-center gap-3 bg-cream/70 border border-sun-soft px-5 py-3 rounded-2xl shrink-0">
            <div class="w-10 h-10 rounded-xl bg-brand text-white flex items-center justify-center shrink-0">
                <x-app-icon name="chart-bar" class="w-5 h-5" />
            </div>
            <div>
                <span class="block text-[11px] font-bold text-ink-mute uppercase tracking-wider">Total Suara Masuk</span>
                <div class="flex items-baseline gap-1">
                    <strong class="text-2xl font-black text-navy">{{ $totalVotes }}</strong>
                    <span class="text-xs font-bold text-ink-soft">Hak Suara</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Kartu Perolehan Suara Tiap Paslon --}}
    <div class="space-y-4">
        @forelse ($candidates as $cand)
            @php
                $votes = $cand->votes_count ?? $cand->total_votes_cached;
                $percent = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0;
                $barBg = match ($cand->candidate_number) {
                    1 => 'bg-brand',
                    2 => 'bg-grape',
                    default => 'bg-amber-500',
                };
                $badgeType = match ($cand->candidate_number) {
                    1 => 'blue',
                    2 => 'purple',
                    default => 'orange',
                };
            @endphp
            <x-card class="p-5 sm:p-6 hover:border-brand transition-all space-y-4 shadow-xs">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        {{-- Avatar Paslon --}}
                        <div class="relative shrink-0">
                            @if ($cand->photo_url)
                                <img
                                    src="{{ $cand->photo_url }}"
                                    alt="{{ $cand->candidate_name }}"
                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover border-2 border-sun-soft shadow-2xs"
                                >
                            @else
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-brand text-white flex items-center justify-center font-black text-xl border-2 border-sun-soft shadow-2xs">
                                    {{ $cand->candidate_number }}
                                </div>
                            @endif
                            <div class="absolute -top-1.5 -left-1.5 w-6 h-6 rounded-full bg-sun text-navy font-black text-xs flex items-center justify-center shadow-xs border border-white">
                                {{ $cand->candidate_number }}
                            </div>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <x-badge type="{{ $badgeType }}">Paslon {{ $cand->candidate_number }}</x-badge>
                            </div>
                            <h3 class="font-extrabold text-navy text-base sm:text-lg leading-snug">
                                {{ $cand->candidate_name }} & {{ $cand->vice_candidate_name }}
                            </h3>
                            @if ($cand->vision)
                                <p class="text-xs text-ink-soft italic line-clamp-1 mt-0.5 font-medium">
                                    "{{ $cand->vision }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="text-right shrink-0 w-full sm:w-auto flex sm:flex-col justify-between sm:justify-center items-end border-t sm:border-t-0 pt-2 sm:pt-0 border-sun-soft/60">
                        <span class="text-2xl sm:text-3xl font-black text-navy leading-none">
                            {{ $percent }}%
                        </span>
                        <span class="text-xs font-extrabold text-brand mt-1">
                            {{ $votes }} Suara
                        </span>
                    </div>
                </div>

                {{-- Track & Fill Bar Beranimasi --}}
                <div class="space-y-1">
                    <div class="h-4 w-full bg-sun-soft/60 rounded-full overflow-hidden p-0.5 border border-sun-soft">
                        <div
                            class="h-full {{ $barBg }} rounded-full transition-all duration-700 ease-out shadow-xs"
                            style="width: {{ $percent }}%;"
                        ></div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-card class="text-center py-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-sky-soft text-brand flex items-center justify-center">
                    <x-app-icon name="chart-bar" class="w-6 h-6" />
                </div>
                <h3 class="font-extrabold text-navy text-base">Belum Ada Paslon Terdaftar</h3>
                <p class="text-xs text-ink-soft max-w-md mx-auto">
                    Kandidat calon ketua dan wakil ketua OSIS sedang dipersiapkan oleh tim panitia pemilihan.
                </p>
            </x-card>
        @endforelse
    </div>

    {{-- Keterangan Keamanan & Transparansi --}}
    <div class="p-4 rounded-2xl bg-sky-soft/60 border border-brand/20 text-xs text-navy flex items-start gap-3">
        <x-app-icon name="shield" class="w-5 h-5 text-brand shrink-0 mt-0.5" />
        <div class="leading-relaxed font-semibold">
            Penghitungan suara digital ini diproteksi oleh validasi kriptografi token perangkat keras dan pencegahan suara ganda. Setiap suara masuk dicatat secara langsung tanpa perantara demi menjamin asas LUBER JURDIL.
        </div>
    </div>
</div>
