@php
    $resolvedActionMode = in_array(($actionMode ?? 'hand_over'), ['auto', 'hand_over', 'return', 'view_only'], true)
        ? ($actionMode ?? 'hand_over')
        : 'hand_over';
    $isReturnMode = $resolvedActionMode === 'return';
@endphp
<div
    x-data="circulationScanner(@js($resolvedActionMode))"
    x-on:close-modal.window="refreshOnClose()"
    class="circulation-modal-root {{ $isReturnMode ? 'is-return' : 'is-handover' }}"
>
    {{-- Input 1: Kamera Bawaan HP (Shutter Kamera Native Ponsel) --}}
    <input
        type="file"
        accept="image/*"
        capture="environment"
        x-ref="nativeCameraInput"
        class="circulation-hidden-input"
        x-on:change="scanPhotoFile($event.target.files[0], $event.target)"
    />

    {{-- Input 2: Unggah Berkas Gambar / Screenshot dari Galeri --}}
    <input
        type="file"
        accept="image/*"
        x-ref="fileUploadInput"
        class="circulation-hidden-input"
        x-on:change="scanPhotoFile($event.target.files[0], $event.target)"
    />

    {{-- PENANDA MODE AKTIF (TERPISAH DARI TOMBOL HALAMAN RESERVASI) --}}
    <div class="circulation-mode-banner {{ $isReturnMode ? 'is-return' : '' }}">
        <span class="circulation-mode-badge">
            {{ $isReturnMode ? 'Mode Pengembalian' : 'Mode Peminjaman' }}
        </span>
        <p class="circulation-mode-desc">
            {{ $isReturnMode
                ? 'Tiket berstatus Sedang Dipinjam akan otomatis dicatat kembali ke perpustakaan dan stok fisik bertambah.'
                : 'Tiket berstatus Menunggu Pengambilan akan otomatis diserahkan ke siswa dan mulai masa pinjam.' }}
        </p>
    </div>

    {{-- NAVIGASI TAB METODE PEMINDAIAN (FOTO / MANUAL) --}}
    <div class="circulation-tabs">
        <button
            type="button"
            x-on:click="activeTab = 'photo'"
            class="circulation-tab-btn"
            :class="activeTab === 'photo' ? 'is-active' : ''"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
            </svg>
            <span>Foto Kamera / Galeri</span>
        </button>

        <button
            type="button"
            x-on:click="activeTab = 'manual'"
            class="circulation-tab-btn is-manual"
            :class="activeTab === 'manual' ? 'is-active' : ''"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
            </svg>
            <span>Ketik / Alat Barcode</span>
        </button>
    </div>

    {{-- TAB 1: AMBIL FOTO KAMERA HP / UNGGAH GALERI --}}
    <div x-show="activeTab === 'photo'" class="circulation-panel-stack">
        <div class="circulation-photo-card">
            <div class="circulation-photo-header">
                <div class="circulation-photo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="circulation-photo-title">
                        Pindai QR Code via Foto / Gambar
                    </h4>
                    <p class="circulation-photo-subtitle">
                        Ambil foto QR tiket dengan kamera ponsel atau pilih gambar/tangkapan layar dari galeri.
                    </p>
                </div>
            </div>

            <div class="circulation-panel-stack">
                <button
                    type="button"
                    x-on:click="$refs.nativeCameraInput.click()"
                    class="circulation-btn-primary"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    <span>Buka Kamera &amp; Foto Tiket</span>
                </button>

                <button
                    type="button"
                    x-on:click="$refs.fileUploadInput.click()"
                    class="circulation-btn-secondary"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                    </svg>
                    <span>Pilih Gambar / Screenshot dari Galeri</span>
                </button>
            </div>

            <div
                class="circulation-dropzone circulation-desktop-hint"
                :class="dragOver ? 'is-dragover' : ''"
                x-on:click="$refs.fileUploadInput.click()"
                x-on:dragover.prevent="dragOver = true"
                x-on:dragleave.prevent="dragOver = false"
                x-on:drop.prevent="handleDrop($event)"
            >
                Atau lepas gambar di sini / tempel tangkapan layar (<strong>Ctrl + V</strong>)
            </div>

            {{-- PRATINJAU FOTO TERAKHIR --}}
            <template x-if="previewImage">
                <div class="circulation-preview-box">
                    <img :src="previewImage" alt="Foto tiket" class="circulation-preview-thumb" />
                    <div class="circulation-preview-meta">
                        <span class="circulation-preview-label">Pratinjau Gambar</span>
                        <p class="circulation-preview-status" x-text="statusText"></p>
                    </div>
                    <button
                        type="button"
                        x-on:click="$refs.nativeCameraInput.click()"
                        class="circulation-retake-btn"
                    >
                        Foto Ulang
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- TAB 2: INPUT CEPAT / SCANNER GUN / TEMPEL KODE --}}
    <div x-show="activeTab === 'manual'" class="circulation-panel-stack">
        <div class="circulation-manual-card">
            <div class="circulation-manual-row">
                <input
                    type="text"
                    x-model="manualCode"
                    x-on:keydown.enter.prevent="submitCode(manualCode)"
                    placeholder="Ketik kode tiket (contoh: RSXSD / LIB-2026-RSXSD) atau NISN"
                    class="circulation-manual-input"
                />
                <button
                    type="button"
                    x-on:click="submitCode(manualCode)"
                    class="circulation-manual-submit"
                >
                    {{ $isReturnMode ? 'Proses Pengembalian' : 'Proses Peminjaman' }}
                </button>
            </div>
            <span style="font-size: 11.5px; color: #475569;">
                Mendukung alat scanner barcode gun USB fisik (arahkan gun ke tiket lalu tekan picu).
            </span>
        </div>
    </div>

    {{-- INDIKATOR LOADING SAAT MEMPROSES --}}
    <div x-show="isProcessing" class="circulation-loading-bar">
        <div class="circulation-spinner" style="width: 18px; height: 18px; border: 2.5px solid #b45309; border-top-color: transparent; border-radius: 50%; flex-shrink: 0;"></div>
        <span x-text="statusText"></span>
    </div>

    {{-- KARTU HASIL PEMINDAIAN (SUKSES / GAGAL) --}}
    <div
        x-show="lastResult !== null"
        x-transition
        class="circulation-result-card"
        :class="lastResult?.success ? 'is-success' : ''"
    >
        <div class="circulation-result-head">
            <div class="circulation-result-icon-wrap">
                <span
                    class="circulation-result-badge"
                    x-text="lastResult?.success ? '✓' : '✕'"
                ></span>
                <div>
                    <h5 class="circulation-result-title" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal Diproses'"></h5>
                    <p class="circulation-result-msg" x-text="lastResult?.message"></p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="lastResult = null"
                class="circulation-result-close"
                title="Tutup pemberitahuan"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div class="circulation-ticket-box">
                <div class="circulation-ticket-row">
                    <span class="circulation-ticket-label">Kode Sirkulasi:</span>
                    <span class="circulation-ticket-value circulation-ticket-code" x-text="lastResult.ticket.code"></span>
                </div>
                <div class="circulation-ticket-row">
                    <span class="circulation-ticket-label">Peminjam:</span>
                    <span class="circulation-ticket-value" x-text="lastResult.ticket.borrower_name + ' (' + lastResult.ticket.borrower_class + ')'"></span>
                </div>
                <div class="circulation-ticket-row">
                    <span class="circulation-ticket-label">Buku Fisik:</span>
                    <span class="circulation-ticket-value" x-text="lastResult.ticket.book_title"></span>
                </div>
                <div class="circulation-ticket-row">
                    <span class="circulation-ticket-label">Status Terbaru:</span>
                    <span class="circulation-ticket-value circulation-ticket-status" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div class="circulation-ticket-row is-bordered">
                        <span class="circulation-ticket-label">Tenggat Pengembalian:</span>
                        <span class="circulation-ticket-value circulation-ticket-due" x-text="lastResult.ticket.due_date"></span>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
