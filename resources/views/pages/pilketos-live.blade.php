<x-layouts.app title="Quick Count Real-Time Pilketos">
    <x-page-header
        title="Quick Count Suara Pilketos"
        subtitle="Pantau perolehan suara pemilihan ketua dan wakil ketua OSIS secara real-time dan terbuka."
        crumb="Quick Count"
    />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('pilketos') }}" class="link-brand text-sm inline-flex items-center gap-1">
                <x-app-icon name="arrow-left" class="w-4 h-4" />
                <span>Kembali ke Bilik Suara</span>
            </a>
        </div>

        <livewire:pilketos.live-count />
    </div>
</x-layouts.app>
