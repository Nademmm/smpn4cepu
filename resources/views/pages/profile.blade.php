<x-layouts.app title="Profil Sekolah & Sejarah">
    {{-- Page Header --}}
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Identitas Satuan Pendidikan</span>
            <h1 class="text-3xl sm:text-4xl font-black mt-2 tracking-tight">Profil, Sejarah & Visi Misi</h1>
            <p class="text-sm text-slate-300 mt-2 max-w-2xl">
                Mengenal lebih dekat perjalanan panjang SMP Negeri 4 Cepu sejak berdirinya pada tahun 1979 hingga transformasi era digital masa kini.
            </p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        {{-- Visi & Misi Sekolah --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Visi --}}
            <div class="lg:col-span-5 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-lg flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-200">Visi Resmi Sekolah</span>
                    <h2 class="text-2xl font-black mt-3 mb-6 leading-snug">
                        "CERIA BERIMAN"
                    </h2>
                    <blockquote class="text-sm text-blue-50 leading-relaxed italic border-l-2 border-blue-300 pl-4 mb-6">
                        "Terwujudnya Peserta Didik yang Cerdas, Inovatif, Kreatif, Berintegritas, Mandiri dalam Pelestarian Lingkungan Hidup."
                    </blockquote>
                </div>
                <div class="text-xs text-blue-200 pt-4 border-t border-blue-500/40">
                    Karakteristik: Kawasan industri vokasi migas, agrikultur, dan pelestarian alam terpadu.
                </div>
            </div>

            {{-- 11 Misi --}}
            <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">11 Misi Satuan Pendidikan</span>
                <h2 class="text-xl font-black text-slate-900 dark:text-white mt-1 mb-6">Komitmen Penyelenggaraan Pendidikan</h2>
                
                <ol class="space-y-3 text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed list-decimal list-inside">
                    <li class="pl-1">Menyelenggarakan pembelajaran dan bimbingan secara efektif untuk mengoptimalkan potensi akademik dan non-akademik yang dimiliki peserta didik.</li>
                    <li class="pl-1">Mengoptimalkan proses pembelajaran dengan pendekatan pembelajaran yang berpusat pada peserta didik (student centered learning), antara lain CTL, PAIKEM, serta bimbingan konseling.</li>
                    <li class="pl-1">Melaksanakan pembelajaran berbasis IT (Information Technology) dengan Google Suite for Education.</li>
                    <li class="pl-1">Melaksanakan pendidikan karakter melalui pembiasaan.</li>
                    <li class="pl-1">Mendorong dan mengajak warga sekolah untuk mematuhi aturan dan tata tertib sekolah.</li>
                    <li class="pl-1">Menyelenggarakan sholat dhuhur berjamaah dan kegiatan Baca Tulis Alquran.</li>
                    <li class="pl-1">Memberikan wadah kreasi, bakat, minat, dan kemampuan siswa melalui kegiatan ekstrakurikuler.</li>
                    <li class="pl-1">Mewujudkan lingkungan sekolah yang bersih, hijau, sejuk, dan sehat.</li>
                    <li class="pl-1">Mewujudkan pelestarian lingkungan hidup.</li>
                    <li class="pl-1">Mencegah pencemaran lingkungan hidup.</li>
                    <li class="pl-1">Menanggulangi kerusakan lingkungan hidup.</li>
                </ol>
            </div>
        </div>

        {{-- Sejarah Resmi --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-10 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Napak Tilas 1979</span>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Sejarah Berdirinya SMP Negeri 4 Cepu</h2>
            
            <div class="prose dark:prose-invert max-w-none text-sm text-slate-700 dark:text-slate-300 leading-relaxed space-y-4">
                <p>
                    SMP Negeri 4 Cepu didirikan berdasarkan Surat Keputusan Menteri Pendidikan dan Kebudayaan Republik Indonesia Nomor <strong>0188/O/1979</strong> tertanggal <strong>03 September 1979</strong>, dan secara resmi mulai beroperasi penuh pada tanggal <strong>4 September 1979</strong>.
                </p>
                <p>
                    Pada masa awal perintisannya, sekolah ini berstatus sebagai SMP Filial dari SMP Negeri 1 Cepu untuk menampung tingginya animo masyarakat kawasan Cepu dan sekitarnya akan pendidikan menengah pertama yang bermutu. Kepemimpinan satuan pendidikan pertama kali diamanahkan kepada Bapak <strong>YB. Moertadji</strong> sebagai kepala sekolah perintis.
                </p>
                <p>
                    Kini, SMP Negeri 4 Cepu telah bertransformasi menjadi satuan pendidikan terakreditasi <strong>A</strong> yang berwawasan lingkungan dan memadukan kearifan lokal kawasan vokasi industri minyak dan gas bumi (Migas) Cepu dengan inovasi teknologi informasi modern melalui konsep <em>"Satu Sekolah, Satu Ruang Digital"</em>.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-slate-100 dark:border-slate-800 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="block text-slate-400">Nomor Pokok Sekolah Nasional (NPSN)</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono text-sm mt-0.5 block">20314928</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="block text-slate-400">Status & Akreditasi</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm mt-0.5 block">Negeri (Terakreditasi A)</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="block text-slate-400">Wilayah Administratif</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm mt-0.5 block">Kec. Cepu, Kab. Blora</span>
                </div>
            </div>
        </div>

        {{-- Karakteristik Lingkungan Satuan Pendidikan & Wilayah Cepu --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    1
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Kawasan Vokasi & Energi Migas</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Kecamatan Cepu dikenal secara nasional sebagai sentra industri minyak bumi dan pusat pendidikan vokasi energi (Pusdiklat / PPSDM Migas). Kondisi ini membentuk kultur kedisiplinan, orientasi teknologi terapan, dan semangat adaptasi sains di lingkungan SMPN 4 Cepu.
                </p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    2
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Wawasan Lingkungan Hidup</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Terletak di Desa Mulyorejo dengan bentang alam agraris Blora yang asri, sekolah berkomitmen mengintegrasikan nilai pelestarian alam, pengelolaan sampah mandiri, pencegahan pencemaran, dan penghijauan ke dalam kurikulum pembelajaran.
                </p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    3
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">Penguatan Literasi & Karakter</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    Dukungan fasilitas perpustakaan terpadu, pembiasaan sholat berjamaah, BTA (Baca Tulis Alquran), serta ruang digital interaktif menjadikan peserta didik memiliki integritas spiritual sekaligus cakap bernalar kritis.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
