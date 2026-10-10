<x-layouts.app title="Profil, Sejarah & Visi Misi">
    <x-page-header
        title="Profil & Visi Misi Sekolah"
        subtitle="Mengenal lebih dekat identitas, sejarah berdirinya, visi misi, dan karakteristik satuan pendidikan SMP Negeri 4 Cepu."
        crumb="Profil Sekolah"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 space-y-10">
        {{-- Visi & Misi Resmi --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            {{-- Visi --}}
            <div class="lg:col-span-5 rounded-card p-8 bg-gradient-to-br from-brand to-brand-dark text-white flex flex-col justify-between shadow-xs">
                <div>
                    <span class="inline-block bg-sun text-navy font-black text-xs px-3 py-1 rounded-full uppercase tracking-wider mb-4">
                        Visi Satuan Pendidikan
                    </span>
                    <h2 class="text-3xl font-extrabold text-white mb-4 leading-tight">
                        "CERIA BERIMAN"
                    </h2>
                    <blockquote class="text-sm text-white/95 leading-relaxed italic border-l-4 border-sun pl-4 font-semibold">
                        "TERWUJUDNYA PESERTA DIDIK YANG CERIA BERIMAN (CERDAS, INOVATIF, KREATIF, BERINTEGRITAS, MANDIRI DALAM PELESTARIAN LINGKUNGAN HIDUP)"
                    </blockquote>
                </div>
                <div class="mt-8 pt-4 border-t border-white/20 text-xs text-white/80 space-y-1">
                    <p class="font-bold text-sun">Prinsip Pengembangan Karakter:</p>
                    <p>Pembiasaan ibadah, penanaman disiplin, tanggung jawab, kemandirian, dan wawasan lingkungan hidup berkelanjutan.</p>
                </div>
            </div>

            {{-- 11 Misi Resmi --}}
            <x-card class="lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b-2 border-sun-soft">
                    <div>
                        <h2 class="text-xl font-extrabold text-navy">11 Butir Misi Sekolah</h2>
                        <p class="text-xs text-ink-soft">Kegiatan strategis perwujudan visi CERIA BERIMAN</p>
                    </div>
                    <x-badge type="blue">Resmi</x-badge>
                </div>

                <ol class="space-y-2 text-xs sm:text-sm text-navy leading-relaxed list-decimal list-inside font-semibold">
                    <li>Menyelenggarakan pembelajaran dan bimbingan secara efektif untuk mengoptimalkan potensi akademik dan non akademik yang dimiliki peserta didik.</li>
                    <li>Mengoptimalkan proses pembelajaran dengan pendekatan pembelajaran yang berpusat pada peserta didik (student centered learning), antara lain CTL, PAIKEM, serta layanan bimbingan dan konseling.</li>
                    <li>Melaksanakan pembelajaran berbasis IT (Information Technology) dengan Google Suite for Education.</li>
                    <li>Melaksanakan pendidikan karakter melalui pembiasaan positif di lingkungan sekolah.</li>
                    <li>Mendorong dan mengajak warga sekolah untuk mematuhi aturan dan tata tertib sekolah.</li>
                    <li>Menyelenggarakan sholat dhuhur berjamaah dan kegiatan Baca Tulis Alquran (BTA).</li>
                    <li>Memberikan wadah kreasi, bakat, minat, dan kemampuan siswa melalui kegiatan ekstrakurikuler.</li>
                    <li>Mewujudkan lingkungan sekolah yang bersih, hijau, sejuk, dan sehat.</li>
                    <li>Mewujudkan pelestarian lingkungan hidup secara berkelanjutan.</li>
                    <li>Mencegah pencemaran lingkungan hidup di satuan pendidikan.</li>
                    <li>Menanggulangi kerusakan lingkungan hidup di lingkungan sekitar.</li>
                </ol>
            </x-card>
        </div>

        {{-- Sejarah Resmi SK 1979 & Kepemimpinan --}}
        <x-card class="space-y-6">
            <div class="pb-3 border-b-2 border-sun-soft flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-1">Napak Tilas Pendirian</span>
                    <h2 class="text-2xl font-extrabold text-navy">Sejarah Singkat SMP Negeri 4 Cepu</h2>
                </div>
                <span class="text-xs font-black text-brand bg-sky-soft px-3.5 py-1.5 rounded-full border border-brand/20">
                    SK No. 0188/O/1979
                </span>
            </div>

            <div class="prose max-w-none text-sm text-ink-soft leading-relaxed space-y-3 font-medium">
                <p>
                    SMP Negeri 4 Cepu merupakan salah satu lembaga pendidikan tingkat Sekolah Menengah Pertama yang berada di Kecamatan Cepu, Kabupaten Blora, Provinsi Jawa Tengah. Satuan pendidikan ini secara resmi didirikan berdasarkan <strong>Surat Keputusan Nomor 0188/O/1979 tanggal 3 September 1979</strong>, dan mulai berdiri serta beroperasi pada tanggal <strong>4 September 1979</strong> sebagai bagian dari upaya pemerintah dalam meningkatkan pemerataan dan pelayanan pendidikan bagi masyarakat di wilayah Cepu dan sekitarnya.
                </p>
                <p>
                    Pada awal berdirinya, SMP Negeri 4 Cepu dipimpin oleh <strong>Bapak YB. Moertadji</strong> sebagai kepala sekolah pertama. Di bawah kepemimpinan beliau, sekolah mulai melaksanakan kegiatan pendidikan dan pembelajaran serta membangun dasar-dasar penyelenggaraan pendidikan yang kokoh dan menjadi bagian penting dalam perjalanan perkembangan sekolah.
                </p>
                <p>
                    Seiring berjalannya waktu, SMP Negeri 4 Cepu terus mengalami perkembangan dalam berbagai bidang, baik dalam penyediaan sarana dan prasarana, peningkatan kualitas tenaga pendidik dan kependidikan, maupun pengembangan kegiatan akademik dan nonakademik. Dalam perjalanan panjangnya sejak 4 September 1979, sekolah telah mendidik dan meluluskan banyak generasi yang melanjutkan pendidikan ke jenjang yang lebih tinggi serta berkiprah di berbagai bidang kehidupan dengan memegang teguh nilai kedisiplinan, kemandirian, dan akhlak mulia.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t-2 border-sun-soft text-xs">
                <div class="bg-cream/40 border-2 border-sun-soft rounded-2xl p-4 shadow-xs">
                    <span class="text-ink-mute block text-[11px] font-bold uppercase tracking-wider mb-1">Tanggal SK</span>
                    <strong class="font-black text-brand text-sm sm:text-base">3 September 1979</strong>
                </div>
                <div class="bg-cream/40 border-2 border-sun-soft rounded-2xl p-4 shadow-xs">
                    <span class="text-ink-mute block text-[11px] font-bold uppercase tracking-wider mb-1">Mulai Berdiri</span>
                    <strong class="font-black text-navy text-sm sm:text-base">4 September 1979</strong>
                </div>
                <div class="bg-cream/40 border-2 border-sun-soft rounded-2xl p-4 shadow-xs">
                    <span class="text-ink-mute block text-[11px] font-bold uppercase tracking-wider mb-1">Kepala Sekolah Ke-1</span>
                    <strong class="font-black text-navy text-sm sm:text-base">YB. Moertadji</strong>
                </div>
                <div class="bg-cream/40 border-2 border-sun-soft rounded-2xl p-4 shadow-xs">
                    <span class="text-ink-mute block text-[11px] font-bold uppercase tracking-wider mb-1">Status Akreditasi</span>
                    <strong class="font-black text-emerald-700 text-sm sm:text-base">Akreditasi A Unggul</strong>
                </div>
            </div>
        </x-card>

        {{-- Profil Geografis & Karakteristik Wilayah --}}
        <div>
            <div class="mb-5">
                <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-0.5">Letak Geografis & Potensi</span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-navy">Karakteristik & Lingkungan Satuan Pendidikan</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <x-card class="p-5 sm:p-6 space-y-3 hover:border-brand transition-all shadow-xs group">
                    <div class="w-11 h-11 rounded-2xl bg-sky-soft text-brand flex items-center justify-center font-black text-base group-hover:scale-105 transition-transform">
                        <x-app-icon name="pin" class="w-5 h-5" />
                    </div>
                    <h3 class="font-extrabold text-navy text-base leading-snug group-hover:text-brand transition-colors">
                        Letak Geografis Strategis
                    </h3>
                    <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium">
                        Terletak di <strong>Desa Mulyorejo</strong>, Kecamatan Cepu, berjarak ±3,5 km dari Kantor Kecamatan Cepu dan ±36 km dari pusat kota Blora. Berada pada titik koordinat <strong>Lintang 7.1576081</strong> dan <strong>Bujur 111.5662531</strong>.
                    </p>
                </x-card>

                <x-card class="p-5 sm:p-6 space-y-3 hover:border-brand transition-all shadow-xs group">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center font-black text-base group-hover:scale-105 transition-transform">
                        <x-app-icon name="building" class="w-5 h-5" />
                    </div>
                    <h3 class="font-extrabold text-navy text-base leading-snug group-hover:text-brand transition-colors">
                        Kawasan Kota Vokasi & Energi
                    </h3>
                    <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium">
                        Berada di kawasan kota vokasi dengan peran strategis di bidang migas dan energi (Pusdiklat Migas). Diperkuat potensi alam hasil pertanian dan perkebunan untuk membekali peserta didik dengan <em>life skill</em> kewirausahaan lokal.
                    </p>
                </x-card>

                <x-card class="p-5 sm:p-6 space-y-3 hover:border-brand transition-all shadow-xs group">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black text-base group-hover:scale-105 transition-transform">
                        <x-app-icon name="book" class="w-5 h-5" />
                    </div>
                    <h3 class="font-extrabold text-navy text-base leading-snug group-hover:text-brand transition-colors">
                        Komitmen Literasi & Inklusivitas
                    </h3>
                    <p class="text-xs sm:text-sm text-ink-soft leading-relaxed font-medium">
                        Penerimaan PPDB ramah lingkungan sosial tanpa sekat passing grade. Sekolah memprioritaskan penguatan fondasi literasi baca-tulis, numerasi, dan budi pekerti melalui layanan perpustakaan berdedikasi.
                    </p>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
