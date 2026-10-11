<div style="width: 100%; margin-bottom: 1.5rem;">
    {{-- Tombol Navigasi Kembali ke Beranda & Badge Portal --}}
    <div class="fi-login-divider" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid;">
        <a
            href="{{ url('/') }}"
            class="fi-login-back-btn"
            style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: color 0.2s;"
        >
            <svg style="width: 15px; height: 15px; min-width: 15px; min-height: 15px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>

        <span class="fi-login-badge" style="display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase;">
            Panel Internal
        </span>
    </div>

    {{-- Logo Sekolah & Branding Tengah --}}
    <div style="text-align: center; margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 0.85rem;">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Logo SMP Negeri 4 Cepu"
                style="height: 72px; width: auto; max-width: 100%; object-fit: contain; display: block;"
            />
        </div>

        <h1 class="fi-login-title" style="font-size: 1.25rem; font-weight: 900; letter-spacing: -0.02em; margin: 0; line-height: 1.3;">
            Masuk Administrator
        </h1>
        <p class="fi-login-subtitle" style="font-size: 0.75rem; font-weight: 600; margin-top: 0.25rem; margin-bottom: 0; line-height: 1.4;">
            Panel Pengelolaan Resmi SMP Negeri 4 Cepu
        </p>
    </div>
</div>
