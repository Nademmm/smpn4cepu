<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) && $title !== 'SMP Negeri 4 Cepu' ? $title . ' - SMP Negeri 4 Cepu' : 'SMP Negeri 4 Cepu - Satu Sekolah, Satu Ruang Digital' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Portal Resmi dan Layanan Digital Terpadu SMP Negeri 4 Cepu (NPSN: 20314928), Blora, Jawa Tengah.' }}">
    
    {{-- Favicon / Icon Tab Browser --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-cream text-navy font-sans antialiased flex flex-col min-h-screen">

    {{-- Sticky Main Navbar (Sesuai Desain Figma) --}}
    <nav class="sticky top-0 z-40 bg-white border-b-2 border-sun-soft shadow-xs" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-[70px] gap-4">
                {{-- Logo & Brand: Minimalis, Sejajar & Profesional --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3 shrink-0 group py-1">
                    <x-logo size="42" class="h-10 sm:h-11 w-auto object-contain drop-shadow-xs group-hover:scale-105 transition-transform" />
                    <div class="flex items-center gap-1.5 select-none">
                        <span class="text-[15px] sm:text-[18px] font-black tracking-tight text-navy group-hover:text-brand transition-colors">
                            SMP NEGERI 4
                        </span>
                        <span class="text-[15px] sm:text-[18px] font-black tracking-tight text-brand">
                            CEPU
                        </span>
                    </div>
                </a>

                {{-- Desktop Menu: 5 Menu Utama Lebih Besar, Tegas & Nyaman Dilihat --}}
                <div class="hidden lg:flex items-center justify-center gap-1.5 text-[15px]">
                    {{-- 1. Beranda --}}
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-full transition-colors {{ request()->routeIs('home') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand font-bold' }}">
                        Beranda
                    </a>

                    {{-- 2. Dropdown Profil --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->routeIs('profile') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
                            <span>Profil</span>
                            <x-app-icon name="chevron-down" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': open }" />
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
                            class="absolute left-0 top-full pt-1.5 w-72 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-2 space-y-1">
                                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('profile') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="building" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Sejarah & Visi Misi</span>
                                </a>
                                <a href="{{ route('facilities') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('facilities') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="check" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>11 Fasilitas Sekolah</span>
                                </a>
                                <a href="{{ route('staff') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('staff') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="users" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Guru & Tenaga Kependidikan</span>
                                </a>
                                <a href="{{ route('student-stats') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="chart" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Statistik Rombel Siswa</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Dropdown Belajar --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
                            <span>Belajar</span>
                            <x-app-icon name="chevron-down" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': open }" />
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
                            class="absolute left-0 top-full pt-1.5 w-72 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-2 space-y-1">
                                <a href="{{ route('materials') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('materials') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="book" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Materi Pelajaran</span>
                                </a>
                                <a href="{{ route('quizzes') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('quizzes') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="quiz" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Latihan Soal Mandiri</span>
                                </a>
                                <a href="{{ route('library') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('library') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="book" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Perpustakaan Digital</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Dropdown Kesiswaan --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->is('pilketos*') || request()->routeIs('gallery') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
                            <span>Kesiswaan</span>
                            <x-app-icon name="chevron-down" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': open }" />
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
                            class="absolute left-0 top-full pt-1.5 w-72 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-2 space-y-1">
                                <a href="{{ route('pilketos') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('pilketos') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="vote" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Bilik Suara E-Voting</span>
                                </a>
                                <a href="{{ route('pilketos.live') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('pilketos.live') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="chart" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Quick Count Suara</span>
                                </a>
                                <a href="{{ route('gallery') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('gallery') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="photo" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Galeri Foto Kegiatan</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Dropdown Informasi --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
                            <span>Informasi</span>
                            <x-app-icon name="chevron-down" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': open }" />
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
                            class="absolute left-0 top-full pt-1.5 w-72 z-50"
                        >
                            <div class="bg-white rounded-2xl shadow-xl border-2 border-sun-soft p-2 space-y-1">
                                <a href="{{ route('posts') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('posts*') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="news" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Berita & Pengumuman</span>
                                </a>
                                <a href="{{ route('downloads') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('downloads') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="download" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Pusat Unduhan Berkas</span>
                                </a>
                                <a href="{{ route('contact') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('contact') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="phone" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Kontak & Lokasi</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksi Kanan (Masuk / Login Gaya Pill Figma) --}}
                <div class="flex items-center gap-2.5">
                    <a href="{{ url('/admin') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-sun hover:bg-sun-hover active:scale-95 text-navy font-black text-sm tracking-wide shadow-xs hover:shadow-md transition-all">
                        <x-app-icon name="user" class="w-4 h-4 text-navy shrink-0" />
                        <span>Masuk / Login</span>
                    </a>

                    {{-- Hamburger Mobile Button --}}
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2.5 rounded-2xl text-navy hover:bg-cream lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center transition-colors" aria-label="Buka menu navigasi">
                        <x-app-icon name="menu" class="w-6 h-6" x-show="!mobileMenuOpen" />
                        <x-app-icon name="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak />
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Drawer Menu: Accordion Bersih & Profesional (Tidak Menumpuk Semua Menu) --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-data="{
                activeSection: '{{ request()->is('profil*') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') ? 'profil' : (request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'belajar' : (request()->is('pilketos*') || request()->routeIs('gallery') ? 'kesiswaan' : (request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'informasi' : ''))) }}'
            }"
            class="lg:hidden border-t-2 border-sun-soft bg-white px-4 py-4 space-y-2 shadow-xl max-h-[85vh] overflow-y-auto"
        >
            {{-- 1. Beranda --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[15px] font-black transition-colors {{ request()->routeIs('home') ? 'bg-sky-soft text-brand' : 'text-navy hover:bg-cream' }}">
                <x-app-icon name="home" class="w-5 h-5 text-brand shrink-0" />
                <span>Beranda</span>
            </a>

            {{-- 2. Profil Sekolah (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->is('profil*') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'profil' ? '' : 'profil')"
                    class="w-full flex items-center justify-between px-4 py-3 text-[15px] font-extrabold text-navy text-left select-none transition-colors hover:bg-cream"
                >
                    <div class="flex items-center gap-3">
                        <x-app-icon name="building" class="w-5 h-5 text-brand shrink-0" />
                        <span>Profil Sekolah</span>
                    </div>
                    <x-app-icon name="chevron-down" class="w-4 h-4 text-ink-mute transition-transform duration-200" ::class="{ 'rotate-180': activeSection === 'profil' }" />
                </button>
                <div
                    x-show="activeSection === 'profil'"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="px-3 pb-2.5 pt-1 space-y-1 border-t border-sun-soft/40 bg-white"
                >
                    <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('profile') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Sejarah & Visi Misi</span>
                    </a>
                    <a href="{{ route('facilities') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('facilities') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>11 Fasilitas Sekolah</span>
                    </a>
                    <a href="{{ route('staff') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('staff') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Guru & Tenaga Kependidikan</span>
                    </a>
                    <a href="{{ route('student-stats') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Statistik Rombel Siswa</span>
                    </a>
                </div>
            </div>

            {{-- 3. Ruang Belajar (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'belajar' ? '' : 'belajar')"
                    class="w-full flex items-center justify-between px-4 py-3 text-[15px] font-extrabold text-navy text-left select-none transition-colors hover:bg-cream"
                >
                    <div class="flex items-center gap-3">
                        <x-app-icon name="book" class="w-5 h-5 text-brand shrink-0" />
                        <span>Ruang Belajar</span>
                    </div>
                    <x-app-icon name="chevron-down" class="w-4 h-4 text-ink-mute transition-transform duration-200" ::class="{ 'rotate-180': activeSection === 'belajar' }" />
                </button>
                <div
                    x-show="activeSection === 'belajar'"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="px-3 pb-2.5 pt-1 space-y-1 border-t border-sun-soft/40 bg-white"
                >
                    <a href="{{ route('materials') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('materials') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Materi Pelajaran</span>
                    </a>
                    <a href="{{ route('quizzes') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('quizzes') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Latihan Soal Mandiri</span>
                    </a>
                    <a href="{{ route('library') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('library') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Perpustakaan Digital</span>
                    </a>
                </div>
            </div>

            {{-- 4. Kesiswaan (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->is('pilketos*') || request()->routeIs('gallery') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'kesiswaan' ? '' : 'kesiswaan')"
                    class="w-full flex items-center justify-between px-4 py-3 text-[15px] font-extrabold text-navy text-left select-none transition-colors hover:bg-cream"
                >
                    <div class="flex items-center gap-3">
                        <x-app-icon name="vote" class="w-5 h-5 text-brand shrink-0" />
                        <span>Kesiswaan</span>
                    </div>
                    <x-app-icon name="chevron-down" class="w-4 h-4 text-ink-mute transition-transform duration-200" ::class="{ 'rotate-180': activeSection === 'kesiswaan' }" />
                </button>
                <div
                    x-show="activeSection === 'kesiswaan'"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="px-3 pb-2.5 pt-1 space-y-1 border-t border-sun-soft/40 bg-white"
                >
                    <a href="{{ route('pilketos') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('pilketos') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Bilik Suara E-Voting</span>
                    </a>
                    <a href="{{ route('pilketos.live') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('pilketos.live') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Quick Count Suara Real-Time</span>
                    </a>
                    <a href="{{ route('gallery') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('gallery') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Galeri Foto Kegiatan</span>
                    </a>
                </div>
            </div>

            {{-- 5. Informasi & Layanan (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'informasi' ? '' : 'informasi')"
                    class="w-full flex items-center justify-between px-4 py-3 text-[15px] font-extrabold text-navy text-left select-none transition-colors hover:bg-cream"
                >
                    <div class="flex items-center gap-3">
                        <x-app-icon name="news" class="w-5 h-5 text-brand shrink-0" />
                        <span>Informasi & Layanan</span>
                    </div>
                    <x-app-icon name="chevron-down" class="w-4 h-4 text-ink-mute transition-transform duration-200" ::class="{ 'rotate-180': activeSection === 'informasi' }" />
                </button>
                <div
                    x-show="activeSection === 'informasi'"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="px-3 pb-2.5 pt-1 space-y-1 border-t border-sun-soft/40 bg-white"
                >
                    <a href="{{ route('posts') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('posts*') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Berita & Pengumuman</span>
                    </a>
                    <a href="{{ route('downloads') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('downloads') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Pusat Unduhan Berkas</span>
                    </a>
                    <a href="{{ route('contact') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('contact') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <span>Kontak & Lokasi</span>
                    </a>
                </div>
            </div>

            {{-- Tombol Masuk / Login CTA --}}
            <div class="pt-3 border-t-2 border-sun-soft">
                <a href="{{ url('/admin') }}" class="flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-sun text-navy font-black text-sm text-center shadow-xs hover:bg-sun-hover active:scale-95 transition-all">
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
    <footer class="bg-navy text-white pt-12 pb-8 px-4 sm:px-6 lg:px-8 border-t-2 border-brand-dark">
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
