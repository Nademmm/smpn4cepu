<x-layouts.app title="Pusat Unduhan Berkas" metaDescription="Unduh dokumen resmi SMP Negeri 4 Cepu, mulai dari formulir hingga panduan dan berkas administrasi sekolah, dikelompokkan per kategori.">
    <x-page-header
        title="Pusat Unduhan Berkas"
        subtitle="Dokumen resmi, formulir administrasi kesiswaan, tata tertib, dan kalender akademik SMP Negeri 4 Cepu."
        crumb="Unduhan Berkas"
    />

    @php
        $documents = $documents ?? \App\Models\SchoolDocument::where('is_active', true)
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        $categories = collect(['Semua'])->merge($documents->pluck('category')->unique()->filter())->values();
        $docsData = $documents->map(fn($d) => [
            'id' => $d->id,
            'title' => $d->title,
            'category' => $d->category,
            'description' => $d->description ?? '',
        ])->values();
    @endphp

    <div
        class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-6"
        x-data="{
            selectedCategory: 'Semua',
            searchQuery: '',
            allDocs: @js($docsData),
            matches(cat, title, desc) {
                const matchesCat = this.selectedCategory === 'Semua' || this.selectedCategory === cat;
                if (!matchesCat) return false;
                if (!this.searchQuery.trim()) return true;
                const q = this.searchQuery.toLowerCase();
                return (title && title.toLowerCase().includes(q)) || (desc && desc.toLowerCase().includes(q));
            },
            get filteredCount() {
                return this.allDocs.filter(d => this.matches(d.category, d.title, d.description)).length;
            }
        }"
    >
        @if ($documents->isNotEmpty())
            {{-- Filter Kategori & Pencarian Berkas --}}
            <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-end justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-ink-mute block mb-2">KATEGORI DOKUMEN</span>
                    <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter Kategori Dokumen">
                        @foreach ($categories as $cat)
                            <button
                                type="button"
                                @click="selectedCategory = @js($cat)"
                                :class="selectedCategory === @js($cat) ? 'chip-active' : ''"
                                class="chip transition-all"
                            >
                                {{ $cat }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="w-full md:w-80">
                    <label for="doc-search" class="text-xs font-bold uppercase tracking-wider text-ink-mute block mb-2">CARI BERKAS</label>
                    <div class="relative">
                        <input
                            id="doc-search"
                            type="search"
                            x-model="searchQuery"
                            placeholder="Cari nama dokumen atau formulir..."
                            aria-label="Cari dokumen resmi"
                            class="field !py-2.5 pl-9 pr-4 text-xs font-bold"
                        >
                        <x-app-icon name="search" class="w-4 h-4 text-ink-mute absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    </div>
                </div>
            </div>
        @endif

        @if ($documents->isEmpty())
            <x-card class="text-center py-12 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-sky-soft text-brand flex items-center justify-center">
                    <x-app-icon name="download" class="w-6 h-6" />
                </div>
                <h3 class="font-extrabold text-navy text-base">Belum Ada Dokumen Publik</h3>
                <p class="text-xs text-ink-soft max-w-md mx-auto">
                    Berkas resmi sedang dalam proses pembaruan oleh bagian Tata Usaha SMP Negeri 4 Cepu.
                </p>
            </x-card>
        @else
            <div class="space-y-4">
                @foreach ($documents as $doc)
                    <div
                        x-show="matches(@js($doc->category), @js($doc->title), @js($doc->description ?? ''))"
                        x-transition
                    >
                        <x-card class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 p-5 sm:p-6 hover:border-brand transition-all group">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-sky-soft text-brand flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-app-icon name="download" class="w-6 h-6" />
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <x-badge type="blue">{{ $doc->category }}</x-badge>
                                        <span class="text-[11px] font-bold text-ink-mute uppercase tracking-wider">
                                            {{ strtoupper($doc->file_extension ?: 'PDF') }} · {{ $doc->file_size ?: 'Dokumen Resmi' }}
                                        </span>
                                    </div>
                                    <h2 class="font-extrabold text-navy text-base leading-snug group-hover:text-brand transition-colors">
                                        {{ $doc->title }}
                                    </h2>
                                    @if ($doc->description)
                                        <p class="text-xs text-ink-soft leading-relaxed mt-1 font-medium">
                                            {{ $doc->description }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 w-full sm:w-auto">
                                <a
                                    href="{{ route('downloads.file', $doc->id) }}"
                                    class="btn-primary text-xs w-full sm:w-auto !min-h-[44px] px-5 shadow-2xs inline-flex items-center justify-center gap-2"
                                >
                                    <x-app-icon name="download" class="w-4 h-4" />
                                    <span>Unduh Berkas</span>
                                </a>
                            </div>
                        </x-card>
                    </div>
                @endforeach

                {{-- Status Pencarian Kosong --}}
                <div
                    x-show="filteredCount === 0"
                    x-cloak
                >
                    <x-card class="text-center py-10 space-y-3">
                        <p class="text-base font-extrabold text-navy">Tidak Ada Berkas yang Sesuai</p>
                        <p class="mx-auto max-w-md text-sm text-ink-soft">
                            Tidak ditemukan berkas untuk kata kunci pencarian atau kategori yang dipilih.
                        </p>
                        <div class="pt-2 flex justify-center gap-2">
                            <button
                                type="button"
                                @click="selectedCategory = 'Semua'; searchQuery = ''"
                                class="btn-primary text-xs"
                            >
                                Tampilkan Semua Berkas
                            </button>
                        </div>
                    </x-card>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
