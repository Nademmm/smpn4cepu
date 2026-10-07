<x-layouts.app title="Perpustakaan Digital">
    <x-page-header
        title="Perpustakaan Digital"
        subtitle="Temukan katalog buku pelajaran, nomor rak penyimpanan fisik, dan ketersediaan stok pinjam."
        crumb="Perpustakaan"
    />

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-6"
        x-data="{
            selectedBook: null,
            openDetail(book) {
                this.selectedBook = book;
            }
        }"
    >
        {{-- Search & Chip Kategori Figma --}}
        <div class="space-y-4">
            <form method="GET" action="{{ route('library') }}" class="relative max-w-xl">
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari judul buku, pengarang, atau nomor ISBN..."
                    class="field pl-11"
                >
                <x-app-icon name="search" class="w-5 h-5 text-ink-mute absolute left-3.5 top-1/2 -translate-y-1/2" />
            </form>

            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('library', request('q') ? ['q' => request('q')] : []) }}"
                        class="chip {{ !request('category') ? 'chip-active' : '' }}"
                    >
                        Semua Kategori
                    </a>
                    @foreach ($categories as $cat)
                        <a
                            href="{{ route('library', array_merge(request('q') ? ['q' => request('q')] : [], ['category' => $cat])) }}"
                            class="chip {{ request('category') === $cat ? 'chip-active' : '' }}"
                        >
                            {{ $cat }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Grid Katalog Buku Figma --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse ($books as $book)
                <x-card class="flex flex-col justify-between hover:border-brand transition-all p-5 group">
                    <div>
                        {{-- Cover Area Proporsional --}}
                        <div class="h-44 sm:h-48 rounded-xl bg-sky-soft flex items-center justify-center text-brand mb-4 overflow-hidden border border-sun-soft/60 relative">
                            @if ($book->cover_image && file_exists(public_path('storage/' . $book->cover_image)))
                                <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="flex flex-col items-center gap-2 text-brand">
                                    <x-app-icon name="book" class="w-12 h-12" />
                                    <span class="text-[11px] font-bold text-ink-mute uppercase tracking-wider">Koleksi Buku</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2 text-xs mb-2">
                            <x-badge type="blue">{{ $book->category ?? 'Umum' }}</x-badge>
                            @if ($book->available_stock > 0)
                                <x-badge type="green">Tersedia ({{ $book->available_stock }})</x-badge>
                            @else
                                <x-badge type="orange">Dipinjam</x-badge>
                            @endif
                        </div>

                        <h3 class="font-extrabold text-navy text-[15px] sm:text-base leading-snug line-clamp-2 mb-1 group-hover:text-brand transition-colors">
                            {{ $book->title }}
                        </h3>
                        <p class="text-xs text-ink-soft mb-3 line-clamp-1 font-medium">
                            {{ $book->author ?? 'Penulis tidak tercatat' }}
                        </p>

                        <div class="bg-cream/80 rounded-xl p-2.5 text-xs text-navy font-semibold flex items-center justify-between gap-1.5 mb-4 border border-sun-soft/60">
                            <span class="text-ink-mute text-[11px]">Lokasi Rak:</span>
                            <span class="font-black text-brand">{{ $book->shelf_location ?? 'Layanan Sirkulasi' }}</span>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="openDetail({{ json_encode($book) }})"
                        class="btn-primary w-full text-xs !min-h-[38px] shadow-2xs"
                    >
                        Lihat Informasi Rak
                    </button>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Buku Belum Ditemukan"
                        text="Tidak ada buku yang sesuai dengan pencarian atau filter kategori yang dipilih."
                    />
                </div>
            @endforelse
        </div>

        {{-- Modal Detail Buku Sesuai Figma --}}
        <div
            x-show="selectedBook !== null"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/60 backdrop-blur-xs"
        >
            <div
                @click.away="selectedBook = null"
                class="card max-w-lg w-full space-y-4 max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-start justify-between gap-4 pb-3 border-b-2 border-sun-soft">
                    <div>
                        <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-1" x-text="selectedBook?.category || 'Katalog'"></span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-navy leading-snug" x-text="selectedBook?.title"></h2>
                    </div>
                    <button
                        type="button"
                        @click="selectedBook = null"
                        class="p-1 rounded-lg text-ink-mute hover:text-navy"
                    >
                        <x-app-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="card p-3 bg-sky-soft/50">
                        <span class="text-[11px] text-ink-mute block">Penulis</span>
                        <strong class="font-extrabold text-navy" x-text="selectedBook?.author || '-'"></strong>
                    </div>
                    <div class="card p-3 bg-sky-soft/50">
                        <span class="text-[11px] text-ink-mute block">Penerbit & Tahun</span>
                        <strong class="font-extrabold text-navy" x-text="(selectedBook?.publisher || '-') + ' (' + (selectedBook?.publication_year || '-') + ')'"></strong>
                    </div>
                    <div class="card p-3 bg-sky-soft/50">
                        <span class="text-[11px] text-ink-mute block">Nomor Rak Fisik</span>
                        <strong class="font-extrabold text-brand" x-text="selectedBook?.shelf_location || 'Layanan Sirkulasi'"></strong>
                    </div>
                    <div class="card p-3 bg-sky-soft/50">
                        <span class="text-[11px] text-ink-mute block">Ketersediaan Stok</span>
                        <strong class="font-extrabold text-emerald-700" x-text="selectedBook?.available_stock + ' dari ' + selectedBook?.total_stock + ' eksemplar'"></strong>
                    </div>
                </div>

                <div class="rounded-xl bg-amber-50 border border-sun-soft p-3 text-xs text-amber-900 leading-relaxed font-semibold">
                    Silakan kunjungi ruang perpustakaan sekolah dan temukan buku ini pada nomor rak yang tertera untuk peminjaman fisik melalui pustakawan sekolah.
                </div>

                <div class="text-right pt-2">
                    <button
                        type="button"
                        @click="selectedBook = null"
                        class="btn-primary text-xs"
                    >
                        Tutup Informasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
