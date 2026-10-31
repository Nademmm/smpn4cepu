<x-layouts.app
    title="Perpustakaan Digital"
    metaDescription="Perpustakaan Digital SMP Negeri 4 Cepu: Temukan buku paket Kurikulum Merdeka (Kelas VII, VIII, IX), karya literasi nusantara, buletin sekolah, dan baca e-book resmi secara langsung."
>
    {{-- Page Header --}}
    <x-page-header
        title="Perpustakaan Digital"
        subtitle="Ruang Belajar Mandiri: Akses buku paket Kurikulum Merdeka, karya literasi nusantara, dan publikasi SMP Negeri 4 Cepu."
        crumb="Perpustakaan"
    />

    {{-- Main Livewire Component --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <livewire:public.digital-library />
    </div>
</x-layouts.app>
