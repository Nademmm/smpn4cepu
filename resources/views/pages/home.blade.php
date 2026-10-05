<x-layouts.app title="Beranda">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-20 lg:py-28">
        {{-- Background Geometric Accents --}}
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px]"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                {{-- Text Content --}}
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                        Portal Resmi & Ekosistem Digital SMP Negeri 4 Cepu
                    </div>
                    
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight sm:leading-none">
                        Satu Sekolah,<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-300">
                            Satu Ruang Digital.
                        </span>
                    </h1>
                    
                    <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto lg:mx-0 font-normal leading-relaxed">
                        Mewujudkan generasi pembelajar yang cerdas, terampil, mandiri, dan berakhlak mulia melalui keterbukaan informasi dan digitalisasi layanan sekolah terpadu.
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                        <a href="{{ route('profile') }}" class="px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition-all hover:scale-[1.02]">
                            Jelajahi Profil Sekolah
                        </a>
                        <a href="{{ route('pilketos') }}" class="px-6 py-3.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 border border-slate-700 font-bold text-sm backdrop-blur-sm transition-all hover:scale-[1.02]">
                            Bilik Suara Pilketos
                        </a>
                    </div>
                </div>

                {{-- Hero Visual / Foto Sekolah --}}
                <div class="lg:col-span-5">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white/10 group">
                            <img src="{{ asset('images/assets/20260717_092659.jpg') }}" alt="Gedung SMP Negeri 4 Cepu" class="w-full h-80 sm:h-96 object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex items-end p-6">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-blue-300">Gedung Utama</span>
                                    <h2 class="text-white font-bold text-base sm:text-lg">SMP Negeri 4 Cepu, Blora</h2>
                                    <p class="text-xs text-slate-300">Berdiri sejak 4 September 1979</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Baris Statistik Resmi Sekolah --}}
    <section class="relative z-10 -mt-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-slate-800">
            <div class="text-center p-3 border-r border-slate-100 dark:border-slate-800 last:border-none">
                <span class="block text-2xl sm:text-3xl font-black text-blue-600 dark:text-blue-400 font-mono">1979</span>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1 block">Tahun Berdiri</span>
            </div>
            <div class="text-center p-3 border-r border-slate-100 dark:border-slate-800 last:border-none">
                <span class="block text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">33</span>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1 block">Guru & Tendik (GTK)</span>
            </div>
            <div class="text-center p-3 border-r border-slate-100 dark:border-slate-800 last:border-none">
                <span class="block text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">11</span>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1 block">Fasilitas Sekolah</span>
            </div>
            <div class="text-center p-3">
                <span class="block text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">A</span>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1 block">Akreditasi Sekolah</span>
            </div>
        </div>
    </section>

    {{-- Akses Cepat (Quick Access Sesuai Bab 2.2 Dokumen Proposal) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('posts', ['cat' => 'pengumuman']) }}" class="flex items-center gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm group-hover:text-blue-600">Pengumuman</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Info resmi & kalender</p>
                </div>
            </a>

            <a href="{{ route('staff') }}" class="flex items-center gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm group-hover:text-blue-600">Data Guru & Tendik</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">33 staf pendidik</p>
                </div>
            </a>

            <a href="{{ route('downloads') }}" class="flex items-center gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm group-hover:text-blue-600">Unduhan Berkas</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Formulir, tata tertib, SK</p>
                </div>
            </a>

            <a href="{{ route('contact') }}" class="flex items-center gap-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm group-hover:text-blue-600">Kontak & Lokasi</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Mulyorejo, Cepu, Blora</p>
                </div>
            </a>
        </div>
    </section>

    {{-- Fitur Unggulan Digital --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Ekosistem V1</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">Layanan Digital Terpadu</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('pilketos') }}" class="group p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-blue-500 dark:hover:border-blue-500 shadow-sm hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1 group-hover:text-blue-600 transition-colors">Bilik Suara Pilketos</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">E-Voting pemilihan ketua OSIS berbasis sidik jari perangkat tanpa akun rumit.</p>
            </a>

            <a href="{{ route('library') }}" class="group p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 dark:hover:border-indigo-500 shadow-sm hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1 group-hover:text-indigo-600 transition-colors">Perpustakaan Digital</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Cari judul buku pelajaran, ketahui posisi nomor rak, dan pantau stok tersedia.</p>
            </a>

            <a href="{{ route('quizzes') }}" class="group p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-500 dark:hover:border-emerald-500 shadow-sm hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1 group-hover:text-emerald-600 transition-colors">Latihan Mandiri (CBT)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Uji pemahaman materi ajar dengan timer otomatis dan penilaian server-side.</p>
            </a>

            <a href="{{ route('materials') }}" class="group p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500 dark:hover:border-amber-500 shadow-sm hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1 group-hover:text-amber-600 transition-colors">Materi Ajar 11 Mapel</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Modul pembelajaran mandiri Kurikulum Merdeka untuk jenjang kelas 7, 8, dan 9.</p>
            </a>
        </div>
    </section>

    {{-- Sambutan Kepala Sekolah --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-3xl p-8 sm:p-12 text-white shadow-xl relative overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-4 text-center">
                    <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl bg-blue-800 border-4 border-white/20 mx-auto overflow-hidden shadow-lg flex items-center justify-center">
                        <span class="text-4xl font-extrabold text-blue-200">KS</span>
                    </div>
                    <h3 class="text-lg font-bold mt-4">Drs. H. Hartono, M.Pd.</h3>
                    <p class="text-xs text-blue-300">Kepala SMP Negeri 4 Cepu</p>
                </div>
                <div class="lg:col-span-8 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-300">Sambutan Kepala Sekolah</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight">Membangun Budaya Digital yang Unggul dan Berkarakter</h2>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        Selamat datang di portal resmi SMP Negeri 4 Cepu. Melalui inisiatif "Satu Sekolah, Satu Ruang Digital", kami berikhtiar menghadirkan ruang kolaborasi modern bagi seluruh warga sekolah. Teknologi kami dayagunakan untuk memperkuat karakter CERIA BERIMAN (Cerdas, Inovatif, Kreatif, Berintegritas, Mandiri dalam Pelestarian Lingkungan Hidup).
                    </p>
                    <div>
                        <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-white hover:text-blue-200 underline">
                            <span>Baca Profil & Sejarah Lengkap</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Berita & Agenda Terkini --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Kabar Sekolah</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">Berita & Informasi Terbaru</h2>
            </div>
            <a href="{{ route('posts') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Lihat Semua Informasi &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($recentPosts as $post)
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        @if($post->featured_image)
                            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div class="p-6">
                            <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300 mb-3">
                                {{ $post->category->value }}
                            </span>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base leading-snug mb-2 line-clamp-2">
                                <a href="{{ route('post.detail', $post->slug) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                {{ $post->excerpt }}
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                        <a href="{{ route('post.detail', $post->slug) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Baca Selengkapnya
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-400">Belum ada berita yang dipublikasikan.</div>
            @endforelse
        </div>
    </section>

    {{-- Cuplikan Fasilitas Sekolah --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mb-12">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Sarana & Prasarana</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">Fasilitas Unggulan Sekolah</h2>
            </div>
            <a href="{{ route('facilities') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                Lihat 11 Fasilitas Selengkapnya &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredFacilities as $fac)
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                    <div>
                        @if($fac->photo_path)
                            <img src="{{ asset($fac->photo_path) }}" alt="{{ $fac->name }}" class="w-full h-44 object-cover">
                        @else
                            <div class="w-full h-44 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                        @endif
                        <div class="p-6">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2">{{ $fac->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $fac->description }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
