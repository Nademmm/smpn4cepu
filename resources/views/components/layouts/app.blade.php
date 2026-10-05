<!DOCTYPE html>
<html lang="id" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    mobileMenuOpen: false,
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
    }
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SMP Negeri 4 Cepu' }} | Satu Sekolah, Satu Ruang Digital</title>
    <meta name="description" content="{{ $metaDescription ?? 'Portal Resmi dan Layanan Digital Terpadu SMP Negeri 4 Cepu (NPSN: 20314928), Blora, Jawa Tengah.' }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white dark:bg-slate-950 dark:text-slate-100 flex flex-col min-h-screen transition-colors duration-200">

    {{-- Topbar Info Sekolah --}}
    <header class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800 z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="flex items-center gap-1.5 font-medium text-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    NPSN: <strong class="text-white">20314928</strong>
                </span>
                <span class="hidden sm:inline-block text-slate-600">|</span>
                <span class="hidden sm:flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kec. Cepu, Kab. Blora, Jawa Tengah
                </span>
                <span class="hidden md:inline-block text-slate-600">|</span>
                <span class="hidden md:flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    (0296) 421631
                </span>
            </div>
            <div class="flex items-center gap-3">
                {{-- Toggle Dark/Light Mode --}}
                <button type="button" @click="toggleTheme()" class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors" aria-label="Ganti Tema Tampilan">
                    <template x-if="!darkMode">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                            <span>Terang</span>
                        </span>
                    </template>
                    <template x-if="darkMode">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                            <span>Gelap</span>
                        </span>
                    </template>
                </button>
                <span class="text-slate-700">|</span>
                <a href="{{ url('/admin') }}" class="font-semibold text-blue-400 hover:text-blue-300 transition-colors flex items-center gap-1">
                    <span>Portal Guru & Staf</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </header>

    {{-- Sticky Main Navbar --}}
    <nav class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                {{-- Logo & Brand Title --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md group-hover:scale-105 transition-transform duration-200">
                        4
                    </div>
                    <div>
                        <div class="font-extrabold text-lg sm:text-xl tracking-tight text-slate-900 dark:text-white leading-tight">
                            SMP Negeri 4 Cepu
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold tracking-wide">
                            Satu Sekolah, Satu Ruang Digital
                        </div>
                    </div>
                </a>

                {{-- Desktop Menu --}}
                <div class="hidden lg:flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-semibold' : 'text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">
                        Beranda
                    </a>

                    {{-- Dropdown Profil --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="px-3 py-2 rounded-lg flex items-center gap-1 text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors {{ request()->is('profil*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                            <span>Profil</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-52 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 py-2 z-50">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Sejarah & Visi Misi</a>
                            <a href="{{ route('facilities') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">11 Fasilitas Sekolah</a>
                            <a href="{{ route('gallery') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Galeri Kegiatan</a>
                        </div>
                    </div>

                    <a href="{{ route('staff') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('staff') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-semibold' : 'text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">
                        Guru & Tendik
                    </a>

                    {{-- Dropdown Kesiswaan --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="px-3 py-2 rounded-lg flex items-center gap-1 text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors {{ request()->is('pilketos*') || request()->is('rekap-siswa') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                            <span>Kesiswaan</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-56 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 py-2 z-50">
                            <a href="{{ route('pilketos') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Bilik Suara Pilketos</a>
                            <a href="{{ route('pilketos.live') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Quick Count Real-Time</a>
                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                            <a href="{{ route('student-stats') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Statistik Rombel Siswa</a>
                        </div>
                    </div>

                    <a href="{{ route('library') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('library') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-semibold' : 'text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">
                        Perpustakaan
                    </a>

                    {{-- Dropdown Belajar Mandiri --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" class="px-3 py-2 rounded-lg flex items-center gap-1 text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 transition-colors {{ request()->is('materi*') || request()->is('latihan-soal*') ? 'text-blue-600 dark:text-blue-400 font-semibold' : '' }}">
                            <span>Ruang Belajar</span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute left-0 mt-1 w-52 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 py-2 z-50">
                            <a href="{{ route('materials') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Materi Pelajaran</a>
                            <a href="{{ route('quizzes') }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm">Latihan Soal Mandiri</a>
                        </div>
                    </div>

                    <a href="{{ route('posts') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('posts') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-semibold' : 'text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">
                        Berita & Info
                    </a>

                    <a href="{{ route('downloads') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('downloads') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-semibold' : 'text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">
                        Unduhan
                    </a>

                    <a href="{{ route('contact') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('contact') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-semibold' : 'text-slate-700 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400' }}">
                        Kontak
                    </a>
                </div>

                {{-- Mobile Hamburger Button --}}
                <div class="flex items-center lg:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700" aria-label="Buka Menu Navigasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Drawer Menu --}}
        <div x-show="mobileMenuOpen" x-cloak class="lg:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pt-2 pb-6 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-bold' : 'text-slate-800 dark:text-slate-200' }}">
                Beranda
            </a>
            <div class="border-t border-slate-100 dark:border-slate-800/60 pt-2">
                <span class="px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Profil Sekolah</span>
                <a href="{{ route('profile') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Sejarah & Visi Misi</a>
                <a href="{{ route('facilities') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">11 Fasilitas Sekolah</a>
                <a href="{{ route('gallery') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Galeri Foto Kegiatan</a>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800/60 pt-2">
                <span class="px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Layanan Digital</span>
                <a href="{{ route('staff') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Direktori Guru & Tendik</a>
                <a href="{{ route('pilketos') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Bilik Suara Pilketos</a>
                <a href="{{ route('pilketos.live') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Quick Count Pilketos</a>
                <a href="{{ route('student-stats') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Statistik Rombel Siswa</a>
                <a href="{{ route('library') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Perpustakaan Digital</a>
                <a href="{{ route('materials') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Materi Pelajaran</a>
                <a href="{{ route('quizzes') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Latihan Soal Mandiri</a>
            </div>
            <div class="border-t border-slate-100 dark:border-slate-800/60 pt-2">
                <a href="{{ route('posts') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Berita & Informasi</a>
                <a href="{{ route('downloads') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Pusat Unduhan Berkas</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-300">Kontak Sekolah</a>
                <a href="{{ url('/admin') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-blue-600 dark:text-blue-400">Masuk Panel Guru / Staf</a>
            </div>
        </div>
    </nav>

    {{-- Main Content Slot --}}
    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- Footer Resmi Terpadu --}}
    <footer class="bg-slate-950 text-slate-400 pt-14 pb-8 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                {{-- Kolom 1: Profil Sekolah --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-extrabold text-lg">
                            4
                        </div>
                        <div>
                            <h2 class="text-white font-bold text-lg leading-tight">SMP Negeri 4 Cepu</h2>
                            <p class="text-xs text-blue-400">NPSN: 20314928</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Mewujudkan Peserta Didik yang Ceria Beriman (Cerdas, Inovatif, Kreatif, Berintegritas, Mandiri dalam Pelestarian Lingkungan Hidup).
                    </p>
                    <div class="text-xs text-slate-500">
                        SK Pendirian: No. 0188/O/1979 (03 September 1979)
                    </div>
                </div>

                {{-- Kolom 2: Tautan Cepat --}}
                <div>
                    <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Navigasi Utama</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('profile') }}" class="hover:text-blue-400 transition-colors">Sejarah & Visi Misi</a></li>
                        <li><a href="{{ route('facilities') }}" class="hover:text-blue-400 transition-colors">Fasilitas Sarana Prasarana</a></li>
                        <li><a href="{{ route('gallery') }}" class="hover:text-blue-400 transition-colors">Galeri Foto Kegiatan</a></li>
                        <li><a href="{{ route('staff') }}" class="hover:text-blue-400 transition-colors">Direktori Guru & Tendik</a></li>
                        <li><a href="{{ route('student-stats') }}" class="hover:text-blue-400 transition-colors">Statistik Rombongan Belajar</a></li>
                        <li><a href="{{ route('downloads') }}" class="hover:text-blue-400 transition-colors">Pusat Unduhan Berkas</a></li>
                    </ul>
                </div>

                {{-- Kolom 3: Layanan Digital Siswa --}}
                <div>
                    <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Layanan Digital</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('pilketos') }}" class="hover:text-blue-400 transition-colors">Bilik Suara E-Voting Pilketos</a></li>
                        <li><a href="{{ route('pilketos.live') }}" class="hover:text-blue-400 transition-colors">Live Quick Count Suara</a></li>
                        <li><a href="{{ route('library') }}" class="hover:text-blue-400 transition-colors">Katalog Perpustakaan Digital</a></li>
                        <li><a href="{{ route('materials') }}" class="hover:text-blue-400 transition-colors">Materi Kurikulum Merdeka</a></li>
                        <li><a href="{{ route('quizzes') }}" class="hover:text-blue-400 transition-colors">Latihan Mandiri Bank Soal</a></li>
                        <li><a href="{{ url('/admin') }}" class="hover:text-blue-400 transition-colors">Login Admin & Guru</a></li>
                    </ul>
                </div>

                {{-- Kolom 4: Kontak Resmi --}}
                <div class="space-y-3">
                    <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Hubungi Kami</h3>
                    <p class="text-sm flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-400 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315</span>
                    </p>
                    <p class="text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>(0296) 421631</span>
                    </p>
                    <p class="text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>smpn4cepu@smpn4cepu.sch.id</span>
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
                <p>&copy; {{ date('Y') }} SMP Negeri 4 Cepu. Konsep Satu Sekolah, Satu Ruang Digital.</p>
                <p>Dikembangkan untuk ekosistem pendidikan modern yang berintegritas.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
