<x-layouts.app :title="'Tiket Peminjaman: ' . $ticket->reservation_code . ' - SMPN 4 Cepu'" :metaDescription="'Bukti tiket sirkulasi peminjaman buku fisik perpustakaan SMP Negeri 4 Cepu dengan kode ' . $ticket->reservation_code">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        {{-- Tombol Navigasi Kembali & Unduh (Sembunyi saat Cetak) --}}
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3 print:hidden">
            <a href="{{ route('library') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-mute hover:text-brand transition-colors">
                <x-app-icon name="arrow-left" class="w-4 h-4" />
                <span>Kembali ke Katalog Perpustakaan</span>
            </a>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="downloadBarcodePng('ticket-page-barcode-box', 'barcode-{{ $ticket->reservation_code }}.png')"
                    class="btn-primary text-xs flex items-center gap-1.5 font-extrabold px-3.5 py-2 rounded-lg cursor-pointer shadow-2xs"
                >
                    <x-app-icon name="arrow-down-tray" class="w-4 h-4 text-white" />
                    <span>Unduh Gambar Barcode (PNG)</span>
                </button>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-ghost bg-white border border-slate-300 hover:border-brand text-xs flex items-center gap-1.5 font-extrabold px-3.5 py-2 rounded-lg cursor-pointer shadow-2xs"
                >
                    <x-app-icon name="printer" class="w-4 h-4 text-navy" />
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        {{-- Kartu Tiket Sirkulasi Resmi --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:border-none print:shadow-none print:m-0">
            {{-- Header Tiket --}}
            <div class="p-5 sm:p-6 bg-gradient-to-r from-navy via-navy to-brand-deep text-white flex items-center justify-between gap-4 border-b border-navy/40">
                <div class="flex items-center gap-3">
                    <picture>
                        <source srcset="{{ asset('images/logo.webp') }}" type="image/webp">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo SMP Negeri 4 Cepu" class="w-11 h-11 object-contain drop-shadow" />
                    </picture>
                    <div>
                        <h1 class="text-sm sm:text-base font-black tracking-tight leading-tight">SMP NEGERI 4 CEPU</h1>
                        <p class="text-[11px] text-slate-200 font-medium">Unit Layanan Perpustakaan &amp; Sirkulasi Fisik</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[10px] uppercase font-extrabold tracking-wider text-slate-200 block leading-tight">Kode Sirkulasi</span>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-lg bg-white/10 border border-white/20 font-mono font-black text-sm sm:text-base text-amber-300 tracking-wider shadow-2xs">
                        {{ $ticket->reservation_code }}
                    </span>
                </div>
            </div>

            {{-- Status Banner --}}
            <div class="px-5 sm:px-6 py-3 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2
                @if($ticket->status === 'menunggu') bg-amber-50/70 text-amber-900
                @elseif($ticket->status === 'diambil') bg-blue-50/70 text-blue-900
                @elseif($ticket->status === 'selesai') bg-emerald-50/70 text-emerald-900
                @else bg-slate-50 text-slate-700
                @endif"
            >
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full
                        @if($ticket->status === 'menunggu') bg-amber-500 animate-pulse
                        @elseif($ticket->status === 'diambil') bg-blue-600
                        @elseif($ticket->status === 'selesai') bg-emerald-600
                        @else bg-slate-400
                        @endif">
                    </span>
                    <span class="text-xs font-black uppercase tracking-wide">Status: {{ $ticket->status_label }}</span>
                </div>

                @if($ticket->is_overdue)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-extrabold bg-rose-600 text-white">
                        Terlambat {{ $ticket->days_late }} Hari
                    </span>
                @endif
            </div>

            {{-- Barcode 1D & QR Code 2D --}}
            <div class="p-6 bg-slate-50/60 border-b border-slate-200 text-center space-y-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs inline-block max-w-full">
                    <div id="ticket-page-barcode-box" class="max-w-[360px] mx-auto overflow-hidden">
                        {!! $ticket->barcode_svg !!}
                    </div>
                    <p class="text-[11px] text-ink-mute font-mono font-bold mt-1 tracking-wider">
                        *{{ $ticket->reservation_code }}*
                    </p>
                </div>

                {{-- Tombol Aksi Unduh Barcode --}}
                <div class="flex flex-wrap items-center justify-center gap-2 print:hidden">
                    <button
                        type="button"
                        onclick="downloadBarcodePng('ticket-page-barcode-box', 'barcode-{{ $ticket->reservation_code }}.png')"
                        class="btn-primary text-xs flex items-center gap-1.5 font-extrabold px-3.5 py-2 rounded-lg cursor-pointer shadow-2xs"
                    >
                        <x-app-icon name="arrow-down-tray" class="w-3.5 h-3.5 text-white" />
                        <span>Unduh Barcode (PNG)</span>
                    </button>
                    <a
                        href="{{ route('library.ticket.download-barcode', $ticket->reservation_code) }}"
                        download="barcode-{{ $ticket->reservation_code }}.svg"
                        class="btn-ghost bg-white border border-slate-300 hover:border-brand text-xs font-bold px-3.5 py-2 rounded-lg inline-flex items-center gap-1.5"
                    >
                        <x-app-icon name="arrow-down-tray" class="w-3.5 h-3.5 text-navy" />
                        <span>Unduh Berkas SVG</span>
                    </a>
                </div>

                <p class="text-xs text-ink-mute max-w-md mx-auto">
                    Tunjukkan barcode di atas kepada staf perpustakaan untuk proses serah terima atau pengembalian buku fisik. Simpan ke galeri ponsel Anda agar tidak hilang.
                </p>
            </div>

            {{-- Detail Rincian Tiket --}}
            <div class="p-6 space-y-5">
                {{-- Data Buku --}}
                <div class="rounded-xl border border-slate-200 p-4 bg-white">
                    <h2 class="text-xs font-bold text-ink-mute uppercase tracking-wider mb-2">Informasi Buku</h2>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between gap-4 py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Judul Buku:</span>
                            <strong class="text-navy text-right font-bold">{{ $ticket->book?->title ?? 'Buku Perpustakaan' }}</strong>
                        </div>
                        <div class="flex justify-between gap-4 py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Penulis:</span>
                            <span class="text-slate-800 text-right">{{ $ticket->book?->author ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-4 py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Penerbit / Tahun:</span>
                            <span class="text-slate-800 text-right">{{ $ticket->book?->publisher ?? '-' }} ({{ $ticket->book?->published_year ?? '-' }})</span>
                        </div>
                        @if($ticket->book?->isbn)
                            <div class="flex justify-between gap-4 py-1">
                                <span class="text-ink-mute">ISBN:</span>
                                <span class="text-slate-800 font-mono text-right">{{ $ticket->book->isbn }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Data Peminjam & Jadwal --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-xl border border-slate-200 p-4 bg-white space-y-2">
                        <h2 class="font-bold text-ink-mute uppercase tracking-wider mb-2">Identitas Siswa</h2>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Nama:</span>
                            <strong class="text-navy font-bold">{{ $ticket->borrower_name }}</strong>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Kelas:</span>
                            <strong class="text-navy font-bold">{{ $ticket->borrower_class }}</strong>
                        </div>
                        @if($ticket->borrower_nisn)
                            <div class="flex justify-between py-1">
                                <span class="text-ink-mute">NISN:</span>
                                <span class="font-mono text-slate-800">{{ $ticket->borrower_nisn }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 bg-white space-y-2">
                        <h2 class="font-bold text-ink-mute uppercase tracking-wider mb-2">Waktu Sirkulasi</h2>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Rencana Ambil:</span>
                            <strong class="text-emerald-700 font-bold">{{ $ticket->pickup_date ? $ticket->pickup_date->format('d M Y') : '-' }}</strong>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-ink-mute">Batas Ambil:</span>
                            <span class="text-slate-700">{{ $ticket->pickup_deadline ? $ticket->pickup_deadline->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-ink-mute">Tenggat Kembali:</span>
                            <strong class="text-brand font-black">{{ $ticket->due_date ? $ticket->due_date->format('d M Y') : '7 hari setelah diambil' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Ketentuan Peminjaman --}}
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-ink-mute space-y-1">
                    <strong class="text-navy block font-bold mb-1">Tata Tertib Peminjaman:</strong>
                    <p>1. Tunjukkan barcode ini kepada petugas di Ruang Perpustakaan SMPN 4 Cepu saat mengambil buku.</p>
                    <p>2. Buku wajib dikembalikan selambat-lambatnya pada tanggal tenggat pengembalian dalam kondisi rapi dan utuh.</p>
                    <p>3. Jika membutuhkan perpanjangan, silakan hubungi petugas perpustakaan sebelum tanggal jatuh tempo.</p>
                </div>
            </div>

            {{-- Footer Aksi --}}
            <div class="p-5 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 print:hidden">
                <a
                    href="{{ route('library') }}"
                    class="text-xs font-bold text-brand hover:underline"
                >
                    &larr; Lihat Buku Lainnya
                </a>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="navigator.clipboard.writeText('{{ $ticket->reservation_code }}').then(() => alert('Kode tiket berhasil disalin!'))"
                        class="btn-ghost bg-white border border-slate-300 text-xs px-3 py-2 rounded-lg font-bold cursor-pointer"
                    >
                        Salin Kode
                    </button>
                    <button
                        type="button"
                        onclick="window.print()"
                        class="btn-primary text-xs px-4 py-2 font-black shadow-2xs cursor-pointer"
                    >
                        Cetak Tiket
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadBarcodePng(containerId, filename) {
            const box = document.getElementById(containerId);
            if (!box) return;
            const svg = box.querySelector('svg');
            if (!svg) return;

            const xml = new XMLSerializer().serializeToString(svg);
            const svgBlob = new Blob([xml], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            const img = new Image();

            img.onload = function () {
                const canvas = document.createElement('canvas');
                const scale = 2;
                const w = (svg.viewBox.baseVal.width || 360);
                const h = (svg.viewBox.baseVal.height || 90);
                canvas.width = w * scale;
                canvas.height = h * scale;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);

                const link = document.createElement('a');
                link.download = filename || 'barcode.png';
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };
            img.src = url;
        }
    </script>
</x-layouts.app>

