<x-layouts.app title="Ekstrakurikuler & Pengembangan Bakat">
    <x-page-header
        title="Ekstrakurikuler Sekolah"
        subtitle="Wadah eksplorasi potensi, kreasi bakat, kepemimpinan, dan sportivitas peserta didik SMP Negeri 4 Cepu."
        crumb="Ekstrakurikuler"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8" x-data="{ activeCategory: 'Semua' }">
        {{-- Filter Kategori Interaktif --}}
        <div class="flex flex-wrap items-center gap-2 pb-2 border-b-2 border-sun-soft">
            <button
                type="button"
                @click="activeCategory = 'Semua'"
                :class="activeCategory === 'Semua' ? 'bg-brand text-white shadow-xs' : 'bg-white text-navy hover:bg-cream border border-sun-soft'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-extrabold transition-all"
            >
                Semua Bidang
            </button>
            @php
                $categories = $extracurriculars->pluck('category')->unique()->values();
            @endphp
            @foreach ($categories as $cat)
                <button
                    type="button"
                    @click="activeCategory = '{{ $cat }}'"
                    :class="activeCategory === '{{ $cat }}' ? 'bg-brand text-white shadow-xs' : 'bg-white text-navy hover:bg-cream border border-sun-soft'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-extrabold transition-all"
                >
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        {{-- Grid Ekstrakurikuler --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($extracurriculars as $extra)
                <div
                    x-show="activeCategory === 'Semua' || activeCategory === '{{ $extra->category }}'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="h-full"
                >
                    <x-card class="p-0 overflow-hidden flex flex-col h-full hover:border-brand transition-colors group">
                        @if ($extra->photo_url)
                            <div class="w-full h-48 overflow-hidden bg-cream">
                                <img
                                    src="{{ $extra->photo_url }}"
                                    alt="{{ $extra->name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                            </div>
                        @else
                            <div class="w-full h-48 bg-sky-soft flex items-center justify-center text-brand">
                                <x-app-icon name="sparkles" class="w-12 h-12" />
                            </div>
                        @endif

                        <div class="p-5 flex flex-col grow space-y-3">
                            <h2 class="text-lg font-black text-navy leading-snug group-hover:text-brand transition-colors">
                                {{ $extra->name }}
                            </h2>

                            @if ($extra->description)
                                <p class="text-xs text-ink-soft leading-relaxed font-medium line-clamp-3">
                                    {{ $extra->description }}
                                </p>
                            @endif

                            <div class="pt-3 border-t border-sun-soft/60 space-y-1.5 text-xs text-navy font-semibold mt-auto">
                                @if ($extra->leader_name)
                                    <div class="flex items-center gap-2">
                                        <x-app-icon name="user" class="w-4 h-4 text-brand shrink-0" />
                                        <span>Pembina: <strong class="text-brand">{{ $extra->leader_name }}</strong></span>
                                    </div>
                                @endif
                                @if ($extra->schedule_day)
                                    <div class="flex items-center gap-2">
                                        <x-app-icon name="calendar" class="w-4 h-4 text-amber-600 shrink-0" />
                                        <span>{{ $extra->schedule_day }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-card>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Belum Ada Data Ekstrakurikuler"
                        text="Data kegiatan ekstrakurikuler sekolah akan segera diumumkan oleh pihak kesiswaan."
                    />
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
