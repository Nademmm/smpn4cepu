<style>
/* Reset & Styling Khusus Pemindai QR Sirkulasi Filament (Mandiri tanpa dependensi Tailwind) */
.qr-modal-wrapper,
.qr-modal-wrapper * {
    box-sizing: border-box;
}

.qr-modal-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 100%;
    color: #0f172a;
    font-family: inherit;
}

#circulation-camera-reader {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
    position: relative !important;
    background: #020617 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
}

#circulation-camera-reader video {
    width: 100% !important;
    height: 100% !important;
    max-height: 290px !important;
    object-fit: cover !important;
    border-radius: 14px !important;
    background: #020617 !important;
}

#circulation-camera-reader__scan_region {
    background: transparent !important;
    border: none !important;
}

#circulation-camera-reader__scan_region img,
#circulation-camera-reader__header_message,
#circulation-camera-reader__status_span,
#circulation-camera-reader__dashboard,
#circulation-camera-reader input[type="file"],
#circulation-camera-reader button {
    display: none !important;
}

@keyframes qr-laser-sweep {
    0%, 100% {
        top: 12%;
        opacity: 0.2;
    }
    50% {
        top: 85%;
        opacity: 1;
    }
}

.qr-laser-line {
    position: absolute;
    left: 8%;
    right: 8%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #10b981, #34d399, transparent);
    box-shadow: 0 0 12px #10b981;
    animation: qr-laser-sweep 2.2s ease-in-out infinite;
    pointer-events: none;
    z-index: 5;
}

@keyframes qr-spin {
    to { transform: rotate(360deg); }
}

.qr-spinner {
    width: 34px;
    height: 34px;
    border: 3px solid #10b981;
    border-top-color: transparent;
    border-radius: 50%;
    animation: qr-spin 0.8s linear infinite;
}
</style>

<div
    x-data="circulationCameraScanner()"
    class="qr-modal-wrapper"
    style="display: flex; flex-direction: column; gap: 12px; width: 100%; color: #0f172a;"
