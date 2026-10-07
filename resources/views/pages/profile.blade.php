<x-layouts.app title="Profil, Sejarah & Visi Misi">
    <x-page-header
        title="Profil & Visi Misi Sekolah"
        subtitle="Mengenal lebih dekat identitas, sejarah berdirinya, visi misi, dan karakteristik satuan pendidikan SMP Negeri 4 Cepu."
        crumb="Profil Sekolah"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-10">
        {{-- Visi & Misi --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            {{-- Visi --}}
            <div class="lg:col-span-5 rounded-card p-8 bg-gradient-to-br from-brand to-brand-dark text-white flex flex-col justify-between shadow-xs">
                <div>
                    <span class="inline-block bg-sun text-navy font-black text-xs px-3 py-1 rounded-full uppercase tracking-wider mb-4">
                        Visi Resmi
                    </span>
                    <h2 class="text-3xl font-extrabold text-white mb-4 leading-tight">
                        "CERIA BERIMAN"
                    </h2>
                    <blockquote class="text-sm text-white/90 leading-relaxed italic border-l-4 border-sun pl-4">
                        "Terwujudnya Peserta Didik yang Cerdas, Inovatif, Kreatif, Berintegritas, Mandiri dalam Pelestarian Lingkungan Hidup."
                    </blockquote>
                </div>
                <div class="mt-8 pt-4 border-t border-white/20 text-xs text-white/80">
                    Karakteristik: Industri vokasi energi migas, agrikultur Blora, dan wawasan adiwiyata.
                </div>
            </div>

            {{-- 11 Misi --}}
            <x-card class="lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b-2 border-sun-soft">
                    <h2 class="text-xl font-extrabold text-navy">11 Misi Satuan Pendidikan</h2>
                    <x-badge type="blue">Kurikulum Merdeka</x-badge>
                </div>

                <ol class="space-y-2.5 text-xs sm:text-sm text-navy leading-relaxed list-decimal list-inside font-semibold">
                    <li>Menyelenggarakan pembelajaran dan bimbingan secara efektif untuk mengoptimalkan potensi akademik dan non-akademik peserta didik.</li>
                    <li>Mengoptimalkan pendekatan pembelajaran yang berpusat pada peserta didik (student centered learning).</li>
                    <li>Melaksanakan pembelajaran berbasis IT (Information Technology) secara terintegrasi.</li>
                    <li>Melaksanakan pendidikan karakter melalui keteladanan dan pembiasaan positif.</li>
                    <li>Mendorong seluruh warga sekolah untuk mematuhi aturan dan tata tertib sekolah.</li>
                    <li>Menyelenggarakan sholat dhuhur berjamaah dan kegiatan Baca Tulis Alquran (BTA).</li>
                    <li>Memberikan wadah kreasi, bakat, minat, dan kemampuan siswa melalui ragam ekstrakurikuler.</li>
                    <li>Mewujudkan lingkungan sekolah yang bersih, hijau, sejuk, dan sehat.</li>
                    <li>Mewujudkan pelestarian lingkungan hidup secara berkelanjutan.</li>
                    <li>Mencegah terjadinya pencemaran lingkungan hidup di satuan pendidikan.</li>
                    <li>Menanggulangi dan memitigasi kerusakan lingkungan hidup di sekitar sekolah.</li>
                </ol>
            </x-card>
        </div>

        {{-- Sejarah Resmi SK 1979 --}}
        <x-card class="space-y-5">
            <div class="pb-3 border-b-2 border-sun-soft">
                <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-1">Napak Tilas Pendirian</span>
                <h2 class="text-2xl font-extrabold text-navy">Sejarah Berdirinya SMP Negeri 4 Cepu</h2>
            </div>

            <div class="prose max-w-none text-sm text-ink-soft leading-relaxed space-y-3 font-medium">
                <p>
                    SMP Negeri 4 Cepu didirikan berdasarkan Surat Keputusan Menteri Pendidikan dan Kebudayaan Republik Indonesia Nomor <strong>0188/O/1979</strong> tertanggal <strong>03 September 1979</strong>, dan secara resmi mulai beroperasi penuh mendidik generasi muda Cepu pada tanggal <strong>4 September 1979</strong>.
                </p>
                <p>
                    Pada masa awal perintisannya, sekolah ini berstatus sebagai SMP Filial dari SMP Negeri 1 Cepu untuk menampung tingginya animo masyarakat kawasan Cepu dan sekitarnya akan akses pendidikan menengah pertama yang berkualitas. Amanah kepemimpinan satuan pendidikan pertama kali dipercayakan kepada Bapak <strong>YB. Moertadji</strong> sebagai kepala sekolah perintis.
                </p>
                <p>
                    Kini, SMP Negeri 4 Cepu telah terakreditasi <strong>A</strong> dan terus bertransformasi mengintegrasikan nilai kearifan lokal kawasan energi minyak dan gas bumi Cepu dengan inovasi digital modern melalui inisiatif <em>"Satu Sekolah, Satu Ruang Digital"</em>.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t-2 border-sun-soft text-xs">
                <div class="card bg-sky-soft p-4">
                    <span class="text-ink-mute block text-[11px]">Nomor Pokok Sekolah Nasional</span>
                    <strong class="font-extrabold text-navy text-sm">20314928</strong>
                </div>
                <div class="card bg-sky-soft p-4">
                    <span class="text-ink-mute block text-[11px]">Status & Akreditasi</span>
                    <strong class="font-extrabold text-navy text-sm">Negeri · Akreditasi A</strong>
                </div>
                <div class="card bg-sky-soft p-4">
                    <span class="text-ink-mute block text-[11px]">Wilayah Administratif</span>
                    <strong class="font-extrabold text-navy text-sm">Mulyorejo, Cepu, Blora</strong>
                </div>
            </div>
        </x-card>

        {{-- 3 Karakteristik Satuan Pendidikan --}}
        <div>
            <h2 class="text-xl font-extrabold text-navy mb-4">Karakteristik Lingkungan Satuan Pendidikan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <x-card class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-brand text-white flex items-center justify-center font-black">
                        1
                    </div>
                    <h3 class="font-extrabold text-navy text-base">Kawasan Vokasi Energi Migas</h3>
                    <p class="text-xs text-ink-soft leading-relaxed">
                        Kedekatan dengan sentra industri migas dan pusat pendidikan energi (PPSDM Migas Cepu) menumbuhkan etos kedisiplinan kerja, kecintaan pada sains, dan adaptasi teknologi terapan sejak dini.
                    </p>
                </x-card>

                <x-card class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black">
                        2
                    </div>
                    <h3 class="font-extrabold text-navy text-base">Wawasan Adiwiyata & Lingkungan</h3>
                    <p class="text-xs text-ink-soft leading-relaxed">
                        Terletak di Desa Mulyorejo yang asri, sekolah mengintegrasikan budaya pelestarian alam, pemilahan sampah organik, dan penghijauan ke dalam proyek profil pelajar Pancasila.
                    </p>
                </x-card>

                <x-card class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-grape text-white flex items-center justify-center font-black">
                        3
                    </div>
                    <h3 class="font-extrabold text-navy text-base">Penguatan Literasi & Spiritual</h3>
                    <p class="text-xs text-ink-soft leading-relaxed">
                        Fasilitas perpustakaan digital terpadu, pembiasaan sholat berjamaah, dan kegiatan Baca Tulis Alquran (BTA) melahirkan lulusan yang cakap bernalar kritis sekaligus kokoh secara moral.
                    </p>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
