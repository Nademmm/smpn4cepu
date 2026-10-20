<x-layouts.app title="Bilik Suara E-Voting Pilketos" :noindex="true" metaDescription="Bilik suara e-voting Pilketos SMP Negeri 4 Cepu untuk memilih Ketua dan Wakil Ketua OSIS, satu perangkat satu suara.">
    <x-page-header
        title="Pemilihan Ketua & Wakil OSIS"
        subtitle="Gunakan hak suaramu secara cerdas, berintegritas, langsung, umum, bebas, rahasia, jujur, dan adil."
        crumb="Bilik Suara"
        variant="amber"
    >
        <x-slot:tabs>
            <a href="{{ route('pilketos') }}" class="tab tab-active">
                Bilik Suara (E-Voting)
            </a>
            <a href="{{ route('pilketos.live') }}" class="tab">
                Quick Count Suara
            </a>
        </x-slot:tabs>
    </x-page-header>

    <livewire:pilketos.voting-booth />
</x-layouts.app>
