<x-layouts.app title="Quick Count Real-Time Pilketos" :noindex="true" metaDescription="Perolehan suara Pilketos SMP Negeri 4 Cepu yang diperbarui langsung selama pemungutan suara berlangsung.">
    <x-page-header
        title="Pemilihan Ketua & Wakil OSIS"
        subtitle="Pantau perolehan suara pemilihan ketua dan wakil ketua OSIS secara real-time, jujur, dan terbuka."
        crumb="Quick Count"
        variant="amber"
    >
        <x-slot:tabs>
            <a href="{{ route('pilketos') }}" class="tab">
                Bilik Suara (E-Voting)
            </a>
            <a href="{{ route('pilketos.live') }}" class="tab tab-active">
                Quick Count Suara
            </a>
        </x-slot:tabs>
    </x-page-header>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-6">
        <livewire:pilketos.live-count />
    </div>
</x-layouts.app>
