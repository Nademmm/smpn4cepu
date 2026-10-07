<x-layouts.app title="Fasilitas Sarana Prasarana">
    <x-page-header
        title="Fasilitas Sekolah"
        subtitle="11 sarana dan prasarana pendukung pembelajaran yang representatif dan berwawasan lingkungan."
        crumb="Fasilitas"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($facilities as $facility)
                <x-card class="p-0 overflow-hidden flex flex-col justify-between hover:border-brand transition-colors">
                    <div>
                        @if ($facility->photo_path && file_exists(public_path('images/assets/' . $facility->photo_path)))
                            <img
                                src="{{ asset('images/assets/' . $facility->photo_path) }}"
                                alt="{{ $facility->name }}"
                                class="w-full h-48 object-cover"
                            >
                        @else
                            <div class="w-full h-48 bg-sky-soft flex items-center justify-center text-brand">
                                <x-app-icon name="building" class="w-12 h-12" />
                            </div>
                        @endif

                        <div class="p-5">
                            <h2 class="text-lg font-extrabold text-navy mb-2 leading-snug">
                                {{ $facility->name }}
                            </h2>
                            <p class="text-xs text-ink-soft leading-relaxed">
                                {{ $facility->description }}
                            </p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-0">
                        <span class="inline-block text-[11px] font-bold text-brand bg-sky-soft px-3 py-1 rounded-full">
                            Fasilitas Resmi SMPN 4 Cepu
                        </span>
                    </div>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-empty-state
                        title="Fasilitas Belum Tercatat"
                        text="Data sarana dan prasarana sekolah sedang dalam proses pembaruan inventaris."
                    />
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
