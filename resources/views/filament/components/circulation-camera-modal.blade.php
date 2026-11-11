<div
    x-data="circulationScanner()"
    style="display: flex; flex-direction: column; gap: 14px; width: 100%; color: #0f172a; box-sizing: border-box;"
>
    {{-- Input file tersembunyi untuk fallback shutter kamera HP --}}
    <input
        type="file"
        accept="image/*"
        capture="environment"
        x-ref="photoInput"
        style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
        x-on:change="scanPhotoFile($event.target.files[0])"
    />

    {{-- Kotak Viewfinder Kamera --}}
    <div style="position: relative; width: 100%; height: 310px; background: #020617; border: 2px solid #334155; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 2px 12px rgba(0,0,0,0.7);">
        {{-- Video Element Native --}}
        <video
            autoplay
            playsinline
            muted
            style="width: 100%; height: 100%; object-fit: cover; display: block;"
        ></video>

        {{-- Canvas Tersembunyi untuk Frame Analysis --}}
        <canvas style="display: none;"></canvas>

        {{-- Garis Laser Pemindai Animasi --}}
        <div x-show="isRunning && !isProcessing" class="qr-scanner-laser"></div>

        {{-- 4 Sudut Bidik (Reticle Hijau) --}}
        <div x-show="isRunning && !isProcessing" style="position: absolute; top: 32px; bottom: 32px; left: 32px; right: 32px; pointer-events: none; display: flex; flex-direction: column; justify-content: space-between; z-index: 5;">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 26px; height: 26px; border-top: 3px solid #10b981; border-left: 3px solid #10b981; border-top-left-radius: 8px;"></div>
                <div style="width: 26px; height: 26px; border-top: 3px solid #10b981; border-right: 3px solid #10b981; border-top-right-radius: 8px;"></div>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 26px; height: 26px; border-bottom: 3px solid #10b981; border-left: 3px solid #10b981; border-bottom-left-radius: 8px;"></div>
                <div style="width: 26px; height: 26px; border-bottom: 3px solid #10b981; border-right: 3px solid #10b981; border-bottom-right-radius: 8px;"></div>
            </div>
        </div>

        {{-- Layar Fallback Saat Kamera Belum Nyala / Izin Ditolak --}}
        <div x-show="!isRunning && !isProcessing" style="position: absolute; inset: 0; background: rgba(2, 6, 23, 0.94); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; gap: 12px; z-index: 15;">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: #1e293b; display: flex; align-items: center; justify-content: center; color: #10b981; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 26px; height: 26px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                </svg>
            </div>

            <div>
                <h4 style="font-size: 14px; font-weight: 800; color: #ffffff; margin: 0;">Kamera Belum Terhubung</h4>
                <p style="font-size: 12px; color: #94a3b8; line-height: 1.45; margin: 6px 0 0 0; max-width: 280px;" x-text="errorMessage || 'Sedang menghubungkan ke kamera... Klik tombol di bawah jika ingin mencoba ulang atau mengambil foto tiket.'"></p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 240px; margin-top: 4px;">
                <button
                    type="button"
                    x-on:click="start()"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: #059669; color: #ffffff; border: none; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.35); min-height: 42px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    <span>Coba Nyalakan Ulang</span>
                </button>

                <button
                    type="button"
                    x-on:click="$refs.photoInput.click()"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: #1e293b; color: #f1f5f9; border: 1px solid #334155; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; min-height: 42px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    <span>Foto via Kamera HP</span>
                </button>
            </div>
        </div>

        {{-- Layar Loading Saat Tiket Sedang Diproses --}}
        <div x-show="isProcessing" style="position: absolute; inset: 0; background: rgba(2, 6, 23, 0.92); backdrop-filter: blur(2px); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #ffffff; z-index: 25;">
            <div class="qr-spinner-circle"></div>
            <p style="font-size: 13px; font-weight: 800; letter-spacing: 0.02em; margin: 0;" x-text="statusText"></p>
        </div>
    </div>

    {{-- Indikator Status Pemindai --}}
    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 12.5px; font-weight: 700; color: #475569; text-align: center;">
        <span
            style="width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0;"
            :style="isProcessing ? 'background: #f59e0b;' : (isRunning ? 'background: #10b981;' : 'background: #94a3b8;')"
        ></span>
        <span x-text="statusText"></span>
    </div>

    {{-- Kartu Hasil Pemindaian (Muncul Otomatis Saat Sukses atau Gagal) --}}
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
                    <p style="font-size: 12px; margin: 2px 0 0 0; font-weight: 600;" x-text="lastResult?.message"></p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="lastResult = null"
                style="background: none; border: none; color: #94a3b8; font-size: 14px; font-weight: 800; cursor: pointer; padding: 2px 6px; border-radius: 4px;"
                title="Tutup pemberitahuan"
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
            </div>
        </template>
    </div>

    {{-- Fallback: Tombol Cepat Foto Kamera HP (Berguna saat di jaringan lokal HTTP) --}}
    <div style="display: flex; justify-content: center; padding-top: 4px;">
        <button
            type="button"
            x-on:click="$refs.photoInput.click()"
            style="display: inline-flex; align-items: center; gap: 6px; background: none; border: none; font-size: 12px; font-weight: 700; color: #0284c7; cursor: pointer; text-decoration: underline;"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
            </svg>
            <span>Atau ambil foto QR via Kamera HP</span>
        </button>
    </div>
</div>
