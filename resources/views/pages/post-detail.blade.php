<x-layouts.app :title="$post->title">
    <section class="bg-gradient-to-b from-blue-900 via-indigo-950 to-slate-950 text-white py-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('posts') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-300 hover:text-white mb-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Kabar Sekolah</span>
            </a>
            <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide bg-blue-500/20 border border-blue-400/30 text-blue-300 mb-3">
                {{ $post->category->value }}
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold leading-tight tracking-tight mb-4">
                {{ $post->title }}
            </h1>
            <div class="flex items-center gap-4 text-xs text-slate-300">
                <span>Oleh: <strong>{{ $post->author->name ?? 'Admin Sekolah' }}</strong></span>
                <span>•</span>
                <span>{{ $post->published_at ? $post->published_at->format('d F Y') : $post->created_at->format('d F Y') }}</span>
                @if($post->event_date)
                    <span>•</span>
                    <span class="text-amber-300 font-bold">Tanggal Agenda: {{ \Carbon\Carbon::parse($post->event_date)->format('d F Y') }}</span>
                @endif
            </div>
        </div>
    </section>

    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($post->featured_image)
            <div class="rounded-3xl overflow-hidden mb-10 shadow-lg border border-slate-200 dark:border-slate-800">
                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-80 sm:h-96 object-cover">
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-base sm:text-lg font-semibold text-slate-700 dark:text-slate-300 leading-relaxed mb-6 italic border-l-4 border-blue-600 pl-4">
                {{ $post->excerpt }}
            </p>

            <div class="prose dark:prose-invert max-w-none text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line text-sm sm:text-base">
                {{ $post->content }}
            </div>
        </div>
    </article>
</x-layouts.app>
