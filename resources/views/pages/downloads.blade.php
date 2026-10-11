<x-layouts.app title="Pusat Unduhan Berkas">
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
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-4">
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
            @foreach ($documents as $doc)
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
                            class="btn-primary text-xs w-full sm:w-auto !min-h-[38px] shadow-2xs inline-flex items-center justify-center gap-2"
                        >
                            <x-app-icon name="download" class="w-4 h-4" />
                            Unduh Berkas
                        </a>
                    </div>
                </x-card>
            @endforeach
        @endif
    </div>
</x-layouts.app>
