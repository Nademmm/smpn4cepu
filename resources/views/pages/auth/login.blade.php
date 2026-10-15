<x-layouts.app title="Masuk Portal Akun">
    <div class="min-h-[75vh] flex items-center justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-6">
            {{-- Kartu Utama Login --}}
            <div class="bg-white rounded-3xl border-2 border-sun-soft/80 shadow-xl p-7 sm:p-9 space-y-6">
                {{-- Header Kartu --}}
                <div class="text-center space-y-2">
                    <a href="{{ route('home') }}" class="inline-block transition-transform hover:scale-105" title="Kembali ke Beranda">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Logo SMP Negeri 4 Cepu"
                            class="w-20 h-20 sm:w-24 sm:h-24 mx-auto object-contain drop-shadow-xs mb-1"
                        >
                    </a>
                    <h1 class="text-2xl font-black text-navy tracking-tight">Portal Masuk Terpadu</h1>
                    <p class="text-xs sm:text-sm text-ink-soft font-medium leading-relaxed">
                        Satu gerbang akses untuk Peserta Didik, Guru, dan Civitas SMP Negeri 4 Cepu.
                    </p>
                </div>

                {{-- Alert Error --}}
                @if ($errors->any())
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                        <div class="flex items-center gap-2 font-bold text-rose-900">
                            <x-app-icon name="x-circle" class="w-4 h-4 text-rose-600 shrink-0" />
                            <span>Gagal Masuk</span>
                        </div>
                        <p class="text-[12px] leading-relaxed">
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif

                {{-- Form Login --}}
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Input Akun / Email / NISN --}}
                    <div>
                        <label for="login_id" class="block text-xs font-black text-navy uppercase tracking-wider mb-1.5">
                            Email / Nama Akun / NISN
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="login_id"
                                name="login_id"
                                value="{{ old('login_id') }}"
                                required
                                autofocus
                                placeholder="Contoh: siswa@smpn4cepu.sch.id atau nama akun"
                                class="w-full px-4 py-3 rounded-xl border border-sand bg-cream/30 text-sm font-semibold text-navy placeholder:text-ink-mute focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand transition-all"
                            >
                        </div>
                    </div>

                    {{-- Input Password --}}
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-xs font-black text-navy uppercase tracking-wider mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input
                                :type="show ? 'text' : 'password'"
                                id="password"
                                name="password"
                                required
                                placeholder="Masukkan kata sandi Anda..."
                                class="w-full pl-4 pr-11 py-3 rounded-xl border border-sand bg-cream/30 text-sm font-semibold text-navy placeholder:text-ink-mute focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand transition-all"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-ink-mute hover:text-brand transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/40"
                                :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                tabindex="-1"
                            >
                                {{-- Ikon Mata Terbuka (Tampilkan) --}}
                                <svg
                                    x-show="!show"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{-- Ikon Mata Tertutup / Silang (Sembunyikan) --}}
                                <svg
                                    x-show="show"
                                    x-cloak
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 text-brand"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                                class="rounded border-sand text-brand focus:ring-brand w-4 h-4 cursor-pointer"
                            >
                            <span class="text-xs font-bold text-ink-soft">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full py-3.5 px-6 rounded-xl bg-brand hover:bg-brand-dark active:scale-[0.99] text-white font-extrabold text-sm tracking-wide shadow-md transition-all flex items-center justify-center gap-2"
                    >
                        <span>Masuk ke Akun</span>
                        <x-app-icon name="arrow-right" class="w-4 h-4 text-sun" />
                    </button>
                </form>
            </div>

            {{-- Navigasi Balik --}}
            <div class="text-center">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-soft hover:text-brand transition-colors"
                >
                    <x-app-icon name="arrow-left" class="w-3.5 h-3.5" />
                    <span>Kembali ke Halaman Beranda</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
