<div
    x-data="circulationCameraScanner()"
    x-init="initScanner()"
    x-on:unmount="stopCamera()"
    class="space-y-4 text-slate-800"
>
    {{-- Bar Navigasi Opsi Tindakan & Pemilihan Kamera --}}
    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
            <div>
                <label for="scanner-action-mode" class="text-xs font-bold text-slate-700 block mb-1">
                    Tindakan Sirkulasi:
                </label>
                <select
                    id="scanner-action-mode"
                    x-model="actionMode"
                    class="text-xs font-semibold rounded-lg border-slate-300 bg-white py-1.5 px-3 shadow-xs focus:border-primary-500 focus:ring-primary-500 w-full sm:w-auto"
                >
                    <option value="auto">Deteksi Otomatis (Serahkan jika menunggu, Terima jika dipinjam)</option>
                    <option value="hand_over">Khusus Serahkan Buku ke Siswa</option>
                    <option value="return">Khusus Terima Pengembalian Buku</option>
                    <option value="view_only">Hanya Cek Data &amp; Status Tiket</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select
                    x-model="selectedCameraId"
                    x-show="cameras.length > 1"
                    x-on:change="switchCamera()"
                    class="text-xs rounded-lg border-slate-300 bg-white py-1.5 px-2.5 shadow-xs"
                >
                    <template x-for="cam in cameras" :key="cam.id">
                        <option :value="cam.id" x-text="cam.label || 'Kamera ' + cam.id"></option>
                    </template>
                </select>

                <button
                    type="button"
                    x-on:click="toggleCamera()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg shadow-xs cursor-pointer transition-colors"
                    :class="isCameraRunning ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-primary-600 hover:bg-primary-700 text-white'"
                >
                    <span x-text="isCameraRunning ? 'Matikan Kamera' : 'Nyalakan Kamera'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Jendela Kamera Pemindai Barcode / QR --}}
    <div class="relative w-full rounded-2xl overflow-hidden bg-slate-900 border-2 border-slate-700 shadow-inner flex flex-col items-center justify-center min-h-[300px]">
        {{-- Viewfinder html5-qrcode --}}
        <div id="circulation-camera-reader" class="w-full"></div>

        {{-- Layar Placeholder saat Kamera Mati --}}
        <div
            x-show="!isCameraRunning && !isProcessing"
            class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-slate-300 bg-slate-900/95 space-y-3"
        >
            <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white">Kamera Belum Aktif</h4>
                <p class="text-xs text-slate-400 max-w-sm mt-1">
                    Klik tombol di bawah untuk menyalakan kamera laptop atau gawai. Arahkan lensa ke Barcode atau QR Code tiket siswa.
                </p>
            </div>
            <button
                type="button"
                x-on:click="startCamera()"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer inline-flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Nyalakan Kamera Sekarang</span>
            </button>
        </div>

        {{-- Loading / Processing Overlay saat Kode Ditemukan --}}
        <div
            x-show="isProcessing"
            class="absolute inset-0 bg-slate-900/80 backdrop-blur-xs flex flex-col items-center justify-center text-white p-4 z-20 space-y-2"
        >
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-primary-500 border-t-transparent"></div>
            <p class="text-xs font-bold" x-text="'Memproses Tiket: ' + scannedCode"></p>
        </div>
    </div>

    {{-- Kartu Hasil Pemindaian Terakhir --}}
    <div
        x-show="lastResult !== null"
        x-transition
        class="p-4 rounded-xl border shadow-xs space-y-2.5"
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
                class="text-slate-400 hover:text-slate-600 text-xs font-bold"
            >
                ✕
            </button>
        </div>

        <template x-if="lastResult?.ticket">
            <div class="bg-white/90 p-3 rounded-lg border border-slate-200 text-xs space-y-1 text-slate-800">
                <div class="flex justify-between">
                    <span class="text-slate-500">Kode Sirkulasi:</span>
                    <strong class="font-mono" x-text="lastResult.ticket.code"></strong>
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
                    <span class="font-bold uppercase" x-text="lastResult.ticket.status_label"></span>
                </div>
                <template x-if="lastResult.ticket.due_date">
                    <div class="flex justify-between border-t border-slate-100 pt-1 mt-1 text-primary-700">
                        <span>Batas Pengembalian:</span>
                        <strong x-text="lastResult.ticket.due_date"></strong>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Fallback: Input Manual / Scanner USB --}}
    <div class="pt-2 border-t border-slate-200 flex items-center gap-2">
        <input
            type="text"
            x-model="manualCode"
            x-on:keydown.enter.prevent="processCode(manualCode)"
            placeholder="Atau ketik/scan barcode USB di sini..."
            class="text-xs rounded-lg border-slate-300 w-full py-2 px-3 shadow-2xs focus:border-primary-500 focus:ring-primary-500"
        />
        <button
            type="button"
            x-on:click="processCode(manualCode)"
            class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-lg shadow-2xs shrink-0 cursor-pointer"
        >
            Proses
        </button>
    </div>
