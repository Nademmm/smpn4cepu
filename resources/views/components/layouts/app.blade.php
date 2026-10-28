<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $siteName = 'SMP Negeri 4 Cepu';
        $defaultTitle = $siteName . ' - Sekolah Menengah Pertama di Cepu, Blora';
        $defaultDescription = 'Situs resmi SMP Negeri 4 Cepu (SMPN 4 Cepu, NPSN 20314928), Blora, Jawa Tengah: berita sekolah, profil, direktori guru, perpustakaan digital, dan layanan belajar daring.';

        $pageTitle = (isset($title) && $title !== '' && $title !== 'Beranda' && $title !== $siteName)
            ? (str_contains($title, $siteName) ? $title : $title . ' - ' . $siteName)
            : $defaultTitle;

        $pageDescription = \Illuminate\Support\Str::limit(
            trim(preg_replace('/\s+/', ' ', strip_tags((string) ($metaDescription ?? $defaultDescription)))),
            158,
            '...'
        );

        // Seluruh URL SEO memakai domain utama dari APP_URL, bukan host request (hindari duplikat www / domain sementara)
        $baseUrl = \App\Support\Seo::baseUrl();
        $logoUrl = \App\Support\Seo::url('/images/logo.png');

        $hasOwnImage = ! empty($metaImage);
        $pageImage = $hasOwnImage ? \App\Support\Seo::absoluteAsset($metaImage) : $logoUrl;
        $pageOgType = $ogType ?? 'website';

        $currentPage = (int) request()->query('page', 1);
        $canonicalUrl = \App\Support\Seo::url(trim(request()->path(), '/')) . ($currentPage > 1 ? '?page=' . $currentPage : '');

        $isNoindex = ! empty($noindex) || request()->filled('q');
        $robotsContent = $isNoindex
            ? 'noindex, follow'
            : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $sameAs = array_values(array_filter([
            $schoolSetting->instagram_url ?? null,
            $schoolSetting->youtube_url ?? null,
            $schoolSetting->facebook_url ?? null,
            $schoolSetting->tiktok_url ?? null,
            config('seo.kemdikbud_profile_url'),
        ]));

        $weekdayHours = [
            ['dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'opens' => '07:00', 'closes' => '14:00'],
            ['dayOfWeek' => 'Friday', 'opens' => '07:00', 'closes' => '11:00'],
            ['dayOfWeek' => 'Saturday', 'opens' => '07:00', 'closes' => '13:00'],
        ];

        $schemaGraph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'EducationalOrganization',
                    '@id' => $baseUrl . '#school',
                    'name' => $siteName,
                    'alternateName' => ['SMPN 4 Cepu', 'SMP 4 Cepu'],
                    'description' => $defaultDescription,
                    'identifier' => config('seo.npsn'),
                    'url' => $baseUrl,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $logoUrl,
                        'width' => 300,
                        'height' => 300,
                    ],
                    'image' => $logoUrl,
                    'telephone' => $schoolSetting->school_phone ?? '(0296) 421631',
                    'email' => $schoolSetting->school_email ?? 'smpn4cepu@gmail.com',
                    'foundingDate' => '1979-09-04',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => $schoolSetting->school_address ?? 'Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315',
                        'addressLocality' => 'Cepu',
                        'addressRegion' => 'Jawa Tengah',
                        'postalCode' => '58315',
                        'addressCountry' => 'ID',
                    ],
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => config('seo.latitude'),
                        'longitude' => config('seo.longitude'),
                    ],
                    'hasMap' => 'https://www.google.com/maps?q=' . config('seo.latitude') . ',' . config('seo.longitude'),
                    'areaServed' => 'Cepu, Kabupaten Blora, Jawa Tengah',
                    'openingHoursSpecification' => array_map(
                        fn (array $h) => ['@type' => 'OpeningHoursSpecification'] + $h,
                        $weekdayHours
                    ),
                    'sameAs' => $sameAs ?: null,
                ]),
                [
                    '@type' => 'WebSite',
                    '@id' => $baseUrl . '#website',
                    'url' => $baseUrl,
                    'name' => $siteName,
                    'alternateName' => 'SMPN 4 Cepu',
                    'inLanguage' => 'id-ID',
                    'publisher' => ['@id' => $baseUrl . '#school'],
                ],
                array_filter([
                    '@type' => $pageType ?? 'WebPage',
                    '@id' => $canonicalUrl . '#webpage',
                    'url' => $canonicalUrl,
                    'name' => $pageTitle,
                    'description' => $pageDescription,
                    'inLanguage' => 'id-ID',
                    'isPartOf' => ['@id' => $baseUrl . '#website'],
                    'about' => ['@id' => $baseUrl . '#school'],
                    'primaryImageOfPage' => $hasOwnImage ? ['@type' => 'ImageObject', 'url' => $pageImage] : null,
                ]),
            ],
        ];
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1F5FBF">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="{{ $robotsContent }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="id" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalUrl }}">
    <link rel="alternate" type="application/rss+xml" title="Berita {{ $siteName }}" href="{{ $baseUrl }}/feed.xml">

    @if (config('seo.google_site_verification'))
        <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
    @endif
    @if (config('seo.bing_site_verification'))
        <meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
    @endif

    {{-- Sinyal lokasi untuk pencarian lokal (Cepu, Blora) --}}
    <meta name="geo.region" content="ID-JT">
    <meta name="geo.placename" content="Cepu, Blora">
    <meta name="geo.position" content="{{ config('seo.latitude') }};{{ config('seo.longitude') }}">
    <meta name="ICBM" content="{{ config('seo.latitude') }}, {{ config('seo.longitude') }}">

    {{-- Open Graph / Facebook & WhatsApp Share Card --}}
    <meta property="og:type" content="{{ $pageOgType }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:image:alt" content="{{ $hasOwnImage ? $pageTitle : 'Logo ' . $siteName }}">
    @unless ($hasOwnImage)
        <meta property="og:image:width" content="300">
        <meta property="og:image:height" content="300">
    @endunless

    {{-- Twitter Cards: kartu besar hanya bila halaman punya gambar sendiri --}}
    <meta name="twitter:card" content="{{ $hasOwnImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">
    <meta name="twitter:image:alt" content="{{ $hasOwnImage ? $pageTitle : 'Logo ' . $siteName }}">

    @stack('meta')

    {{-- Schema.org JSON-LD: Organisasi Pendidikan + WebSite + WebPage --}}
    <script type="application/ld+json">{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>

    {{-- Favicon / Icon Tab Browser --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    @if (request()->routeIs('home'))
        <link rel="preload" as="image" href="{{ asset('images/logo.webp') }}" type="image/webp" fetchpriority="high">
    @endif

    {{-- Font Nunito di-host sendiri (lihat resources/css/app.css), cukup preload dua bobot utama --}}
    <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ \Illuminate\Support\Facades\Vite::asset('node_modules/@fontsource/nunito/files/nunito-latin-400-normal.woff2') }}">
    <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ \Illuminate\Support\Facades\Vite::asset('node_modules/@fontsource/nunito/files/nunito-latin-800-normal.woff2') }}">

    {{-- Critical CSS: Menjamin First Contentful Paint instan tanpa layar putih kosong --}}
    <style>
        body{background-color:#F1EBDA;color:#1B2A4A;font-family:'Nunito',system-ui,-apple-system,sans-serif;margin:0}
        .bg-cream{background-color:#F1EBDA}
        .bg-brand{background-color:#1F5FBF}
        .from-brand{--tw-gradient-from:#1F5FBF var(--tw-gradient-from-position)}
        .to-brand-dark{--tw-gradient-to:#163D8A var(--tw-gradient-to-position)}
        .bg-gradient-to-br{background-image:linear-gradient(to bottom right,var(--tw-gradient-stops))}
    </style>

    @vite('resources/css/app.css')
    @livewireStyles


    {{-- Speculation Rules API: Prefetch instan saat kursor mengarah ke tautan internal --}}
    <script type="speculationrules">
    {
        "prefetch": [
            {
                "where": {
                    "and": [
                        { "href_matches": "/*" },
                        { "not": { "href_matches": "/admin/*" } },
                        { "not": { "href_matches": "/livewire/*" } },
                        { "not": { "href_matches": "/logout" } },
                        { "not": { "href_matches": "/unduhan/*/download" } }
                    ]
                },
                "eagerness": "moderate"
            }
        ]
    }
    </script>
</head>
<body class="bg-cream text-navy font-sans antialiased flex flex-col min-h-screen">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-navy focus:px-4 focus:py-2.5 focus:text-sm focus:font-bold focus:text-white focus:shadow-lg">
        Lewati ke konten utama
    </a>

    @php
        try {
            $currentUser = auth()->user();
        } catch (\Throwable $e) {
            $currentUser = null;
        }
    @endphp

    {{-- Banner Pengumuman Kilat (Marquee / Alert Beranda) --}}
    @if (!empty($schoolSetting?->announcement_bar_active) && !empty($schoolSetting?->announcement_bar_text))
        <div class="bg-brand-dark text-white text-xs sm:text-sm py-2 px-4 border-b border-sun/30 font-bold">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 overflow-hidden">
                    <span class="inline-flex items-center justify-center bg-sun text-navy font-black text-[10px] px-2 py-0.5 rounded-full uppercase shrink-0">
                        Info
                    </span>
                    <span class="truncate">
                        {{ $schoolSetting->announcement_bar_text }}
                    </span>
                </div>
                @if (!empty($schoolSetting->announcement_bar_link))
                    <a href="{{ $schoolSetting->announcement_bar_link }}" class="underline text-sun-soft hover:text-white shrink-0 text-xs font-black">
                        Lihat Selengkapnya &rarr;
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Sticky Main Navbar (Sesuai Desain Figma) --}}
    <nav class="sticky top-0 z-40 bg-white border-b-2 border-sun-soft shadow-xs" x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false">
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
                    <a href="{{ route('home') }}" {!! request()->routeIs('home') ? 'aria-current="page"' : '' !!} class="px-4 py-2 rounded-full transition-colors {{ request()->routeIs('home') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand font-bold' }}">
                        Beranda
                    </a>

                    {{-- 2. Dropdown Profil --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->routeIs('profile') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') || request()->routeIs('achievements') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
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
                                    <x-app-icon name="flag" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Sejarah & Visi Misi</span>
                                </a>
                                <a href="{{ route('facilities') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('facilities') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="facility" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Fasilitas Sekolah</span>
                                </a>
                                <a href="{{ route('staff') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('staff') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="users" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Guru & Tenaga Kependidikan</span>
                                </a>
                                <a href="{{ route('student-stats') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="chart" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Statistik Rombel Siswa</span>
                                </a>
                                <a href="{{ route('achievements') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('achievements') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="trophy" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Prestasi Siswa & Sekolah</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Dropdown Belajar --}}
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
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
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->is('pilketos*') || request()->routeIs('gallery') || request()->routeIs('extracurriculars') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
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
                                <a href="{{ route('extracurriculars') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] font-bold transition-colors {{ request()->routeIs('extracurriculars') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream hover:text-brand' }}">
                                    <x-app-icon name="sparkles" class="w-[18px] h-[18px] text-brand shrink-0" />
                                    <span>Ekstrakurikuler & Bakat</span>
                                </a>
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
                    <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true" class="px-4 py-2 rounded-full flex items-center gap-1.5 transition-colors font-bold {{ request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:text-brand' }}">
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

                {{-- Aksi Kanan (Masuk Portal / Profil Akun) --}}
                <div class="flex items-center gap-2.5">
                    @if (!$currentUser)
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-sun hover:bg-sun-hover active:scale-95 text-navy font-black text-sm tracking-wide shadow-xs hover:shadow-md transition-all">
                            <x-app-icon name="user" class="w-4 h-4 text-navy shrink-0" />
                            <span>Masuk Portal</span>
                        </a>
                    @else
                        <div class="hidden sm:flex items-center gap-2.5 bg-cream/80 border border-sun-soft/80 px-3 py-1.5 rounded-full">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-brand text-white text-xs font-black flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-black text-navy max-w-[120px] truncate">{{ $currentUser->name }}</span>
                            </div>
                            @if($currentUser->hasAnyRole(['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos']))
                                <a href="{{ url('/admin') }}" class="px-2.5 py-1 text-[11px] font-extrabold bg-brand text-white rounded-full hover:bg-brand-dark transition-colors">
                                    Admin
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Keluar dari akun" aria-label="Keluar dari akun" class="p-1 rounded-full text-ink-mute hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                    <x-app-icon name="logout" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Hamburger Mobile Button --}}
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" class="p-2.5 rounded-2xl text-navy hover:bg-cream lg:hidden min-h-[44px] min-w-[44px] flex items-center justify-center transition-colors" aria-label="Buka menu navigasi">
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
                activeSection: '{{ request()->is('profil*') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') || request()->routeIs('achievements') ? 'profil' : (request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'belajar' : (request()->is('pilketos*') || request()->routeIs('gallery') || request()->routeIs('extracurriculars') ? 'kesiswaan' : (request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'informasi' : ''))) }}'
            }"
            class="lg:hidden border-t-2 border-sun-soft bg-white px-4 py-4 space-y-2 shadow-xl max-h-[85vh] overflow-y-auto"
        >
            {{-- 1. Beranda --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[15px] font-black transition-colors {{ request()->routeIs('home') ? 'bg-sky-soft text-brand' : 'text-navy hover:bg-cream' }}">
                <x-app-icon name="home" class="w-5 h-5 text-brand shrink-0" />
                <span>Beranda</span>
            </a>

            {{-- 2. Profil Sekolah (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->is('profil*') || request()->routeIs('facilities') || request()->routeIs('staff') || request()->routeIs('student-stats') || request()->routeIs('achievements') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'profil' ? '' : 'profil')"
                    :aria-expanded="(activeSection === 'profil').toString()"
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
                        <x-app-icon name="flag" class="w-4 h-4 text-brand shrink-0" />
                        <span>Sejarah & Visi Misi</span>
                    </a>
                    <a href="{{ route('facilities') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('facilities') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="facility" class="w-4 h-4 text-brand shrink-0" />
                        <span>Fasilitas Sekolah</span>
                    </a>
                    <a href="{{ route('staff') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('staff') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="users" class="w-4 h-4 text-brand shrink-0" />
                        <span>Guru & Tenaga Kependidikan</span>
                    </a>
                    <a href="{{ route('student-stats') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('student-stats') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="chart" class="w-4 h-4 text-brand shrink-0" />
                        <span>Statistik Rombel Siswa</span>
                    </a>
                    <a href="{{ route('achievements') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('achievements') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="trophy" class="w-4 h-4 text-brand shrink-0" />
                        <span>Prestasi Siswa & Sekolah</span>
                    </a>
                </div>
            </div>

            {{-- 3. Ruang Belajar (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->is('materi*') || request()->is('latihan-soal*') || request()->routeIs('library') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'belajar' ? '' : 'belajar')"
                    :aria-expanded="(activeSection === 'belajar').toString()"
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
                        <x-app-icon name="book" class="w-4 h-4 text-brand shrink-0" />
                        <span>Materi Pelajaran</span>
                    </a>
                    <a href="{{ route('quizzes') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('quizzes') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="quiz" class="w-4 h-4 text-brand shrink-0" />
                        <span>Latihan Soal Mandiri</span>
                    </a>
                    <a href="{{ route('library') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('library') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="book" class="w-4 h-4 text-brand shrink-0" />
                        <span>Perpustakaan Digital</span>
                    </a>
                </div>
            </div>

            {{-- 4. Kesiswaan (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->is('pilketos*') || request()->routeIs('gallery') || request()->routeIs('extracurriculars') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'kesiswaan' ? '' : 'kesiswaan')"
                    :aria-expanded="(activeSection === 'kesiswaan').toString()"
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
                    <a href="{{ route('extracurriculars') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('extracurriculars') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="sparkles" class="w-4 h-4 text-brand shrink-0" />
                        <span>Ekstrakurikuler & Bakat</span>
                    </a>
                    <a href="{{ route('pilketos') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('pilketos') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="vote" class="w-4 h-4 text-brand shrink-0" />
                        <span>Bilik Suara E-Voting</span>
                    </a>
                    <a href="{{ route('pilketos.live') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('pilketos.live') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="chart" class="w-4 h-4 text-brand shrink-0" />
                        <span>Quick Count Suara Real-Time</span>
                    </a>
                    <a href="{{ route('gallery') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('gallery') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="photo" class="w-4 h-4 text-brand shrink-0" />
                        <span>Galeri Foto Kegiatan</span>
                    </a>
                </div>
            </div>

            {{-- 5. Informasi & Layanan (Accordion) --}}
            <div class="rounded-2xl border-2 {{ request()->routeIs('posts*') || request()->routeIs('downloads') || request()->routeIs('contact') ? 'border-brand/40 bg-sky-soft/30' : 'border-sun-soft/70 bg-white' }} overflow-hidden">
                <button
                    type="button"
                    @click="activeSection = (activeSection === 'informasi' ? '' : 'informasi')"
                    :aria-expanded="(activeSection === 'informasi').toString()"
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
                        <x-app-icon name="news" class="w-4 h-4 text-brand shrink-0" />
                        <span>Berita & Pengumuman</span>
                    </a>
                    <a href="{{ route('downloads') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('downloads') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="download" class="w-4 h-4 text-brand shrink-0" />
                        <span>Pusat Unduhan Berkas</span>
                    </a>
                    <a href="{{ route('contact') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('contact') ? 'bg-sky-soft text-brand font-black' : 'text-navy hover:bg-cream' }}">
                        <x-app-icon name="phone" class="w-4 h-4 text-brand shrink-0" />
                        <span>Kontak & Lokasi</span>
                    </a>
                </div>
            </div>

            {{-- Tombol Masuk / Profil Akun Mobile --}}
            <div class="pt-3 border-t-2 border-sun-soft">
                @if (!$currentUser)
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-sun text-navy font-black text-sm text-center shadow-xs hover:bg-sun-hover active:scale-95 transition-all">
                        <x-app-icon name="user" class="w-4 h-4 text-navy" />
                        <span>Masuk Portal Sekolah</span>
                    </a>
                @else
                    <div class="p-3.5 rounded-2xl bg-sky-soft/60 border border-brand/20 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand text-white text-xs font-black flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <span class="text-xs font-bold text-ink-mute uppercase block">Akun Aktif</span>
                                <h4 class="text-sm font-black text-navy leading-tight">{{ $currentUser->name }}</h4>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-1 border-t border-brand/10">
                            @if($currentUser->hasAnyRole(['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos']))
                                <a href="{{ url('/admin') }}" class="flex-1 py-2 text-center text-xs font-extrabold bg-brand text-white rounded-xl hover:bg-brand-dark transition-colors">
                                    Panel Admin
                                </a>
                            @else
                                <a href="{{ route('quizzes') }}" class="flex-1 py-2 text-center text-xs font-extrabold bg-brand text-white rounded-xl hover:bg-brand-dark transition-colors">
                                    Latihan Saya
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-2 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors border border-rose-200 inline-flex items-center gap-1.5">
                                    <x-app-icon name="logout" class="w-3.5 h-3.5 text-rose-600" />
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    {{-- Main Slot --}}
    <main id="main-content" tabindex="-1" class="grow">
        {{ $slot }}
    </main>

    {{-- Footer Resmi (Sesuai Desain Figma) --}}
    <footer class="bg-navy text-white pt-12 pb-8 px-4 sm:px-6 lg:px-8 border-t-2 border-brand-dark">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-8">
            {{-- Kolom 1: Identitas --}}
            <div class="lg:col-span-4">
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
            <div class="lg:col-span-2">
                <div class="font-extrabold text-sm text-sun mb-3 uppercase tracking-wider">Tautan Cepat</div>
                <div class="grid grid-cols-1 gap-2 text-sm text-slate-200 font-semibold">
                    <a href="{{ route('home') }}" class="hover:text-sun transition-colors">Beranda</a>
                    <a href="{{ route('profile') }}" class="hover:text-sun transition-colors">Profil Sekolah</a>
                    <a href="{{ route('achievements') }}" class="hover:text-sun transition-colors">Prestasi Siswa</a>
                    <a href="{{ route('facilities') }}" class="hover:text-sun transition-colors">Fasilitas Sekolah</a>
                    <a href="{{ route('staff') }}" class="hover:text-sun transition-colors">Guru & Tendik</a>
                    <a href="{{ route('materials') }}" class="hover:text-sun transition-colors">Ruang Belajar</a>
                    <a href="{{ route('extracurriculars') }}" class="hover:text-sun transition-colors">Ekstrakurikuler</a>
                    <a href="{{ route('library') }}" class="hover:text-sun transition-colors">Perpustakaan</a>
                    <a href="{{ route('pilketos') }}" class="hover:text-sun transition-colors">Pilketos</a>
                    <a href="{{ route('downloads') }}" class="hover:text-sun transition-colors">Pusat Unduhan</a>
                    <a href="{{ route('contact') }}" class="hover:text-sun transition-colors">Kontak</a>
                </div>
            </div>

            {{-- Kolom 3: Kontak Resmi Dinamis --}}
            <div class="lg:col-span-3">
                <div class="font-extrabold text-sm text-sun mb-3 uppercase tracking-wider">Kontak Sekolah</div>
                <div class="text-sm text-slate-300 space-y-2.5 leading-relaxed">
                    <div class="flex items-start gap-2">
                        <x-app-icon name="pin" class="w-4 h-4 text-sun shrink-0 mt-1" />
                        <span>{{ $schoolSetting->school_address ?? 'Jl. Raya Cepu Randu Km. 3,5, Mulyorejo, Cepu, Blora 58315' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-app-icon name="phone" class="w-4 h-4 text-sun shrink-0" />
                        <span>{{ $schoolSetting->school_phone ?? '(0296) 421631' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-app-icon name="mail" class="w-4 h-4 text-sun shrink-0" />
                        <span>{{ $schoolSetting->school_email ?? 'smpn4cepu@gmail.com' }}</span>
                    </div>

                    {{-- Kanal Media Sosial Resmi --}}
                    <div class="pt-3 mt-3 border-t border-slate-800">
                        <div class="text-xs font-bold text-sun uppercase tracking-wider mb-2.5">Ikuti Media Sosial Resmi</div>
                        <div class="flex items-center gap-2">
                            {{-- TikTok --}}
                            <a
                                href="{{ $schoolSetting->tiktok_url ?? 'https://www.tiktok.com/@smpn4cepu?_r=1&_t=ZS-9AQlZajBgUG' }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="TikTok Resmi SMPN 4 Cepu"
                                class="w-10 h-10 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-all hover:scale-105 shadow-xs"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.86 4.43c.8-.8 1.34-1.84 1.54-2.97V9.08a8.27 8.27 0 0 0 4.19 1.12V6.75a4.8 4.8 0 0 1-.0-.06z"/></svg>
                            </a>

                            {{-- YouTube --}}
                            <a
                                href="{{ $schoolSetting->youtube_url ?? 'https://youtube.com/@smpn4cepu?si=fh-hGAcEl9b_rj2S' }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="YouTube Resmi SMPN 4 Cepu"
                                class="w-10 h-10 rounded-full bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white flex items-center justify-center transition-all hover:scale-105 shadow-xs"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>

                            {{-- Facebook --}}
                            <a
                                href="{{ $schoolSetting->facebook_url ?? 'https://www.facebook.com/share/1F72gGkHoD/' }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Facebook Resmi SMPN 4 Cepu"
                                class="w-10 h-10 rounded-full bg-slate-800 hover:bg-blue-600 text-slate-300 hover:text-white flex items-center justify-center transition-all hover:scale-105 shadow-xs"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>

                            {{-- Instagram --}}
                            <a
                                href="{{ $schoolSetting->instagram_url ?? 'https://www.instagram.com/smpn4cepu_official?cplk=MW52cmoxajVnenVxNg==' }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Instagram Resmi SMPN 4 Cepu"
                                class="w-10 h-10 rounded-full bg-slate-800 hover:bg-pink-600 text-slate-300 hover:text-white flex items-center justify-center transition-all hover:scale-105 shadow-xs"
                            >
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom 4: Peta Lokasi Langsung --}}
            <div class="lg:col-span-3">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-extrabold text-sm text-sun uppercase tracking-wider">Lokasi Sekolah</div>
                    <a
                        href="https://maps.google.com/?q=SMP+Negeri+4+Cepu"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Buka rute navigasi SMP Negeri 4 Cepu di Google Maps"
                        class="text-[11px] font-bold text-sky-300 hover:text-sun transition-colors inline-flex items-center gap-1"
                        title="Buka rute navigasi di Google Maps"
                    >
                        <span>Buka Peta</span>
                        <x-app-icon name="arrow-right" class="w-3 h-3" />
                    </a>
                </div>

                <div class="rounded-2xl overflow-hidden border border-slate-700/80 shadow-md bg-slate-900 relative">
                    <iframe
                        src="{{ $schoolSetting?->maps_embed_url ?: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3958.8256193796336!2d111.56367817574345!3d-7.157602770284422!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e776994781498b3%3A0x6a053cbfd38cce88!2sSMP%20Negeri%204%20Cepu!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid' }}"
                        width="100%"
                        height="176"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Peta Interaktif SMP Negeri 4 Cepu"
                        class="w-full h-44"
                    ></iframe>
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
