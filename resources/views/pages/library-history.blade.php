<x-layouts.app title="Riwayat Peminjaman Buku Saya - SMPN 4 Cepu" metaDescription="Daftar buku fisik yang sedang dan pernah dipinjam dari Perpustakaan SMP Negeri 4 Cepu.">
    <x-page-header
        title="Riwayat Peminjaman Buku Saya"
        subtitle="Pantau status sirkulasi buku fisik, jadwal jatuh tempo, dan bukti tiket QR Code peminjaman Anda."
        crumb="Riwayat Peminjaman"
    />

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
        {{-- Ringkasan Statistik Peminjam --}}
        @php
            $activeLoans = $history->whereIn('status', ['diambil', 'dipinjam']);
            $pendingLoans = $history->where('status', 'menunggu');
            $returnedLoans = $history->whereIn('status', ['selesai', 'dikembalikan']);
            $overdueLoans = $history->filter(fn ($r) => $r->is_overdue);
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-5 bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Total Diajukan</span>
                <strong class="text-2xl sm:text-3xl font-black text-navy leading-none mt-1 block">{{ $history->count() }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Semua Riwayat</span>
            </div>

            <div class="card p-5 bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Sedang Dipinjam</span>
                <strong class="text-2xl sm:text-3xl font-black text-brand leading-none mt-1 block">{{ $activeLoans->count() }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Ada di Tangan Anda</span>
            </div>

            <div class="card p-5 bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Menunggu Ambil</span>
                <strong class="text-2xl sm:text-3xl font-black text-amber-600 leading-none mt-1 block">{{ $pendingLoans->count() }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Siap di Meja Perpustakaan</span>
            </div>

            <div class="card p-5 bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Selesai Kembali</span>
                <strong class="text-2xl sm:text-3xl font-black text-emerald-700 leading-none mt-1 block">{{ $returnedLoans->count() }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Sudah Selesai</span>
            </div>
        </div>

        {{-- Peringatan Keterlambatan jika ada --}}
        @if ($overdueLoans->isNotEmpty())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3.5 text-rose-900 shadow-2xs">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                    <x-app-icon name="exclamation-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wide">Pemberitahuan Keterlambatan</h2>
                    <p class="text-xs mt-0.5">
                        Anda memiliki <strong>{{ $overdueLoans->count() }} buku</strong> yang telah melewati batas tanggal pengembalian.
                        Harap segera mengembalikan buku ke Ruang Perpustakaan SMPN 4 Cepu agar tidak menghambat peminjaman buku berikutnya.
                    </p>
                </div>
            </div>
        @endif

        {{-- Daftar Buku yang Pernah & Sedang Dipinjam --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-sm font-extrabold text-navy uppercase tracking-wider flex items-center gap-2">
                    <x-app-icon name="bookmark" class="w-4 h-4 text-brand" />
                    <span>Daftar Transaksi Peminjaman</span>
                </h2>

                <a href="{{ route('library') }}" class="btn-primary text-xs font-bold px-4 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-2xs">
                    <x-app-icon name="book-open" class="w-4 h-4 text-white" />
                    <span>Jelajahi Katalog Buku</span>
                </a>
            </div>

            @if ($history->isEmpty())
                <div class="card p-12 text-center bg-white border border-slate-200/90 shadow-xs space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 text-ink-mute flex items-center justify-center mx-auto">
                        <x-app-icon name="book-open" class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-navy">Belum Ada Riwayat Peminjaman</h3>
                        <p class="text-xs text-ink-mute max-w-md mx-auto mt-1">
                            Anda belum pernah meminjam buku fisik dari perpustakaan sekolah. Temukan buku pelajaran, fiksi, dan literasi yang menarik di katalog digital kami!
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('library') }}" class="btn-primary text-xs font-extrabold px-6 py-2.5 inline-flex items-center gap-2 rounded-xl shadow-2xs">
                            <span>Buka Katalog Perpustakaan</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($history as $item)
                        <div class="card p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-brand transition-all shadow-xs rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                            {{-- Info Buku & Siswa --}}
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="flex items-center flex-wrap gap-2">
                                    <span class="font-mono text-xs font-black text-navy bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                        {{ $item->reservation_code }}
                                    </span>
                                    <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full
                                        @if($item->is_overdue) bg-rose-100 text-rose-800 border border-rose-200
                                        @elseif($item->status === 'menunggu') bg-amber-100 text-amber-800 border border-amber-200
                                        @elseif($item->status === 'diambil') bg-blue-100 text-blue-800 border border-blue-200
                                        @elseif($item->status === 'selesai') bg-emerald-100 text-emerald-800 border border-emerald-200
                                        @else bg-slate-100 text-slate-700 border border-slate-200
                                        @endif"
                                    >
                                        {{ $item->status_label }}
                                    </span>

                                    @if ($item->renew_count > 0)
                                        <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-200">
                                            Diperpanjang {{ $item->renew_count }}x
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-sm sm:text-base font-extrabold text-navy truncate" title="{{ $item->book?->title }}">
                                    {{ $item->book?->title ?? 'Buku Perpustakaan' }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-mute">
                                    <span>Penulis: <strong class="text-slate-800">{{ $item->book?->author ?? '-' }}</strong></span>
                                    <span>Rencana Ambil: <strong class="text-slate-800">{{ $item->pickup_date ? $item->pickup_date->format('d M Y') : '-' }}</strong></span>
                                    <span>Tenggat: <strong class="{{ $item->is_overdue ? 'text-rose-600 font-black' : 'text-brand font-bold' }}">{{ $item->due_date ? $item->due_date->format('d M Y') : '-' }}</strong></span>
                                </div>
                            </div>

                            {{-- Tombol Aksi per Transaksi --}}
                            <div class="flex flex-wrap items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <a
                                    href="{{ route('library.ticket', $item->reservation_code) }}"
                                    class="btn-ghost bg-slate-50 border border-slate-200 hover:border-brand text-xs font-bold px-3 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-2xs"
                                >
                                    <x-app-icon name="qr-code" class="w-4 h-4 text-navy" />
                                    <span>Lihat Tiket &amp; QR Code</span>
                                </a>

                                <a
                                    href="{{ route('library.ticket.download-barcode', $item->reservation_code) }}"
                                    download="qr-{{ $item->reservation_code }}.svg"
                                    class="btn-primary text-xs font-extrabold px-3 py-2 rounded-xl inline-flex items-center gap-1.5 shadow-2xs"
                                >
                                    <x-app-icon name="arrow-down-tray" class="w-4 h-4 text-white" />
                                    <span>Unduh QR Code</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>

