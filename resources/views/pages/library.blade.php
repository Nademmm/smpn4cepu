<x-layouts.app title="Perpustakaan Digital">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Pusat Sumber Literasi</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Katalog Perpustakaan Digital</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Temukan buku pelajaran, buku fiksi sastra, dan referensi teknologi. Periksa lokasi rak fisik dan ketersediaan stok pinjam.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Form Pencarian Buku --}}
        <form method="GET" action="{{ route('library') }}" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-8 flex flex-col sm:flex-row gap-4">
            <div class="flex-grow relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul buku, penulis, atau ISBN..."
                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-colors flex-shrink-0">
                Cari Koleksi
            </button>
            @if(request('q'))
                <a href="{{ route('library') }}" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>

        {{-- Grid Buku --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($books as $book)
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                {{ $book->category }}
                            </span>
                            <span class="font-mono text-xs text-slate-400 font-semibold">
                                {{ $book->publication_year }}
                            </span>
                        </div>

                        <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug mb-2 line-clamp-2">
                            {{ $book->title }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                            Penulis: <strong class="text-slate-700 dark:text-slate-300">{{ $book->author }}</strong>
                        </p>
                        <p class="text-xs text-slate-400">
                            Penerbit: {{ $book->publisher }}
                        </p>
                    </div>

                    <div class="px-6 pb-6 pt-3 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-800/20 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Lokasi Rak:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $book->shelf_location ?: 'Gudang' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block text-[11px]">Sisa Stok:</span>
                            <span class="font-mono font-black {{ $book->available_stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                                {{ $book->available_stock }} / {{ $book->total_stock }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800">
                    <p class="text-slate-500 text-sm">Tidak ada buku yang sesuai dengan pencarian Anda.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
