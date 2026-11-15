<x-filament-widgets::widget>
    <x-filament::section>
        <div
            x-data="circulationWidgetScanner()"
            class="space-y-4 w-full max-w-full"
        >
            <input
                type="file"
                accept="image/*"
                capture="environment"
                x-ref="widgetPhotoRef"
                style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
                x-on:change="scanSelectedFile($event.target.files[0], $event.target)"
            />

            <input
                type="file"
                accept="image/*"
                x-ref="widgetGalleryRef"
                style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
                x-on:change="scanSelectedFile($event.target.files[0], $event.target)"
            />

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <x-filament::icon icon="heroicon-o-qr-code" class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white leading-tight">
                            Pemindai Foto QR Code Sirkulasi
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                            Foto QR tiket siswa atau pilih gambar untuk serah terima maupun pengembalian buku fisik.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        x-on:click="actionMode = 'hand_over'; $refs.widgetPhotoRef.click()"
                        class="flex-1 sm:flex-initial px-4 py-2.5 text-xs font-black rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5 transition-all min-h-[44px]"
                    >
                        <x-filament::icon icon="heroicon-o-camera" class="w-4 h-4" />
                        <span>Scan Pinjam (Foto)</span>
                    </button>

                    <button
                        type="button"
                        x-on:click="actionMode = 'return'; $refs.widgetPhotoRef.click()"
                        class="flex-1 sm:flex-initial px-4 py-2.5 text-xs font-black rounded-xl bg-sky-700 hover:bg-sky-800 text-white shadow-xs cursor-pointer inline-flex items-center justify-center gap-1.5 transition-all min-h-[44px]"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="w-4 h-4" />
                        <span>Scan Kembali (Foto)</span>
                    </button>
                </div>
            </div>

            <div
                x-show="isProcessing"
                class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-bold flex items-center justify-center gap-2"
            >
                <div class="circulation-spinner w-4 h-4 rounded-full border-2 border-amber-700 border-t-transparent"></div>
                <span x-text="scannedCode || 'Memproses foto QR Code...'"></span>
            </div>

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
                            :class="lastResult?.success ? 'bg-emerald-700' : 'bg-rose-700'"
                            x-text="lastResult?.success ? '✓' : '✕'"
                        ></span>
                        <div>
                            <h5 class="text-xs font-extrabold uppercase tracking-wide" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal'"></h5>
                            <p class="text-xs font-medium mt-0.5" x-text="lastResult?.message"></p>
                        </div>
                    </div>
                    <button type="button" x-on:click="lastResult = null" class="text-slate-500 hover:text-slate-700 font-bold text-xs min-w-[44px] min-h-[44px]">✕</button>
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
                            <span class="font-black uppercase text-sky-700" x-text="lastResult.ticket.status_label"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
