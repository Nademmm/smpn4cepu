<x-layouts.app title="404 - Halaman Tidak Ditemukan">
    <div class="min-h-[70vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl w-full text-center space-y-6">
            {{-- Visual Angka 404 & Badge --}}
            <div class="relative inline-block select-none">
                <span class="text-8xl sm:text-9xl font-black text-sky-100/90 tracking-tighter block leading-none">
                    404
                </span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-brand/10 border border-brand/20 text-brand flex items-center justify-center shadow-xs">
                        <x-app-icon name="information-circle" class="w-8 h-8" />
                    </div>
                </div>
            </div>

            {{-- Pesan Utama --}}
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight">
                    Halaman Tidak Ditemukan
                </h1>
                <p class="text-sm sm:text-base text-ink-soft max-w-md mx-auto leading-relaxed font-medium">
                    Mohon maaf, tautan yang Anda tuju mungkin sudah dipindahkan, dihapus, atau alamat URL yang Anda masukkan salah.
                </p>
            </div>

            {{-- Pintasan Navigasi Cepat --}}
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-primary !min-h-[44px] px-6 text-sm shadow-xs flex items-center gap-2">
                    <x-app-icon name="home" class="w-4 h-4" />
                    <span>Kembali ke Beranda</span>
                </a>
                <a href="{{ route('posts') }}" class="btn-secondary !min-h-[44px] px-5 text-sm flex items-center gap-2">
                    <x-app-icon name="news" class="w-4 h-4" />
                    <span>Jelajahi Berita</span>
                </a>
                <a href="{{ route('contact') }}" class="btn-ghost !min-h-[44px] px-4 text-sm flex items-center gap-2">
                    <x-app-icon name="chat" class="w-4 h-4" />
                    <span>Hubungi Sekolah</span>
                </a>
            </div>

            {{-- Kotak Pencarian Alternatif --}}
            <div class="pt-6 border-t border-sun-soft max-w-md mx-auto">
                <p class="text-xs font-bold text-ink-mute uppercase tracking-wider mb-3">
                    Atau cari informasi yang Anda butuhkan:
                </p>
                <form action="{{ route('posts') }}" method="GET" class="relative">
                    <input
                        type="text"
                        name="q"
                        placeholder="Ketik topik atau kata kunci..."
                        class="field pl-11 !py-2.5 pr-20 text-xs shadow-2xs"
                    >
                    <x-app-icon name="search" class="w-4 h-4 text-ink-mute absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 btn-primary !min-h-[32px] px-3 text-xs">
                        Cari
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
