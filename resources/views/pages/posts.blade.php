<x-layouts.app title="Berita & Informasi">
    <x-page-header
        title="Berita Sekolah"
        subtitle="Informasi terkini seputar kegiatan dan prestasi SMP Negeri 4 Cepu"
        crumb="Berita"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Kolom Utama: Pencarian & Daftar Berita --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Form Pencarian Berita --}}
                <form method="GET" action="{{ route('posts') }}" class="relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari berita sekolah..."
                        class="field pl-11 !py-3 pr-24 shadow-2xs"
                    >
                    <x-app-icon name="search" class="w-5 h-5 text-ink-mute absolute left-3.5 top-1/2 -translate-y-1/2" />
                    @if (request('q'))
                        <a
                            href="{{ route('posts') }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-ink-mute hover:text-navy px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-200 transition-colors"
                        >
                            Reset
                        </a>
                    @endif
                </form>

                {{-- Daftar Berita Kartu Horizontal --}}
                <div class="space-y-4">
                    @forelse ($posts as $post)
                        <x-card class="p-0 overflow-hidden flex flex-col sm:flex-row hover:border-brand transition-all group shadow-xs">
                            @if ($post->featured_image_url)
                                <div class="w-full sm:w-56 h-48 sm:h-auto shrink-0 overflow-hidden">
                                    <img
                                        src="{{ $post->featured_image_url }}"
                                        alt="{{ $post->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    >
                                </div>
                            @else
                                <div class="w-full sm:w-56 h-48 sm:h-auto bg-sky-soft flex items-center justify-center text-brand shrink-0">
                                    <x-app-icon name="news" class="w-10 h-10" />
                                </div>
                            @endif

                            <div class="p-5 sm:p-6 flex flex-col justify-between grow">
                                <div>
                                    <h2 class="text-base sm:text-lg font-extrabold text-navy leading-snug mb-2">
                                        <a href="{{ route('post.detail', $post->slug) }}" class="group-hover:text-brand transition-colors">
                                            {{ $post->title }}
                                        </a>
                                    </h2>
                                    <p class="text-xs sm:text-sm text-ink-soft line-clamp-2 leading-relaxed mb-3 font-medium">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between text-xs pt-3 border-t border-sun-soft">
                                    <span class="font-semibold text-ink-mute">
                                        {{ $post->published_at?->translatedFormat('d M Y') ?? $post->created_at->translatedFormat('d M Y') }}
                                    </span>
                                    <a href="{{ route('post.detail', $post->slug) }}" class="link-brand font-black inline-flex items-center gap-1">
                                        <span>Baca Selengkapnya</span>
                                        <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </div>
                        </x-card>
                    @empty
                        <div class="card p-6 sm:p-8 bg-white border border-slate-200/80 text-center space-y-5 shadow-xs">
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                                <x-app-icon name="search" class="w-7 h-7" />
                            </div>

                            <div>
                                <h3 class="text-lg font-extrabold text-navy">
                                    {{ request('q') ? 'Tidak ada berita yang cocok dengan "' . request('q') . '"' : 'Belum Ada Berita' }}
                                </h3>
                                <p class="text-xs sm:text-sm text-ink-soft max-w-md mx-auto mt-1.5 leading-relaxed font-medium">
                                    {{ request('q') 
                                        ? 'Coba gunakan kata kunci yang lebih umum, periksa ejaan, atau jelajahi rekomendasi berita terbaru di bawah ini.' 
                                        : 'Daftar berita dan dokumentasi kegiatan sekolah akan segera dipublikasikan di sini.' }}
                                </p>
                            </div>

                            @if (request('q'))
                                <div class="flex items-center justify-center gap-3 pt-1">
                                    <a href="{{ route('posts') }}" class="btn-primary !min-h-[40px] px-5 text-xs">
                                        <span>Tampilkan Semua Berita</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Rekomendasi Berita Pilihan Saat Pencarian Tidak Ditemukan --}}
                        @if (isset($suggestedPosts) && $suggestedPosts->isNotEmpty())
                            <div class="pt-4 space-y-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-brand"></div>
                                    <h4 class="text-sm font-extrabold text-navy uppercase tracking-wider">
                                        Mungkin Anda Tertarik Membaca Ini:
                                    </h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                    @foreach ($suggestedPosts as $sPost)
                                        <a href="{{ route('post.detail', $sPost->slug) }}" class="card p-4 bg-white hover:border-brand transition-all group block shadow-2xs">
                                            @if ($sPost->featured_image)
                                                <img
                                                    src="{{ asset('storage/' . $sPost->featured_image) }}"
                                                    alt="{{ $sPost->title }}"
                                                    class="w-full h-28 object-cover rounded-xl mb-2.5 group-hover:scale-105 transition-transform"
                                                >
                                            @else
                                                <div class="w-full h-28 bg-sky-soft rounded-xl mb-2.5 flex items-center justify-center text-brand">
                                                    <x-app-icon name="news" class="w-8 h-8" />
                                                </div>
                                            @endif
                                            <h5 class="text-xs font-bold text-navy group-hover:text-brand line-clamp-2 leading-snug">
                                                {{ $sPost->title }}
                                            </h5>
                                            <span class="block text-[10px] text-ink-mute mt-1.5 font-medium">
                                                {{ $sPost->published_at?->translatedFormat('d M Y') ?? $sPost->created_at->translatedFormat('d M Y') }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforelse
                </div>

                {{-- Paginasi --}}
                @if ($posts->hasPages())
                    <div class="pt-4 flex justify-center">
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>

            {{-- Kolom Sidebar (300px Sesuai Figma) --}}
            <aside class="lg:col-span-4 space-y-6">
                <x-card class="p-5">
                    <div class="flex items-center gap-2 font-extrabold text-base text-navy pb-3 border-b-2 border-sun-soft mb-3">
                        <x-app-icon name="megaphone" class="w-5 h-5 text-brand" />
                        <span>Pengumuman</span>
                    </div>

                    <div class="divide-y divide-sun-soft">
                        @forelse ($announcements as $ann)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <a href="{{ route('post.detail', $ann->slug) }}" class="font-bold text-xs sm:text-sm text-navy hover:text-brand line-clamp-2 leading-snug">
                                    {{ $ann->title }}
                                </a>
                                <span class="block text-[11px] font-semibold text-ink-mute mt-1">
                                    {{ $ann->published_at?->translatedFormat('d M Y') ?? $ann->created_at->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-ink-soft py-2">Belum ada pengumuman terbaru.</p>
                        @endforelse
                    </div>
                </x-card>
            </aside>
        </div>
    </div>
</x-layouts.app>
