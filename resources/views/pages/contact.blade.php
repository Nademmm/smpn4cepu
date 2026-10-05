<x-layouts.app title="Kontak & Lokasi">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Layanan Komunikasi</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Hubungi SMP Negeri 4 Cepu</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Layanan informasi, pengaduan masyarakat, konsultasi akademik, dan petunjuk arah lokasi fisik kampus sekolah.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Informasi Kontak Resmi --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-800">
                        Informasi Kantor Resmi
                    </h2>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Alamat Fisik:</span>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                Jl. Raya Cepu Randu Km. 3,5, Desa Mulyorejo, Kec. Cepu, Kab. Blora, Jawa Tengah 58315
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Telepon Sekolah:</span>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5 font-mono">
                                (0296) 421631
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Surel Resmi:</span>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5 font-mono">
                                smpn4cepu@smpn4cepu.sch.id
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Jam Pelayanan Kantor:</span>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                                Senin - Jumat: 07.00 - 15.30 WIB
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sematan Peta Lokasi Google Maps --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Titik Koordinat GPS</span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Peta Lokasi Kampus Sekolah</h2>
                        </div>
                        <span class="text-xs font-mono text-slate-400">-7.1576081, 111.5662531</span>
                    </div>

                    <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 h-96 w-full">
                        <iframe 
                            src="https://maps.google.com/maps?q=-7.1576081,111.5662531&hl=id&z=15&output=embed" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>

                    <p class="text-xs text-slate-400 text-center">
                        Titik koordinat resmi SMP Negeri 4 Cepu terdaftar di Dapodik Kemendikbudristek.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
