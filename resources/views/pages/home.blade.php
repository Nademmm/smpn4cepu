<x-layouts.app title="Beranda">
    {{-- Hero Section dengan Logo SMPN 4 Cepu Lurus & Menyatu di Background --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand to-brand-dark text-white pt-10 sm:pt-14 pb-14 sm:pb-20 px-4 sm:px-6 lg:px-8">
        {{-- Lingkaran Cahaya Dekoratif Latar Belakang --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-sun/15 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-16 left-1/4 w-80 h-80 rounded-full bg-white/5 blur-2xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            {{-- Kolom Teks Utama --}}
            <div class="lg:col-span-7 space-y-6 relative z-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-xs border border-white/20 text-xs font-black text-sun tracking-wider uppercase">
                    <span>NPSN: 20314928</span>
                    <span class="text-white/40">·</span>
                    <span class="text-white/90">Blora, Jawa Tengah</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-[54px] font-black text-white leading-[1.15] tracking-tight max-w-xl">
                    Tumbuh bersama,<br>berkarya nyata di Cepu.
                </h1>

                <p class="text-base sm:text-lg text-white/90 leading-relaxed max-w-lg font-medium">
                    SMP Negeri 4 Cepu berkomitmen mencetak generasi berkarakter CERIA BERIMAN, mandiri, berprestasi, dan berwawasan lingkungan hidup.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('posts') }}" class="btn-primary shadow-md">
                        Jelajahi Sekolah
                    </a>
                    <a href="{{ route('profile') }}" class="btn-outline">
                        Profil Sekolah
                    </a>
                </div>
            </div>

            {{-- Kolom Kanan: Logo SMPN 4 Cepu Lurus, Gede, Menyatu Transparan dengan Background --}}
            <div class="lg:col-span-5 flex items-center justify-center relative py-6 select-none">
                {{-- Ambient Cahaya di Belakang Logo --}}
                <div class="absolute w-72 sm:w-96 h-72 sm:h-96 bg-sun/15 blur-3xl rounded-full pointer-events-none"></div>

                {{-- Logo Lurus Tegak dengan Opasitas Nyala Tetap Menyatu di Background --}}
                <div class="relative">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Logo SMP Negeri 4 Cepu"
                        class="w-72 sm:w-88 lg:w-[420px] xl:w-[460px] h-auto object-contain opacity-55 drop-shadow-[0_15px_30px_rgba(0,0,0,0.3)]"
                    >
                </div>
            </div>
        </div>
    </section>

    {{-- Statistik Resmi Sekolah (Data Nyata Berwawasan Resmi) --}}
    <section class="bg-brand py-10 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 text-center">
            <div class="bg-white/10 backdrop-blur-xs border border-white/20 rounded-2xl p-4 sm:p-5">
                <div class="text-3xl sm:text-4xl font-black text-sun">1979</div>
                <div class="text-xs sm:text-sm font-bold text-white/90 mt-1">Tahun Berdiri</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xs border border-white/20 rounded-2xl p-4 sm:p-5">
                <div class="text-3xl sm:text-4xl font-black text-sun">33</div>
                <div class="text-xs sm:text-sm font-bold text-white/90 mt-1">Tenaga Pendidik & Staf</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xs border border-white/20 rounded-2xl p-4 sm:p-5">
                <div class="text-3xl sm:text-4xl font-black text-sun">11</div>
                <div class="text-xs sm:text-sm font-bold text-white/90 mt-1">Fasilitas Utama</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xs border border-white/20 rounded-2xl p-4 sm:p-5">
                <div class="text-3xl sm:text-4xl font-black text-sun">A</div>
                <div class="text-xs sm:text-sm font-bold text-white/90 mt-1">Akreditasi Unggul</div>
            </div>
        </div>
    </section>

    {{-- Akses Cepat (5 Layanan Utama Terstruktur) --}}
    <section class="bg-cream py-12 sm:py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-8">
                <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-1">Layanan Terpadu</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-navy">Akses Cepat Informasi</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                {{-- 1. Berita --}}
                <a href="{{ route('posts') }}" class="card bg-white hover:border-brand hover:-translate-y-0.5 transition-all text-center flex flex-col items-center gap-3 p-5 group shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-sky-soft flex items-center justify-center text-brand group-hover:scale-105 transition-transform">
                        <x-app-icon name="news" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-[15px] text-navy group-hover:text-brand">Berita Sekolah</span>
                </a>

                {{-- 2. Guru & Tendik --}}
                <a href="{{ route('staff') }}" class="card bg-white hover:border-brand hover:-translate-y-0.5 transition-all text-center flex flex-col items-center gap-3 p-5 group shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-700 group-hover:scale-105 transition-transform">
                        <x-app-icon name="users" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-[15px] text-navy group-hover:text-brand">Guru & Tendik</span>
                </a>

                {{-- 3. Belajar --}}
                <a href="{{ route('materials') }}" class="card bg-white hover:border-brand hover:-translate-y-0.5 transition-all text-center flex flex-col items-center gap-3 p-5 group shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-grape group-hover:scale-105 transition-transform">
                        <x-app-icon name="quiz" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-[15px] text-navy group-hover:text-brand">Materi Belajar</span>
                </a>

                {{-- 4. Perpustakaan --}}
                <a href="{{ route('library') }}" class="card bg-white hover:border-brand hover:-translate-y-0.5 transition-all text-center flex flex-col items-center gap-3 p-5 group shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-700 group-hover:scale-105 transition-transform">
                        <x-app-icon name="book" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-[15px] text-navy group-hover:text-brand">Perpustakaan</span>
                </a>

                {{-- 5. Pilketos --}}
                <a href="{{ route('pilketos') }}" class="card bg-white hover:border-brand hover:-translate-y-0.5 transition-all text-center flex flex-col items-center gap-3 p-5 col-span-2 sm:col-span-1 group shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-700 group-hover:scale-105 transition-transform">
                        <x-app-icon name="vote" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-[15px] text-navy group-hover:text-brand">Bilik Pilketos</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Sambutan Kepala Sekolah --}}
    <section class="bg-white py-12 sm:py-16 px-4 sm:px-6 lg:px-8 border-b-2 border-sun-soft/40">
        <div class="max-w-4xl mx-auto">
            <x-card class="flex flex-col sm:flex-row gap-6 sm:gap-8 items-center sm:items-start p-6 sm:p-8">
                <div class="w-24 h-24 rounded-2xl bg-brand text-white flex items-center justify-center shrink-0 shadow-md">
                    <x-app-icon name="users" class="w-12 h-12 text-sun" />
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-1">
                        Sambutan Kepala Sekolah
                    </span>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-navy mb-3">
                        Drs. Suharto Widodo, M.Pd.
                    </h3>
                    <p class="text-sm text-ink-soft leading-relaxed font-medium">
                        "Selamat datang di portal resmi SMP Negeri 4 Cepu. Kami berkomitmen memberikan pendidikan terbaik yang tidak hanya berfokus pada prestasi akademik, melainkan juga membentuk karakter siswa yang berintegritas, mandiri, dan berbudaya lingkungan hidup. Mari bersama mewujudkan ekosistem belajar digital yang inklusif dan berkualitas."
                    </p>
                </div>
            </x-card>
        </div>
    </section>

    {{-- Berita Terkini --}}
    <section class="bg-sky-soft py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between gap-4 mb-7">
                <div>
                    <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-0.5">Kabar Sekolah</span>
                    <h2 class="text-2xl font-extrabold text-navy">Berita Terkini</h2>
                </div>
                <a href="{{ route('posts') }}" class="btn-ghost text-xs">
                    Lihat Semua
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($recentPosts as $post)
                    <x-card class="p-0 overflow-hidden flex flex-col h-full hover:border-brand transition-colors group">
                        @if ($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-44 bg-cream flex items-center justify-center text-brand">
                                <x-app-icon name="news" class="w-10 h-10" />
                            </div>
                        @endif
                        <div class="p-5 flex flex-col grow">
                            <h3 class="font-extrabold text-navy text-base leading-snug mb-2 line-clamp-2">
                                <a href="{{ route('post.detail', $post->slug) }}" class="hover:text-brand transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-ink-soft line-clamp-2 mb-4 leading-relaxed font-medium">
                                {{ $post->excerpt }}
                            </p>
                            <span class="mt-auto text-xs font-semibold text-ink-mute">
                                {{ $post->published_at?->translatedFormat('d M Y') ?? $post->created_at->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    </x-card>
                @empty
                    <div class="col-span-3">
                        <x-empty-state title="Belum Ada Berita" text="Berita dan pengumuman terbaru akan segera dipublikasikan di sini." />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Agenda Sekolah --}}
    @if ($agendas->isNotEmpty())
        <section class="bg-cream py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-0.5">Kalender Kegiatan</span>
                    <h2 class="text-2xl font-extrabold text-navy">Agenda Sekolah</h2>
                </div>
                <div class="space-y-3">
                    @foreach ($agendas as $agenda)
                        <div class="flex items-center gap-4 py-3.5 border-b-2 border-sun-soft last:border-none">
                            <div class="bg-brand text-white rounded-xl px-3 py-2 text-center shrink-0 min-w-[64px]">
                                <span class="block text-xs font-extrabold">
                                    {{ $agenda->event_date?->translatedFormat('d M') ?? $agenda->published_at?->translatedFormat('d M') }}
                                </span>
                            </div>
                            <div class="flex-1 font-bold text-navy text-sm sm:text-base">
                                <a href="{{ route('post.detail', $agenda->slug) }}" class="hover:text-brand transition-colors">
                                    {{ $agenda->title }}
                                </a>
                            </div>
                            <x-badge type="blue">Agenda</x-badge>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Fasilitas Sekolah --}}
    <section class="bg-white py-14 sm:py-16 px-4 sm:px-6 lg:px-8 border-t-2 border-sun-soft/40">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between gap-4 mb-7">
                <div>
                    <span class="text-xs font-bold text-brand uppercase tracking-wider block mb-0.5">Sarana Prasarana</span>
                    <h2 class="text-2xl font-extrabold text-navy">Fasilitas Sekolah</h2>
                </div>
                <a href="{{ route('facilities') }}" class="btn-ghost text-xs">
                    11 Fasilitas Lengkap
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($featuredFacilities as $facility)
                    <x-card class="p-0 overflow-hidden flex flex-col hover:border-brand transition-colors group">
                        @if ($facility->photo_path && file_exists(public_path('images/assets/' . $facility->photo_path)))
                            <img src="{{ asset('images/assets/' . $facility->photo_path) }}" alt="{{ $facility->name }}" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-40 bg-sky-soft flex items-center justify-center text-brand">
                                <x-app-icon name="building" class="w-8 h-8" />
                            </div>
                        @endif
                        <div class="p-4 sm:p-5 flex flex-col grow">
                            <h3 class="font-extrabold text-navy text-base mb-1.5 leading-snug">{{ $facility->name }}</h3>
                            <p class="text-xs text-ink-soft leading-relaxed line-clamp-2 font-medium">{{ $facility->description }}</p>
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
