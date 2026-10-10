<div class="w-full text-center mb-6">
    {{-- Tombol Kembali ke Beranda --}}
    <div class="w-full flex items-center justify-between mb-6 pb-2 border-b border-slate-100">
        <a
            href="{{ url('/') }}"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>

        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
            Panel Internal
        </span>
    </div>

    {{-- Logo Sekolah & Branding Tengah --}}
    <div class="space-y-3">
        <div class="flex items-center justify-center">
            <div class="w-16 h-16 rounded-2xl bg-white p-2 shadow-xs border border-slate-100 flex items-center justify-center">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Logo SMP Negeri 4 Cepu"
                    class="w-full h-full object-contain"
                />
            </div>
        </div>

        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">
                SMP NEGERI 4 CEPU
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Satu Sekolah, Satu Ruang Digital
            </p>
        </div>
    </div>
</div>
