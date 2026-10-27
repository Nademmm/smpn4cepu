@props(['title', 'subtitle' => null, 'crumb' => null, 'variant' => 'blue'])
@php
    $hasTabs = isset($tabs) && $tabs->isNotEmpty();
    $bg = match ($variant) {
        'violet' => 'bg-gradient-to-br from-grape to-brand',
        'amber' => 'bg-gradient-to-br from-navy to-brand-dark',
        default => 'bg-brand',
    };
@endphp
<header class="{{ $bg }} px-4 sm:px-6 pt-12 {{ $hasTabs ? 'pb-0' : 'pb-9' }}">
    <div class="mx-auto max-w-7xl">
        @if ($crumb)
            <nav aria-label="Jejak halaman" class="mb-2 text-[13px] font-semibold text-white/85">
                <ol class="inline-flex items-center gap-1.5" itemscope itemtype="https://schema.org/BreadcrumbList">
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ route('home') }}" itemprop="item" class="hover:underline">
                            <span itemprop="name">Beranda</span>
                        </a>
                        <meta itemprop="position" content="1" />
                    </li>
                    <li aria-hidden="true">/</li>
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                        <span itemprop="name">{{ $crumb }}</span>
                        <meta itemprop="position" content="2" />
                    </li>
                </ol>
            </nav>
        @endif
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 max-w-2xl text-[15px] text-white/90 {{ $hasTabs ? 'mb-7' : '' }}">{{ $subtitle }}</p>
        @endif
        @if ($hasTabs)
            <div class="flex gap-1.5 overflow-x-auto" role="navigation" aria-label="Bagian halaman">
                {{ $tabs }}
            </div>
        @endif
    </div>
</header>
