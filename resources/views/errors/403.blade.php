<x-layouts.app title="403 - Akses Dibatasi">
    <div class="min-h-[70vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl w-full text-center space-y-6">
            {{-- Visual Angka 403 --}}
            <div class="relative inline-block select-none" aria-hidden="true">
                <span class="text-8xl sm:text-9xl font-black text-rose-200 tracking-tighter block leading-none">
                    403
                </span>
            </div>

            {{-- Pesan Utama --}}
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight">
                    Akses Dibatasi
                </h1>
                <p class="text-sm sm:text-base text-ink-soft max-w-md mx-auto leading-relaxed font-medium">
                    Anda tidak memiliki hak akses untuk membuka halaman atau aksi ini. Silakan masuk menggunakan akun yang sesuai.
                </p>
            </div>

            {{-- Pintasan Navigasi Cepat --}}
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-primary !min-h-[44px] px-6 text-sm shadow-xs flex items-center gap-2">
                    <x-app-icon name="home" class="w-4 h-4" />
                    <span>Kembali ke Beranda</span>
                </a>
                <a href="{{ route('login') }}" class="btn-ghost !min-h-[44px] px-4 text-sm flex items-center gap-2">
                    <x-app-icon name="shield" class="w-4 h-4" />
                    <span>Portal Masuk Terpadu</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