</div>

<script>
    function circulationCameraScanner() {
        return {
            html5QrCode: null,
            isCameraRunning: false,
            isProcessing: false,
            actionMode: 'auto',
            cameras: [],
            selectedCameraId: null,
            manualCode: '',
            scannedCode: '',
            lastResult: null,
            lastScannedAt: 0,
            lastScannedCode: '',

            initScanner() {
                this.loadScript(() => {
                    this.detectCameras();
                });
            },

            loadScript(callback) {
                if (window.Html5Qrcode) {
                    callback();
                    return;
                }
                const script = document.createElement('script');
                script.src = '/vendor/html5-qrcode/html5-qrcode.min.js';
                script.onload = callback;
                document.head.appendChild(script);
            },

            detectCameras() {
                if (!window.Html5Qrcode) return;
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        this.cameras = devices;
                        // Prioritaskan kamera belakang jika di smartphone
                        const backCam = devices.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang') || c.label.toLowerCase().includes('environment'));
                        this.selectedCameraId = backCam ? backCam.id : devices[0].id;
                        // Nyalakan otomatis saat modal pertama kali dibuka
                        this.startCamera();
                    }
                }).catch(err => {
                    console.warn('Gagal membaca daftar kamera:', err);
                });
            },

            startCamera() {
                if (this.isCameraRunning) return;
                if (!window.Html5Qrcode) return;

                const readerElement = document.getElementById('circulation-camera-reader');
                if (!readerElement) return;

                if (!this.html5QrCode) {
                    this.html5QrCode = new Html5Qrcode('circulation-camera-reader');
                }

                const config = {
                    fps: 10,
                    qrbox: { width: 280, height: 180 },
                    aspectRatio: 1.333334
                };

                const cameraIdOrConfig = this.selectedCameraId
                    ? { deviceId: { exact: this.selectedCameraId } }
                    : { facingMode: 'environment' };

                this.html5QrCode.start(
                    cameraIdOrConfig,
                    config,
                    (decodedText) => {
                        this.onCodeDetected(decodedText);
                    },
                    (errorMessage) => {
                        // frame tidak mengandung barcode, biarkan loop scanning berjalan
                    }
                ).then(() => {
                    this.isCameraRunning = true;
                }).catch(err => {
                    console.error('Tidak dapat memulai kamera:', err);
                    this.isCameraRunning = false;
                });
            },

            stopCamera() {
                if (this.html5QrCode && this.isCameraRunning) {
                    this.html5QrCode.stop().then(() => {
                        this.isCameraRunning = false;
                    }).catch(err => {
                        console.warn('Error saat menghentikan kamera:', err);
                        this.isCameraRunning = false;
                    });
                }
            },

            toggleCamera() {
                if (this.isCameraRunning) {
                    this.stopCamera();
                } else {
                    this.startCamera();
                }
            },

            switchCamera() {
                if (this.isCameraRunning) {
                    this.stopCamera();
                    setTimeout(() => this.startCamera(), 300);
                }
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime); // 880Hz (Nada A5, cerah & tajam)
                    gain.gain.setValueAtTime(0.15, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.12);
                } catch (e) {
                    // browser audio policy fallback
                }
            },

            onCodeDetected(rawCode) {
                const now = Date.now();
                const code = rawCode.trim();

                // Cegah scanning ganda beruntun untuk kode yang sama dalam 3 detik
                if (code === this.lastScannedCode && (now - this.lastScannedAt) < 3000) {
                    return;
                }

                this.lastScannedAt = now;
                this.lastScannedCode = code;
                this.playBeep();
                this.processCode(code);
            },

            processCode(code) {
                if (!code || this.isProcessing) return;
                this.isProcessing = true;
                this.scannedCode = code;

                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';

                fetch('{{ route("admin.circulation.process-scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        code: code,
                        circulation_action: this.actionMode
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isProcessing = false;
                    this.lastResult = data;
                    this.manualCode = '';
                    
                    // Segarkan data tabel di belakang layar secara otomatis
                    if (window.Livewire) {
                        Livewire.dispatch('refresh');
                    }
                })
                .catch(err => {
                    this.isProcessing = false;
                    this.lastResult = {
                        success: false,
                        message: 'Gagal menghubungi server: ' + err.message
                    };
                });
            }
        };
    }
</script>

