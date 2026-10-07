<x-layouts.app title="Katalog Perpustakaan Digital OPAC">
    {{-- Header Perpustakaan & Info OPAC --}}
    <x-page-header
        title="Perpustakaan Digital"
        subtitle="Katalog Akses Publik Terbuka (OPAC): Temukan koleksi buku fisik, modul digital, posisi rak, dan reservasi sirkulasi."
        crumb="Perpustakaan"
    />

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8"
        x-data="{
            selectedBook: null,
            detailTab: 'info',
            viewMode: 'grid',
            reservationSubmitted: false,
            reservationCode: '',
            borrowerName: '',
            borrowerClass: '',
            borrowerDate: '',
            openDetail(book, tab = 'info') {
                this.selectedBook = book;
                this.detailTab = tab;
                this.reservationSubmitted = false;
                this.reservationCode = '';
                this.borrowerName = '';
                this.borrowerClass = '';
                this.borrowerDate = '';
            },
            generateBooking() {
                if (!this.borrowerName.trim()) return;
                const rand = Math.floor(1000 + Math.random() * 9000);
                this.reservationCode = 'LIB-' + rand + '-' + new Date().getFullYear();
                this.reservationSubmitted = true;
            }
        }"
    >
        {{-- Ringkasan Koleksi Perpustakaan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="card p-5 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-sky-soft text-brand flex items-center justify-center shrink-0">
                    <x-app-icon name="book" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Koleksi Judul</span>
                    <strong class="text-2xl sm:text-3xl font-black text-navy leading-none">{{ $stats['total_titles'] }}</strong>
                    <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Judul Terdaftar</span>
                </div>
            </div>

            <div class="card p-5 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                    <x-app-icon name="building" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Total Eksemplar</span>
                    <strong class="text-2xl sm:text-3xl font-black text-navy leading-none">{{ $stats['total_copies'] }}</strong>
                    <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Buku Fisik</span>
                </div>
            </div>

            <div class="card p-5 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <x-app-icon name="check-circle" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Siap Dipinjam</span>
                    <strong class="text-2xl sm:text-3xl font-black text-emerald-700 leading-none">{{ $stats['available_copies'] }}</strong>
                    <span class="text-[11px] text-ink-soft block mt-1 font-semibold">Eksemplar di Rak</span>
                </div>
            </div>

            <div class="card p-5 bg-white flex items-center gap-4 hover:border-brand transition-all shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-grape flex items-center justify-center shrink-0">
                    <x-app-icon name="download" class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs font-bold text-ink-mute uppercase tracking-wider block">Koleksi Digital</span>
                    <strong class="text-2xl sm:text-3xl font-black text-grape leading-none">{{ $stats['digital_books'] }}</strong>
                    <span class="text-[11px] text-ink-soft block mt-1 font-semibold">E-Book & Modul PDF</span>
                </div>
            </div>
        </div>

        {{-- Toolbar Pencarian & Filter OPAC --}}
        <div class="card p-5 sm:p-6 bg-white space-y-4 shadow-xs">
            <form method="GET" action="{{ route('library') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
                {{-- Input Pencarian Utama --}}
                <div class="lg:col-span-6 relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari judul buku, nama pengarang, penerbit, nomor panggil, atau ISBN..."
                        class="field pl-11 !py-2.5"
                    >
                    <x-app-icon name="search" class="w-5 h-5 text-ink-mute absolute left-3.5 top-1/2 -translate-y-1/2" />
                </div>

                {{-- Filter Kategori --}}
                <div class="lg:col-span-3">
                    <select name="category" onchange="this.form.submit()" class="field !py-2.5 text-xs font-bold">
                        <option value="">Semua Kategori Koleksi</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Ketersediaan & Urutan --}}
                <div class="lg:col-span-3 flex gap-2">
                    <select name="stock" onchange="this.form.submit()" class="field !py-2.5 text-xs font-bold grow">
                        <option value="">Semua Status</option>
                        <option value="available" {{ request('stock') === 'available' ? 'selected' : '' }}>Tersedia di Rak</option>
                        <option value="digital" {{ request('stock') === 'digital' ? 'selected' : '' }}>Ada E-Book</option>
                    </select>

                    <button type="submit" class="btn-primary !min-h-[42px] px-4 shrink-0 text-xs shadow-2xs">
                        Cari
                    </button>
                    @if (request()->hasAny(['q', 'category', 'stock', 'sort']))
                        <a href="{{ route('library') }}" class="btn-ghost !min-h-[42px] px-3 shrink-0 text-xs" title="Reset Pencarian">
                            <x-app-icon name="x" class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            </form>

            {{-- Kategori Cepat & Toggle Mode Tampilan --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-sun-soft/60">
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-ink-mute font-bold mr-1">Kategori:</span>
                    <a
                        href="{{ route('library', request()->except('category')) }}"
                        class="chip {{ !request('category') ? 'chip-active' : '' }} !min-h-[32px] !py-1 !text-xs"
                    >Semua</a>
                    @foreach ($categories->take(5) as $cat)
                        <a
                            href="{{ route('library', array_merge(request()->except('category'), ['category' => $cat])) }}"
                            class="chip {{ request('category') === $cat ? 'chip-active' : '' }} !min-h-[32px] !py-1 !text-xs"
                        >{{ $cat }}</a>
                    @endforeach
                </div>

                {{-- Mode Tampilan: Grid vs List --}}
                <div class="flex items-center gap-1 bg-cream/70 p-1 rounded-xl border border-sun-soft text-xs font-bold">
                    <button
                        type="button"
                        @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-white text-brand shadow-2xs' : 'text-ink-mute hover:text-navy'"
                        class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all"
                    >
                        <x-app-icon name="photo" class="w-3.5 h-3.5" />
                        <span>Grid</span>
                    </button>
                    <button
                        type="button"
                        @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-white text-brand shadow-2xs' : 'text-ink-mute hover:text-navy'"
                        class="px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all"
                    >
                        <x-app-icon name="document" class="w-3.5 h-3.5" />
                        <span>Katalog OPAC</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tampilan Koleksi Buku: Mode Grid --}}
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse ($books as $book)
                <x-card class="flex flex-col justify-between hover:border-brand transition-all p-5 group shadow-xs">
                    <div>
                        {{-- Cover Area Proporsional (Format Buku 3:4) --}}
                        <div class="h-48 sm:h-52 rounded-xl bg-sky-soft flex items-center justify-center text-brand mb-4 overflow-hidden border border-sun-soft/60 relative">
                            @if ($book->cover_image && file_exists(public_path('storage/' . $book->cover_image)))
                                <img
                                    src="{{ asset('storage/' . $book->cover_image) }}"
                                    alt="{{ $book->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                            @else
                                <div class="flex flex-col items-center gap-2 text-brand">
                                    <x-app-icon name="book" class="w-12 h-12" />
                                    <span class="text-[11px] font-bold text-ink-mute uppercase tracking-wider">Koleksi SMPN 4</span>
                                </div>
                            @endif

                            {{-- Badge Digital E-Book Jika Ada --}}
                            @if ($book->digital_file_path)
                                <span class="absolute bottom-2.5 right-2.5 bg-purple-600/90 text-white text-[10px] font-black px-2.5 py-0.5 rounded-md backdrop-blur-xs flex items-center gap-1">
                                    <x-app-icon name="download" class="w-3 h-3" />
                                    <span>E-Book</span>
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2 text-xs mb-2">
                            <x-badge type="blue">{{ $book->category ?? 'Umum' }}</x-badge>
                            @if ($book->available_stock > 0)
                                <x-badge type="green">Tersedia ({{ $book->available_stock }})</x-badge>
                            @else
                                <x-badge type="orange">Sedang Dipinjam</x-badge>
                            @endif
                        </div>

                        <h3 class="font-extrabold text-navy text-[15px] sm:text-base leading-snug line-clamp-2 mb-1 group-hover:text-brand transition-colors">
                            {{ $book->title }}
                        </h3>
                        <p class="text-xs text-ink-soft mb-3 line-clamp-1 font-medium">
                            {{ $book->author ?? 'Penulis tidak tercatat' }}
                        </p>

                        <div class="bg-cream/80 rounded-xl p-2.5 text-xs text-navy font-semibold flex items-center justify-between gap-1.5 mb-4 border border-sun-soft/60">
                            <span class="text-ink-mute text-[11px]">Nomor Rak:</span>
                            <span class="font-black text-brand">{{ $book->shelf_location ?? 'Layanan Sirkulasi' }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-sun-soft/40">
                        <button
                            type="button"
                            @click="openDetail({{ json_encode($book) }}, 'info')"
                            class="btn-primary w-full text-xs !min-h-[38px] shadow-2xs"
                        >
                            Detail Bibliografi & Rak
                        </button>

                        @if ($book->available_stock > 0)
                            <button
                                type="button"
                                @click="openDetail({{ json_encode($book) }}, 'reserve')"
                                class="btn-ghost w-full text-xs !min-h-[36px] hover:bg-sky-soft"
                            >
                                <x-app-icon name="check-circle" class="w-3.5 h-3.5 text-brand" />
                                <span>Reservasi Peminjaman</span>
                            </button>
                        @endif
                    </div>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Buku Belum Ditemukan"
                        text="Tidak ada koleksi buku yang cocok dengan kata kunci pencarian atau filter yang Anda pilih."
                    />
                </div>
            @endforelse
        </div>

        {{-- Tampilan Koleksi Buku: Mode List (Katalog OPAC Resmi) --}}
        <div x-show="viewMode === 'list'" x-cloak class="card p-0 overflow-hidden shadow-xs">
            <div class="p-4 bg-sky-soft border-b-2 border-sun-soft flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-navy text-sm sm:text-base">Daftar Bibliografi Katalog OPAC</h3>
                    <p class="text-xs text-ink-soft">Daftar buku terperinci beserta nomor panggil dan status ketersediaan fisik.</p>
                </div>
                <span class="text-xs font-black text-brand bg-white px-3 py-1 rounded-full border border-sun-soft">
                    Total: {{ $books->total() }} Judul
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-cream/60 border-b-2 border-sun-soft text-navy font-extrabold uppercase text-[11px]">
                        <tr>
                            <th class="p-4 w-16">Sampul</th>
                            <th class="p-4">Informasi Buku & Pengarang</th>
                            <th class="p-4">Kategori & Tahun</th>
                            <th class="p-4">Nomor Rak & Panggil</th>
                            <th class="p-4 text-center">Status Stok</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sun-soft font-semibold text-navy">
                        @forelse ($books as $book)
                            <tr class="hover:bg-sky-soft/40 transition-colors">
                                <td class="p-4">
                                    <div class="w-12 h-16 rounded-lg bg-sky-soft overflow-hidden border border-sun-soft/60 shrink-0 flex items-center justify-center">
                                        @if ($book->cover_image && file_exists(public_path('storage/' . $book->cover_image)))
                                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                        @else
                                            <x-app-icon name="book" class="w-6 h-6 text-brand" />
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-extrabold text-navy text-[15px] leading-snug">{{ $book->title }}</div>
                                    <div class="text-xs text-ink-soft mt-0.5">Penulis: {{ $book->author }} · Penerbit: {{ $book->publisher }}</div>
                                    @if ($book->isbn)
                                        <div class="text-[11px] text-ink-mute mt-0.5">ISBN: {{ $book->isbn }}</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <x-badge type="blue">{{ $book->category }}</x-badge>
                                    <div class="text-xs text-ink-mute mt-1 font-bold">Tahun {{ $book->publication_year }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-black text-brand text-xs sm:text-sm">{{ $book->shelf_location }}</div>
                                    @if ($book->call_number)
                                        <div class="text-[11px] text-ink-mute mt-0.5 font-bold">DDC: {{ $book->call_number }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    @if ($book->available_stock > 0)
                                        <span class="inline-block bg-emerald-50 text-emerald-800 border border-emerald-300 px-2.5 py-1 rounded-full text-xs font-black">
                                            Tersedia ({{ $book->available_stock }}/{{ $book->total_stock }})
                                        </span>
                                    @else
                                        <span class="inline-block bg-amber-50 text-amber-900 border border-amber-300 px-2.5 py-1 rounded-full text-xs font-black">
                                            Dipinjam
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="openDetail({{ json_encode($book) }}, 'info')"
                                            class="btn-ghost !min-h-[34px] !py-1 text-xs"
                                        >
                                            Detail
                                        </button>
                                        @if ($book->available_stock > 0)
                                            <button
                                                type="button"
                                                @click="openDetail({{ json_encode($book) }}, 'reserve')"
                                                class="btn-primary !min-h-[34px] !py-1 text-xs"
                                            >
                                                Pinjam
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-ink-soft">
                                    Tidak ada data buku yang sesuai dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paginasi Halaman OPAC --}}
        @if ($books->hasPages())
            <div class="pt-4 flex justify-center">
                {{ $books->links() }}
            </div>
        @endif

        {{-- Panduan Layanan Sirkulasi --}}
        <div class="card p-6 bg-gradient-to-r from-sky-soft to-cream border-2 border-brand/20 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-1 text-center md:text-left">
                <span class="text-xs font-black text-brand uppercase tracking-wider">Tata Tertib Sirkulasi</span>
                <h3 class="text-lg font-extrabold text-navy">Peminjaman Buku Fisik Perpustakaan SMPN 4 Cepu</h3>
                <p class="text-xs sm:text-sm text-ink-soft max-w-2xl font-medium leading-relaxed">
                    Setiap peserta didik berhak meminjam maksimal 2 eksemplar buku selama 7 hari kalender. Bawalah kartu pelajar atau tunjukkan kode reservasi sirkulasi digital kepada petugas perpustakaan di meja layanan.
                </p>
            </div>
            <div class="shrink-0 text-center">
                <span class="inline-block bg-white text-navy border-2 border-sun-soft px-4 py-2 rounded-xl text-xs font-extrabold shadow-2xs">
                    Jam Layanan: 07.30 - 14.00 WIB
                </span>
            </div>
        </div>

        {{-- Modal Komprehensif: Detail Bibliografi & Reservasi Peminjaman --}}
        <div
            x-show="selectedBook !== null"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/70 backdrop-blur-xs"
        >
            <div
                @click.away="selectedBook = null"
                class="card max-w-2xl w-full p-0 overflow-hidden shadow-2xl max-h-[92vh] flex flex-col bg-white"
            >
                {{-- Header Modal --}}
                <div class="p-5 bg-sky-soft border-b-2 border-sun-soft flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-black text-brand uppercase tracking-wider" x-text="selectedBook?.category || 'Katalog'"></span>
                            <span class="text-ink-mute">·</span>
                            <span class="text-xs text-ink-mute font-bold" x-text="'Tahun ' + (selectedBook?.publication_year || '-')"></span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-navy leading-snug" x-text="selectedBook?.title"></h2>
                    </div>
                    <button
                        type="button"
                        @click="selectedBook = null"
                        class="p-1.5 rounded-xl text-ink-mute hover:text-navy hover:bg-white transition-colors"
                    >
                        <x-app-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Tabs Modal: Informasi vs Reservasi --}}
                <div class="flex border-b-2 border-sun-soft bg-cream/50 px-5 pt-2 gap-2 text-xs font-extrabold">
                    <button
                        type="button"
                        @click="detailTab = 'info'"
                        :class="detailTab === 'info' ? 'border-b-2 border-brand text-brand bg-white rounded-t-xl py-2.5 px-4' : 'text-ink-mute hover:text-navy py-2.5 px-4'"
                        class="transition-all"
                    >
                        Informasi Bibliografi & Rak
                    </button>
                    <button
                        type="button"
                        @click="detailTab = 'reserve'"
                        :class="detailTab === 'reserve' ? 'border-b-2 border-brand text-brand bg-white rounded-t-xl py-2.5 px-4' : 'text-ink-mute hover:text-navy py-2.5 px-4'"
                        class="transition-all flex items-center gap-1.5"
                    >
                        <x-app-icon name="check-circle" class="w-3.5 h-3.5" />
                        <span>Form Reservasi Peminjaman</span>
                    </button>
                </div>

                {{-- Konten Modal Body (Scrollable) --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-5 grow">
                    {{-- TAB 1: Detail Bibliografi --}}
                    <div x-show="detailTab === 'info'" class="space-y-5">
                        <div class="flex flex-col sm:flex-row gap-5 items-start">
                            {{-- Cover Buku --}}
                            <div class="w-28 sm:w-36 h-40 sm:h-48 rounded-xl bg-sky-soft overflow-hidden border border-sun-soft shrink-0 flex items-center justify-center shadow-xs">
                                <template x-if="selectedBook?.cover_image">
                                    <img :src="'{{ asset('storage') }}/' + selectedBook?.cover_image" :alt="selectedBook?.title" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!selectedBook?.cover_image">
                                    <div class="text-center p-3 text-brand">
                                        <x-app-icon name="book" class="w-10 h-10 mx-auto mb-1" />
                                        <span class="text-[10px] font-extrabold block text-ink-mute">Koleksi Buku</span>
                                    </div>
                                </template>
                            </div>

                            {{-- Grid Metadata Bibliografi --}}
                            <div class="grid grid-cols-2 gap-3 text-xs flex-1 w-full">
                                <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                    <span class="text-[11px] text-ink-mute block font-bold">Penulis / Pengarang</span>
                                    <strong class="font-extrabold text-navy text-sm" x-text="selectedBook?.author || '-'"></strong>
                                </div>
                                <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                    <span class="text-[11px] text-ink-mute block font-bold">Penerbit</span>
                                    <strong class="font-extrabold text-navy text-sm" x-text="selectedBook?.publisher || '-'"></strong>
                                </div>
                                <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                    <span class="text-[11px] text-ink-mute block font-bold">ISBN / Barcode</span>
                                    <strong class="font-extrabold text-navy text-sm" x-text="selectedBook?.isbn || '-'"></strong>
                                </div>
                                <div class="bg-cream/60 p-3 rounded-xl border border-sun-soft/60">
                                    <span class="text-[11px] text-ink-mute block font-bold">Halaman & Bahasa</span>
                                    <strong class="font-extrabold text-navy text-sm" x-text="(selectedBook?.page_count ? selectedBook?.page_count + ' Hlm · ' : '') + (selectedBook?.language || 'Indonesia')"></strong>
                                </div>
                                <div class="bg-sky-soft p-3 rounded-xl border border-brand/20 col-span-2 flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] text-brand font-black block uppercase tracking-wider">Lokasi Rak Fisik</span>
                                        <strong class="font-black text-navy text-base" x-text="selectedBook?.shelf_location || 'Layanan Sirkulasi'"></strong>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[11px] text-ink-mute block font-bold">Nomor Panggil (DDC)</span>
                                        <strong class="font-black text-brand text-sm" x-text="selectedBook?.call_number || '-'"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sinopsis / Ringkasan Buku --}}
                        <div class="space-y-2 pt-3 border-t border-sun-soft/60">
                            <span class="text-xs font-black text-navy uppercase tracking-wider block">Sinopsis / Ringkasan Buku</span>
                            <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium bg-cream/40 p-3.5 rounded-xl border border-sun-soft/50" x-text="selectedBook?.synopsis || 'Ringkasan buku belum ditambahkan ke katalog.'"></p>
                        </div>

                        {{-- Status Eksemplar & Opsi Digital --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-sun-soft/60">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-ink-mute">Ketersediaan Fisik:</span>
                                <span
                                    class="text-xs font-black px-3 py-1 rounded-full"
                                    :class="selectedBook?.available_stock > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : 'bg-amber-50 text-amber-900 border border-amber-300'"
                                    x-text="selectedBook?.available_stock > 0 ? 'Tersedia ' + selectedBook?.available_stock + ' dari ' + selectedBook?.total_stock + ' Eksemplar' : 'Semua Eksemplar Sedang Dipinjam'"
                                ></span>
                            </div>

                            <template x-if="selectedBook?.digital_file_path">
                                <a
                                    :href="'{{ asset('storage') }}/' + selectedBook?.digital_file_path"
                                    target="_blank"
                                    class="btn-primary !min-h-[36px] text-xs px-4 flex items-center gap-1.5 shadow-2xs"
                                >
                                    <x-app-icon name="download" class="w-3.5 h-3.5" />
                                    <span>Buka Versi E-Book PDF</span>
                                </a>
                            </template>
                        </div>
                    </div>

                    {{-- TAB 2: Formulir Reservasi Peminjaman --}}
                    <div x-show="detailTab === 'reserve'" class="space-y-4">
                        <template x-if="!reservationSubmitted">
                            <div class="space-y-4">
                                <div class="bg-sky-soft/70 border border-brand/20 p-4 rounded-xl text-xs text-navy leading-relaxed font-semibold flex items-start gap-3">
                                    <x-app-icon name="megaphone" class="w-5 h-5 text-brand shrink-0 mt-0.5" />
                                    <div>
                                        Reservasi peminjaman ini menyiapkan buku di meja sirkulasi selama <strong>24 jam kerja</strong>. Tunjukkan kode tiket reservasi kepada staf perpustakaan untuk pengambilan buku.
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-navy mb-1.5">Nama Lengkap Siswa</label>
                                        <input
                                            type="text"
                                            x-model="borrowerName"
                                            placeholder="Contoh: Rian Anggara"
                                            class="field"
                                            required
                                        >
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-navy mb-1.5">Kelas / Rombel</label>
                                            <input
                                                type="text"
                                                x-model="borrowerClass"
                                                placeholder="Contoh: Kelas 8A"
                                                class="field"
                                                required
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-navy mb-1.5">Tanggal Rencana Ambil</label>
                                            <input
                                                type="date"
                                                x-model="borrowerDate"
                                                class="field"
                                                required
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 text-right">
                                    <button
                                        type="button"
                                        @click="generateBooking()"
                                        class="btn-primary text-xs !min-h-[40px] px-6 shadow-xs"
                                    >
                                        Buat Tiket Reservasi Sekarang
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- Tampilan Tiket Reservasi yang Berhasil Dibuat --}}
                        <template x-if="reservationSubmitted">
                            <div class="p-6 rounded-2xl bg-gradient-to-br from-emerald-50 to-cream border-2 border-emerald-400 text-center space-y-4 shadow-sm">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-xs">
                                    <x-app-icon name="check-circle" class="w-8 h-8" />
                                </div>

                                <div>
                                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Reservasi Berhasil</span>
                                    <h4 class="text-xl font-black text-navy mt-1">Tiket Sirkulasi Perpustakaan</h4>
                                </div>

                                <div class="bg-white p-4 rounded-xl border border-emerald-300 max-w-sm mx-auto shadow-2xs space-y-2 text-left">
                                    <div class="flex justify-between items-center pb-2 border-b border-sun-soft">
                                        <span class="text-[11px] text-ink-mute font-bold">KODE BOOKING</span>
                                        <span class="text-base font-black text-brand tracking-wider" x-text="reservationCode"></span>
                                    </div>
                                    <div class="text-xs text-navy font-semibold">
                                        <div class="text-ink-mute text-[11px]">Judul Buku:</div>
                                        <div class="font-extrabold text-navy" x-text="selectedBook?.title"></div>
                                    </div>
                                    <div class="text-xs text-navy font-semibold">
                                        <div class="text-ink-mute text-[11px]">Peminjam:</div>
                                        <div x-text="borrowerName + ' (' + (borrowerClass || 'Siswa') + ')'"></div>
                                    </div>
                                    <div class="text-xs text-brand font-black pt-1">
                                        Lokasi Rak: <span x-text="selectedBook?.shelf_location"></span>
                                    </div>
                                </div>

                                <p class="text-xs text-ink-soft max-w-md mx-auto leading-relaxed">
                                    Simpan atau catat kode di atas dan tunjukkan kepada petugas perpustakaan sebelum batas waktu 24 jam.
                                </p>

                                <div class="pt-2">
                                    <button
                                        type="button"
                                        @click="selectedBook = null"
                                        class="btn-primary text-xs px-6"
                                    >
                                        Selesai & Tutup
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-4 bg-cream/60 border-t-2 border-sun-soft flex items-center justify-between">
                    <span class="text-xs font-semibold text-ink-mute">
                        Sistem Informasi Perpustakaan SMP Negeri 4 Cepu
                    </span>
                    <button
                        type="button"
                        @click="selectedBook = null"
                        class="btn-ghost text-xs !min-h-[34px] !py-1"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
