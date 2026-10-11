<x-layouts.app title="Galeri Foto Kegiatan">
    <x-page-header
        title="Galeri Kegiatan"
        subtitle="Dokumentasi aktivitas pembelajaran, ekstrakurikuler, dan momentum kebersamaan di SMP Negeri 4 Cepu."
        crumb="Galeri Foto"
    />

    @php
        $photos = $photos ?? \App\Models\GalleryPhoto::where('is_active', true)
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        $tags = collect(['Semua'])->merge($photos->pluck('tag')->unique()->filter())->values();
    @endphp

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8"
        x-data="{
            selectedTag: 'Semua',
            activeImage: null,
            activeTitle: '',
            activeCaption: '',
            openPreview(img, title, caption) {
                this.activeImage = img;
                this.activeTitle = title;
                this.activeCaption = caption;
            }
        }"
    >
        {{-- Filter Kategori / Tag --}}
        @if ($tags->count() > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach ($tags as $tag)
                    <button
                        type="button"
                        @click="selectedTag = '{{ $tag }}'"
                        :class="selectedTag === '{{ $tag }}' ? 'bg-brand text-white border-brand shadow-xs' : 'bg-white text-navy hover:bg-sky-soft border-sun-soft'"
                        class="text-xs font-extrabold px-4 py-2 rounded-full border transition-all shrink-0 cursor-pointer"
                    >
                        {{ $tag }}
                    </button>
                @endforeach
            </div>
        @endif

        @if ($photos->isEmpty())
            <x-card class="text-center py-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-sky-soft text-brand flex items-center justify-center">
                    <x-app-icon name="photo" class="w-6 h-6" />
                </div>
                <h3 class="font-extrabold text-navy text-base">Belum Ada Dokumentasi Kegiatan</h3>
                <p class="text-xs text-ink-soft max-w-md mx-auto">
                    Foto dokumentasi sekolah akan segera diperbarui oleh tim IT dan kesiswaan SMP Negeri 4 Cepu.
                </p>
            </x-card>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($photos as $p)
                    <div
                        x-show="selectedTag === 'Semua' || selectedTag === '{{ $p->tag }}'"
                        x-transition
                        class="card p-0 overflow-hidden flex flex-col justify-between hover:border-brand transition-all group cursor-pointer shadow-xs"
                        @click="openPreview('{{ $p->photo_url }}', '{{ addslashes($p->title) }}', '{{ addslashes($p->caption ?? '') }}')"
                    >
                        <div>
                            <div class="relative overflow-hidden">
                                <img
                                    src="{{ $p->photo_url }}"
                                    alt="{{ $p->title }}"
                                    class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-navy/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                    <span class="bg-white/90 text-navy text-xs font-black px-3.5 py-1.5 rounded-full shadow-md flex items-center gap-1.5">
                                        <x-app-icon name="photo" class="w-3.5 h-3.5 text-brand" />
                                        <span>Perbesar</span>
                                    </span>
                                </div>
                            </div>
                            <div class="p-5 sm:p-6">
                                <h3 class="font-extrabold text-navy text-base leading-snug mb-1.5 group-hover:text-brand transition-colors">
                                    {{ $p->title }}
                                </h3>
                                @if ($p->caption)
                                    <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium">
                                        {{ $p->caption }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Modal Preview Foto (Teleport ke Body) --}}
        <template x-teleport="body">
            <div
                x-show="activeImage !== null"
                x-cloak
                @keydown.escape.window="activeImage = null"
                class="fixed inset-0 z-[100] w-screen h-screen min-h-screen flex items-center justify-center p-4 bg-navy/80 backdrop-blur-sm overflow-y-auto"
            >
                <div
                    @click.away="activeImage = null"
                    class="card max-w-2xl w-full p-4 space-y-3 bg-white shadow-2xl"
                >
                    <div class="flex items-center justify-between pb-2 border-b-2 border-sun-soft">
                        <h3 class="font-extrabold text-navy text-base" x-text="activeTitle"></h3>
                        <button type="button" @click="activeImage = null" class="p-1 rounded-lg text-ink-mute hover:text-navy">
                            <x-app-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>
                    <img :src="activeImage" :alt="activeTitle" class="w-full max-h-[70vh] object-contain rounded-xl border border-sun-soft">
                    <p class="text-xs text-ink-soft font-semibold" x-text="activeCaption"></p>
                </div>
            </div>
        </template>
    </div>
</x-layouts.app>
