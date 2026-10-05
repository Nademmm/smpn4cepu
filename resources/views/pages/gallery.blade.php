<x-layouts.app title="Galeri Dokumentasi Kegiatan">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Dokumentasi Sekolah</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Galeri Foto SMP Negeri 4 Cepu</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Dokumentasi resmi aktivitas pembelajaran, fasilitas sarana prasarana, apel upacara, dan lingkungan asri kampus sekolah.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group">
                <div class="overflow-hidden h-64">
                    <img src="{{ asset('images/assets/20260717_092659.jpg') }}" alt="Gedung Gerbang Utama SMPN 4 Cepu" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">Sarana & Prasarana</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Gedung dan Gerbang Utama Sekolah</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Tampak depan gerbang utama dan lingkungan asri SMP Negeri 4 Cepu di Jl. Raya Cepu Randu Km. 3,5.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group">
                <div class="overflow-hidden h-64">
                    <img src="{{ asset('images/assets/IMG_5797.JPG') }}" alt="Ruang Pembelajaran dan Fasilitas" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">Aktivitas Akademik</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Ruang Pembelajaran Interaktif</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Suasana ruang kelas yang kondusif, bersih, dan berorientasi student-centered learning.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group">
                <div class="overflow-hidden h-64">
                    <img src="{{ asset('images/assets/IMG_6518.JPG') }}" alt="Kegiatan Upacara dan Pembiasaan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">Pendidikan Karakter</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Lapangan Upacara dan Apel Siswa</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Area luas untuk upacara bendera hari Senin dan pembiasaan apel kedisiplinan berkarakter.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group">
                <div class="overflow-hidden h-64">
                    <img src="{{ asset('images/assets/IMG_6522.JPG') }}" alt="Gedung Serbaguna Indoor" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">Kesiswaan & Ekstrakurikuler</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Gedung Indoor dan Ruang Olahraga</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Fasilitas serbaguna untuk olahraga bulu tangkis, latihan seni, dan pertemuan sekolah.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group sm:col-span-2 lg:col-span-2">
                <div class="overflow-hidden h-64">
                    <img src="{{ asset('images/assets/IMG_8383.JPG') }}" alt="Lanskap Lingkungan Asri" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-6">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block mb-1">Pelestarian Lingkungan</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Lanskap Lingkungan Berkelanjutan SMPN 4 Cepu</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Perwujudan misi sekolah dalam menciptakan suasana belajar yang hijau, sejuk, sehat, dan berwawasan pelestarian lingkungan hidup.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
