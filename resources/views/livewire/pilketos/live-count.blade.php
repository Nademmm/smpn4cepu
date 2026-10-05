<div wire:poll.5s class="space-y-8">
    {{-- Status Banner Live Polling --}}
    <div class="flex flex-col sm:flex-row items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm gap-4">
        <div class="flex items-center gap-3">
            <span class="relative flex h-3.5 w-3.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
            </span>
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Rekapitulasi Suara Aktif (Live Polling)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Data otomatis disinkronkan dari server setiap 5 detik tanpa perlu memuat ulang.</p>
            </div>
        </div>
        <div class="text-right">
            <span class="text-xs font-semibold text-slate-500">Total Suara Masuk:</span>
            <span class="ml-2 font-mono text-lg font-black text-blue-600 dark:text-blue-400">{{ number_format($totalVotes) }}</span>
        </div>
    </div>

    {{-- Grid Persentase Hasil Suara --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($candidates as $c)
            @php
                $percentage = $totalVotes > 0 ? round(($c->total_votes_cached / $totalVotes) * 100, 1) : 0;
            @endphp
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-10 h-10 rounded-xl bg-slate-900 dark:bg-blue-600 text-white font-extrabold flex items-center justify-center text-base">
                            0{{ $c->candidate_number }}
                        </span>
                        <span class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                            {{ $percentage }}%
                        </span>
                    </div>

                    <h3 class="font-extrabold text-slate-900 dark:text-white text-lg leading-tight">
                        {{ $c->candidate_name }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-6">
                        Wakil: {{ $c->vice_candidate_name }}
                    </p>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-4 overflow-hidden mb-3">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <span>Perolehan Suara:</span>
                    <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">{{ number_format($c->total_votes_cached) }} Suara</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Kartu Aksi Kembali ke Bilik Suara --}}
    <div class="text-center pt-4">
        <a href="{{ route('pilketos') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Bilik Suara</span>
        </a>
    </div>
</div>
