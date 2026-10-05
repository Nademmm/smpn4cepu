<x-layouts.app title="Berita & Informasi Sekolah">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Informasi Kedinasan & Berita</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Kabar & Agenda SMP Negeri 4 Cepu</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Dapatkan kabar terkini seputar prestasi siswa, pengumuman resmi sekolah, dan jadwal agenda akademik mendatang.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Kategori Filter --}}
        <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2">
            <a href="{{ route('posts') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ !request('cat') ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50' }}">
                Semua Informasi
            </a>
            <a href="{{ route('posts', ['cat' => 'berita']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ request('cat') === 'berita' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50' }}">
                Berita
            </a>
            <a href="{{ route('posts', ['cat' => 'pengumuman']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ request('cat') === 'pengumuman' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50' }}">
                Pengumuman
            </a>
            <a href="{{ route('posts', ['cat' => 'agenda']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-colors whitespace-nowrap {{ request('cat') === 'agenda' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50' }}">
                Agenda
            </a>
        </div>

        {{-- Grid Post --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($posts as $post)
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        @if($post->featured_image)
                            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                        @endif
                        <div class="p-6">
                            <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300 mb-3">
                                {{ $post->category->value }}
                            </span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug mb-2 line-clamp-2">
                                <a href="{{ route('post.detail', $post->slug) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                {{ $post->excerpt }}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                        <a href="{{ route('post.detail', $post->slug) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Baca Selengkapnya
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-300 dark:border-slate-800">
                    <p class="text-slate-500 text-sm">Belum ada artikel atau pengumuman yang sesuai.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
