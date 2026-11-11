<div
    class="space-y-8"
    x-effect="
        const modalActive = $wire.showPdfModal || $wire.showDetailModal || $wire.showBorrowModal || $wire.showTicketModal;
        document.documentElement.classList.toggle('overflow-hidden', modalActive);
        document.body.classList.toggle('overflow-hidden', modalActive);
    "
    @keydown.window="if ($event.key === 'Escape' && $wire.showPdfModal) { $wire.closePdfModal(); } if ($event.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') { $event.preventDefault(); const s = document.getElementById('library-search-input'); if (s) s.focus(); }"
>
    {{-- 1. Hero Ringkasan Statistik Koleksi Real-Time --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="card p-5 sm:p-6 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs group">
            <div class="w-13 h-13 rounded-2xl bg-sky-soft text-brand flex items-center justify-center shrink-0 border border-brand/20 group-hover:scale-105 transition-transform">
                <x-app-icon name="book" class="w-7 h-7" />
            </div>
            <div>
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Katalog Judul</span>
                <strong class="text-2xl sm:text-3xl font-black text-navy leading-none">{{ $stats['total_titles'] }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Judul Koleksi Terdaftar</span>
            </div>
        </div>

        <div class="card p-5 sm:p-6 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs group">
            <div class="w-13 h-13 rounded-2xl bg-purple-50 text-grape flex items-center justify-center shrink-0 border border-purple-200 group-hover:scale-105 transition-transform">
                <x-app-icon name="academic-cap" class="w-7 h-7" />
            </div>
            <div>
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Koleksi Digital</span>
                <strong class="text-2xl sm:text-3xl font-black text-grape leading-none">{{ $stats['total_ebooks'] }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">E-Book & Modul Siap Baca</span>
            </div>
        </div>

        <div class="card p-5 sm:p-6 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs sm:col-span-2 lg:col-span-1 group">
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200 group-hover:scale-105 transition-transform">
                <x-app-icon name="check-circle" class="w-7 h-7" />
            </div>
            <div>
                <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Ketersediaan Fisik</span>
                <strong class="text-2xl sm:text-3xl font-black text-emerald-700 leading-none">{{ $stats['total_physical'] }}</strong>
                <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Eksemplar Buku di Ruang Perpustakaan</span>
            </div>
        </div>
    </div>

    {{-- Banner Unit Pengelola Perpustakaan Resmi --}}
    <div class="card p-5 sm:p-6 bg-gradient-to-r from-sky-soft/70 via-white to-cream/60 border border-sky-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-brand text-white flex items-center justify-center shrink-0 shadow-xs">
                <x-app-icon name="building" class="w-6 h-6" />
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-navy">Layanan Perpustakaan Terpadu SMP Negeri 4 Cepu</h3>
                <p class="text-xs text-ink-mute mt-0.5">Mendukung gerakan literasi sekolah, pembelajaran Kurikulum Merdeka, dan akses buku daring.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 text-xs">
            <div class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[10px] uppercase font-bold text-ink-mute block">Penanggung Jawab</span>
                <span class="font-extrabold text-navy">Laeli Hasanah, S.Pd.</span>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs">
                <span class="text-[10px] uppercase font-bold text-ink-mute block">Tenaga Teknis</span>
                <span class="font-extrabold text-brand">Yaatun, A.Ma.Pust.</span>
            </div>

            @auth
                <button
                    type="button"
                    wire:click="openHistoryModal"
                    class="btn-primary text-xs flex items-center gap-1.5 font-extrabold px-3.5 py-2 rounded-xl shadow-2xs cursor-pointer min-h-[40px]"
                >
                    <x-app-icon name="bookmark" class="w-4 h-4 text-white" />
                    <span>Histori Pinjam Saya</span>
                    @if ($this->myBorrowingHistory->isNotEmpty())
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] font-black bg-white text-navy ml-0.5">
                            {{ $this->myBorrowingHistory->count() }}
                        </span>
                    @endif
                </button>
            @endauth

            <button
                type="button"
                wire:click="openCheckTicketModal"
                class="btn-ghost bg-white border border-slate-300 hover:border-brand text-xs flex items-center gap-1.5 font-extrabold px-3.5 py-2 rounded-xl shadow-2xs cursor-pointer min-h-[40px]"
            >
                <x-app-icon name="qr-code" class="w-4 h-4 text-navy" />
                <span>Lacak Tiket &amp; Barcode</span>
            </button>
        </div>
    </div>

    {{-- 2. Interactive Search, Pill Filters, & Controls (Real-time Livewire) --}}
    <div class="card p-5 sm:p-6 bg-white space-y-5 shadow-xs">
        {{-- Search Bar & Sort Dropdown --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <div class="md:col-span-8 relative">
                <input
                    type="text"
                    id="library-search-input"
                    wire:model.live.debounce.300ms="search"
                    aria-label="Cari koleksi buku perpustakaan"
                    placeholder="Cari judul buku, nama pengarang, penerbit, atau nomor ISBN..."
                    class="field pl-11 pr-20 !py-3 text-sm"
                >
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-mute pointer-events-none">
                    <x-app-icon name="search" class="w-5 h-5" />
                </div>
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                    @if (!empty($search))
                        <button
                            type="button"
                            wire:click="$set('search', '')"
                            class="text-ink-mute hover:text-navy p-1 rounded-md"
                            title="Hapus pencarian"
                        >
                            <x-app-icon name="x" class="w-4 h-4" />
                        </button>
                    @else
                        <span class="hidden sm:inline-block text-[10px] font-extrabold text-ink-mute bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded shadow-2xs">
                            /
                        </span>
                    @endif
                </div>
            </div>

            <div class="md:col-span-4 flex items-center gap-2">
                <div class="relative w-full">
                    <select
                        wire:model.live="sort"
                        aria-label="Urutkan koleksi buku"
                        class="field !py-3 text-xs font-bold"
                    >
                        <option value="latest">Terbaru & Rekomendasi</option>
                        <option value="popular">Paling Sering Dibaca</option>
                        <option value="title_asc">Judul Buku (A - Z)</option>
                        <option value="year_desc">Tahun Terbit Terbaru</option>
                    </select>
                </div>

                @if (!empty($search) || $category !== 'semua' || $format !== 'all' || $sort !== 'latest')
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="btn-ghost shrink-0 px-3.5 !py-3 text-xs font-extrabold min-h-[44px]"
                        title="Reset semua filter"
                    >
                        <x-app-icon name="x" class="w-4 h-4 text-brand" />
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Pill Filters Kategori Koleksi dengan Badge Jumlah --}}
        <div class="space-y-3 pt-2 border-t border-sun-soft/60">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-extrabold text-ink-mute uppercase tracking-wider">Kategori Koleksi</span>
                {{-- Indikator Loading Livewire --}}
                <div wire:loading.delay class="text-xs font-bold text-brand flex items-center gap-1.5 animate-pulse">
                    <svg class="animate-spin h-3.5 w-3.5 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memperbarui katalog...</span>
                </div>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach ($categoriesList as $key => $label)
                    <button
                        type="button"
                        wire:click="setCategory('{{ $key }}')"
                        class="{{ $category === $key ? 'chip-active' : 'chip' }} shrink-0 !text-xs !py-2 flex items-center gap-1.5 min-h-[44px]"
                    >
                        <span>{{ $label }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $category === $key ? 'bg-white/20 text-white font-black' : 'bg-slate-200/80 text-ink-mute font-bold' }}">
                            {{ $categoryCounts[$key] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Filter Format Koleksi: Semua, E-Book Digital, Stok Fisik --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-sun-soft/60">
            <div class="flex items-center gap-1.5 bg-cream/70 p-1 rounded-xl border border-sun-soft text-xs font-extrabold">
                <button
                    type="button"
                    wire:click="setFormat('all')"
                    class="px-3.5 py-2 rounded-lg transition-all min-h-[40px] {{ $format === 'all' ? 'bg-white text-brand shadow-2xs font-black' : 'text-ink-mute hover:text-navy' }}"
                >
                    Semua Format
                </button>
                <button
                    type="button"
                    wire:click="setFormat('digital')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-1.5 transition-all min-h-[40px] {{ $format === 'digital' ? 'bg-white text-brand shadow-2xs font-black' : 'text-ink-mute hover:text-navy' }}"
                >
                    <x-app-icon name="document" class="w-3.5 h-3.5 text-grape" />
                    <span>E-Book (Baca Online)</span>
                </button>
                <button
                    type="button"
                    wire:click="setFormat('physical')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-1.5 transition-all min-h-[40px] {{ $format === 'physical' ? 'bg-white text-brand shadow-2xs font-black' : 'text-ink-mute hover:text-navy' }}"
                >
                    <x-app-icon name="check-circle" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Tersedia di Rak Fisik</span>
                </button>
            </div>

            <div class="text-xs text-ink-mute font-bold">
                Menampilkan <strong class="text-navy font-black">{{ $books->total() }}</strong> judul buku
            </div>
        </div>
    </div>

    {{-- 4. Grid Katalog Buku Modern (Rasio Cover 2:3) --}}
    @if ($books->isEmpty())
        <x-card class="text-center py-16 space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-soft text-brand flex items-center justify-center">
                <x-app-icon name="book" class="w-8 h-8" />
            </div>
            @if (($stats['total_titles'] ?? 0) === 0 && empty($search) && $category === 'semua' && $format === 'all')
                <div class="space-y-1">
                    <h3 class="font-extrabold text-navy text-lg">Belum Ada Koleksi Buku Terdaftar</h3>
                    <p class="text-xs sm:text-sm text-ink-soft max-w-md mx-auto">
                        Katalog buku paket dan e-book digital akan tampil di halaman ini setelah diunggah oleh pengelola perpustakaan melalui Panel Admin.
                    </p>
                </div>
            @else
                <div class="space-y-1">
                    <h3 class="font-extrabold text-navy text-lg">Koleksi Buku Tidak Ditemukan</h3>
                    <p class="text-xs sm:text-sm text-ink-soft max-w-md mx-auto">
                        Tidak ada koleksi buku yang sesuai dengan kata kunci pencarian atau filter yang Anda pilih. Silakan coba kata kunci lain.
                    </p>
                </div>
                <div>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="btn-primary text-xs min-h-[44px] px-5"
                    >
                        Tampilkan Semua Koleksi
                    </button>
                </div>
            @endif
        </x-card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 sm:gap-6">
            @foreach ($books as $b)
                <div
                    wire:key="book-card-{{ $b->id }}"
                    class="card p-0 overflow-hidden flex flex-col justify-between hover:border-brand focus-within:ring-2 focus-within:ring-brand transition-all group shadow-xs bg-white"
                >
                    <div>
                        {{-- Cover Area Rasio 2:3 --}}
                        <div
                            wire:click="openDetail({{ $b->id }})"
                            class="relative w-full aspect-[2/3] bg-sky-soft overflow-hidden border-b border-sun-soft/60 cursor-pointer"
                            title="Lihat detail {{ $b->title }}"
                        >
                            <img
                                src="{{ $b->cover_image }}"
                                alt="{{ $b->title }}"
                                loading="lazy"
                                decoding="async"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            >

                            {{-- Badge Kategori di Sudut Atas Kiri --}}
                            <div class="absolute top-2.5 left-2.5 z-10">
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-black tracking-wider uppercase bg-navy/85 text-white backdrop-blur-xs shadow-xs">
                                    {{ $b->category_label }}
                                </span>
                            </div>

                            {{-- Badge Unggulan Jika Ada --}}
                            @if ($b->is_featured)
                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-sun text-navy shadow-xs">
                                        <x-app-icon name="star" class="w-3 h-3" />
                                        <span>Pilihan</span>
                                    </span>
                                </div>
                            @endif

                            {{-- Badges Ketersediaan Format di Sudut Bawah --}}
                            <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1 pointer-events-none">
                                @if ($b->has_digital)
                                    <span class="inline-flex items-center gap-1 bg-grape text-white text-[10px] font-black px-2 py-0.5 rounded backdrop-blur-xs shadow-xs">
                                        <x-app-icon name="document" class="w-3 h-3" />
                                        <span>E-Book</span>
                                    </span>
                                @else
                                    <span></span>
                                @endif

                                @if ($b->physical_stock > 0)
                                    <span class="inline-flex items-center gap-1 bg-emerald-700 text-white text-[10px] font-black px-2 py-0.5 rounded backdrop-blur-xs shadow-xs">
                                        <x-app-icon name="check-circle" class="w-3 h-3" />
                                        <span>Rak: {{ $b->physical_stock }} Eks</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Metadata Buku --}}
                        <div class="p-4 sm:p-5 space-y-2">
                            <h3
                                wire:click="openDetail({{ $b->id }})"
                                class="font-extrabold text-navy text-[15px] sm:text-base leading-snug line-clamp-2 group-hover:text-brand transition-colors cursor-pointer"
                            >
                                {{ $b->title }}
                            </h3>

                            <p class="text-xs text-ink-soft line-clamp-1 font-semibold">
                                {{ $b->author ?? 'Penulis Resmi' }}
                            </p>

                            <div class="flex items-center justify-between text-[11px] text-ink-mute pt-1 border-t border-sun-soft/50 font-bold">
                                <span>Tahun {{ $b->published_year ?? '-' }}</span>
                                <span class="flex items-center gap-1 text-brand">
                                    <x-app-icon name="chevron-right" class="w-3 h-3" />
                                    <span>{{ $b->views_count }} dibaca</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="p-4 sm:p-5 pt-0 space-y-2">
                        @if ($b->has_digital)
                            <button
                                type="button"
                                wire:click="readEbook({{ $b->id }})"
                                class="btn-primary w-full text-xs font-extrabold !py-2.5 shadow-2xs flex items-center justify-center gap-1.5 min-h-[44px]"
                            >
                                <x-app-icon name="book" class="w-4 h-4 text-navy" />
                                <span>Baca E-Book Sekarang</span>
                            </button>
                            <button
                                type="button"
                                wire:click="openDetail({{ $b->id }})"
                                class="btn-ghost w-full text-xs font-bold !py-2 hover:bg-sky-soft flex items-center justify-center gap-1.5 min-h-[44px]"
                            >
                                <span>Detail Bibliografi</span>
                            </button>
                        @else
                            @if ($b->physical_stock > 0)
                                <button
                                    type="button"
                                    wire:click="openBorrowModal({{ $b->id }})"
                                    class="btn-primary w-full text-xs font-extrabold !py-2.5 shadow-2xs flex items-center justify-center gap-1.5 !bg-emerald-600 hover:!bg-emerald-700 !text-white min-h-[44px]"
                                >
                                    <x-app-icon name="bookmark" class="w-4 h-4 text-white" />
                                    <span>Pinjam Buku Fisik</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="btn-ghost w-full text-xs font-bold !py-2.5 bg-slate-100 text-ink-mute flex items-center justify-center gap-1.5 min-h-[44px] cursor-not-allowed opacity-75"
                                >
                                    <x-app-icon name="x-circle" class="w-4 h-4 text-rose-500" />
                                    <span>Stok Habis Dipinjam</span>
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="openDetail({{ $b->id }})"
                                class="btn-ghost w-full text-xs font-bold !py-2 hover:bg-sky-soft flex items-center justify-center gap-1.5 min-h-[44px]"
                            >
                                <span>Detail & Info Pinjam</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginasi Modern & Minimalis --}}
        @if ($books->hasPages())
            <div class="pt-6">
                {{ $books->links('livewire.public.library-pagination') }}
            </div>
        @endif
    @endif

    {{-- Panduan Tata Tertib Sirkulasi Sekolah --}}
    <div class="card p-6 bg-gradient-to-r from-sky-soft to-cream border-2 border-brand/20 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-6 shadow-xs">
        <div class="space-y-1 text-center md:text-left">
            <span class="text-xs font-black text-brand uppercase tracking-wider">Tata Tertib Ruang Baca</span>
            <h3 class="text-lg font-extrabold text-navy">Peminjaman Buku Fisik Perpustakaan SMPN 4 Cepu</h3>
            <p class="text-xs sm:text-sm text-ink-soft max-w-2xl font-medium leading-relaxed">
                Setiap peserta didik berhak membaca di tempat atau meminjam buku paket dan buku literasi dengan menunjukkan Kartu Pelajar kepada petugas sirkulasi di ruang perpustakaan sekolah.
            </p>
        </div>
        <div class="shrink-0 text-center">
            <span class="inline-block bg-white text-navy border-2 border-sun-soft px-4 py-2.5 rounded-xl text-xs font-extrabold shadow-2xs">
                Jam Layanan: 07.30 - 14.00 WIB
            </span>
        </div>
    </div>

    {{-- 5. Modal Detail Bibliografi Buku --}}
    @if ($showDetailModal && $selectedBook)
        <template x-teleport="body">
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="detail-modal-title"
                @keydown.escape.window="$wire.closeDetail()"
                class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 bg-navy/85 backdrop-blur-md overflow-y-auto w-full h-full m-0"
            >
                <div
                    @click.outside="$wire.closeDetail()"
                    class="card max-w-2xl w-full p-0 overflow-hidden shadow-2xl max-h-[90vh] flex flex-col bg-white my-auto animate-fade-in relative z-10"
                >
                {{-- Modal Header --}}
                <div class="p-5 bg-sky-soft border-b-2 border-sun-soft flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-black text-brand uppercase tracking-wider">{{ $selectedBook->category_label }}</span>
                            <span class="text-ink-mute">·</span>
                            <span class="text-xs text-ink-mute font-bold">Tahun {{ $selectedBook->published_year ?? '-' }}</span>
                        </div>
                        <h2 id="detail-modal-title" class="text-lg sm:text-xl font-extrabold text-navy leading-snug">
                            {{ $selectedBook->title }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        wire:click="closeDetail"
                        aria-label="Tutup jendela modal"
                        class="p-2 min-h-[44px] min-w-[44px] flex items-center justify-center rounded-xl text-ink-mute hover:text-navy hover:bg-white transition-colors"
                    >
                        <x-app-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-5 grow">
                    <div class="flex flex-col sm:flex-row gap-5 items-start">
                        {{-- Cover Image --}}
                        <div class="w-32 sm:w-40 aspect-[2/3] rounded-xl bg-sky-soft overflow-hidden border border-sun-soft shrink-0 shadow-xs">
                            <img
                                src="{{ $selectedBook->cover_image }}"
                                alt="{{ $selectedBook->title }}"
                                class="w-full h-full object-cover"
                            >
                        </div>

                        {{-- Metadata Grid --}}
                        <div class="grid grid-cols-2 gap-3 text-xs flex-1 w-full">
                            <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                <span class="text-[11px] text-ink-mute block font-bold">Penulis / Pengarang</span>
                                <strong class="font-extrabold text-navy text-sm">{{ $selectedBook->author ?? 'Penyusun Resmi' }}</strong>
                            </div>
                            <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                <span class="text-[11px] text-ink-mute block font-bold">Penerbit</span>
                                <strong class="font-extrabold text-navy text-sm">{{ $selectedBook->publisher ?? '-' }}</strong>
                            </div>
                            <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                <span class="text-[11px] text-ink-mute block font-bold">Nomor ISBN</span>
                                <strong class="font-extrabold text-navy text-sm">{{ $selectedBook->isbn ?? '-' }}</strong>
                            </div>
                            <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                <span class="text-[11px] text-ink-mute block font-bold">Halaman & Bahasa</span>
                                <strong class="font-extrabold text-navy text-sm">
                                    {{ $selectedBook->page_count ? $selectedBook->page_count . ' Hlm · ' : '' }}{{ strtoupper($selectedBook->language ?? 'id') }}
                                </strong>
                            </div>
                            <div class="bg-sky-soft p-3.5 rounded-xl border border-brand/20 col-span-2 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] text-brand font-black block uppercase tracking-wider">Ketersediaan di Perpustakaan SMPN 4 Cepu</span>
                                    <strong class="font-black text-navy text-base">
                                        {{ $selectedBook->physical_stock > 0 ? 'Tersedia ' . $selectedBook->physical_stock . ' Eksemplar Fisik' : 'Koleksi Digital / Arsip' }}
                                    </strong>
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] text-ink-mute block font-bold">Total Pembaca</span>
                                    <strong class="font-black text-brand text-sm">{{ $selectedBook->views_count }} Kali</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sinopsis / Ringkasan --}}
                    <div class="space-y-2 pt-2 border-t border-sun-soft/60">
                        <span class="text-xs font-black text-navy uppercase tracking-wider block">Sinopsis & Deskripsi Materi</span>
                        <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium bg-cream/40 p-4 rounded-xl border border-sun-soft/50">
                            {{ $selectedBook->description ?? 'Deskripsi untuk koleksi buku ini belum ditambahkan ke dalam basis data katalog.' }}
                        </p>
                    </div>

                    {{-- Kutipan Daftar Pustaka & Bagikan Tautan --}}
                    @php
                        $citationText = ($selectedBook->author ?? 'Penyusun Resmi') . '. (' . ($selectedBook->published_year ?? date('Y')) . '). ' . $selectedBook->title . '. ' . ($selectedBook->publisher ?? 'SMP Negeri 4 Cepu') . '.';
                        $shareUrl = route('library', ['buku' => $selectedBook->slug]);
                    @endphp
                    <div
                        x-data="{ copiedCite: false, copiedLink: false }"
                        class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 space-y-2.5"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-[11px] font-black text-navy uppercase tracking-wider flex items-center gap-1.5">
                                <x-app-icon name="academic-cap" class="w-3.5 h-3.5 text-brand" />
                                <span>Kutipan Daftar Pustaka</span>
                            </span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="navigator.clipboard.writeText(@js($citationText)); copiedCite = true; setTimeout(() => copiedCite = false, 2000)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-white border border-slate-200 text-navy hover:border-brand hover:text-brand transition-colors shadow-2xs cursor-pointer"
                                >
                                    <span x-show="!copiedCite">Salin Kutipan</span>
                                    <span x-show="copiedCite" x-cloak class="text-emerald-700">Kutipan Tersalin!</span>
                                </button>
                                <button
                                    type="button"
                                    @click="navigator.clipboard.writeText(@js($shareUrl)); copiedLink = true; setTimeout(() => copiedLink = false, 2000)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-white border border-slate-200 text-navy hover:border-brand hover:text-brand transition-colors shadow-2xs cursor-pointer"
                                >
                                    <span x-show="!copiedLink">Salin Tautan Buku</span>
                                    <span x-show="copiedLink" x-cloak class="text-emerald-700">Tautan Tersalin!</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-ink-soft font-mono bg-white p-2.5 rounded-lg border border-slate-200/70 select-all">
                            {{ $citationText }}
                        </p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 sm:p-5 bg-sky-soft/50 border-t-2 border-sun-soft flex flex-wrap items-center justify-between gap-3">
                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="btn-ghost text-xs min-h-[44px]"
                    >
                        Tutup
                    </button>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($selectedBook->physical_stock > 0)
                            <button
                                type="button"
                                wire:click="openBorrowModal({{ $selectedBook->id }})"
                                class="btn-primary text-xs flex items-center gap-2 shadow-2xs font-black min-h-[44px] !bg-emerald-600 hover:!bg-emerald-700 !text-white"
                            >
                                <x-app-icon name="bookmark" class="w-4 h-4 text-white" />
                                <span>Pinjam Buku Fisik (Stok: {{ $selectedBook->physical_stock }})</span>
                            </button>
                        @endif

                        @if ($selectedBook->has_digital)
                            <a
                                href="{{ $selectedBook->pdf_file }}"
                                download="{{ $selectedBook->slug }}.pdf"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-ghost bg-white border border-slate-200 hover:border-brand text-xs flex items-center gap-1.5 font-extrabold min-h-[44px] px-4"
                            >
                                <x-app-icon name="download" class="w-4 h-4 text-brand" />
                                <span>Unduh PDF</span>
                            </a>
                            <button
                                type="button"
                                wire:click="readEbook({{ $selectedBook->id }})"
                                class="btn-primary text-xs flex items-center gap-2 shadow-2xs font-black min-h-[44px]"
                            >
                                <x-app-icon name="book" class="w-4 h-4 text-navy" />
                                <span>Baca E-Book</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
