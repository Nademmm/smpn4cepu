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
        $photosData = $photos->map(fn($p) => [
            'url' => $p->photo_url,
            'title' => $p->title,
            'caption' => $p->caption ?? '',
            'tag' => $p->tag ?? '',
        ])->values();
    @endphp

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8"
        x-data="{
            selectedTag: 'Semua',
            allPhotos: @js($photosData),
            currentIndex: -1,
            openPreview(idx) {
                this.currentIndex = idx;
            },
            closePreview() {
                this.currentIndex = -1;
            },
            prevPhoto() {
                const list = this.filteredIndices;
                if (!list.length) return;
                const pos = list.indexOf(this.currentIndex);
                if (pos > 0) {
                    this.currentIndex = list[pos - 1];
                } else {
                    this.currentIndex = list[list.length - 1];
                }
            },
            nextPhoto() {
                const list = this.filteredIndices;
                if (!list.length) return;
                const pos = list.indexOf(this.currentIndex);
                if (pos >= 0 && pos < list.length - 1) {
                    this.currentIndex = list[pos + 1];
                } else {
                    this.currentIndex = list[0];
                }
            },
            get filteredIndices() {
                return this.allPhotos
                    .map((p, i) => ({ tag: p.tag, i }))
                    .filter(p => this.selectedTag === 'Semua' || p.tag === this.selectedTag)
                    .map(p => p.i);
            },
            get activePhoto() {
                return this.currentIndex >= 0 ? this.allPhotos[this.currentIndex] : null;
            },
            get positionText() {
                const list = this.filteredIndices;
                const pos = list.indexOf(this.currentIndex) + 1;
                return pos > 0 ? `Foto ${pos} dari ${list.length}` : '';
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', currentIndex >= 0)"
    >
        {{-- Filter Kategori / Tag --}}
        @if ($tags->count() > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                @foreach ($tags as $tag)
                    <button
                        type="button"
                        @click="selectedTag = @js($tag)"
                        :class="selectedTag === @js($tag) ? 'bg-brand text-white border-brand shadow-xs' : 'bg-white text-navy hover:bg-sky-soft border-sun-soft'"
                        class="text-xs font-extrabold px-4 py-2 rounded-full border transition-all shrink-0 cursor-pointer min-h-[38px]"
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
                @foreach ($photos as $index => $p)
                    <div
                        x-show="selectedTag === 'Semua' || selectedTag === @js($p->tag)"
                        x-transition
                        tabindex="0"
                        role="button"
                        aria-label="Lihat foto {{ $p->title }}"
                        class="card p-0 overflow-hidden flex flex-col justify-between hover:border-brand focus:outline-none focus:ring-2 focus:ring-brand transition-all group cursor-pointer shadow-xs"
                        @click="openPreview({{ $index }})"
                        @keydown.enter.prevent="openPreview({{ $index }})"
                        @keydown.space.prevent="openPreview({{ $index }})"
                    >
                        <div>
                            <div class="relative overflow-hidden">
                                <img
                                    src="{{ $p->photo_url }}"
                                    alt="{{ $p->title }}"
                                    class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <div class="absolute inset-0 bg-navy/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                    <span class="bg-white/90 text-navy text-xs font-black px-3.5 py-1.5 rounded-full shadow-md flex items-center gap-1.5">
                                        <x-app-icon name="photo" class="w-3.5 h-3.5 text-brand" />
                                        <span>Perbesar</span>
                                    </span>
                                </div>
                            </div>
                            <div class="p-5 sm:p-6">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    @if ($p->tag)
                                        <span class="text-[11px] font-black uppercase tracking-wider text-brand">
                                            {{ $p->tag }}
                                        </span>
                                    @endif
                                </div>
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

        {{-- Modal Preview Foto (Teleport ke Body) Dengan Navigasi Panah & Keyboard --}}
        <template x-teleport="body">
            <div
                x-show="currentIndex >= 0"
                x-cloak
                role="dialog"
                aria-modal="true"
                :aria-label="activePhoto?.title || 'Pratinjau foto dokumentasi'"
                @keydown.escape.window="closePreview()"
                @keydown.arrow-left.window="if (currentIndex >= 0) prevPhoto()"
                @keydown.arrow-right.window="if (currentIndex >= 0) nextPhoto()"
                class="fixed inset-0 z-[100] w-screen h-screen min-h-screen flex items-center justify-center p-3 sm:p-6 bg-navy/85 backdrop-blur-sm overflow-y-auto"
            >
                <div
                    @click.away="closePreview()"
                    class="card max-w-3xl w-full p-4 sm:p-6 space-y-4 bg-white shadow-2xl relative"
                >
                    <div class="flex items-center justify-between pb-3 border-b-2 border-sun-soft">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2.5 py-0.5 rounded-full bg-cream text-navy border border-sun-soft text-xs font-black" x-text="positionText"></span>
                            <h3 class="font-extrabold text-navy text-base sm:text-lg line-clamp-1" x-text="activePhoto?.title"></h3>
                        </div>
                        <button
                            type="button"
                            @click="closePreview()"
                            aria-label="Tutup pratinjau foto"
                            class="p-2 min-h-[44px] min-w-[44px] rounded-xl text-ink-mute hover:text-navy hover:bg-cream/60 transition-colors flex items-center justify-center"
                        >
                            <x-app-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Wadah Foto dengan Tombol Navigasi Next/Prev --}}
                    <div class="relative flex items-center justify-center bg-sand/30 rounded-2xl overflow-hidden min-h-[260px] max-h-[65vh]">
                        <img
                            :src="activePhoto?.url"
                            :alt="activePhoto?.title"
                            class="max-h-[65vh] w-auto max-w-full object-contain rounded-xl"
                        >

                        {{-- Tombol Navigasi Kiri / Sebelumnya --}}
                        <button
                            type="button"
                            @click.stop="prevPhoto()"
                            aria-label="Foto sebelumnya"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 hover:bg-white text-navy flex items-center justify-center shadow-lg hover:scale-105 transition-all focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                            <x-app-icon name="arrow-left" class="w-5 h-5" />
                        </button>

                        {{-- Tombol Navigasi Kanan / Selanjutnya --}}
                        <button
                            type="button"
                            @click.stop="nextPhoto()"
                            aria-label="Foto selanjutnya"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 hover:bg-white text-navy flex items-center justify-center shadow-lg hover:scale-105 transition-all focus:outline-none focus:ring-2 focus:ring-brand"
                        >
                            <x-app-icon name="arrow-right" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pt-1">
                        <p class="text-xs sm:text-sm text-ink-soft font-semibold leading-relaxed" x-text="activePhoto?.caption || 'Dokumentasi kegiatan resmi SMP Negeri 4 Cepu'"></p>
                        <span class="text-[11px] text-ink-mute font-medium shrink-0">Gunakan tombol panah keyboard ← → untuk berpindah</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-layouts.app>
