<x-layouts.app title="Fasilitas Sarana Prasarana">
    <x-page-header
        title="Fasilitas Sekolah"
        subtitle="11 sarana dan prasarana pendukung pembelajaran yang representatif dan berwawasan lingkungan."
        crumb="Fasilitas"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($facilities as $facility)
                <x-card class="p-0 overflow-hidden flex flex-col hover:border-brand transition-all group">
                    @if ($facility->photo_path && file_exists(public_path('images/assets/' . $facility->photo_path)))
                        <div class="overflow-hidden">
                            <img
                                src="{{ asset('images/assets/' . $facility->photo_path) }}"
                                alt="{{ $facility->name }}"
                                class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-300"
                            >
                        </div>
                    @else
                        <div class="w-full h-52 bg-sky-soft flex items-center justify-center text-brand">
                            <x-app-icon name="building" class="w-12 h-12" />
                        </div>
                    @endif

                    <div class="p-5 sm:p-6 flex flex-col grow">
                        <h2 class="text-lg font-extrabold text-navy mb-2 leading-snug group-hover:text-brand transition-colors">
                            {{ $facility->name }}
                        </h2>
                        <p class="text-sm text-ink-soft leading-relaxed font-medium">
                            {{ $facility->description }}
                        </p>
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
