@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman Katalog" class="w-full flex flex-col sm:flex-row items-center justify-between gap-4 py-4 select-none">
        {{-- Kiri: Keterangan Jumlah Hasil (Bahasa Indonesia) --}}
        <div class="text-xs text-ink-mute font-bold text-center sm:text-left">
            <span>Menampilkan</span>
            <strong class="text-navy font-black">{{ $paginator->firstItem() }}</strong>
            <span>sampai</span>
            <strong class="text-navy font-black">{{ $paginator->lastItem() }}</strong>
            <span>dari</span>
            <strong class="text-brand font-black">{{ $paginator->total() }}</strong>
            <span>koleksi buku</span>
        </div>

        {{-- Kanan: Tombol Navigasi Halaman Modern --}}
        <div class="flex items-center gap-1.5 flex-wrap justify-center">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span
                    aria-disabled="true"
                    class="min-h-[44px] px-3.5 rounded-xl border border-slate-200/60 bg-white/50 text-slate-300 font-extrabold text-xs flex items-center gap-1.5 cursor-not-allowed opacity-50"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    <span class="hidden md:inline">Sebelumnya</span>
                </span>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="min-h-[44px] px-3.5 rounded-xl border border-slate-200 bg-white hover:border-brand hover:text-brand text-navy font-extrabold text-xs flex items-center gap-1.5 shadow-2xs hover:bg-sky-soft/40 transition-all active:scale-95"
                    aria-label="Halaman Sebelumnya"
                >
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    <span class="hidden md:inline">Sebelumnya</span>
                </button>
            @endif

            {{-- Nomor Halaman --}}
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Separator Titik Tiga --}}
                    @if (is_string($element))
                        <span class="w-10 h-10 flex items-center justify-center text-xs font-black text-ink-mute">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Daftar Tombol Angka --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span
                                    aria-current="page"
                                    class="w-10 h-10 min-w-[40px] rounded-xl bg-brand text-white font-black text-xs flex items-center justify-center shadow-xs border border-brand/20 select-none"
                                >
                                    {{ $page }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    wire:loading.attr="disabled"
                                    class="w-10 h-10 min-w-[40px] rounded-xl bg-white border border-slate-200 hover:border-brand hover:text-brand text-navy font-extrabold text-xs flex items-center justify-center shadow-2xs hover:bg-sky-soft/40 transition-all active:scale-95"
                                    aria-label="Pindah ke halaman {{ $page }}"
                                >
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="min-h-[44px] px-3.5 rounded-xl border border-slate-200 bg-white hover:border-brand hover:text-brand text-navy font-extrabold text-xs flex items-center gap-1.5 shadow-2xs hover:bg-sky-soft/40 transition-all active:scale-95"
                    aria-label="Halaman Selanjutnya"
                >
                    <span class="hidden md:inline">Selanjutnya</span>
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            @else
                <span
                    aria-disabled="true"
                    class="min-h-[44px] px-3.5 rounded-xl border border-slate-200/60 bg-white/50 text-slate-300 font-extrabold text-xs flex items-center gap-1.5 cursor-not-allowed opacity-50"
                >
                    <span class="hidden md:inline">Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif

