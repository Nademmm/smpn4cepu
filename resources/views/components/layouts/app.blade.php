<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SMP Negeri 4 Cepu' }} | Satu Sekolah, Satu Ruang Digital</title>
    <meta name="description" content="{{ $metaDescription ?? 'Portal Resmi dan Layanan Digital Terpadu SMP Negeri 4 Cepu (NPSN: 20314928), Blora, Jawa Tengah.' }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-cream text-navy font-sans antialiased flex flex-col min-h-screen">

    {{-- Sticky Main Navbar (Sesuai Desain Figma) --}}
    <nav class="sticky top-0 z-50 bg-white border-b-2 border-sun-soft shadow-xs" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                {{-- Logo & Brand --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group py-1">
                    <x-logo size="44" class="h-11 w-auto object-contain drop-shadow-xs group-hover:scale-105 transition-transform" />
                    <div class="flex flex-col justify-center select-none">
                        <span class="text-[11px] sm:text-xs font-bold text-brand uppercase tracking-wider leading-none">SMP NEGERI 4</span>
                        <span class="text-base sm:text-lg font-black text-navy tracking-tight leading-none mt-1">CEPU</span>
                    </div>
                </a>

                {{-- Desktop Menu: 5 Menu Utama Ringkas & Terorganisir --}}
                <div class="hidden lg:flex items-center justify-center gap-1.5 text-sm font-semibold">
                    {{-- 1. Beranda --}}
                    <a href="{{ route('home') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('home') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Beranda
                    </a>

                    {{-- 2. Dropdown Profil --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->routeIs('profile') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                            <span>Profil</span>
                            <x-app-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform" ::class="{ 'rotate-180': open }" />
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 top-full pt-1 w-64 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-1.5 space-y-0.5">
                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('profile') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="building" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Sejarah & Visi Misi</span>
                                </a>
                                <a href="{{ route('facilities') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('facilities') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="check" class="w-4 h-4 text-brand shrink-0" />
                                    <span>11 Fasilitas Sekolah</span>
                                </a>
                                <a href="{{ route('staff') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('staff') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="users" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Guru & Tenaga Kependidikan</span>
                                </a>
                                <a href="{{ route('student-stats') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="chart" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Statistik Rombel Siswa</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Dropdown Belajar --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                            <span>Belajar</span>
                            <x-app-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform" ::class="{ 'rotate-180': open }" />
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 top-full pt-1 w-60 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-1.5 space-y-0.5">
                                <a href="{{ route('materials') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('materials') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="book" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Materi Pelajaran</span>
                                </a>
                                <a href="{{ route('quizzes') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('quizzes') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="quiz" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Latihan Soal Mandiri</span>
                                </a>
                                <a href="{{ route('library') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('library') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="book" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Perpustakaan Digital</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Dropdown Kesiswaan --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->is('pilketos*') || request()->routeIs('gallery') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                            <span>Kesiswaan</span>
                            <x-app-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform" ::class="{ 'rotate-180': open }" />
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 top-full pt-1 w-64 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-1.5 space-y-0.5">
                                <a href="{{ route('pilketos') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('pilketos') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="vote" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Bilik Suara E-Voting</span>
                                </a>
                                <a href="{{ route('pilketos.live') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('pilketos.live') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="chart" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Quick Count Suara</span>
                                </a>
                                <a href="{{ route('gallery') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('gallery') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="photo" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Galeri Foto Kegiatan</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Dropdown Informasi --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                            <span>Informasi</span>
                            <x-app-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform" ::class="{ 'rotate-180': open }" />
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 top-full pt-1 w-60 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-1.5 space-y-0.5">
                                <a href="{{ route('posts') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('posts*') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="news" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Berita & Pengumuman</span>
                                </a>
                                <a href="{{ route('downloads') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('downloads') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="download" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Pusat Unduhan Berkas</span>
                                </a>
                                <a href="{{ route('contact') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('contact') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="phone" class="w-4 h-4 text-brand shrink-0" />
                                    <span>Kontak & Lokasi</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksi Kanan (Masuk / Login Gaya Pill Figma) --}}
                <div class="flex items-center gap-2.5">
                    <a href="{{ url('/admin') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-sun hover:bg-sun-hover active:scale-95 text-navy font-black text-xs tracking-wide shadow-xs hover:shadow-md transition-all">
                        <x-app-icon name="user" class="w-3.5 h-3.5 text-navy shrink-0" />
                        <span>Masuk / Login</span>
                    </a>

                    {{-- Hamburger Mobile Button --}}
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-xl text-navy hover:bg-cream lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Buka menu navigasi">
                        <x-app-icon name="menu" class="w-6 h-6" x-show="!mobileMenuOpen" />
                        <x-app-icon name="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak />
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Drawer Menu: 4 Kategori Terstruktur --}}
        <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t-2 border-sun-soft bg-white px-4 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-xl text-sm font-black {{ request()->routeIs('home') ? 'bg-sky-soft text-brand' : 'text-navy' }}">
                Beranda
            </a>

            <div>
                <span class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-ink-mute">Profil Sekolah</span>
                <div class="mt-1 space-y-1">
                    <a href="{{ route('profile') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Sejarah & Visi Misi</a>
                    <a href="{{ route('facilities') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">11 Fasilitas Sekolah</a>
                    <a href="{{ route('staff') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Guru & Tenaga Kependidikan</a>
                    <a href="{{ route('student-stats') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Statistik Rombel Siswa</a>
                </div>
            </div>

            <div>
                <span class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-ink-mute">Ruang Belajar</span>
                <div class="mt-1 space-y-1">
                    <a href="{{ route('materials') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Materi Pelajaran</a>
                    <a href="{{ route('quizzes') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Latihan Soal Mandiri</a>
                    <a href="{{ route('library') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Perpustakaan Digital</a>
                </div>
            </div>

            <div>
                <span class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-ink-mute">Kesiswaan</span>
                <div class="mt-1 space-y-1">
                    <a href="{{ route('pilketos') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Bilik Suara E-Voting</a>
                    <a href="{{ route('pilketos.live') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Quick Count Real-Time</a>
                    <a href="{{ route('gallery') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Galeri Foto Kegiatan</a>
                </div>
            </div>

            <div>
                <span class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-ink-mute">Informasi & Layanan</span>
                <div class="mt-1 space-y-1">
                    <a href="{{ route('posts') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Berita & Pengumuman</a>
                    <a href="{{ route('downloads') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Pusat Unduhan Berkas</a>
                    <a href="{{ route('contact') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Kontak & Lokasi</a>
                </div>
            </div>

            <div class="pt-2 border-t-2 border-sun-soft">
                <a href="{{ url('/admin') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-full bg-sun text-navy font-black text-sm text-center shadow-xs">
                    <x-app-icon name="user" class="w-4 h-4 text-navy" />
                    <span>Masuk / Login</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Slot --}}
    <main class="grow">
        {{ $slot }}
    </main>

    {{-- Footer Resmi (Sesuai Desain Figma) --}}
    <footer class="bg-navy text-white pt-12 pb-8 px-4 sm:px-6 lg:px-8 mt-16 border-t-2 border-brand-dark">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">
            {{-- Kolom 1: Identitas --}}
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <x-logo size="38" />
                    <div>
                        <div class="text-sm font-extrabold text-sun leading-tight">SMP NEGERI 4 CEPU</div>
                        <div class="text-xs text-slate-300">Kabupaten Blora, Jawa Tengah</div>
                    </div>
                </div>
                <p class="text-sm text-slate-300 leading-relaxed max-w-sm">
                    Mewujudkan peserta didik yang cerdas, inovatif, kreatif, berintegritas, dan mandiri dalam pelestarian lingkungan hidup.
                </p>
                <div class="mt-3 text-xs text-slate-400">
                    NPSN: 20314928 · SK Pendirian No. 0188/O/1979
                </div>
            </div>

            {{-- Kolom 2: Tautan Cepat --}}
            <div>
                <div class="font-extrabold text-sm text-sun mb-3 uppercase tracking-wider">Tautan Cepat</div>
                <div class="grid grid-cols-2 gap-2 text-sm text-slate-200 font-semibold">
                    <a href="{{ route('home') }}" class="hover:text-sun transition-colors">Beranda</a>
                    <a href="{{ route('profile') }}" class="hover:text-sun transition-colors">Profil Sekolah</a>
                    <a href="{{ route('posts') }}" class="hover:text-sun transition-colors">Berita</a>
                    <a href="{{ route('facilities') }}" class="hover:text-sun transition-colors">11 Fasilitas</a>
                    <a href="{{ route('staff') }}" class="hover:text-sun transition-colors">Guru & Tendik</a>
                    <a href="{{ route('materials') }}" class="hover:text-sun transition-colors">Ruang Belajar</a>
                    <a href="{{ route('library') }}" class="hover:text-sun transition-colors">Perpustakaan</a>
                    <a href="{{ route('pilketos') }}" class="hover:text-sun transition-colors">Pilketos</a>
                    <a href="{{ route('downloads') }}" class="hover:text-sun transition-colors">Pusat Unduhan</a>
                    <a href="{{ route('contact') }}" class="hover:text-sun transition-colors">Kontak</a>
                </div>
            </div>

            {{-- Kolom 3: Kontak Resmi --}}
            <div>
                <div class="font-extrabold text-sm text-sun mb-3 uppercase tracking-wider">Kontak Sekolah</div>
                <div class="text-sm text-slate-300 space-y-2 leading-relaxed">
                    <div class="flex items-start gap-2">
                        <x-app-icon name="pin" class="w-4 h-4 text-sun shrink-0 mt-1" />
                        <span>Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-app-icon name="phone" class="w-4 h-4 text-sun shrink-0" />
                        <span>(0296) 421631</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-app-icon name="mail" class="w-4 h-4 text-sun shrink-0" />
                        <span>smpn4cepu@smpn4cepu.sch.id</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-10 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} SMP Negeri 4 Cepu. Hak cipta dilindungi.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
