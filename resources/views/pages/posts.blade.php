<x-layouts.app title="Berita & Informasi">
    <x-page-header
        title="Berita Sekolah"
        subtitle="Informasi terkini seputar kegiatan dan prestasi SMP Negeri 4 Cepu"
        crumb="Berita"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Kolom Utama: Pencarian, Filter Chip, Daftar Berita --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Form Pencarian & Chip Kategori --}}
                <div class="space-y-3">
                    <form method="GET" action="{{ route('posts') }}" class="relative">
                        @if (request('cat'))
                            <input type="hidden" name="cat" value="{{ request('cat') }}">
                        @endif
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari berita..."
                            class="field pl-11"
                        >
                        <x-app-icon name="search" class="w-5 h-5 text-ink-mute absolute left-3.5 top-1/2 -translate-y-1/2" />
                    </form>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('posts', request('q') ? ['q' => request('q')] : []) }}"
                            class="chip {{ !request('cat') ? 'chip-active' : '' }}"
                        >Semua</a>
                        @foreach (\App\Enums\PostCategory::cases() as $cat)
                            <a
                                href="{{ route('posts', array_merge(request('q') ? ['q' => request('q')] : [], ['cat' => $cat->value])) }}"
                                class="chip {{ request('cat') === $cat->value ? 'chip-active' : '' }}"
                            >{{ $cat->label() }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Daftar Berita Kartu Horizontal Figma --}}
                <div class="space-y-4">
                    @forelse ($posts as $post)
                        <x-card class="p-0 overflow-hidden flex flex-col sm:flex-row hover:border-brand transition-all group shadow-xs">
                            @if ($post->featured_image)
                                <div class="w-full sm:w-56 h-48 sm:h-auto shrink-0 overflow-hidden">
                                    <img
                                        src="{{ asset('storage/' . $post->featured_image) }}"
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
                                    <div class="mb-2.5">
                                        <x-badge type="blue">{{ $post->category->label() }}</x-badge>
                                    </div>
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
                        <x-empty-state
                            title="Berita tidak ditemukan"
                            text="Tidak ada berita yang cocok dengan kata kunci atau kategori yang Anda pilih."
                        />
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
