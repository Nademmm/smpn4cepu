<x-layouts.app title="Quick Count Real-Time Pilketos">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Transparansi Pemilihan</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Quick Count & Rekapitulasi Suara</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Pantau perolehan suara pemilihan ketua OSIS secara real-time dan terbuka. Grafik disinkronkan otomatis dari server setiap 5 detik.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <livewire:pilketos.live-count />
    </div>
</x-layouts.app>
