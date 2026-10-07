<x-layouts.app title="Beranda">
    {{-- Hero Section (Sesuai Desain Figma) --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand to-brand-dark text-white py-16 sm:py-20 px-4 sm:px-6 lg:px-8">
        {{-- Lingkaran Dekoratif Aksen Figma --}}
        <div class="absolute -top-16 -right-16 w-80 h-80 rounded-full bg-sun/10 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-10 left-1/3 w-52 h-52 rounded-full bg-white/5 pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7">
                <span class="inline-block rounded-full bg-sun px-3.5 py-1 text-xs font-extrabold text-navy mb-4">
                    Sekolah Unggulan Cepu
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-white leading-tight mb-4 max-w-xl">
                    Tumbuh bersama,<br>berkarya nyata di Cepu.
                </h1>
                <p class="text-base sm:text-lg text-white/85 leading-relaxed mb-8 max-w-lg">
                    SMP Negeri 4 Cepu berkomitmen mencetak generasi berkarakter, berprestasi, mandiri, dan siap menghadapi tantangan masa depan.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('posts') }}" class="btn-primary">
                        Jelajahi Sekolah
                    </a>
                    <a href="{{ route('profile') }}" class="btn-outline">
                        Profil Sekolah
                    </a>
                </div>
            </div>

            {{-- Ilustrasi / Foto Gedung Sekolah --}}
            <div class="lg:col-span-5 flex flex-col items-center">
                <div class="w-full max-w-xs sm:max-w-sm rounded-3xl overflow-hidden border-2 border-white/20 shadow-2xl bg-white/10">
                    <img
                        src="{{ asset('images/assets/20260717_092659.jpg') }}"
                        alt="Gedung SMP Negeri 4 Cepu"
                        class="w-full h-56 object-cover"
                    >
                </div>
                <div class="mt-3 rounded-xl bg-sun/25 border border-sun/40 px-5 py-2 text-xs font-bold text-sun text-center">
                    Terakreditasi A · NPSN 20314928
                </div>
            </div>
        </div>
    </section>

    {{-- Akses Cepat (5 Kartu Pastel Sesuai Desain Figma) --}}
    <section class="bg-cream py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-2xl font-extrabold text-navy text-center mb-6">Akses Cepat</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                {{-- 1. Berita --}}
                <a href="{{ route('posts') }}" class="card bg-sky-soft hover:-translate-y-1 transition-transform text-center flex flex-col items-center gap-2 p-5 group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-brand shadow-xs">
                        <x-app-icon name="news" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-sm sm:text-base text-navy group-hover:text-brand">Berita Sekolah</span>
                </a>

                {{-- 2. Guru & Tendik --}}
                <a href="{{ route('staff') }}" class="card bg-[#FFFBEB] hover:-translate-y-1 transition-transform text-center flex flex-col items-center gap-2 p-5 group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-amber-600 shadow-xs">
                        <x-app-icon name="users" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-sm sm:text-base text-navy group-hover:text-brand">Guru & Tendik</span>
                </a>

                {{-- 3. Belajar --}}
                <a href="{{ route('materials') }}" class="card bg-[#F5F3FF] hover:-translate-y-1 transition-transform text-center flex flex-col items-center gap-2 p-5 group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-grape shadow-xs">
                        <x-app-icon name="quiz" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-sm sm:text-base text-navy group-hover:text-brand">Belajar Mandiri</span>
                </a>

                {{-- 4. Perpustakaan --}}
                <a href="{{ route('library') }}" class="card bg-[#F0FDF4] hover:-translate-y-1 transition-transform text-center flex flex-col items-center gap-2 p-5 group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-emerald-600 shadow-xs">
                        <x-app-icon name="book" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-sm sm:text-base text-navy group-hover:text-brand">Perpustakaan</span>
                </a>

                {{-- 5. Pilketos --}}
                <a href="{{ route('pilketos') }}" class="card bg-[#FFF7ED] hover:-translate-y-1 transition-transform text-center flex flex-col items-center gap-2 p-5 col-span-2 sm:col-span-1 group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-orange-600 shadow-xs">
                        <x-app-icon name="vote" class="w-6 h-6" />
                    </div>
                    <span class="font-extrabold text-sm sm:text-base text-navy group-hover:text-brand">Bilik Pilketos</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Statistik Resmi Sekolah (Band Biru Figma dengan Data Nyata) --}}
    <section class="bg-brand py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold text-sun">1979</div>
                <div class="text-xs sm:text-sm font-semibold text-white/80 mt-1">Tahun Berdiri</div>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold text-sun">33</div>
                <div class="text-xs sm:text-sm font-semibold text-white/80 mt-1">Guru & Tenaga Pendidik</div>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold text-sun">11</div>
                <div class="text-xs sm:text-sm font-semibold text-white/80 mt-1">Fasilitas Utama</div>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold text-sun">A</div>
                <div class="text-xs sm:text-sm font-semibold text-white/80 mt-1">Akreditasi Sekolah</div>
            </div>
        </div>
    </section>

    {{-- Sambutan Kepala Sekolah (Sesuai Desain Figma) --}}
    <section class="bg-cream py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <x-card class="flex flex-col sm:flex-row gap-6 sm:gap-8 items-center sm:items-start">
                <div class="w-24 h-24 rounded-full bg-brand text-white flex items-center justify-center shrink-0 shadow-md">
                    <x-app-icon name="users" class="w-12 h-12 text-sun" />
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <div class="text-xs font-bold text-brand uppercase tracking-wider mb-1">
                        Sambutan Kepala Sekolah
                    </div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-navy mb-3">
                        Drs. Suharto Widodo, M.Pd.
                    </h3>
                    <p class="text-sm text-ink-soft leading-relaxed">
                        "Selamat datang di portal resmi SMP Negeri 4 Cepu. Kami berkomitmen memberikan pendidikan terbaik yang tidak hanya berfokus pada prestasi akademik, melainkan juga membentuk karakter siswa yang berintegritas, mandiri, dan berbudaya lingkungan hidup. Mari bersama mewujudkan ekosistem belajar digital yang inklusif dan berkualitas."
                    </p>
                </div>
            </x-card>
        </div>
    </section>

    {{-- Berita Terkini (Sesuai Desain Figma) --}}
    <section class="bg-sky-soft py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between gap-4 mb-7">
                <h2 class="text-2xl font-extrabold text-navy">Berita Terkini</h2>
                <a href="{{ route('posts') }}" class="btn-ghost text-xs">
                    Lihat Semua
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($recentPosts as $post)
                    <x-card class="p-0 overflow-hidden flex flex-col h-full hover:border-brand transition-colors">
                        @if ($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-44 object-cover">
                        @else
                            <div class="w-full h-44 bg-cream flex items-center justify-center text-brand">
                                <x-app-icon name="news" class="w-10 h-10" />
                            </div>
                        @endif
                        <div class="p-5 flex flex-col grow">
                            <div class="mb-2">
                                <x-badge type="blue">{{ $post->category->label() }}</x-badge>
                            </div>
                            <h3 class="font-extrabold text-navy text-base leading-snug mb-2 line-clamp-2">
                                <a href="{{ route('post.detail', $post->slug) }}" class="hover:text-brand">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-ink-soft line-clamp-2 mb-4 leading-relaxed">
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

    {{-- Agenda Sekolah (Sesuai Desain Figma) --}}
    @if ($agendas->isNotEmpty())
        <section class="bg-cream py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-extrabold text-navy mb-6">Agenda Sekolah</h2>
                <div class="space-y-3">
                    @foreach ($agendas as $agenda)
                        <div class="flex items-center gap-4 py-3.5 border-b-2 border-sun-soft last:border-none">
                            <div class="bg-brand text-white rounded-xl px-3 py-2 text-center shrink-0 min-w-[64px]">
                                <span class="block text-xs font-extrabold">
                                    {{ $agenda->event_date?->translatedFormat('d M') ?? $agenda->published_at?->translatedFormat('d M') }}
                                </span>
                            </div>
                            <div class="flex-1 font-bold text-navy text-sm sm:text-base">
                                <a href="{{ route('post.detail', $agenda->slug) }}" class="hover:text-brand">
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

    {{-- Fasilitas Sekolah (Sesuai Desain Figma) --}}
    <section class="bg-sky-soft py-14 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between gap-4 mb-7">
                <h2 class="text-2xl font-extrabold text-navy">Fasilitas Sekolah</h2>
                <a href="{{ route('facilities') }}" class="btn-ghost text-xs">
                    11 Fasilitas Lengkap
                    <x-app-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($featuredFacilities as $facility)
                    <x-card class="flex flex-col hover:border-brand transition-colors">
                        @if ($facility->photo_path && file_exists(public_path('images/assets/' . $facility->photo_path)))
                            <img src="{{ asset('images/assets/' . $facility->photo_path) }}" alt="{{ $facility->name }}" class="w-full h-32 object-cover rounded-xl mb-3">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-cream flex items-center justify-center text-brand mb-3">
                                <x-app-icon name="building" class="w-6 h-6" />
                            </div>
                        @endif
                        <h3 class="font-extrabold text-navy text-base mb-1">{{ $facility->name }}</h3>
                        <p class="text-xs text-ink-soft leading-relaxed line-clamp-2">{{ $facility->description }}</p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
