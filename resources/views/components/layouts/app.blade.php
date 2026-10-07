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
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 group">
                    <x-logo size="40" />
                    <div class="text-left">
                        <div class="text-[11px] font-bold text-brand tracking-wider leading-none">SMP NEGERI 4</div>
                        <div class="text-sm font-extrabold text-navy tracking-tight leading-tight mt-0.5">CEPU</div>
                    </div>
                </a>

                {{-- Desktop Menu (Gaya Pill Figma) --}}
                <div class="hidden lg:flex items-center justify-center gap-1 text-sm font-semibold">
                    <a href="{{ route('home') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('home') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Beranda
                    </a>

                    {{-- Dropdown Profil --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->is('profil*') || request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
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
                            class="absolute left-0 top-full pt-1 w-56 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft py-2">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Sejarah & Visi Misi</a>
                                <a href="{{ route('facilities') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">11 Fasilitas Sekolah</a>
                                <a href="{{ route('gallery') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Galeri Foto Kegiatan</a>
                                <a href="{{ route('student-stats') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Statistik Rombel Siswa</a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('posts') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('posts*') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Berita
                    </a>

                    <a href="{{ route('staff') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('staff') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Guru & Tendik
                    </a>

                    {{-- Dropdown Belajar --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->is('materi*') || request()->is('latihan-soal*') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
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
                            class="absolute left-0 top-full pt-1 w-52 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft py-2">
                                <a href="{{ route('materials') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Materi Pelajaran</a>
                                <a href="{{ route('quizzes') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Latihan Soal Mandiri</a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('library') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('library') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Perpustakaan
                    </a>

                    {{-- Dropdown Pilketos --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-3.5 py-1.5 rounded-full flex items-center gap-1 transition-colors {{ request()->is('pilketos*') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                            <span>Pilketos</span>
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
                            class="absolute left-0 top-full pt-1 w-52 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft py-2">
                                <a href="{{ route('pilketos') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Bilik Suara E-Voting</a>
                                <a href="{{ route('pilketos.live') }}" class="block px-4 py-2 hover:bg-sky-soft text-navy text-sm font-semibold">Quick Count Suara</a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('downloads') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('downloads') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Unduhan
                    </a>

                    <a href="{{ route('contact') }}" class="px-3.5 py-1.5 rounded-full transition-colors {{ request()->routeIs('contact') ? 'bg-sky-soft text-brand font-extrabold' : 'text-navy hover:text-brand' }}">
                        Kontak
                    </a>
                </div>

                {{-- Aksi Kanan (Panel Staf / Admin) --}}
                <div class="flex items-center gap-2">
                    <a href="{{ url('/admin') }}" class="hidden sm:inline-flex items-center justify-center min-h-[38px] rounded-full border-2 border-sun-soft bg-white px-4 text-xs font-bold text-navy hover:border-brand transition-colors">
                        Panel Staf
                    </a>

                    {{-- Hamburger Mobile Button --}}
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-xl text-navy hover:bg-cream lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Buka menu navigasi">
                        <x-app-icon name="menu" class="w-6 h-6" x-show="!mobileMenuOpen" />
                        <x-app-icon name="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak />
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Drawer Menu --}}
        <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t-2 border-sun-soft bg-white px-4 py-4 space-y-2">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-xl text-sm font-bold {{ request()->routeIs('home') ? 'bg-sky-soft text-brand' : 'text-navy' }}">
                Beranda
            </a>
            <div class="pt-1">
                <span class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-ink-mute">Profil</span>
                <a href="{{ route('profile') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Sejarah & Visi Misi</a>
                <a href="{{ route('facilities') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">11 Fasilitas Sekolah</a>
                <a href="{{ route('gallery') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Galeri Kegiatan</a>
                <a href="{{ route('student-stats') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Statistik Rombel Siswa</a>
            </div>
            <div class="pt-1">
                <span class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-ink-mute">Layanan Sekolah</span>
                <a href="{{ route('posts') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Berita & Informasi</a>
                <a href="{{ route('staff') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Guru & Tenaga Kependidikan</a>
                <a href="{{ route('materials') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Materi Belajar</a>
                <a href="{{ route('quizzes') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Latihan Soal Mandiri</a>
                <a href="{{ route('library') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Perpustakaan Digital</a>
                <a href="{{ route('pilketos') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Bilik Suara Pilketos</a>
                <a href="{{ route('pilketos.live') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Quick Count Real-Time</a>
                <a href="{{ route('downloads') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Pusat Unduhan Berkas</a>
                <a href="{{ route('contact') }}" class="block px-3 py-1.5 rounded-lg text-sm font-semibold text-navy hover:bg-sky-soft">Kontak & Lokasi</a>
            </div>
            <div class="pt-2 border-t border-sun-soft">
                <a href="{{ url('/admin') }}" class="block px-3 py-2 rounded-xl text-sm font-extrabold text-brand bg-sky-soft text-center">
                    Masuk Panel Staf / Admin
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
