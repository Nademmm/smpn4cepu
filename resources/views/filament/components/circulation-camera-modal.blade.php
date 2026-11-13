<div
    x-data="circulationScanner()"
    x-on:close-modal.window="stopLiveCamera()"
    x-on:keydown.escape.window="stopLiveCamera()"
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

    {{-- PILIHAN MODE SIRKULASI --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
        <label for="circulation-action-mode" style="font-size: 12px; font-weight: 800; color: #334155; white-space: nowrap;">
            Mode Aksi:
        </label>
        <select
            id="circulation-action-mode"
            x-model="actionMode"
            style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; color: #0f172a; font-size: 12px; font-weight: 700;"
        >
            <option value="auto">Otomatis (Serahkan jika Menunggu / Terima jika Dipinjam)</option>
            <option value="hand_over">Khusus Serahkan Buku</option>
            <option value="return">Khusus Terima Kembali</option>
            <option value="view_only">Hanya Cek Info Tiket</option>
        </select>
    </div>

    {{-- NAVIGASI TAB METODE PEMINDAIAN --}}
    <div style="display: flex; gap: 6px; padding: 4px; background: #f1f5f9; border-radius: 12px; border: 1px solid #e2e8f0;">
        <button
            type="button"
            x-on:click="activeTab = 'live'; startLiveCamera();"
            style="flex: 1; padding: 8px 12px; border: none; border-radius: 9px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s ease;"
            :style="activeTab === 'live' ? 'background: #ffffff; color: #0284c7; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: #64748b;'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <span>Video Live Scanner</span>
        </button>

        <button
            type="button"
            x-on:click="activeTab = 'photo'; stopLiveCamera();"
            style="flex: 1; padding: 8px 12px; border: none; border-radius: 9px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s ease;"
            :style="activeTab === 'photo' ? 'background: #ffffff; color: #059669; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: #64748b;'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
            </svg>
            <span>Foto Kamera / Galeri</span>
        </button>

        <button
            type="button"
            x-on:click="activeTab = 'manual'; stopLiveCamera();"
            style="flex: 1; padding: 8px 12px; border: none; border-radius: 9px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s ease;"
            :style="activeTab === 'manual' ? 'background: #ffffff; color: #334155; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'background: transparent; color: #64748b;'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
            </svg>
            <span>Ketik / Alat Barcode</span>
        </button>
    </div>

    {{-- TAB 1: LIVE STREAM VIDEO KAMERA LANGSUNG --}}
    <div x-show="activeTab === 'live'" style="display: flex; flex-direction: column; gap: 10px;">
        <template x-if="availableCameras.length > 1 && isLiveActive">
            <div style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                <span style="font-size: 11.5px; font-weight: 800; color: #334155; white-space: nowrap;">Pilih Kamera:</span>
                <select
                    x-model="selectedCameraId"
                    x-on:change="switchLiveCamera()"
                    style="flex: 1; padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; color: #0f172a; font-size: 12px;"
                >
                    <template x-for="(cam, idx) in availableCameras" :key="cam.deviceId || idx">
                        <option :value="cam.deviceId" x-text="cam.label || ('Kamera ' + (idx + 1))"></option>
                    </template>
                </select>
            </div>
        </template>

        <div style="background: #0f172a; border-radius: 14px; padding: 12px; color: #ffffff; display: flex; flex-direction: column; align-items: center; position: relative; overflow: hidden; min-height: 240px; justify-content: center;">
            {{-- Elemen video selalu berada di DOM agar $refs.liveVideoEl langsung siap saat izin kamera diberikan --}}
            <div x-show="isLiveActive" style="width: 100%; position: relative; border-radius: 10px; overflow: hidden; background: #020617;">
                <video
                    x-ref="liveVideoEl"
                    playsinline
                    muted
                    autoplay
                    style="width: 100%; max-height: 300px; min-height: 210px; object-fit: cover; border-radius: 10px; display: block;"
                ></video>
                <div class="qr-laser-line"></div>
                <div style="position: absolute; bottom: 8px; left: 0; right: 0; text-align: center;">
                    <span style="padding: 4px 12px; background: rgba(0,0,0,0.75); color: #86efac; font-size: 11px; font-weight: 800; border-radius: 20px;">
                        Arahkan QR Code tiket ke dalam kotak kamera
                    </span>
                </div>
            </div>

            <div x-show="isLiveStarting && !isLiveActive" style="text-align: center; padding: 24px 12px;">
                <div class="circulation-spinner" style="width: 28px; height: 28px; border: 3px solid #38bdf8; border-top-color: transparent; border-radius: 50%; margin: 0 auto 12px auto;"></div>
                <p style="font-size: 13px; font-weight: 700; margin: 0;">Menghubungkan ke kamera perangkat...</p>
                <p style="font-size: 11.5px; color: #94a3b8; margin: 4px 0 0 0;">Pilih "Izinkan / Allow" apabila browser meminta izin kamera.</p>
            </div>

            <div x-show="!isLiveActive && !isLiveStarting && !liveError" style="text-align: center; padding: 20px 10px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 38px; height: 38px; color: #38bdf8; margin: 0 auto 10px auto;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                <p style="font-size: 13px; font-weight: 700; margin: 0 0 6px 0;">Kamera Video Langsung (Webcam / HP)</p>
                <p style="font-size: 11.5px; color: #94a3b8; margin: 0 0 14px 0; max-width: 340px;">
                    Pindai QR Code secara real-time tanpa perlu menekan tombol foto.
                </p>
                <button
                    type="button"
                    x-on:click="startLiveCamera()"
                    style="padding: 10px 18px; background: #0284c7; color: #ffffff; border: none; border-radius: 9px; font-size: 12.5px; font-weight: 800; cursor: pointer; min-height: 44px;"
                >
                    Nyalakan Kamera Video Live
                </button>
            </div>

            <div x-show="liveError && !isLiveActive && !isLiveStarting" style="background: rgba(239, 68, 68, 0.18); border: 1px solid #f87171; border-radius: 10px; padding: 14px; text-align: center; width: 100%; box-sizing: border-box;">
                <p style="font-size: 12px; color: #fecaca; margin: 0 0 12px 0; line-height: 1.5;" x-text="liveError"></p>
                <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                    <button
                        type="button"
                        x-on:click="startLiveCamera()"
                        style="padding: 8px 14px; background: #0284c7; color: #ffffff; border: none; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; min-height: 40px;"
                    >
                        Coba Nyalakan Lagi
                    </button>
                    <button
                        type="button"
                        x-on:click="activeTab = 'photo'"
                        style="padding: 8px 14px; background: #059669; color: #ffffff; border: none; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; min-height: 40px;"
                    >
                        Pakai Foto Kamera / Galeri
                    </button>
                </div>
            </div>
        </div>

        <div x-show="isLiveActive" style="display: flex; gap: 8px;">
            <button
                type="button"
                x-on:click="stopLiveCamera()"
                style="flex: 1; padding: 9px 14px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 9px; color: #b91c1c; font-size: 12px; font-weight: 800; cursor: pointer; min-height: 42px;"
            >
                Matikan Kamera Video
            </button>
        </div>
    </div>

    {{-- TAB 2: AMBIL FOTO KAMERA HP / UNGGAH GALERI --}}
    <div x-show="activeTab === 'photo'" style="display: flex; flex-direction: column; gap: 10px;">
        <div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.35);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 style="font-size: 14px; font-weight: 800; color: #065f46; margin: 0;">Pindai QR Code via Foto / Gambar</h4>
                    <p style="font-size: 12px; color: #166534; margin: 3px 0 0 0; line-height: 1.4;">
                        Ambil foto QR tiket dengan kamera ponsel atau pilih tangkapan layar tiket dari galeri.
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <button
                    type="button"
                    x-on:click="$refs.nativeCameraInput.click()"
                    style="width: 100%; padding: 13px 20px; background: #059669; color: #ffffff; border: none; border-radius: 11px; font-size: 13.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 3px 10px rgba(5, 150, 105, 0.4); min-height: 48px;"
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
                    style="width: 100%; padding: 9px 14px; background: #ffffff; color: #047857; border: 1px solid #a7f3d0; border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px;"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                    </svg>
                    <span>Pilih Gambar / Screenshot dari Galeri</span>
                </button>
            </div>

            {{-- PRATINJAU FOTO TERAKHIR --}}
            <template x-if="previewImage">
                <div style="background: #ffffff; border: 1px solid #d1fae5; border-radius: 10px; padding: 10px; display: flex; align-items: center; gap: 12px;">
                    <img :src="previewImage" alt="Foto tiket" style="width: 54px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;" />
                    <div style="flex: 1;">
                        <span style="font-size: 11px; font-weight: 800; color: #047857; text-transform: uppercase;">Pratinjau Gambar</span>
                        <p style="font-size: 11.5px; color: #475569; margin: 2px 0 0 0;" x-text="statusText"></p>
                    </div>
                    <button
                        type="button"
                        x-on:click="$refs.fileUploadInput.click()"
                        style="padding: 6px 10px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 7px; color: #059669; font-size: 11px; font-weight: 800; cursor: pointer;"
                    >
                        Ganti Gambar
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- TAB 3: INPUT CEPAT / SCANNER GUN / TEMPEL (CTRL + V) --}}
    <div x-show="activeTab === 'manual'" style="display: flex; flex-direction: column; gap: 10px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <input
                    type="text"
                    x-model="manualCode"
                    x-on:keydown.enter.prevent="submitCode(manualCode)"
                    placeholder="Ketik kode tiket (contoh: RSXSD / LIB-2026-RSXSD) atau NISN"
                    style="flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 12.5px; background: #ffffff; color: #0f172a; outline: none; min-height: 44px; box-sizing: border-box;"
                />
                <button
                    type="button"
                    x-on:click="submitCode(manualCode)"
                    style="padding: 10px 18px; background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 800; cursor: pointer; white-space: nowrap; min-height: 44px;"
                >
                    Proses Tiket
                </button>
            </div>
            <span style="font-size: 11px; color: #64748b;">
                Mendukung alat scanner barcode gun USB fisik (arahkan gun ke tiket lalu tekan picu).
            </span>
        </div>

        <div
            style="border: 2px dashed #cbd5e1; border-radius: 14px; padding: 14px; background: #f8fafc; text-align: center; cursor: pointer;"
            :style="dragOver ? 'border-color: #0284c7; background: #f0f9ff;' : ''"
            x-on:click="$refs.fileUploadInput.click()"
            x-on:dragover.prevent="dragOver = true"
            x-on:dragleave.prevent="dragOver = false"
            x-on:drop.prevent="handleDrop($event)"
        >
            <span style="font-size: 12.5px; font-weight: 800; color: #0369a1; display: block; margin-bottom: 3px;">
                Tempel Gambar (Ctrl + V) atau Lepas Berkas di Sini
            </span>
            <p style="font-size: 11.5px; color: #64748b; margin: 0;">
                Klik untuk memilih berkas dari komputer / galeri atau tekan <strong>Ctrl + V</strong> jika Anda menyalin gambar tiket.
            </p>
        </div>
    </div>

    {{-- INDIKATOR LOADING SAAT MEMPROSES --}}
    <div
        x-show="isProcessing"
        style="padding: 12px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; color: #92400e; font-size: 13px; font-weight: 800;"
    >
        <div class="circulation-spinner" style="width: 18px; height: 18px; border: 2.5px solid #d97706; border-top-color: transparent; border-radius: 50%;"></div>
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

        <template x-if="activeTab === 'live' && !isLiveActive">
            <button
                type="button"
                x-on:click="lastResult = null; startLiveCamera();"
                style="margin-top: 4px; padding: 10px 14px; background: #0284c7; color: #ffffff; border: none; border-radius: 9px; font-size: 12.5px; font-weight: 800; cursor: pointer; min-height: 44px;"
            >
                Pindai Tiket Berikutnya (Nyalakan Kamera)
            </button>
        </template>
    </div>
</div>

