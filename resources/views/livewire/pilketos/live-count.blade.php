<div wire:poll.5s class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b-2 border-sun-soft">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-navy">Hasil Sementara Perolehan Suara</h2>
            <p class="text-xs text-ink-soft">Diperbarui otomatis setiap 5 detik langsung dari bilik suara digital.</p>
        </div>
        <div class="bg-brand text-white px-4 py-2 rounded-xl text-center shrink-0">
            <span class="block text-xs font-semibold text-white/80">Total Suara Masuk</span>
            <strong class="text-xl font-extrabold text-sun">{{ $totalVotes }}</strong>
        </div>
    </div>

    {{-- Progress Bars Persis Sesuai Desain Figma --}}
    <x-card class="space-y-6">
        @forelse ($candidates as $cand)
            @php
                $votes = $cand->votes_count ?? $cand->total_votes_cached;
                $percent = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0;
                $barColor = match ($cand->candidate_number) {
                    1 => 'bg-brand',
                    2 => 'bg-grape',
                    default => 'bg-amber-500',
                };
            @endphp
            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm sm:text-base font-extrabold">
                    <span class="text-navy">
                        Paslon {{ $cand->candidate_number }} — {{ $cand->candidate_name }} & {{ $cand->vice_candidate_name }}
                    </span>
                    <span class="text-brand">
                        {{ $votes }} suara ({{ $percent }}%)
                    </span>
                </div>

                {{-- Track Kuning Muda & Fill Bar Figma --}}
                <div class="h-4 w-full bg-sun-soft rounded-full overflow-hidden">
                    <div
                        class="h-full {{ $barColor }} rounded-full transition-all duration-700 ease-out"
                        style="width: {{ $percent }}%;"
                    ></div>
                </div>
            </div>
        @empty
            <p class="text-xs text-ink-soft text-center py-4">Belum ada kandidat terdaftar.</p>
        @endforelse
    </x-card>
</div>