@endif

    {{-- 6. In-Browser Modern E-Book Reader Modal (100% Fullscreen, Clean & Minimalis) --}}
    @if ($showPdfModal && $activePdfUrl)
        <template x-teleport="body">
            <div
                id="pdf-reader-modal-root"
                role="dialog"
                aria-modal="true"
                aria-labelledby="reader-header-title"
                class="fixed inset-0 z-[999999] w-full h-full flex flex-col bg-slate-950 text-white select-none overflow-hidden m-0 p-0"
                @keydown.window.escape="$wire.closePdfModal()"
            >
                {{-- Header Minimalis: Hanya Judul, Tombol Buka Tab Baru, dan Tombol Close --}}
                <header class="h-14 sm:h-16 px-4 sm:px-6 bg-slate-900 border-b border-slate-800 text-white flex items-center justify-between gap-4 shrink-0 shadow-md z-10 m-0">
                    {{-- Kiri: Judul Buku & Kategori --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-sun text-navy flex items-center justify-center shrink-0">
                            <x-app-icon name="book" class="w-4 h-4" />
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-black uppercase tracking-wider text-sun block leading-tight">
                                {{ $activePdfCategory ?? 'Perpustakaan Digital' }}
                            </span>
                            <h2 id="reader-header-title" class="text-xs sm:text-sm font-extrabold truncate text-white max-w-sm sm:max-w-xl lg:max-w-3xl leading-tight">
                                {{ $activePdfTitle }}
                            </h2>
                        </div>
                    </div>

                    {{-- Kanan: HANYA Tombol Buka Tab Baru & Tombol Close --}}
                    <div class="flex items-center gap-2.5 shrink-0">
                        {{-- Tombol Buka Tab Baru --}}
                        <a
                            href="{{ $activePdfUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn-outline !py-2 !px-3 text-xs inline-flex items-center gap-1.5 border-slate-700 bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white transition-colors min-h-[40px]"
                            title="Buka dokumen PDF di tab baru"
                        >
                            <svg class="w-4 h-4 text-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <span class="hidden sm:inline font-bold">Buka Tab Baru</span>
                        </a>

                        {{-- Tombol Close --}}
                        <button
                            type="button"
                            wire:click="closePdfModal"
                            class="p-2 min-h-[40px] min-w-[40px] flex items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition-colors"
                            title="Tutup Pembaca (Esc)"
                            aria-label="Tutup"
                        >
                            <x-app-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>
                </header>

                {{-- Area PDF Langsung Full 100% Tanpa Space Kosong / Padding --}}
                <main class="w-full flex-1 min-h-0 overflow-hidden bg-slate-900 p-0 m-0 relative">
                    <iframe
                        src="{{ $activePdfUrl }}#toolbar=1&navpanes=0&view=FitH"
                        class="absolute inset-0 w-full h-full border-0 block p-0 m-0"
                        title="{{ $activePdfTitle }}"
                        style="width: 100%; height: 100%; border: none;"
                    ></iframe>
                </main>
            </div>
        </template>
    @endif

    {{-- 7. Modal Formulir Peminjaman Buku Fisik --}}
    @if ($showBorrowModal && $borrowBook)
        <template x-teleport="body">
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="borrow-modal-title"
                @keydown.escape.window="$wire.closeBorrowModal()"
                class="fixed inset-0 z-[99999] flex items-center justify-center p-4 sm:p-6 bg-navy/85 backdrop-blur-md overflow-y-auto w-full h-full m-0"
            >
                <div
                    @click.outside="$wire.closeBorrowModal()"
                    class="card max-w-xl w-full p-0 overflow-hidden shadow-2xl max-h-[92vh] flex flex-col bg-white my-auto animate-fade-in relative z-10"
                >
                    {{-- Header Modal --}}
                    <div class="p-5 bg-sky-soft border-b-2 border-sun-soft flex items-start justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-brand block">
                                Layanan Sirkulasi Fisik
                            </span>
                            <h2 id="borrow-modal-title" class="text-base sm:text-lg font-extrabold text-navy leading-snug">
                                Formulir Peminjaman Buku Fisik
                            </h2>
                        </div>
                        <button
                            type="button"
                            wire:click="closeBorrowModal"
                            aria-label="Tutup formulir"
                            class="p-2 min-h-[40px] min-w-[40px] flex items-center justify-center rounded-xl text-ink-mute hover:text-navy hover:bg-white transition-colors"
                        >
                            <x-app-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Body Form --}}
                    <div class="p-5 sm:p-6 overflow-y-auto space-y-4 grow">
                        {{-- Ringkasan Buku yang Dipilih --}}
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3">
                            <div class="w-12 h-16 rounded-lg bg-navy/10 overflow-hidden shrink-0 border border-slate-200 shadow-2xs">
                                <img
                                    src="{{ $borrowBook->cover_image }}"
                                    alt="{{ $borrowBook->title }}"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-bold text-ink-mute uppercase tracking-wider block">Buku Terpilih</span>
                                <h3 class="text-xs sm:text-sm font-bold text-navy truncate" title="{{ $borrowBook->title }}">
                                    {{ $borrowBook->title }}
                                </h3>
                                <p class="text-[11px] text-ink-soft truncate">
                                    {{ $borrowBook->author ?? 'Penyusun Resmi' }} &bull; Tersedia: <strong class="text-emerald-700">{{ $borrowBook->physical_stock }} Eks</strong>
                                </p>
                            </div>
                        </div>

                        {{-- Pesan Error Jika Ada --}}
                        @if ($borrowErrorMessage)
                            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-2">
                                <x-app-icon name="exclamation-circle" class="w-4 h-4 shrink-0 text-rose-600" />
                                <span>{{ $borrowErrorMessage }}</span>
                            </div>
                        @endif

                        {{-- Form Inputs --}}
                        <div class="space-y-3.5 text-xs">
                            <div>
                                <label for="borrower-name" class="block font-bold text-navy mb-1">
                                    Nama Lengkap Siswa / Peminjam <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="borrower-name"
                                    type="text"
                                    wire:model="borrowerName"
                                    placeholder="Contoh: Muhammad Rizky Pratama"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none text-navy font-medium"
                                />
                                @error('borrowerName')
                                    <span class="text-[11px] text-rose-600 font-bold block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="borrower-nisn" class="block font-bold text-navy mb-1">
                                        NISN (Nomor Induk Siswa)
                                    </label>
                                    <input
                                        id="borrower-nisn"
                                        type="text"
                                        wire:model="borrowerNisn"
                                        placeholder="10 digit NISN"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none text-navy font-medium"
                                    />
                                </div>
                                <div>
                                    <label for="borrower-class" class="block font-bold text-navy mb-1">
                                        Kelas / Rombel <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        id="borrower-class"
                                        type="text"
                                        wire:model="borrowerClass"
                                        placeholder="Contoh: 7A / 8B / 9C"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none text-navy font-medium"
                                    />
                                    @error('borrowerClass')
                                        <span class="text-[11px] text-rose-600 font-bold block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="pickup-date" class="block font-bold text-navy mb-1">
                                    Rencana Tanggal Pengambilan Buku <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    id="pickup-date"
                                    type="date"
                                    wire:model="pickupDate"
                                    min="{{ date('Y-m-d') }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none text-navy font-medium"
                                />
                                <span class="text-[11px] text-ink-mute block mt-1">Masa pinjam standar berlaku 7 hari kalender sejak buku diambil.</span>
                                @error('pickupDate')
                                    <span class="text-[11px] text-rose-600 font-bold block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="borrow-notes" class="block font-bold text-navy mb-1">
                                    Catatan Tambahan (Opsional)
                                </label>
                                <textarea
                                    id="borrow-notes"
                                    wire:model="borrowNotes"
                                    rows="2"
                                    placeholder="Tuliskan catatan khusus bila diperlukan..."
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none text-navy font-medium resize-none"
                                ></textarea>
                            </div>

                            {{-- Petunjuk Prosedur --}}
                            <div class="p-3 bg-sky-soft/60 border border-sky-100 rounded-xl text-[11px] text-navy leading-relaxed">
                                <strong>Petunjuk:</strong> Setelah mengajukan, sistem akan menerbitkan <strong>Tiket Digital Ber-Barcode</strong>. Tunjukkan tiket tersebut kepada petugas di Ruang Perpustakaan SMPN 4 Cepu saat mengambil buku fisik.
                            </div>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                        <button
                            type="button"
                            wire:click="closeBorrowModal"
                            class="btn-ghost text-xs min-h-[44px]"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="submitBorrow"
                            wire:loading.attr="disabled"
                            class="btn-primary text-xs font-black min-h-[44px] flex items-center gap-2 !bg-emerald-600 hover:!bg-emerald-700 !text-white shadow-2xs"
                        >
                            <x-app-icon name="check-circle" class="w-4 h-4 text-white" />
                            <span wire:loading.remove>Konfirmasi & Dapatkan Tiket Barcode</span>
                            <span wire:loading>Memproses Tiket...</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- 8. Modal Tiket Digital Ber-Barcode & QR Code --}}
    @if ($showTicketModal && $activeReservation)
        <template x-teleport="body">
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="ticket-modal-title"
                @keydown.escape.window="$wire.closeTicketModal()"
                class="fixed inset-0 z-[999999] flex items-center justify-center p-4 sm:p-6 bg-navy/90 backdrop-blur-md overflow-y-auto w-full h-full m-0"
            >
                <div
                    class="card max-w-lg w-full p-0 overflow-hidden shadow-2xl bg-white my-auto animate-fade-in relative z-10 border-2 border-sun-soft"
                >
                    {{-- Area Cetak Tiket --}}
                    <div id="printable-library-ticket" class="p-5 sm:p-6 bg-white space-y-4">
                        {{-- Header Kop Tiket --}}
                        <div class="flex items-center gap-3.5 pb-3.5 border-b-2 border-dashed border-slate-200">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="Logo SMP Negeri 4 Cepu"
                                class="w-12 h-12 object-contain shrink-0"
                            />
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-black uppercase tracking-wider text-brand block leading-tight">
                                    SMP NEGERI 4 CEPU
                                </span>
                                <h2 id="ticket-modal-title" class="text-sm sm:text-base font-black text-navy leading-tight">
                                    Tiket Bukti Peminjaman Buku Fisik
                                </h2>
                                <span class="text-[11px] text-ink-mute font-medium block">
                                    Unit Layanan Perpustakaan & Literasi Digital
                                </span>
                            </div>
                        </div>

                        {{-- Kode Tiket & Status Badge --}}
                        <div class="text-center py-2 bg-sky-soft/60 rounded-xl border border-sky-100 space-y-1">
                            <span class="text-[10px] font-bold text-ink-mute uppercase tracking-wider block">Kode Reservasi Sirkulasi</span>
                            <div class="text-xl sm:text-2xl font-black text-navy tracking-wider font-mono">
                                {{ $activeReservation->reservation_code }}
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                    &bull; Menunggu Pengambilan di Perpustakaan
                                </span>
                            </div>
                        </div>

                        {{-- Barcode Code 128 (SVG Vektor Murni) --}}
                        <div class="p-3 bg-white rounded-xl border border-slate-200 text-center shadow-2xs">
                            <div class="w-full max-w-[320px] mx-auto overflow-hidden">
                                {!! $activeReservation->barcode_svg !!}
                            </div>
                            <span class="text-[10px] text-ink-mute font-semibold block mt-1">
                                Scan Barcode di atas saat serah terima buku di meja sirkulasi
                            </span>
                        </div>

                        {{-- Rincian Peminjaman --}}
                        <div class="space-y-2 text-xs divide-y divide-slate-100 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                            <div class="flex justify-between py-1">
                                <span class="text-ink-mute font-medium">Judul Buku:</span>
                                <strong class="text-navy text-right max-w-[240px] truncate" title="{{ $activeReservation->book?->title }}">
                                    {{ $activeReservation->book?->title }}
                                </strong>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-ink-mute font-medium">Nama Siswa:</span>
                                <strong class="text-navy text-right">{{ $activeReservation->borrower_name }}</strong>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-ink-mute font-medium">Kelas / Rombel:</span>
                                <strong class="text-navy text-right">{{ $activeReservation->borrower_class }}</strong>
                            </div>
                            @if ($activeReservation->borrower_nisn)
                                <div class="flex justify-between py-1">
                                    <span class="text-ink-mute font-medium">NISN:</span>
                                    <strong class="text-navy text-right font-mono">{{ $activeReservation->borrower_nisn }}</strong>
                                </div>
                            @endif
                            <div class="flex justify-between py-1">
                                <span class="text-ink-mute font-medium">Rencana Ambil:</span>
                                <strong class="text-emerald-700 text-right">{{ $activeReservation->pickup_date ? $activeReservation->pickup_date->format('d M Y') : '-' }}</strong>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-ink-mute font-medium">Tenggat Pengembalian:</span>
                                <strong class="text-brand text-right">{{ $activeReservation->due_date ? $activeReservation->due_date->format('d M Y') : '7 hari setelah serah terima' }}</strong>
                            </div>
                        </div>

                        {{-- Petunjuk Singkat --}}
                        <p class="text-[11px] text-ink-mute text-center italic leading-tight">
                            Harap membawa tiket ini dalam bentuk cetak atau tangkapan layar di gawai saat mengambil buku di Ruang Perpustakaan SMPN 4 Cepu.
                        </p>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="window.print()"
                                class="btn-ghost bg-white border border-slate-300 hover:border-brand text-xs flex items-center gap-1.5 font-extrabold min-h-[44px] px-3.5 cursor-pointer"
                            >
                                <x-app-icon name="printer" class="w-4 h-4 text-navy" />
                                <span>Cetak Tiket</span>
                            </button>
                            <a
                                href="{{ route('library.ticket', $activeReservation->reservation_code) }}"
                                target="_blank"
                                class="text-xs font-bold text-brand hover:underline inline-flex items-center gap-1"
                            >
                                <span>Buka Halaman Tiket</span>
                                <x-app-icon name="arrow-top-right-on-square" class="w-3.5 h-3.5" />
                            </a>
                        </div>

                        <button
                            type="button"
                            wire:click="closeTicketModal"
                            class="btn-primary text-xs font-black min-h-[44px] px-6 shadow-2xs"
                        >
                            <span>Selesai & Tutup</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- MODAL 9: CEK STATUS TIKET MANDIRI OLEH SISWA --}}
    @if ($showCheckTicketModal)
        <template x-teleport="body">
            <div
                x-data="{ open: true }"
                x-show="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/60 backdrop-blur-xs overflow-y-auto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="$wire.closeCheckTicketModal()"
            >
                <div
                    class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200"
                    @click.away="$wire.closeCheckTicketModal()"
                >
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50 rounded-t-2xl">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-sky-100 text-brand flex items-center justify-center">
                                <x-app-icon name="qr-code" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-navy">Lacak Tiket Sirkulasi</h3>
                                <p class="text-[11px] text-ink-mute">Periksa status peminjaman buku fisik Anda secara mandiri</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeCheckTicketModal"
                            class="w-8 h-8 rounded-lg text-ink-mute hover:text-navy hover:bg-slate-200 flex items-center justify-center cursor-pointer transition-colors"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-navy mb-1">
                                Kode Tiket Sirkulasi atau Nomor NISN Siswa <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model="ticketSearchCode"
                                    wire:keydown.enter="searchTicket"
                                    placeholder="Contoh: LIB-2026-ABC12 atau nomor NISN"
                                    class="w-full text-xs font-mono uppercase rounded-xl border border-slate-300 px-3.5 py-2.5 focus:border-brand focus:ring-1 focus:ring-brand outline-hidden"
                                />
                                <button
                                    type="button"
                                    wire:click="searchTicket"
                                    class="btn-primary text-xs font-bold px-4 py-2.5 rounded-xl shrink-0 cursor-pointer"
                                >
                                    <span>Cari</span>
                                </button>
                            </div>
                            <span class="text-[11px] text-ink-mute block mt-1">
                                Masukkan kode tiket tunggal, atau masukkan nomor NISN Anda untuk melihat seluruh riwayat peminjaman.
                            </span>
                        </div>

                        @if ($checkTicketErrorMessage)
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
                                {{ $checkTicketErrorMessage }}
                            </div>
                        @endif

                        {{-- Hasil Pencarian Riwayat Berdasarkan NISN --}}
                        @if ($searchedHistory && $searchedHistory->isNotEmpty())
                            <div class="space-y-3">
                                <span class="text-xs font-bold text-navy block">Riwayat Peminjaman Ditemukan ({{ $searchedHistory->count() }} Transaksi):</span>
                                <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                                    @foreach ($searchedHistory as $h)
                                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-1.5 hover:border-brand/40 transition-colors">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-mono font-black text-navy">{{ $h->reservation_code }}</span>
                                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full
                                                    @if($h->status === 'menunggu') bg-amber-100 text-amber-800
                                                    @elseif($h->status === 'diambil') bg-blue-100 text-blue-800
                                                    @elseif($h->status === 'selesai') bg-emerald-100 text-emerald-800
                                                    @else bg-slate-200 text-slate-800
                                                    @endif"
                                                >
                                                    {{ $h->status_label }}
                                                </span>
                                            </div>
                                            <strong class="text-navy block truncate" title="{{ $h->book?->title }}">{{ $h->book?->title ?? 'Buku Perpustakaan' }}</strong>
                                            <div class="flex items-center justify-between text-[11px] text-ink-mute pt-1 border-t border-slate-200">
                                                <span>Tenggat: <strong class="{{ $h->is_overdue ? 'text-rose-600' : 'text-brand' }}">{{ $h->due_date ? $h->due_date->format('d M Y') : '-' }}</strong></span>
                                                <a href="{{ route('library.ticket', $h->reservation_code) }}" target="_blank" class="text-brand font-bold hover:underline inline-flex items-center gap-1">
                                                    <span>Buka Tiket</span>
                                                    <x-app-icon name="arrow-top-right-on-square" class="w-3 h-3" />
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Hasil Pencarian Tiket Tunggal --}}
                        @if ($checkedTicket)
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-mono font-black text-navy">{{ $checkedTicket->reservation_code }}</span>
                                    <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full
                                        @if($checkedTicket->status === 'menunggu') bg-amber-100 text-amber-800 border border-amber-200
                                        @elseif($checkedTicket->status === 'diambil') bg-blue-100 text-blue-800 border border-blue-200
                                        @elseif($checkedTicket->status === 'selesai') bg-emerald-100 text-emerald-800 border border-emerald-200
                                        @else bg-slate-200 text-slate-800
                                        @endif"
                                    >
                                        {{ $checkedTicket->status_label }}
                                    </span>
                                </div>

                                <div class="text-xs space-y-1.5 pt-2 border-t border-slate-200">
                                    <div><span class="text-ink-mute">Judul Buku:</span> <strong class="text-navy">{{ $checkedTicket->book?->title ?? '-' }}</strong></div>
                                    <div><span class="text-ink-mute">Peminjam:</span> <span class="text-slate-800 font-semibold">{{ $checkedTicket->borrower_name }} ({{ $checkedTicket->borrower_class }})</span></div>
                                    <div><span class="text-ink-mute">Rencana Ambil:</span> <span class="text-slate-800">{{ $checkedTicket->pickup_date ? $checkedTicket->pickup_date->format('d M Y') : '-' }}</span></div>
                                    <div><span class="text-ink-mute">Tenggat Kembali:</span> <strong class="text-brand font-bold">{{ $checkedTicket->due_date ? $checkedTicket->due_date->format('d M Y') : '-' }}</strong></div>
                                </div>

                                <div class="p-3 bg-white rounded-lg border border-slate-200 text-center space-y-2">
                                    <div id="checked-ticket-barcode-box" class="max-w-[280px] mx-auto overflow-hidden">
                                        {!! $checkedTicket->barcode_svg !!}
                                    </div>
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            onclick="downloadBarcodePng('checked-ticket-barcode-box', 'barcode-{{ $checkedTicket->reservation_code }}.png')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-extrabold bg-navy text-white hover:bg-brand transition-colors cursor-pointer shadow-2xs"
                                        >
                                            <x-app-icon name="arrow-down-tray" class="w-3.5 h-3.5 text-white" />
                                            <span>Unduh Barcode (PNG)</span>
                                        </button>
                                        <a
                                            href="{{ route('library.ticket.download-barcode', $checkedTicket->reservation_code) }}"
                                            download="barcode-{{ $checkedTicket->reservation_code }}.svg"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors"
                                        >
                                            <span>Unduh SVG</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="pt-2 text-center">
                                    <a
                                        href="{{ route('library.ticket', $checkedTicket->reservation_code) }}"
                                        target="_blank"
                                        class="btn-ghost bg-white border border-slate-300 hover:border-brand text-xs font-bold px-4 py-2 inline-flex items-center gap-1.5 rounded-lg shadow-2xs"
                                    >
                                        <x-app-icon name="printer" class="w-4 h-4 text-navy" />
                                        <span>Buka Bukti Tiket Lengkap</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end rounded-b-2xl">
                        <button
                            type="button"
                            wire:click="closeCheckTicketModal"
                            class="btn-ghost text-xs font-bold px-4 py-2"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- MODAL 10: HISTORI PEMINJAMAN AKUN SISWA --}}
    @if ($showHistoryModal)
        <template x-teleport="body">
            <div
                x-data="{ open: true }"
                x-show="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/60 backdrop-blur-xs overflow-y-auto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="$wire.closeHistoryModal()"
            >
                <div
                    class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-200"
                    @click.away="$wire.closeHistoryModal()"
                >
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50 rounded-t-2xl">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center">
                                <x-app-icon name="bookmark" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-navy">Histori Peminjaman Buku Saya</h3>
                                <p class="text-[11px] text-ink-mute">Daftar buku fisik yang pernah atau sedang Anda pinjam</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeHistoryModal"
                            class="w-8 h-8 rounded-lg text-ink-mute hover:text-navy hover:bg-slate-200 flex items-center justify-center cursor-pointer transition-colors"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        @if ($this->myBorrowingHistory->isEmpty())
                            <div class="text-center py-8 space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-ink-mute flex items-center justify-center mx-auto">
                                    <x-app-icon name="book-open" class="w-6 h-6" />
                                </div>
                                <h4 class="text-xs font-bold text-navy">Belum Ada Transaksi Peminjaman</h4>
                                <p class="text-xs text-ink-mute max-w-sm mx-auto">
                                    Anda belum memiliki riwayat peminjaman buku fisik. Pilih buku di katalog dan klik "Pinjam Buku Fisik" untuk mulai meminjam.
                                </p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($this->myBorrowingHistory as $item)
                                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2 hover:border-brand/40 transition-colors">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <span class="font-mono font-black text-navy bg-white px-2 py-0.5 rounded border border-slate-200">
                                                {{ $item->reservation_code }}
                                            </span>
                                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full
                                                @if($item->is_overdue) bg-rose-100 text-rose-800 border border-rose-200
                                                @elseif($item->status === 'menunggu') bg-amber-100 text-amber-800 border border-amber-200
                                                @elseif($item->status === 'diambil') bg-blue-100 text-blue-800 border border-blue-200
                                                @elseif($item->status === 'selesai') bg-emerald-100 text-emerald-800 border border-emerald-200
                                                @else bg-slate-200 text-slate-700
                                                @endif"
                                            >
                                                {{ $item->status_label }}
                                            </span>
                                        </div>

                                        <strong class="text-sm font-bold text-navy block truncate" title="{{ $item->book?->title }}">
                                            {{ $item->book?->title ?? 'Buku Perpustakaan' }}
                                        </strong>

                                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-ink-mute pt-1 border-t border-slate-200">
                                            <div>
                                                <span>Ambil: <strong class="text-slate-800">{{ $item->pickup_date ? $item->pickup_date->format('d M Y') : '-' }}</strong></span>
                                                <span class="mx-1">&bull;</span>
                                                <span>Tenggat: <strong class="{{ $item->is_overdue ? 'text-rose-600 font-bold' : 'text-brand font-bold' }}">{{ $item->due_date ? $item->due_date->format('d M Y') : '-' }}</strong></span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ route('library.ticket', $item->reservation_code) }}"
                                                    target="_blank"
                                                    class="btn-ghost bg-white border border-slate-300 hover:border-brand text-[11px] font-bold px-2.5 py-1 rounded-md inline-flex items-center gap-1 shadow-2xs"
                                                >
                                                    <x-app-icon name="qr-code" class="w-3.5 h-3.5 text-navy" />
                                                    <span>Tiket &amp; Barcode</span>
                                                </a>

                                                <a
                                                    href="{{ route('library.ticket.download-barcode', $item->reservation_code) }}"
                                                    download="barcode-{{ $item->reservation_code }}.svg"
                                                    class="btn-primary text-[11px] font-bold px-2.5 py-1 rounded-md inline-flex items-center gap-1 shadow-2xs"
                                                >
                                                    <x-app-icon name="arrow-down-tray" class="w-3.5 h-3.5 text-white" />
                                                    <span>Unduh Barcode</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between rounded-b-2xl">
                        <a
                            href="{{ route('library.history') }}"
                            class="text-xs font-bold text-brand hover:underline inline-flex items-center gap-1"
                        >
                            <span>Buka Halaman Riwayat Penuh</span>
                            <x-app-icon name="arrow-top-right-on-square" class="w-3.5 h-3.5" />
                        </a>

                        <button
                            type="button"
                            wire:click="closeHistoryModal"
                            class="btn-ghost text-xs font-bold px-4 py-2"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif

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
</div>
