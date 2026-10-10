<x-layouts.app :title="$post->title">
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

                <div class="text-xs font-semibold text-ink-mute pb-5 border-b-2 border-sun-soft flex flex-wrap gap-3">
                    <span>Tanggal: {{ $post->published_at?->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }}</span>
                    <span>·</span>
                    <span>Oleh: {{ $post->author?->name ?? 'Admin Sekolah' }}</span>
                </div>
            </div>

            <div class="prose max-w-none text-navy text-sm sm:text-base leading-relaxed whitespace-pre-line font-medium">
                {{ $post->content }}
            </div>
        </x-card>
    </div>
</x-layouts.app>
