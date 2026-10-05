<x-layouts.app title="Fasilitas & Sarana Prasarana">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Sarana & Prasarana</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">11 Fasilitas Resmi Sekolah</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Sarana pembelajaran, laboratorium praktikum, sarana olahraga, dan fasilitas penunjang tumbuh kembang siswa di SMP Negeri 4 Cepu.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($facilities as $fac)
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        @if($fac->photo_path)
                            <img src="{{ asset($fac->photo_path) }}" alt="{{ $fac->name }}" class="w-full h-52 object-cover">
                        @else
                            <div class="w-full h-52 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                        @endif
                        <div class="p-6">
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 block mb-1">Fasilitas #{{ $fac->display_order }}</span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-lg mb-2">{{ $fac->name }}</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $fac->description }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