>
    {{-- Input file tersembunyi tanpa tampilan browser default --}}
    <input
        type="file"
        accept="image/*"
        capture="environment"
        x-ref="fileInputRef"
        style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
        x-on:change="scanSelectedFile($event.target.files[0])"
    />

    {{-- Bar Navigasi: Pilihan Mode Sirkulasi & Tombol Aksi Kamera --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
        <div style="display: inline-flex; align-items: center; background: #e2e8f0; padding: 3px; border-radius: 8px; gap: 2px;">
            <button
                type="button"
                x-on:click="actionMode = 'auto'"
                style="padding: 6px 14px; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s ease;"
                :style="actionMode === 'auto' ? 'background: #0f172a; color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.15);' : 'background: transparent; color: #64748b;'"
            >
                Otomatis
            </button>
            <button
                type="button"
                x-on:click="actionMode = 'hand_over'"
                style="padding: 6px 14px; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s ease;"
                :style="actionMode === 'hand_over' ? 'background: #0f172a; color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.15);' : 'background: transparent; color: #64748b;'"
            >
                Serahkan
            </button>
            <button
                type="button"
                x-on:click="actionMode = 'return'"
                style="padding: 6px 14px; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.15s ease;"
                :style="actionMode === 'return' ? 'background: #0f172a; color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.15);' : 'background: transparent; color: #64748b;'"
            >
                Terima
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <template x-if="cameras.length > 1">
                <button
                    type="button"
                    x-on:click="switchCamera()"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #1e293b; font-size: 12px; font-weight: 700; cursor: pointer; min-height: 34px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);"
                    title="Ganti kamera belakang atau depan"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>Ganti Kamera</span>
                </button>
            </template>

            <button
                type="button"
                x-on:click="$refs.fileInputRef.click()"
                style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #1e293b; font-size: 12px; font-weight: 700; cursor: pointer; min-height: 34px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);"
                title="Gunakan kamera bawaan HP untuk memfoto tiket"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" style="width: 15px; height: 15px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                </svg>
                <span>Foto QR</span>
            </button>
        </div>
    </div>

    {{-- Kotak Viewfinder Kamera --}}
    <div style="position: relative; width: 100%; max-width: 360px; height: 290px; margin: 0 auto; background: #020617; border: 2px solid #334155; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 2px 10px rgba(0,0,0,0.6);">
        {{-- Viewfinder html5-qrcode --}}
        <div id="circulation-camera-reader"></div>

        {{-- Garis laser pemindai bergerak saat streaming aktif --}}
        <div x-show="isCameraRunning && !isProcessing" class="qr-laser-line"></div>

        {{-- 4 Sudut Bidik Target Pemindai --}}
        <div x-show="isCameraRunning && !isProcessing" style="position: absolute; top: 24px; bottom: 24px; left: 24px; right: 24px; pointer-events: none; display: flex; flex-direction: column; justify-content: space-between; z-index: 4;">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 22px; height: 22px; border-top: 3px solid #10b981; border-left: 3px solid #10b981; border-top-left-radius: 8px;"></div>
                <div style="width: 22px; height: 22px; border-top: 3px solid #10b981; border-right: 3px solid #10b981; border-top-right-radius: 8px;"></div>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 22px; height: 22px; border-bottom: 3px solid #10b981; border-left: 3px solid #10b981; border-bottom-left-radius: 8px;"></div>
                <div style="width: 22px; height: 22px; border-bottom: 3px solid #10b981; border-right: 3px solid #10b981; border-bottom-right-radius: 8px;"></div>
            </div>
        </div>

        {{-- Layar Saat Kamera Sedang Dihubungkan --}}
        <div x-show="isStarting" style="position: absolute; inset: 0; background: rgba(2, 6, 23, 0.95); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; gap: 10px; z-index: 10;">
            <div class="qr-spinner"></div>
            <p style="color: #ffffff; font-size: 13px; font-weight: 700; margin: 4px 0 0 0;">Menghubungkan ke kamera...</p>
            <p style="color: #94a3b8; font-size: 11px; margin: 0; max-width: 240px;">Mohon klik "Izinkan" jika peramban menampilkan permintaan izin kamera.</p>
        </div>

        {{-- Layar Saat Kamera Belum Nyala atau Butuh Izin --}}
        <div x-show="!isCameraRunning && !isStarting && !isProcessing" style="position: absolute; inset: 0; background: rgba(2, 6, 23, 0.95); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; gap: 10px; z-index: 10;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #1e293b; display: flex; align-items: center; justify-content: center; color: #10b981; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 26px; height: 26px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h3v3h-3v-3zM18 18h2.25v2.25H18V18zM13.5 19.5h2.25v2.25H13.5V19.5zM18 13.5h2.25v2.25H18V13.5z"/>
                </svg>
            </div>

            <div>
                <h4 style="font-size: 14px; font-weight: 800; color: #ffffff; margin: 0;">Kamera Siap Digunakan</h4>
                <p style="font-size: 11.5px; color: #94a3b8; line-height: 1.45; margin: 4px 0 0 0; max-width: 270px;" x-text="permissionError || cameraNotice || 'Klik Nyalakan Kamera untuk mulai memindai atau ambil foto tiket siswa.'"></p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 250px; margin-top: 4px;">
                <button
                    type="button"
                    x-on:click="startCameraDirect()"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: #059669; color: #ffffff; border: none; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.35); min-height: 42px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    <span>Nyalakan Kamera</span>
                </button>

                <button
                    type="button"
                    x-on:click="$refs.fileInputRef.click()"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: #1e293b; color: #f1f5f9; border: 1px solid #334155; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; min-height: 42px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                    </svg>
                    <span>Foto via Kamera HP</span>
                </button>
            </div>
        </div>

        {{-- Layar Loading Saat Tiket Sedang Diproses --}}
        <div x-show="isProcessing" style="position: absolute; inset: 0; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(3px); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; color: #ffffff; z-index: 20;">
            <div class="qr-spinner"></div>
            <p style="font-size: 13px; font-weight: 700; letter-spacing: 0.02em;" x-text="'Memproses Tiket: ' + scannedCode"></p>
        </div>
    </div>

    {{-- Kartu Hasil Pemindaian --}}
    <div
        x-show="lastResult !== null"
        x-transition
        style="border-radius: 12px; padding: 12px 14px; border-width: 1px; border-style: solid; display: flex; flex-direction: column; gap: 8px;"
        :style="lastResult?.success ? 'background: #ecfdf5; border-color: #a7f3d0; color: #065f46;' : 'background: #fff1f2; border-color: #fecdd3; color: #9f1239;'"
    >
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span
                    style="width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 12px; font-weight: 800; flex-shrink: 0;"
                    :style="lastResult?.success ? 'background: #059669;' : 'background: #e11d48;'"
                    x-text="lastResult?.success ? '✓' : '✕'"
                ></span>
                <div>
                    <h5 style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; margin: 0;" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal Diproses'"></h5>
                    <p style="font-size: 12px; margin: 2px 0 0 0; font-weight: 500;" x-text="lastResult?.message"></p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="lastResult = null"
                style="background: none; border: none; color: #94a3b8; font-size: 14px; font-weight: 800; cursor: pointer; padding: 2px 6px; border-radius: 4px;"
                title="Tutup pesan"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #1e293b; display: flex; flex-direction: column; gap: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 11.5px;">Kode Sirkulasi:</span>
                    <strong style="font-family: monospace; font-weight: 800; color: #059669;" x-text="lastResult.ticket.code"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 11.5px;">Peminjam:</span>
                    <span style="font-weight: 700; text-align: right;" x-text="lastResult.ticket.borrower_name + ' (' + lastResult.ticket.borrower_class + ')'"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 11.5px;">Buku Fisik:</span>
                    <span style="font-weight: 700; text-align: right;" x-text="lastResult.ticket.book_title"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 11.5px;">Status Terbaru:</span>
                    <span style="font-weight: 800; text-transform: uppercase; color: #1f5fbf;" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 4px; margin-top: 2px;">
                        <span style="color: #64748b; font-size: 11.5px;">Tenggat Pengembalian:</span>
                        <strong style="color: #059669;" x-text="lastResult.ticket.due_date"></strong>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Input Cadangan Manual / Kode Tiket Suffix --}}
    <div style="display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
        <input
            type="text"
            x-model="manualCode"
            x-on:keydown.enter.prevent="processCode(manualCode)"
            placeholder="Ketik kode tiket (contoh: RSXSD / LIB-2026-RSXSD)"
            style="flex: 1; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 12px; background: #ffffff; color: #1e293b; outline: none; min-height: 40px; box-sizing: border-box;"
        />
        <button
            type="button"
            x-on:click="processCode(manualCode)"
            style="padding: 9px 16px; background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; min-height: 40px; transition: background 0.15s ease;"
        >
            Cari &amp; Proses
        </button>
    </div>
</div>
