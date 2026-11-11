<x-filament-widgets::widget>
    <x-filament::section>
        <div
            x-data="circulationWidgetScanner()"
            class="space-y-4 w-full max-w-full"
        >
            {{-- Input file tersembunyi untuk fallback ambil foto via kamera HP --}}
            <input
                type="file"
                accept="image/*"
                capture="environment"
                x-ref="widgetPhotoRef"
                class="hidden"
                x-on:change="scanSelectedFile($event.target.files[0])"
            />

            {{-- Header Widget Sirkulasi --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <x-filament::icon icon="heroicon-o-qr-code" class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white leading-tight">
                            Kamera Pemindai QR Code Sirkulasi
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Pindai QR Code tiket siswa untuk serah terima atau pengembalian buku fisik secara instan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        x-on:click="toggleCamera()"
                        class="flex-1 sm:flex-initial px-4 py-2.5 text-xs font-black rounded-xl shadow-xs cursor-pointer inline-flex items-center justify-center gap-2 transition-all min-h-[44px]"
                        :class="isCameraRunning ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                    >
                        <x-filament::icon icon="heroicon-o-camera" class="w-4 h-4" />
                        <span x-text="isCameraRunning ? 'Tutup Kamera' : 'Buka Kamera Belakang'"></span>
                    </button>

                    <button
                        type="button"
                        x-on:click="$refs.widgetPhotoRef.click()"
                        class="flex-1 sm:flex-initial px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 text-slate-800 dark:text-slate-200 shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5 transition-all min-h-[44px]"
                        title="Gunakan kamera bawaan HP untuk memfoto QR Code tiket"
                    >
                        <x-filament::icon icon="heroicon-o-photo" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Foto QR</span>
                    </button>
                </div>
            </div>

            {{-- Pesan Peringatan Izin Kamera / Protokol Jaringan --}}
            <div
                x-show="permissionError"
                x-transition
                class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-200 text-xs space-y-2"
            >
                <div class="flex items-start gap-2.5">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                    <p class="leading-relaxed" x-text="permissionError"></p>
                </div>
                <div class="pt-1 flex items-center gap-2">
                    <button
                        type="button"
                        x-on:click="startCamera('environment')"
                        class="px-3 py-1.5 bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold rounded-lg cursor-pointer"
                    >
                        Coba Lagi
                    </button>
                    <button
                        type="button"
                        x-on:click="$refs.widgetPhotoRef.click()"
                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg cursor-pointer"
                    >
                        Pindai via Foto HP
                    </button>
                </div>
            </div>

            {{-- Area Kamera (Muncul saat tombol Buka Kamera diklik) --}}
            <div x-show="isCameraOpen" x-transition class="space-y-4">
                {{-- Kontrol Aksi & Pilihan Kamera --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                        <span class="font-bold text-slate-700 dark:text-slate-300 shrink-0">Mode Sirkulasi:</span>
                        <select
                            x-model="actionMode"
                            class="text-xs font-semibold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-3 w-full sm:w-auto min-h-[44px]"
                        >
                            <option value="auto">Deteksi Otomatis (Serahkan jika Menunggu, Terima jika Dipinjam)</option>
                            <option value="hand_over">Khusus Serahkan Buku</option>
                            <option value="return">Khusus Terima Kembali</option>
                            <option value="view_only">Hanya Cek Data Tiket</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2" x-show="cameras.length > 1">
                        <span class="font-bold text-slate-700 dark:text-slate-300 shrink-0">Kamera:</span>
                        <select
                            x-model="selectedCameraId"
                            x-on:change="switchCamera()"
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 px-2.5 w-full sm:w-auto min-h-[44px]"
                        >
                            <template x-for="cam in cameras" :key="cam.id">
                                <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Kotak Video Kamera --}}
                <div class="relative w-full max-w-md mx-auto rounded-2xl overflow-hidden bg-slate-950 border-2 border-slate-700 shadow-lg aspect-square max-h-[380px] flex items-center justify-center">
                    <div id="widget-camera-reader" class="w-full h-full"></div>

                    {{-- Loading saat memproses tiket --}}
                    <div
                        x-show="isProcessing"
                        class="absolute inset-0 bg-slate-950/85 backdrop-blur-xs flex flex-col items-center justify-center text-white p-4 z-20 space-y-2"
                    >
                        <div class="animate-spin rounded-full h-10 w-10 border-4 border-emerald-500 border-t-transparent"></div>
                        <p class="text-xs font-bold" x-text="'Memproses Tiket: ' + scannedCode"></p>
                    </div>
                </div>

                {{-- Banner Hasil Pemindaian --}}
                <div
                    x-show="lastResult !== null"
                    x-transition
                    class="p-4 rounded-xl border shadow-xs space-y-2 max-w-md mx-auto"
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
                                <h5 class="text-xs font-extrabold uppercase tracking-wide" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal'"></h5>
                                <p class="text-xs font-medium mt-0.5" x-text="lastResult?.message"></p>
                            </div>
                        </div>
                        <button type="button" x-on:click="lastResult = null" class="text-slate-400 hover:text-slate-600 font-bold text-xs p-1">✕</button>
                    </div>

                    <template x-if="lastResult?.ticket">
                        <div class="bg-white dark:bg-slate-900 p-3 rounded-lg border border-slate-200 dark:border-slate-800 text-xs space-y-1 text-slate-800 dark:text-slate-200 mt-2">
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
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
