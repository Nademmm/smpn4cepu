<x-layouts.app :title="'Pemindai QR Code Sirkulasi - Perpustakaan SMPN 4 Cepu'" :metaDescription="'Pemindai kamera QR Code sirkulasi peminjaman dan pengembalian buku fisik perpustakaan SMP Negeri 4 Cepu.'">
    <div class="max-w-xl mx-auto px-4 py-6 sm:py-10">
        {{-- Tombol Navigasi Kembali --}}
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('filament.admin.resources.book-reservations.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-mute hover:text-brand transition-colors">
                <x-app-icon name="arrow-left" class="w-4 h-4" />
                <span>Kembali ke Antrean Reservasi</span>
            </a>
            <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                Mode Sirkulasi Aktif
            </span>
        </div>

        {{-- Kartu Utama Scanner --}}
        <div
            x-data="circulationNativeScanner()"
            class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-4"
        >
            {{-- Header Judul --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h1 class="text-base sm:text-lg font-black text-navy leading-tight">
                        Pemindai QR Code Sirkulasi
                    </h1>
                    <p class="text-xs text-ink-mute mt-0.5">
                        Arahkan kamera ke QR Code tiket peminjaman siswa untuk serah terima atau pengembalian buku fisik.
                    </p>
                </div>
            </div>

            {{-- Input File Tersembunyi untuk Ambil Foto --}}
            <input
                type="file"
                accept="image/*"
                capture="environment"
                x-ref="pagePhotoRef"
                style="display: none !important; position: absolute; left: -9999px; width: 1px; height: 1px;"
                x-on:change="scanSelectedFile($event.target.files[0])"
            />

            {{-- Bar Mode Sirkulasi & Alat --}}
            <div class="flex flex-wrap items-center justify-between gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl">
                <div class="inline-flex bg-slate-200 p-0.5 rounded-lg text-xs">
                    <button
                        type="button"
                        x-on:click="actionMode = 'auto'"
                        class="px-3 py-1.5 rounded-md font-bold transition-all cursor-pointer"
                        :class="actionMode === 'auto' ? 'bg-navy text-white shadow-xs' : 'text-slate-600 hover:text-navy'"
                    >
                        Otomatis
                    </button>
                    <button
                        type="button"
                        x-on:click="actionMode = 'hand_over'"
                        class="px-3 py-1.5 rounded-md font-bold transition-all cursor-pointer"
                        :class="actionMode === 'hand_over' ? 'bg-navy text-white shadow-xs' : 'text-slate-600 hover:text-navy'"
                    >
                        Serahkan
                    </button>
                    <button
                        type="button"
                        x-on:click="actionMode = 'return'"
                        class="px-3 py-1.5 rounded-md font-bold transition-all cursor-pointer"
                        :class="actionMode === 'return' ? 'bg-navy text-white shadow-xs' : 'text-slate-600 hover:text-navy'"
                    >
                        Terima
                    </button>
                </div>

                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        x-on:click="flipCamera()"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-navy text-xs font-bold inline-flex items-center gap-1 hover:bg-slate-100 cursor-pointer shadow-2xs"
                        title="Balik kamera depan / belakang"
                    >
                        <x-app-icon name="arrow-path" class="w-3.5 h-3.5 text-brand" />
                        <span>Balik Kamera</span>
                    </button>

                    <button
                        type="button"
                        x-on:click="$refs.pagePhotoRef.click()"
                        class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-navy text-xs font-bold inline-flex items-center gap-1 hover:bg-slate-100 cursor-pointer shadow-2xs"
                        title="Foto tiket menggunakan kamera HP"
                    >
                        <x-app-icon name="camera" class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Foto QR</span>
                    </button>
                </div>
            </div>

            {{-- Viewfinder Kamera Video Native --}}
            <div class="relative w-full aspect-square max-h-[380px] mx-auto rounded-2xl overflow-hidden bg-slate-950 border-2 border-slate-800 shadow-inner flex items-center justify-center">
                {{-- Video Stream Langsung --}}
                <video
                    id="native-scanner-video"
                    class="scanner-video-target w-full h-full object-cover"
                    autoplay
                    playsinline
                    muted
                ></video>

                {{-- Canvas Pemroses Frame (Tersembunyi) --}}
                <canvas id="native-scanner-canvas" class="scanner-canvas-target hidden" style="display: none;"></canvas>

                {{-- Garis Laser Pemindai Bergerak --}}
                <div x-show="isCameraRunning && !isProcessing" class="absolute left-6 right-6 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_12px_#10B981] animate-pulse pointer-events-none z-10" style="top: 50%;"></div>

                {{-- Bingkai Sudut Target --}}
                <div x-show="isCameraRunning && !isProcessing" class="absolute inset-8 pointer-events-none flex flex-col justify-between z-10">
                    <div class="flex justify-between">
                        <div class="w-7 h-7 border-t-3 border-l-3 border-emerald-400 rounded-tl-lg"></div>
                        <div class="w-7 h-7 border-t-3 border-r-3 border-emerald-400 rounded-tr-lg"></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="w-7 h-7 border-b-3 border-l-3 border-emerald-400 rounded-bl-lg"></div>
                        <div class="w-7 h-7 border-b-3 border-r-3 border-emerald-400 rounded-br-lg"></div>
                    </div>
                </div>

                {{-- Layar Saat Kamera Belum Nyala / Izin Ditolak --}}
                <div
                    x-show="!isCameraRunning && !isStarting && !isProcessing"
                    class="absolute inset-0 bg-slate-950/95 flex flex-col items-center justify-center p-6 text-center text-white space-y-3 z-20"
                >
                    <div class="w-14 h-14 rounded-full bg-slate-800 flex items-center justify-center text-emerald-400 shadow-inner">
                        <x-app-icon name="camera" class="w-7 h-7" />
                    </div>
                    <div class="max-w-xs space-y-1">
                        <h4 class="text-sm font-black text-white">Kamera Belum Aktif</h4>
                        <p class="text-xs text-slate-300 leading-relaxed" x-text="permissionError || cameraNotice || 'Klik tombol Nyalakan Kamera untuk mulai memindai tiket siswa.'"></p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-2 w-full max-w-xs pt-1">
                        <button
                            type="button"
                            x-on:click="startCamera()"
                            class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-xs cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px]"
                        >
                            <x-app-icon name="camera" class="w-4 h-4" />
                            <span>Nyalakan Kamera</span>
                        </button>
                        <button
                            type="button"
                            x-on:click="$refs.pagePhotoRef.click()"
                            class="w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-extrabold text-xs rounded-xl border border-slate-700 cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px]"
                        >
                            <span>Foto via Kamera HP</span>
                        </button>
                    </div>
                </div>

                {{-- Overlay Loading Saat Memproses Tiket --}}
                <div
                    x-show="isProcessing"
                    class="absolute inset-0 bg-slate-950/90 backdrop-blur-xs flex flex-col items-center justify-center text-white p-4 space-y-2 z-30"
                >
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-emerald-500 border-t-transparent"></div>
                    <p class="text-xs font-bold" x-text="'Memproses Tiket: ' + scannedCode"></p>
                </div>
            </div>

            {{-- Kartu Hasil Pemindaian --}}
            <div
                x-show="lastResult !== null"
                x-transition
                class="p-4 rounded-xl border shadow-xs space-y-2"
                :class="lastResult?.success ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : 'bg-rose-50 border-rose-200 text-rose-950'"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-6 h-6 rounded-full flex items-center justify-center font-black text-xs text-white shrink-0"
                            :class="lastResult?.success ? 'bg-emerald-600' : 'bg-rose-600'"
                            x-text="lastResult?.success ? '✓' : '✕'"
                        ></span>
                        <div>
                            <h5 class="text-xs font-black uppercase tracking-wide" x-text="lastResult?.success ? 'Berhasil Diproses' : 'Gagal Diproses'"></h5>
                            <p class="text-xs font-medium mt-0.5" x-text="lastResult?.message"></p>
                        </div>
                    </div>
                    <button type="button" x-on:click="lastResult = null" class="text-slate-400 hover:text-slate-600 font-black text-xs p-1">✕</button>
                </div>

                <template x-if="lastResult?.ticket">
                    <div class="bg-white p-3 rounded-lg border border-slate-200 text-xs space-y-1.5 text-slate-800 mt-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kode Sirkulasi:</span>
                            <strong class="font-mono text-emerald-700" x-text="lastResult.ticket.code"></strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Peminjam:</span>
                            <span class="font-bold" x-text="lastResult.ticket.borrower_name + ' (' + lastResult.ticket.borrower_class + ')'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Buku Fisik:</span>
                            <span class="font-bold text-right" x-text="lastResult.ticket.book_title"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status Terbaru:</span>
                            <span class="font-black uppercase text-brand" x-text="lastResult.ticket.status_label"></span>
                        </div>
                        <template x-if="lastResult.ticket.due_date">
                            <div class="flex justify-between border-t border-slate-100 pt-1 text-emerald-700 font-bold">
                                <span>Tenggat Pengembalian:</span>
                                <span x-text="lastResult.ticket.due_date"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Form Input Kode Cadangan Manual --}}
            <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                <input
                    type="text"
                    x-model="manualCode"
                    x-on:keydown.enter.prevent="processCode(manualCode)"
                    placeholder="Ketik kode tiket (contoh: RSXSD / LIB-2026-RSXSD)"
                    class="text-xs rounded-xl border-slate-300 bg-white w-full py-2.5 px-3 shadow-2xs focus:border-brand focus:ring-brand min-h-[44px]"
                />
                <button
                    type="button"
                    x-on:click="processCode(manualCode)"
                    class="px-5 py-2.5 bg-navy hover:bg-brand text-white font-extrabold text-xs rounded-xl shadow-2xs shrink-0 cursor-pointer min-h-[44px]"
                >
                    Cari &amp; Proses
                </button>
            </div>
        </div>
    </div>

    {{-- Sisipkan pustaka jsQR untuk pemindaian instan --}}
    @push('scripts')
        <script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
        @include('filament.circulation-scanner-scripts')
    @endpush
</x-layouts.app>
