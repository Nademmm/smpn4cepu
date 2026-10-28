<x-layouts.app
    :title="$post->title . ' - SMP Negeri 4 Cepu'"
    :metaDescription="$post->excerpt"
    :metaImage="$post->featured_image_url"
    ogType="article"
>
    @push('meta')
        <meta property="article:published_time" content="{{ ($post->published_at ?? $post->created_at)->toAtomString() }}">
        <meta property="article:modified_time" content="{{ $post->updated_at->toAtomString() }}">
        <meta property="article:section" content="{{ $post->category?->label() ?? 'Berita' }}">
        <script type="application/ld+json">{!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => \App\Support\Seo::url('/berita/' . $post->slug)],
            'headline' => \Illuminate\Support\Str::limit($post->title, 110, ''),
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $post->excerpt), 200),
            'image' => [\App\Support\Seo::absoluteAsset($post->featured_image_url) ?: \App\Support\Seo::url('/images/logo.png')],
            'datePublished' => ($post->published_at ?? $post->created_at)->toAtomString(),
            'dateModified' => $post->updated_at->toAtomString(),
            'articleSection' => $post->category?->label(),
            'inLanguage' => 'id-ID',
            'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? 'Admin Sekolah'],
            'publisher' => [
                '@type' => 'EducationalOrganization',
                '@id' => \App\Support\Seo::url('/') . '#school',
                'name' => 'SMP Negeri 4 Cepu',
                'logo' => ['@type' => 'ImageObject', 'url' => \App\Support\Seo::url('/images/logo.png')],
            ],
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endpush

    <x-page-header
        :title="$post->title"
        :crumb="\Illuminate\Support\Str::limit($post->title, 60)"
        :parents="[['Berita', route('posts')]]"
    />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16">
        <x-card class="space-y-6">
            <div>
                <a href="{{ route('posts') }}" class="link-brand text-sm inline-flex items-center gap-1.5 mb-4">
                    <x-app-icon name="arrow-left" class="w-4 h-4" />
                    <span>Kembali ke daftar berita</span>
                </a>

                @if ($post->featured_image_url)
                    <img
                        src="{{ $post->featured_image_url }}"
                        alt="{{ $post->title }}"
                        width="800"
                        height="384"
                        fetchpriority="high"
                        decoding="async"
                        class="w-full h-72 sm:h-96 object-cover rounded-2xl mb-6 border-2 border-sun-soft"
                    >
                @endif

                <h2 class="text-2xl sm:text-3xl font-extrabold text-navy leading-tight mb-3">
                    {{ $post->title }}
                </h2>

                <div class="text-xs font-semibold text-ink-mute pb-5 border-b-2 border-sun-soft flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span>Tanggal: {{ $post->published_at?->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }}</span>
                        <span>·</span>
                        <span>Oleh: {{ $post->author?->name ?? 'Admin Sekolah' }}</span>
                    </div>

                    {{-- Tombol Bagikan Media Sosial --}}
                    <div
                        class="flex items-center gap-2"
                        x-data="{
                            copied: false,
                            copyLink() {
                                navigator.clipboard.writeText(window.location.href);
                                this.copied = true;
                                setTimeout(() => this.copied = false, 2500);
                            }
                        }"
                    >
                        <a
                            href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' - Baca selengkapnya di portal resmi SMPN 4 Cepu: ' . url()->current()) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 min-h-[40px] rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 text-xs font-extrabold hover:bg-emerald-100 transition-colors shadow-2xs"
                        >
                            <x-app-icon name="chat" class="w-3.5 h-3.5 text-emerald-700" />
                            <span>Bagikan ke WhatsApp</span>
                        </a>

                        <button
                            type="button"
                            @click="copyLink()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 min-h-[40px] rounded-full bg-sky-soft text-brand border border-sun-soft text-xs font-extrabold hover:bg-white transition-colors cursor-pointer"
                        >
                            <x-app-icon name="document" class="w-3.5 h-3.5 text-brand" />
                            <span x-text="copied ? 'Tersalin!' : 'Salin Tautan'"></span>
                        </button>
                    </div>
                </div>
            </div>

            @php
                $hasHtml = preg_match('/<(p|br|h[1-6]|ul|ol|li|strong|b|em|i|blockquote|a)\b/i', (string) $post->content) === 1;
            @endphp
            @if ($hasHtml)
                <div class="prose max-w-none text-navy text-sm sm:text-base leading-relaxed font-medium">
                    {!! strip_tags($post->content, '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4><blockquote><a>') !!}
                </div>
            @else
                <div class="prose max-w-none text-navy text-sm sm:text-base leading-relaxed whitespace-pre-line font-medium">
                    {{ $post->content }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
