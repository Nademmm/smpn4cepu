<div
    x-data="circulationCameraScanner()"
    class="space-y-4 text-slate-800 w-full max-w-full"
>
    {{-- Input file tersembunyi untuk fallback ambil foto via kamera bawaan HP --}}
    <input
        type="file"
        accept="image/*"
        capture="environment"
        x-ref="fileInputRef"
        class="hidden"
        x-on:change="scanSelectedFile($event.target.files[0])"
    />

    {{-- Bar Navigasi Opsi Tindakan & Pemilihan Kamera --}}
    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2.5">
        <div>
            <label for="scanner-action-mode" class="text-xs font-bold text-slate-700 block mb-1">
                Tindakan Sirkulasi:
            </label>
            <select
                id="scanner-action-mode"
                x-model="actionMode"
                class="text-xs font-semibold rounded-lg border-slate-300 bg-white py-2 px-3 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 w-full min-h-[44px]"
            >
                <option value="auto">Deteksi Otomatis (Serahkan jika menunggu, Terima jika dipinjam)</option>
                <option value="hand_over">Khusus Serahkan Buku ke Siswa</option>
                <option value="return">Khusus Terima Pengembalian Buku</option>
                <option value="view_only">Hanya Cek Data &amp; Status Tiket</option>
            </select>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
            <template x-if="cameras.length > 1">
                <select
                    x-model="selectedCameraId"
                    x-on:change="switchCamera()"
                    class="text-xs rounded-lg border-slate-300 bg-white py-2 px-2.5 shadow-xs w-full sm:w-auto min-h-[44px]"
                >
                    <template x-for="cam in cameras" :key="cam.id">
                        <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                    </template>
                </select>
            </template>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button
                    type="button"
                    x-on:click="toggleCamera()"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-bold rounded-xl shadow-xs cursor-pointer transition-colors min-h-[44px]"
                    :class="isCameraRunning ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                >
                    <x-filament::icon icon="heroicon-o-camera" class="w-4 h-4" />
                    <span x-text="isCameraRunning ? 'Matikan Kamera' : 'Buka Kamera Belakang'"></span>
                </button>

                <button
                    type="button"
                    x-on:click="$refs.fileInputRef.click()"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 shadow-xs cursor-pointer transition-colors min-h-[44px]"
                    title="Gunakan kamera bawaan HP untuk memfoto QR Code tiket"
                >
                    <x-filament::icon icon="heroicon-o-photo" class="w-4 h-4 text-emerald-600" />
                    <span>Ambil Foto QR</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Notifikasi Error / Konteks Akses Kamera --}}
    <div
        x-show="permissionError"
        x-transition
        class="p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 text-xs space-y-2"
    >
        <div class="flex items-start gap-2.5">
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
            <div class="space-y-1">
                <strong class="font-extrabold block">Perhatian Akses Kamera:</strong>
                <p class="leading-relaxed" x-text="permissionError"></p>
            </div>
        </div>
        <div class="pt-1 flex items-center gap-2">
            <button
                type="button"
                x-on:click="startCamera('environment')"
                class="px-3 py-1.5 bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold rounded-lg cursor-pointer"
            >
                Coba Buka Kamera Lagi
            </button>
            <button
                type="button"
                x-on:click="$refs.fileInputRef.click()"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg cursor-pointer"
            >
                Buka Kamera Lewat Foto
            </button>
        </div>
    </div>

    {{-- Jendela Kamera Pemindai QR Code --}}
    <div class="relative w-full max-w-full rounded-2xl overflow-hidden bg-slate-950 border-2 border-slate-700 shadow-inner flex flex-col items-center justify-center min-h-[260px] aspect-square max-h-[360px]">
        {{-- Viewfinder html5-qrcode --}}
        <div id="circulation-camera-reader" class="w-full h-full"></div>

        {{-- Layar Placeholder saat Kamera Mati --}}
        <div
            x-show="!isCameraRunning && !isProcessing"
            class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-slate-300 bg-slate-950/95 space-y-3 z-10"
        >
            <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                <x-filament::icon icon="heroicon-o-qr-code" class="w-8 h-8 text-emerald-400" />
            </div>
            <div>
                <h4 class="text-sm font-bold text-white">Kamera Belum Aktif</h4>
                <p class="text-xs text-slate-400 max-w-xs mt-1 leading-relaxed">
                    Klik tombol di bawah untuk meminta izin dan menyalakan kamera belakang HP atau webcam laptop Anda.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full max-w-xs">
                <button
                    type="button"
                    x-on:click="startCamera('environment')"
                    class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px]"
                >
                    <x-filament::icon icon="heroicon-o-camera" class="w-4 h-4" />
                    <span>Izinkan &amp; Buka Kamera Belakang</span>
                </button>
                <button
                    type="button"
                    x-on:click="$refs.fileInputRef.click()"
                    class="w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl shadow-xs cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px]"
                >
                    <x-filament::icon icon="heroicon-o-photo" class="w-4 h-4 text-emerald-400" />
                    <span>Ambil Foto QR (Kamera HP)</span>
                </button>
            </div>
        </div>

        {{-- Loading / Processing Overlay saat Kode Ditemukan --}}
        <div
            x-show="isProcessing"
            class="absolute inset-0 bg-slate-950/85 backdrop-blur-xs flex flex-col items-center justify-center text-white p-4 z-20 space-y-2"
        >
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-emerald-500 border-t-transparent"></div>
            <p class="text-xs font-bold" x-text="'Memproses Tiket: ' + scannedCode"></p>
        </div>
    </div>

    {{-- Kartu Hasil Pemindaian Terakhir --}}
    <div
        x-show="lastResult !== null"
        x-transition
        class="p-4 rounded-xl border shadow-xs space-y-2.5 max-w-full"
        :class="lastResult?.success ? 'bg-emerald-50/90 border-emerald-200 text-emerald-950' : 'bg-rose-50/90 border-rose-200 text-rose-950'"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2">
                <template x-if="lastResult?.success">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 font-bold text-xs">✓</span>
                </template>
                <template x-if="!lastResult?.success">
                    <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold text-xs">✕</span>
                </template>
                <div>
                    <h5 class="text-xs font-extrabold uppercase tracking-wide" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal Diproses'"></h5>
                    <p class="text-xs font-medium mt-0.5" x-text="lastResult?.message"></p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="lastResult = null"
                class="text-slate-400 hover:text-slate-600 text-xs font-bold p-1 cursor-pointer"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div class="bg-white/95 p-3 rounded-lg border border-slate-200 text-xs space-y-1 text-slate-800">
                <div class="flex justify-between">
                    <span class="text-slate-500">Kode Sirkulasi:</span>
                    <strong class="font-mono text-emerald-700" x-text="lastResult.ticket.code"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Peminjam:</span>
                    <strong x-text="lastResult.ticket.borrower_name + ' (' + lastResult.ticket.borrower_class + ')'"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Buku Fisik:</span>
                    <strong class="text-right" x-text="lastResult.ticket.book_title"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status Terbaru:</span>
                    <span class="font-bold uppercase text-brand" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div class="flex justify-between border-t border-slate-100 pt-1 mt-1 text-emerald-700 font-semibold">
                        <span>Batas Pengembalian:</span>
                        <strong x-text="lastResult.ticket.due_date"></strong>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Fallback: Input Manual / Scanner USB --}}
    <div class="pt-2 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
        <input
            type="text"
            x-model="manualCode"
            x-on:keydown.enter.prevent="processCode(manualCode)"
            placeholder="Ketik kode tiket jika kamera bermasalah (LIB-2026-...)"
            class="text-xs rounded-lg border-slate-300 w-full py-2.5 px-3 shadow-2xs focus:border-emerald-500 focus:ring-emerald-500 min-h-[44px]"
        />
        <button
            type="button"
            x-on:click="processCode(manualCode)"
            class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-lg shadow-2xs shrink-0 cursor-pointer min-h-[44px]"
        >
            Proses
        </button>
    </div>
</div>
