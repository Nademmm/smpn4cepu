@php
    $resolvedActionMode = in_array(($actionMode ?? 'hand_over'), ['auto', 'hand_over', 'return', 'view_only'], true)
        ? ($actionMode ?? 'hand_over')
        : 'hand_over';
    $isReturnMode = $resolvedActionMode === 'return';
@endphp
<div
    x-data="circulationScanner(@js($resolvedActionMode))"
    x-on:close-modal.window="refreshOnClose()"
    style="display: flex; flex-direction: column; gap: 14px; width: 100%; color: #0f172a; box-sizing: border-box;"
>
    {{-- Input 1: Kamera Bawaan HP (Shutter Kamera Native Ponsel) --}}
    <input
        type="file"
        accept="image/*"
        capture="environment"
        x-ref="nativeCameraInput"
        style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
        x-on:change="scanPhotoFile($event.target.files[0], $event.target)"
    />

    {{-- Input 2: Unggah Berkas Gambar / Screenshot dari Galeri --}}
    <input
        type="file"
        accept="image/*"
        x-ref="fileUploadInput"
        style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
        x-on:change="scanPhotoFile($event.target.files[0], $event.target)"
    />

    {{-- PENANDA MODE AKTIF (TERPISAH DARI TOMBOL HALAMAN RESERVASI) --}}
    <div style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 10px; border: 1px solid {{ $isReturnMode ? '#bae6fd' : '#a7f3d0' }}; background: {{ $isReturnMode ? '#f0f9ff' : '#ecfdf5' }}; color: {{ $isReturnMode ? '#0c4a6e' : '#064e3b' }};">
        <span style="display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; background: {{ $isReturnMode ? '#0369a1' : '#047857' }}; color: #ffffff; white-space: nowrap;">
            {{ $isReturnMode ? 'Mode Pengembalian' : 'Mode Peminjaman' }}
        </span>
        <p style="font-size: 12px; font-weight: 600; margin: 2px 0 0 0; line-height: 1.45;">
            {{ $isReturnMode
                ? 'Tiket berstatus Sedang Dipinjam akan otomatis dicatat kembali ke perpustakaan dan stok fisik bertambah.'
                : 'Tiket berstatus Menunggu Pengambilan akan otomatis diserahkan ke siswa dan mulai masa pinjam.' }}
        </p>
    </div>

    {{-- NAVIGASI TAB METODE PEMINDAIAN (FOTO / MANUAL) --}}
    <div style="display: flex; gap: 6px; padding: 4px; background: #f1f5f9; border-radius: 12px; border: 1px solid #e2e8f0;">
        <button
            type="button"
            x-on:click="activeTab = 'photo'"
            style="flex: 1; padding: 8px 12px; border: none; border-radius: 9px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; transition: all 0.15s ease;"
            :style="activeTab === 'photo' ? 'background: #ffffff; color: {{ $isReturnMode ? '#0369a1' : '#047857' }}; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: #475569;'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
            </svg>
            <span>Foto Kamera / Galeri</span>
        </button>

        <button
            type="button"
            x-on:click="activeTab = 'manual'"
            style="flex: 1; padding: 8px 12px; border: none; border-radius: 9px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; transition: all 0.15s ease;"
            :style="activeTab === 'manual' ? 'background: #ffffff; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: #475569;'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
            </svg>
            <span>Ketik / Alat Barcode</span>
        </button>
    </div>

    {{-- TAB 1: AMBIL FOTO KAMERA HP / UNGGAH GALERI --}}
    <div x-show="activeTab === 'photo'" style="display: flex; flex-direction: column; gap: 10px;">
        <div style="background: {{ $isReturnMode ? '#f0f9ff' : '#f0fdf4' }}; border: 2px solid {{ $isReturnMode ? '#7dd3fc' : '#86efac' }}; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $isReturnMode ? '#0369a1' : '#047857' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 800; color: {{ $isReturnMode ? '#0c4a6e' : '#064e3b' }}; margin: 0;">
                        Pindai QR Code via Foto / Gambar
                    </h4>
                    <p style="font-size: 12px; color: {{ $isReturnMode ? '#075985' : '#065f46' }}; margin: 3px 0 0 0; line-height: 1.4;">
                        Ambil foto QR tiket dengan kamera ponsel atau pilih gambar/tangkapan layar dari galeri.
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button
                    type="button"
                    x-on:click="$refs.nativeCameraInput.click()"
                    style="width: 100%; padding: 13px 20px; background: {{ $isReturnMode ? '#0369a1' : '#047857' }}; color: #ffffff; border: none; border-radius: 11px; font-size: 13.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    <span>Buka Kamera &amp; Foto Tiket</span>
                </button>

                <button
                    type="button"
                    x-on:click="$refs.fileUploadInput.click()"
                    style="width: 100%; padding: 9px 14px; background: #ffffff; color: {{ $isReturnMode ? '#0369a1' : '#047857' }}; border: 1px solid {{ $isReturnMode ? '#7dd3fc' : '#86efac' }}; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                    </svg>
                    <span>Pilih Gambar / Screenshot dari Galeri</span>
                </button>
            </div>

            <div
                style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 11px 12px; background: #ffffff; text-align: center; cursor: pointer;"
                :style="dragOver ? 'border-color: #0369a1; background: #f0f9ff;' : ''"
                x-on:click="$refs.fileUploadInput.click()"
                x-on:dragover.prevent="dragOver = true"
                x-on:dragleave.prevent="dragOver = false"
                x-on:drop.prevent="handleDrop($event)"
            >
                <span style="font-size: 12px; font-weight: 700; color: #334155;">
                    Atau lepas gambar di sini / tempel tangkapan layar (<strong>Ctrl + V</strong>)
                </span>
            </div>

            {{-- PRATINJAU FOTO TERAKHIR --}}
            <template x-if="previewImage">
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px; display: flex; align-items: center; gap: 12px;">
                    <img :src="previewImage" alt="Foto tiket" style="width: 54px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;" />
                    <div style="flex: 1; min-width: 0;">
                        <span style="font-size: 11px; font-weight: 800; color: {{ $isReturnMode ? '#0369a1' : '#047857' }}; text-transform: uppercase;">Pratinjau Gambar</span>
                        <p style="font-size: 11.5px; color: #334155; margin: 2px 0 0 0; overflow-wrap: anywhere;" x-text="statusText"></p>
                    </div>
                    <button
                        type="button"
                        x-on:click="$refs.nativeCameraInput.click()"
                        style="padding: 8px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; color: #0f172a; font-size: 11.5px; font-weight: 800; cursor: pointer; min-height: 44px; white-space: nowrap;"
                    >
                        Foto Ulang
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- TAB 2: INPUT CEPAT / SCANNER GUN / TEMPEL KODE --}}
    <div x-show="activeTab === 'manual'" style="display: flex; flex-direction: column; gap: 10px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                <input
                    type="text"
                    x-model="manualCode"
                    x-on:keydown.enter.prevent="submitCode(manualCode)"
                    placeholder="Ketik kode tiket (contoh: RSXSD / LIB-2026-RSXSD) atau NISN"
                    style="flex: 1; min-width: 190px; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 12.5px; background: #ffffff; color: #0f172a; outline: none; min-height: 44px; box-sizing: border-box;"
                />
                <button
                    type="button"
                    x-on:click="submitCode(manualCode)"
                    style="padding: 10px 18px; background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 800; cursor: pointer; white-space: nowrap; min-height: 44px;"
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
    <div
        x-show="isProcessing"
        style="padding: 12px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; color: #78350f; font-size: 13px; font-weight: 800;"
    >
        <div class="circulation-spinner" style="width: 18px; height: 18px; border: 2.5px solid #b45309; border-top-color: transparent; border-radius: 50%; flex-shrink: 0;"></div>
        <span x-text="statusText"></span>
    </div>

    {{-- KARTU HASIL PEMINDAIAN (SUKSES / GAGAL) --}}
    <div
        x-show="lastResult !== null"
        x-transition
        style="border-radius: 12px; padding: 14px 16px; border-width: 1px; border-style: solid; display: flex; flex-direction: column; gap: 8px;"
        :style="lastResult?.success ? 'background: #ecfdf5; border-color: #a7f3d0; color: #064e3b;' : 'background: #fff1f2; border-color: #fecdd3; color: #881337;'"
    >
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span
                    style="width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 13px; font-weight: 800; flex-shrink: 0;"
                    :style="lastResult?.success ? 'background: #047857;' : 'background: #be123c;'"
                    x-text="lastResult?.success ? '✓' : '✕'"
                ></span>
                <div>
                    <h5 style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; margin: 0;" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal Diproses'"></h5>
                    <p style="font-size: 12.5px; margin: 2px 0 0 0; font-weight: 600;" x-text="lastResult?.message"></p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="lastResult = null"
                style="background: none; border: none; color: #475569; font-size: 16px; font-weight: 800; cursor: pointer; min-width: 44px; min-height: 44px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;"
                title="Tutup pemberitahuan"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 12.5px; color: #1e293b; display: flex; flex-direction: column; gap: 5px; margin-top: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #475569; font-size: 12px;">Kode Sirkulasi:</span>
                    <strong style="font-family: monospace; font-weight: 800; color: #047857; font-size: 13px;" x-text="lastResult.ticket.code"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #475569; font-size: 12px;">Peminjam:</span>
                    <span style="font-weight: 700; text-align: right;" x-text="lastResult.ticket.borrower_name + ' (' + lastResult.ticket.borrower_class + ')'"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #475569; font-size: 12px;">Buku Fisik:</span>
                    <span style="font-weight: 700; text-align: right;" x-text="lastResult.ticket.book_title"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #475569; font-size: 12px;">Status Terbaru:</span>
                    <span style="font-weight: 800; text-transform: uppercase; color: #0369a1;" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 5px; margin-top: 2px;">
                        <span style="color: #475569; font-size: 12px;">Tenggat Pengembalian:</span>
                        <strong style="color: #047857;" x-text="lastResult.ticket.due_date"></strong>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
