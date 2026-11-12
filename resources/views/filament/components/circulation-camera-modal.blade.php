<div
    x-data="circulationScanner()"
    style="display: flex; flex-direction: column; gap: 14px; width: 100%; color: #0f172a; box-sizing: border-box;"
>
    {{-- Input 1: Kamera Bawaan HP (Shutter Kamera Native Ponsel) --}}
    <input
        type="file"
        accept="image/*"
        capture="environment"
        x-ref="nativeCameraInput"
        style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
        x-on:change="scanPhotoFile($event.target.files[0])"
    />

    {{-- Input 2: Unggah Berkas Gambar / Screenshot dari Galeri atau File Picker --}}
    <input
        type="file"
        accept="image/*"
        x-ref="fileUploadInput"
        style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
        x-on:change="scanPhotoFile($event.target.files[0])"
    />

    {{-- KARTU 1: CARA TERMUDAH DI HP (KAMERA NATIVE SHUTTER) --}}
    <div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 14px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 22px; height: 22px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size: 13.5px; font-weight: 800; color: #065f46; margin: 0;">Cara 1: Foto Langsung via Kamera HP</h4>
                    <p style="font-size: 11.5px; color: #166534; margin: 2px 0 0 0;">Langsung membuka aplikasi kamera bawaan HP tanpa terkendala perizinan WebRTC.</p>
                </div>
            </div>
        </div>

        <button
            type="button"
            x-on:click="$refs.nativeCameraInput.click()"
            style="width: 100%; padding: 12px 18px; background: #059669; color: #ffffff; border: none; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.35); min-height: 46px; transition: background 0.15s ease;"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
            </svg>
            <span>Buka Kamera &amp; Foto Tiket</span>
        </button>
    </div>

    {{-- KARTU 2: UNGGAH GAMBAR / PASTE CLIPBOARD (CTRL + V) --}}
    <div
        style="border: 2px dashed #cbd5e1; border-radius: 14px; padding: 14px; background: #f8fafc; text-align: center; cursor: pointer; transition: all 0.2s ease;"
        :style="dragOver ? 'border-color: #0284c7; background: #f0f9ff;' : ''"
        x-on:click="$refs.fileUploadInput.click()"
        x-on:dragover.prevent="dragOver = true"
        x-on:dragleave.prevent="dragOver = false"
        x-on:drop.prevent="handleDrop($event)"
    >
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; color: #0284c7; margin-bottom: 4px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
            </svg>
            <span style="font-size: 13px; font-weight: 800; color: #0369a1;">Cara 2: Pilih Gambar QR atau Tempel (Ctrl + V)</span>
        </div>
        <p style="font-size: 11.5px; color: #64748b; margin: 0;">
            Klik untuk memilih berkas dari galeri, drag-and-drop, atau tekan <strong>Ctrl + V</strong> jika Anda menyalin gambar tiket dari WhatsApp / chat siswa.
        </p>
    </div>

    {{-- KARTU 3: SCANNER BARCODE GUN USB / KETIK MANUAL --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; color: #334155;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: #64748b;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h3.75m0 3.75h3.75m-3.75 0V20.25m-3.75-6.75v3.75"/>
            </svg>
            <span>Cara 3: Input Cepat / Alat Barcode Scanner USB</span>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <input
                type="text"
                x-model="manualCode"
                x-on:keydown.enter.prevent="process(manualCode)"
                placeholder="Ketik kode tiket (contoh: RSXSD / LIB-2026-RSXSD) atau NISN"
                style="flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 12.5px; background: #ffffff; color: #0f172a; outline: none; min-height: 42px; box-sizing: border-box;"
            />
            <button
                type="button"
                x-on:click="process(manualCode)"
                style="padding: 10px 18px; background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 800; cursor: pointer; white-space: nowrap; min-height: 42px; transition: background 0.15s ease;"
            >
                Proses Tiket
            </button>
        </div>
        <span style="font-size: 11px; color: #64748b;">
            Mendukung alat scanner barcode gun USB (arahkan ke tiket lalu pencet picu gun).
        </span>
    </div>

    {{-- INDIKATOR LOADING SAAT MEMPROSES --}}
    <div
        x-show="isProcessing"
        style="padding: 12px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; color: #92400e; font-size: 13px; font-weight: 800;"
    >
        <div style="width: 18px; height: 18px; border: 2.5px solid #d97706; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
        <span x-text="statusText"></span>
    </div>

    {{-- KARTU HASIL PEMINDAIAN (SUKSES / GAGAL) --}}
    <div
        x-show="lastResult !== null"
        x-transition
        style="border-radius: 12px; padding: 14px 16px; border-width: 1px; border-style: solid; display: flex; flex-direction: column; gap: 8px;"
        :style="lastResult?.success ? 'background: #ecfdf5; border-color: #a7f3d0; color: #065f46;' : 'background: #fff1f2; border-color: #fecdd3; color: #9f1239;'"
    >
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span
                    style="width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 13px; font-weight: 800; flex-shrink: 0;"
                    :style="lastResult?.success ? 'background: #059669;' : 'background: #e11d48;'"
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
                style="background: none; border: none; color: #94a3b8; font-size: 16px; font-weight: 800; cursor: pointer; padding: 2px 6px; border-radius: 4px;"
                title="Tutup pemberitahuan"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 12.5px; color: #1e293b; display: flex; flex-direction: column; gap: 5px; margin-top: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 12px;">Kode Sirkulasi:</span>
                    <strong style="font-family: monospace; font-weight: 800; color: #059669; font-size: 13px;" x-text="lastResult.ticket.code"></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 12px;">Peminjam:</span>
                    <span style="font-weight: 700; text-align: right;" x-text="lastResult.ticket.borrower_name + ' (' + lastResult.ticket.borrower_class + ')'"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 12px;">Buku Fisik:</span>
                    <span style="font-weight: 700; text-align: right;" x-text="lastResult.ticket.book_title"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: #64748b; font-size: 12px;">Status Terbaru:</span>
                    <span style="font-weight: 800; text-transform: uppercase; color: #1f5fbf;" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 5px; margin-top: 2px;">
                        <span style="color: #64748b; font-size: 12px;">Tenggat Pengembalian:</span>
                        <strong style="color: #059669;" x-text="lastResult.ticket.due_date"></strong>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
