<x-layouts.app title="Prestasi Siswa & Satuan Pendidikan">
    <x-page-header
        title="Prestasi & Penghargaan Sekolah"
        subtitle="Rekapitulasi kejuaraan, medali, dan penghargaan yang diraih siswa dan keluarga besar SMP Negeri 4 Cepu."
        crumb="Prestasi Sekolah"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-8" x-data="{ activeLevel: 'Semua' }">
        {{-- Filter Tingkat Kejuaraan --}}
        <div class="flex flex-wrap items-center gap-2 pb-2 border-b-2 border-sun-soft">
            <button
                type="button"
                @click="activeLevel = 'Semua'"
                :class="activeLevel === 'Semua' ? 'bg-brand text-white shadow-xs' : 'bg-white text-navy hover:bg-cream border border-sun-soft'"
                class="px-4 py-2 rounded-full text-xs sm:text-sm font-extrabold transition-all"
            >
                Semua Tingkat
            </button>
            @php
                $levels = $achievements->pluck('level')->unique()->values();
            @endphp
            @foreach ($levels as $lvl)
                <button
                    type="button"
                    @click="activeLevel = @js($lvl)"
                    :class="activeLevel === @js($lvl) ? 'bg-brand text-white shadow-xs' : 'bg-white text-navy hover:bg-cream border border-sun-soft'"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-extrabold transition-all"
                >
                    Tingkat {{ $lvl }}
                </button>
            @endforeach
        </div>

        {{-- Grid Prestasi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($achievements as $ach)
                <div
                    x-show="activeLevel === 'Semua' || activeLevel === @js($ach->level)"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="h-full"
                >
                    <x-card class="p-0 overflow-hidden flex flex-col h-full hover:border-brand transition-colors group">
                        @if ($ach->photo_url)
                            <div class="w-full h-48 overflow-hidden bg-cream relative">
                                <img
                                    src="{{ $ach->photo_url }}"
                                    alt="{{ $ach->title }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                                <span class="absolute top-3 left-3 bg-sun text-navy font-black text-xs px-3 py-1 rounded-full uppercase shadow-xs">
                                    {{ $ach->rank }}
                                </span>
                            </div>
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-amber-50 to-amber-100/60 flex flex-col items-center justify-center text-amber-600 relative">
                                <x-app-icon name="trophy" class="w-14 h-14" />
                                <span class="absolute top-3 left-3 bg-sun text-navy font-black text-xs px-3 py-1 rounded-full uppercase shadow-xs">
                                    {{ $ach->rank }}
                                </span>
                            </div>
                        @endif

                        <div class="p-5 flex flex-col grow space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-black uppercase tracking-wider text-brand bg-sky-soft px-2.5 py-1 rounded-full">
                                    Tingkat {{ $ach->level }}
                                </span>
                                @if ($ach->event_date)
                                    <span class="text-xs font-semibold text-ink-mute">
                                        {{ $ach->event_date->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </div>

                            <h2 class="text-base font-black text-navy leading-snug group-hover:text-brand transition-colors">
                                {{ $ach->title }}
                            </h2>

                            <div class="bg-cream/60 rounded-xl p-3 border border-sun-soft text-xs text-navy font-bold">
                                <span class="text-ink-soft block font-medium text-[11px]">Peraih Penghargaan:</span>
                                {{ $ach->winner_name }}
                            </div>

                            @if ($ach->description)
                                <p class="text-xs text-ink-soft leading-relaxed font-medium line-clamp-3">
                                    {{ $ach->description }}
                                </p>
                            @endif
                        </div>
                    </x-card>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Belum Ada Data Prestasi"
                        text="Data rekapitulasi penghargaan dan kejuaraan siswa akan segera diperbarui."
                    />
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
