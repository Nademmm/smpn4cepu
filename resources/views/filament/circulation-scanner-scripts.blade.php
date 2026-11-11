<script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
<style>
@keyframes qrLaserMove {
    0% { top: 18%; opacity: 0.3; }
    50% { top: 78%; opacity: 1; }
    100% { top: 18%; opacity: 0.3; }
}
.qr-scanner-laser {
    position: absolute;
    left: 12%;
    right: 12%;
    height: 2px;
    background: #10b981;
    box-shadow: 0 0 10px #10b981, 0 0 18px #10b981;
    animation: qrLaserMove 2.2s ease-in-out infinite;
    pointer-events: none;
    z-index: 10;
}
@keyframes qrSpinAnim {
    to { transform: rotate(360deg); }
}
.qr-spinner-circle {
    width: 32px;
    height: 32px;
    border: 3px solid #10b981;
    border-top-color: transparent;
    border-radius: 50%;
    animation: qrSpinAnim 0.75s linear infinite;
}
</style>
<script>
(function () {
    function circulationScannerComponent() {
        return {
            stream: null,
            video: null,
            canvas: null,
            ctx: null,
            animId: null,
            isRunning: false,
            isProcessing: false,
            errorMessage: null,
            statusText: 'Membuka kamera...',
            lastScannedCode: '',
            lastScannedAt: 0,
            lastResult: null,
            manualCode: '',

            init() {
                this.$nextTick(() => {
                    this.video = this.$el.querySelector('video');
                    this.canvas = this.$el.querySelector('canvas');
                    if (this.canvas) {
                        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
                    }
                    this.start();
                });
            },

            destroy() {
                this.stop();
            },

            async start() {
                if (this.isRunning) return;
                this.errorMessage = null;
                this.statusText = 'Menghubungkan ke kamera...';

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.errorMessage = 'Akses video streaming langsung dibatasi oleh peramban. Silakan gunakan tombol Ambil Foto Kamera HP di bawah.';
                    this.statusText = 'Streaming langsung tidak didukung';
                    return;
                }

                try {
                    let stream = null;
                    // Percobaan 1: Kamera belakang ponsel (environment)
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: { ideal: 'environment' } },
                            audio: false
                        });
                    } catch (e1) {
                        // Percobaan 2: Kamera webcam laptop / desktop umum
                        stream = await navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        });
                    }

                    this.stream = stream;
                    if (!this.video) {
                        this.video = this.$el.querySelector('video');
                    }
                    if (this.video) {
                        this.video.srcObject = stream;
                        await this.video.play();
                    }

                    this.isRunning = true;
                    this.errorMessage = null;
                    this.statusText = 'Arahkan kamera ke QR Code tiket siswa';
                    this.loop();
                } catch (err) {
                    this.isRunning = false;
                    const errName = err.name || '';
                    if (errName === 'NotAllowedError' || errName === 'PermissionDeniedError') {
                        this.errorMessage = 'Izin kamera belum aktif. Silakan izinkan akses kamera di ikon gembok peramban.';
                    } else if (errName === 'NotFoundError') {
                        this.errorMessage = 'Tidak ditemukan perangkat kamera di perangkat Anda.';
                    } else {
                        this.errorMessage = 'Gagal membuka kamera: ' + (err.message || errName);
                    }
                    this.statusText = 'Kamera tidak aktif';
                }
            },

            stop() {
                if (this.animId) {
                    cancelAnimationFrame(this.animId);
                    this.animId = null;
                }
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                    this.stream = null;
                }
                if (this.video) {
                    this.video.srcObject = null;
                }
                this.isRunning = false;
            },

            loop() {
                const tick = async () => {
                    if (!this.isRunning) return;

                    if (this.video && this.video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA && !this.isProcessing) {
                        const code = await this.detect();
                        if (code) {
                            this.onDetected(code);
                        }
                    }

                    if (this.isRunning) {
                        this.animId = requestAnimationFrame(tick);
                    }
                };
                this.animId = requestAnimationFrame(tick);
            },

            async detect() {
                // 1. Coba BarcodeDetector bawaan Chromium
                if ('BarcodeDetector' in window) {
                    try {
                        const detector = new BarcodeDetector({ formats: ['qr_code'] });
                        const barcodes = await detector.detect(this.video);
                        if (barcodes.length > 0 && barcodes[0].rawValue) {
                            return barcodes[0].rawValue;
                        }
                    } catch (e) {}
                }

                // 2. jsQR fallback via canvas
                if (typeof jsQR === 'function' && this.canvas && this.ctx && this.video.videoWidth > 0) {
                    const w = this.video.videoWidth;
                    const h = this.video.videoHeight;
                    if (this.canvas.width !== w || this.canvas.height !== h) {
                        this.canvas.width = w;
                        this.canvas.height = h;
                    }
                    this.ctx.drawImage(this.video, 0, 0, w, h);
                    const imgData = this.ctx.getImageData(0, 0, w, h);
                    const result = jsQR(imgData.data, w, h, { inversionAttempts: 'dontInvert' });
                    if (result && result.data) {
                        return result.data;
                    }
                }

                return null;
            },

            onDetected(rawCode) {
                const code = (rawCode || '').trim();
                if (!code) return;

                const now = Date.now();
                if (code === this.lastScannedCode && (now - this.lastScannedAt) < 3000) {
                    return;
                }

                this.lastScannedAt = now;
                this.lastScannedCode = code;
                this.playBeep();
                this.process(code);
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.15);
                } catch (e) {}
            },

            process(code) {
                if (this.isProcessing) return;
                this.isProcessing = true;
                this.statusText = 'Memproses tiket: ' + code;

                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = (tokenMeta ? tokenMeta.getAttribute('content') : '') || '{{ csrf_token() }}';

                fetch('{{ route("admin.circulation.process-scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        code: code,
                        circulation_action: 'auto'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.isProcessing = false;
                    this.lastResult = data;
                    this.statusText = data.success ? 'Berhasil memproses tiket' : 'Gagal memproses tiket';
                    this.manualCode = '';

                    if (window.Livewire) {
                        Livewire.dispatch('refresh');
                    }
                })
                .catch(err => {
                    this.isProcessing = false;
                    this.lastResult = {
                        success: false,
                        message: 'Kendala jaringan: ' + err.message
                    };
                    this.statusText = 'Gagal menghubungi server';
                });
            },

            scanPhotoFile(file) {
                if (!file) return;
                this.isProcessing = true;
                this.statusText = 'Membaca QR Code dari foto...';

                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        let c = this.canvas;
                        let cx = this.ctx;
                        if (!c) {
                            c = document.createElement('canvas');
                            cx = c.getContext('2d');
                        }
                        c.width = img.width;
                        c.height = img.height;
                        cx.drawImage(img, 0, 0);
                        const imgData = cx.getImageData(0, 0, img.width, img.height);

                        let code = null;
                        if (typeof jsQR === 'function') {
                            const res = jsQR(imgData.data, img.width, img.height);
                            if (res && res.data) code = res.data;
                        }

                        if (code) {
                            this.onDetected(code);
                        } else {
                            this.isProcessing = false;
                            this.lastResult = {
                                success: false,
                                message: 'QR Code tidak terdeteksi pada foto. Pastikan gambar jelas dan tidak buram.'
                            };
                            this.statusText = 'QR Code tidak terdeteksi';
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }

    function registerComponent() {
        if (window.Alpine) {
            Alpine.data('circulationScanner', circulationScannerComponent);
            Alpine.data('circulationNativeScanner', circulationScannerComponent);
            Alpine.data('circulationCameraScanner', circulationScannerComponent);
        }
    }

    if (window.Alpine) {
        registerComponent();
    } else {
        document.addEventListener('alpine:init', registerComponent);
    }

    window.circulationScanner = circulationScannerComponent;
})();
</script>
