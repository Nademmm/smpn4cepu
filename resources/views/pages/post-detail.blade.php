<x-layouts.app :title="$post->title">
    @push('meta')
        <meta property="og:title" content="{{ $post->title }} - SMP Negeri 4 Cepu">
        <meta property="og:description" content="{{ $post->excerpt }}">
        @if ($post->featured_image_url)
            <meta property="og:image" content="{{ $post->featured_image_url }}">
            <meta name="twitter:image" content="{{ $post->featured_image_url }}">
        @endif
        <meta name="twitter:title" content="{{ $post->title }} - SMP Negeri 4 Cepu">
        <meta name="twitter:description" content="{{ $post->excerpt }}">
    @endpush

    <x-page-header
        :title="$post->title"
        crumb="Detail Berita"
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
                        class="w-full h-72 sm:h-96 object-cover rounded-2xl mb-6 border-2 border-sun-soft"
                    >
                @endif

                <h1 class="text-2xl sm:text-3xl font-extrabold text-navy leading-tight mb-3">
                    {{ $post->title }}
                </h1>

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
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 text-xs font-extrabold hover:bg-emerald-100 transition-colors shadow-2xs"
                        >
                            <span>💬</span>
                            <span>Bagikan ke WhatsApp</span>
                        </a>

                        <button
                            type="button"
                            @click="copyLink()"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-sky-soft text-brand border border-sun-soft text-xs font-extrabold hover:bg-white transition-colors cursor-pointer"
                        >
                            <span x-text="copied ? '✓ Tersalin!' : '🔗 Salin Tautan'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="prose max-w-none text-navy text-sm sm:text-base leading-relaxed whitespace-pre-line font-medium">
                {{ $post->content }}
            </div>
        </x-card>
    </div>
</x-layouts.app>
