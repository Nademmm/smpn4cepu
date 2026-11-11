<div
    x-data="circulationCameraScanner()"
    class="space-y-4 text-slate-800 w-full max-w-full"
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

    {{-- Kontrol Navigasi Ringkas: Mode Sirkulasi & Tombol Ganti Kamera --}}
    <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl">
        <div class="inline-flex rounded-lg bg-slate-200/80 dark:bg-slate-800 p-0.5 text-xs">
            <button
                type="button"
                x-on:click="actionMode = 'auto'"
                class="px-3 py-1.5 rounded-md font-bold transition-all cursor-pointer"
                :class="actionMode === 'auto' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
                Otomatis
            </button>
            <button
                type="button"
                x-on:click="actionMode = 'hand_over'"
                class="px-3 py-1.5 rounded-md font-bold transition-all cursor-pointer"
                :class="actionMode === 'hand_over' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
                Serahkan
            </button>
            <button
                type="button"
                x-on:click="actionMode = 'return'"
                class="px-3 py-1.5 rounded-md font-bold transition-all cursor-pointer"
                :class="actionMode === 'return' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
            >
                Terima
            </button>
        </div>

        <div class="flex items-center gap-1.5">
            <template x-if="cameras.length > 1">
                <button
                    type="button"
                    x-on:click="switchCamera()"
                    class="px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 hover:bg-slate-100 cursor-pointer shadow-2xs"
                    title="Ganti kamera belakang / depan"
                >
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-3.5 h-3.5 text-slate-500" />
                    <span>Ganti Kamera</span>
                </button>
            </template>

            <button
                type="button"
                x-on:click="$refs.fileInputRef.click()"
                class="px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 hover:bg-slate-100 cursor-pointer shadow-2xs"
                title="Gunakan kamera bawaan ponsel untuk memfoto QR Code tiket"
            >
                <x-filament::icon icon="heroicon-o-camera" class="w-3.5 h-3.5 text-emerald-600" />
                <span>Foto QR</span>
            </button>
        </div>
    </div>

    {{-- Kotak Viewfinder Kamera --}}
    <div class="relative w-full rounded-2xl overflow-hidden bg-slate-950 border-2 border-slate-700 shadow-inner flex flex-col items-center justify-center min-h-[260px] aspect-square max-h-[340px] mx-auto">
        {{-- Viewfinder html5-qrcode --}}
        <div id="circulation-camera-reader" class="w-full h-full"></div>

        {{-- Garis laser pemindai saat kamera aktif --}}
        <div x-show="isCameraRunning && !isProcessing" class="scanner-laser-line"></div>

        {{-- Bingkai target 4 sudut pemindai --}}
        <div x-show="isCameraRunning && !isProcessing" class="absolute inset-8 pointer-events-none flex flex-col justify-between">
            <div class="flex justify-between">
                <div class="w-6 h-6 border-t-2 border-l-2 border-emerald-400 rounded-tl-lg"></div>
                <div class="w-6 h-6 border-t-2 border-r-2 border-emerald-400 rounded-tr-lg"></div>
            </div>
            <div class="flex justify-between">
                <div class="w-6 h-6 border-b-2 border-l-2 border-emerald-400 rounded-bl-lg"></div>
                <div class="w-6 h-6 border-b-2 border-r-2 border-emerald-400 rounded-br-lg"></div>
            </div>
        </div>

        {{-- Layar Saat Sedang Menghubungkan Kamera --}}
        <div
            x-show="isStarting"
            class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center text-slate-300 bg-slate-950/95 space-y-3 z-10"
        >
            <div class="animate-spin rounded-full h-10 w-10 border-3 border-emerald-500 border-t-transparent"></div>
            <p class="text-xs font-bold text-slate-200">Menghubungkan ke kamera...</p>
        </div>

        {{-- Layar Saat Kamera Belum Nyala atau Butuh Izin --}}
        <div
            x-show="!isCameraRunning && !isStarting && !isProcessing"
            class="absolute inset-0 flex flex-col items-center justify-center p-5 text-center text-slate-300 bg-slate-950/95 space-y-3 z-10"
        >
            <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 shadow-inner">
                <x-filament::icon icon="heroicon-o-qr-code" class="w-7 h-7 text-emerald-400" />
            </div>

            <div class="space-y-1 max-w-xs">
                <h4 class="text-sm font-black text-white">Kamera Siap Digunakan</h4>
                <p class="text-xs text-slate-400 leading-relaxed" x-text="permissionError || cameraNotice || 'Klik tombol di bawah untuk menyalakan kamera atau gunakan foto tiket.'"></p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 w-full max-w-xs pt-1">
                <button
                    type="button"
                    x-on:click="startCameraAuto()"
                    class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px]"
                >
                    <x-filament::icon icon="heroicon-o-camera" class="w-4 h-4" />
                    <span>Nyalakan Kamera</span>
                </button>

                <button
                    type="button"
                    x-on:click="$refs.fileInputRef.click()"
                    class="w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px]"
                >
                    <x-filament::icon icon="heroicon-o-photo" class="w-4 h-4 text-emerald-400" />
                    <span>Foto via Kamera HP</span>
                </button>
            </div>
        </div>

        {{-- Overlay Proses Tiket --}}
        <div
            x-show="isProcessing"
            class="absolute inset-0 bg-slate-950/90 backdrop-blur-xs flex flex-col items-center justify-center text-white p-4 z-20 space-y-2"
        >
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-emerald-500 border-t-transparent"></div>
            <p class="text-xs font-bold tracking-wide" x-text="'Memproses Tiket: ' + scannedCode"></p>
        </div>
    </div>

    {{-- Kartu Hasil Pemindaian --}}
    <div
        x-show="lastResult !== null"
        x-transition
        class="p-3.5 rounded-xl border shadow-xs space-y-2 max-w-full"
        :class="lastResult?.success ? 'bg-emerald-50 border-emerald-200 text-emerald-950 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-200' : 'bg-rose-50 border-rose-200 text-rose-950 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-200'"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2">
                <span
                    class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 font-bold text-xs text-white"
                    :class="lastResult?.success ? 'bg-emerald-600' : 'bg-rose-600'"
                    x-text="lastResult?.success ? '✓' : '✕'"
                ></span>
                <div>
                    <h5 class="text-xs font-extrabold uppercase tracking-wide" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal Diproses'"></h5>
                    <p class="text-xs font-medium mt-0.5" x-text="lastResult?.message"></p>
                </div>
            </div>

            <button
                type="button"
                x-on:click="lastResult = null"
                class="text-slate-400 hover:text-slate-600 font-bold text-xs p-1 cursor-pointer"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div class="bg-white dark:bg-slate-900 p-3 rounded-lg border border-slate-200 dark:border-slate-800 text-xs space-y-1 text-slate-800 dark:text-slate-200 mt-1">
                <div class="flex justify-between">
                    <span class="text-slate-500">Kode Sirkulasi:</span>
                    <strong class="font-mono text-emerald-700 dark:text-emerald-400" x-text="lastResult.ticket.code"></strong>
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
                    <span class="font-black uppercase text-brand" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-1 mt-1 text-emerald-700 dark:text-emerald-400 font-semibold">
                        <span>Batas Pengembalian:</span>
                        <strong x-text="lastResult.ticket.due_date"></strong>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Input Cadangan Manual / Kode Tiket Suffix --}}
    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center gap-2">
        <input
            type="text"
            x-model="manualCode"
            x-on:keydown.enter.prevent="processCode(manualCode)"
            placeholder="Atau ketik kode (contoh: RSXSD / LIB-2026-RSXSD)"
            class="text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 w-full py-2.5 px-3 shadow-2xs focus:border-emerald-500 focus:ring-emerald-500 min-h-[44px]"
        />
        <button
            type="button"
            x-on:click="processCode(manualCode)"
            class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-2xs shrink-0 cursor-pointer min-h-[44px]"
        >
            Cari &amp; Proses
        </button>
    </div>
</div>
