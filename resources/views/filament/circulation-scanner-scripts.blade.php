<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
<style>
@keyframes qrPulseRing {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.03); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
.qr-pulse-card {
    animation: qrPulseRing 2.5s ease-in-out infinite;
}
@keyframes laserSweep {
    0% { top: 8%; opacity: 0.4; }
    50% { top: 92%; opacity: 1; }
    100% { top: 8%; opacity: 0.4; }
}
.qr-laser-line {
    position: absolute;
    left: 8%;
    right: 8%;
    height: 3px;
    background: linear-gradient(90deg, transparent, #22c55e, #10b981, transparent);
    box-shadow: 0 0 12px #22c55e;
    animation: laserSweep 2s ease-in-out infinite;
    pointer-events: none;
}
</style>
<script>
(function () {
    /**
     * Mesin Dekoder QR Code Multi-Tier Terpadu
     * Tier 1: Hardware BarcodeDetector API bawaan peramban (Android & Chromium)
     * Tier 2: ZXing Engine via Html5Qrcode.scanFile
     * Tier 3: Multi-Scale Preprocessed jsQR (Downscaling, Binarisasi, Center-Crop, Rotasi)
     */
    async function decodeQrCode(img, file) {
        // TIER 1: BarcodeDetector API (Tercepat & Paling Akurat pada Chrome / Android)
        if (typeof window.BarcodeDetector !== 'undefined') {
            try {
                const detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'code_39'] });
                // Coba pada elemen gambar asli
                let results = await detector.detect(img);
                if (results && results.length > 0 && results[0].rawValue) {
                    return results[0].rawValue.trim();
                }

                // Coba pada canvas resolusi optimal 1200px (mengatasi sensor kamera HP 48MP/108MP)
                const c = document.createElement('canvas');
                const s = Math.min(1, 1200 / Math.max(img.width || 1, img.height || 1));
                c.width = Math.max(1, Math.round((img.width || 1) * s));
                c.height = Math.max(1, Math.round((img.height || 1) * s));
                c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                results = await detector.detect(c);
                if (results && results.length > 0 && results[0].rawValue) {
                    return results[0].rawValue.trim();
                }
            } catch (e) {
                console.debug('BarcodeDetector dialihkan:', e);
            }
        }

        // TIER 2: Html5Qrcode.scanFile (ZXing HybridBinarizer khusus berkas foto)
        if (typeof window.Html5Qrcode !== 'undefined' && file) {
            try {
                let tempEl = document.getElementById('circulation-temp-zxing');
                if (!tempEl) {
                    tempEl = document.createElement('div');
                    tempEl.id = 'circulation-temp-zxing';
                    tempEl.style.cssText = 'position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;';
                    document.body.appendChild(tempEl);
                }
                const html5Qr = new window.Html5Qrcode('circulation-temp-zxing', { verbose: false });
                const zxingResult = await html5Qr.scanFile(file, false);
                try { html5Qr.clear(); } catch(err) {}
                if (zxingResult && typeof zxingResult === 'string' && zxingResult.trim().length > 0) {
                    return zxingResult.trim();
                }
            } catch (e) {
                console.debug('Html5Qrcode scanFile dialihkan:', e);
            }
        }

        // TIER 3: Multi-Scale & Preprocessed jsQR
        if (typeof window.jsQR === 'function') {
            const jsQrResult = scanWithMultiScaleJsQr(img);
            if (jsQrResult && jsQrResult.trim().length > 0) {
                return jsQrResult.trim();
            }
        }

        return null;
    }

    /**
     * Pemrosesan Gambar Multi-Scale & Transformasi Khusus jsQR
     */
    function scanWithMultiScaleJsQr(img) {
        const origW = img.naturalWidth || img.width;
        const origH = img.naturalHeight || img.height;
        if (!origW || !origH) return null;

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d', { willReadFrequently: true });

        function tryDecodeContext(c, w, h) {
            try {
                const imgData = c.getContext('2d', { willReadFrequently: true }).getImageData(0, 0, w, h);

                // 1. Dekode Normal & Inverted
                let res = window.jsQR(imgData.data, w, h, { inversionAttempts: 'attemptBoth' });
                if (res && res.data) return res.data;

                // 2. Kontras Tinggi (+60% contrast boost untuk foto redup)
                const d = imgData.data;
                const contrast = 1.6;
                const intercept = 128 * (1 - contrast);
                for (let i = 0; i < d.length; i += 4) {
                    d[i] = Math.min(255, Math.max(0, d[i] * contrast + intercept));
                    d[i+1] = Math.min(255, Math.max(0, d[i+1] * contrast + intercept));
                    d[i+2] = Math.min(255, Math.max(0, d[i+2] * contrast + intercept));
                }
                res = window.jsQR(d, w, h, { inversionAttempts: 'attemptBoth' });
                if (res && res.data) return res.data;

                // 3. Binarisasi Tajam Hitam Putih (Dynamic Grayscale Threshold)
                let sum = 0;
                const total = w * h;
                for (let i = 0; i < d.length; i += 4) {
                    sum += (d[i] + d[i+1] + d[i+2]) / 3;
                }
                const threshold = sum / total;
                for (let i = 0; i < d.length; i += 4) {
                    const gray = (d[i] + d[i+1] + d[i+2]) / 3;
                    const v = gray > threshold ? 255 : 0;
                    d[i] = v;
                    d[i+1] = v;
                    d[i+2] = v;
                }
                res = window.jsQR(d, w, h, { inversionAttempts: 'attemptBoth' });
                if (res && res.data) return res.data;
            } catch (e) {
                console.debug('jsQR context decode error:', e);
            }
            return null;
        }

        // A. Coba berbagai skala resolusi (1000px, 800px, 1300px, 600px)
        const targetScales = [1000, 800, 1300, 600];
        for (const maxDim of targetScales) {
            const scale = Math.min(1, maxDim / Math.max(origW, origH));
            const w = Math.round(origW * scale);
            const h = Math.round(origH * scale);
            if (w <= 0 || h <= 0) continue;

            canvas.width = w;
            canvas.height = h;
            ctx.drawImage(img, 0, 0, w, h);
            const code = tryDecodeContext(canvas, w, h);
            if (code) return code;
        }

        // B. Coba pemotongan bagian tengah 65% (Fokus area QR code utama)
        const cropW = Math.round(origW * 0.65);
        const cropH = Math.round(origH * 0.65);
        const startX = Math.round((origW - cropW) / 2);
        const startY = Math.round((origH - cropH) / 2);

        const cropCanvas = document.createElement('canvas');
        const cropScale = Math.min(1, 800 / Math.max(cropW, cropH));
        const cW = Math.round(cropW * cropScale);
        const cH = Math.round(cropH * cropScale);
        cropCanvas.width = cW;
        cropCanvas.height = cH;
        cropCanvas.getContext('2d').drawImage(img, startX, startY, cropW, cropH, 0, 0, cW, cH);
        const cropCode = tryDecodeContext(cropCanvas, cW, cH);
        if (cropCode) return cropCode;

        // C. Coba rotasi 90 derajat (Mengatasi orientasi potret/lanskap HP)
        const rotCanvas = document.createElement('canvas');
        const rotScale = Math.min(1, 900 / Math.max(origW, origH));
        const rW = Math.round(origH * rotScale);
        const rH = Math.round(origW * rotScale);
        rotCanvas.width = rW;
        rotCanvas.height = rH;
        const rotCtx = rotCanvas.getContext('2d');
        rotCtx.translate(rW / 2, rH / 2);
        rotCtx.rotate((90 * Math.PI) / 180);
        rotCtx.drawImage(
            img,
            -Math.round(origW * rotScale) / 2,
            -Math.round(origH * rotScale) / 2,
            Math.round(origW * rotScale),
            Math.round(origH * rotScale)
        );
        const rotCode = tryDecodeContext(rotCanvas, rW, rH);
        if (rotCode) return rotCode;

        // D. Coba ukuran mentah jika di bawah 1600px
        if (origW <= 1600 && origH <= 1600) {
            canvas.width = origW;
            canvas.height = origH;
            ctx.drawImage(img, 0, 0, origW, origH);
            const rawCode = tryDecodeContext(canvas, origW, origH);
            if (rawCode) return rawCode;
        }

        return null;
    }

    function circulationScannerComponent() {
        return {
            activeTab: 'photo', // 'photo', 'live', 'manual'
            isProcessing: false,
            errorMessage: null,
            statusText: 'Siap memproses tiket sirkulasi',
            lastScannedCode: '',
            lastScannedAt: 0,
            lastResult: null,
            manualCode: '',
            dragOver: false,
            previewImage: null,
            isLiveActive: false,
            liveError: null,
            _liveStream: null,
            _liveTimer: null,

            init() {
                // Tangani paste clipboard gambar (Ctrl + V)
                this._pasteHandler = (e) => {
                    const items = (e.clipboardData || window.clipboardData)?.items;
                    if (!items) return;
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {
                            const file = items[i].getAsFile();
                            if (file) {
                                this.scanPhotoFile(file);
                                break;
                            }
                        }
                    }
                };
                window.addEventListener('paste', this._pasteHandler);
            },

            destroy() {
                this.stopLiveCamera();
                if (this._pasteHandler) {
                    window.removeEventListener('paste', this._pasteHandler);
                }
            },

            playBeep() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.25, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.18);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.18);
                } catch (e) {}
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
                this.stopLiveCamera();
                this.process(code);
            },

            process(code) {
                const cleanCode = (code || '').trim();
                if (!cleanCode || this.isProcessing) return;

                this.isProcessing = true;
                this.statusText = 'Memproses tiket: ' + cleanCode;

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
                        code: cleanCode,
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
                this.statusText = 'Membaca & mendeteksi QR Code dari foto...';
                this.lastResult = null;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const dataUrl = e.target.result;
                    this.previewImage = dataUrl;

                    const img = new Image();
                    img.onload = async () => {
                        try {
                            const code = await decodeQrCode(img, file);

                            if (code) {
                                this.statusText = 'QR Code terdeteksi: ' + code;
                                this.onDetected(code);
                            } else {
                                this.isProcessing = false;
                                this.lastResult = {
                                    success: false,
                                    message: 'QR Code tidak terdeteksi pada foto ini. Pastikan foto cukup terang, QR Code tidak buram/terpotong, dan lensa kamera bersih. Anda juga bisa mencoba foto ulang atau gunakan Cara 3 (ketik kode manual).'
                                };
                                this.statusText = 'QR Code tidak terdeteksi';
                            }
                        } catch (err) {
                            this.isProcessing = false;
                            this.lastResult = {
                                success: false,
                                message: 'Terjadi kesalahan saat memproses gambar: ' + err.message
                            };
                            this.statusText = 'Gagal memproses foto';
                        }
                    };
                    img.src = dataUrl;
                };
                reader.readAsDataURL(file);
            },

            async startLiveCamera() {
                this.liveError = null;
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.liveError = 'Peramban ini tidak mendukung akses live video kamera. Silakan gunakan tab "Foto Kamera HP" di samping.';
                    return;
                }

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    });

                    this._liveStream = stream;
                    this.isLiveActive = true;

                    this.$nextTick(() => {
                        const video = this.$refs.liveVideoEl;
                        if (video) {
                            video.srcObject = stream;
                            video.play().catch(e => console.debug('Video play error:', e));
                            this.runLiveScanLoop();
                        }
                    });
                } catch (err) {
                    this.isLiveActive = false;
                    this.liveError = 'Akses live video tidak diizinkan oleh sistem atau peramban (' + err.message + '). Silakan gunakan tombol "Buka Kamera & Foto Tiket" di tab sebelah yang bebas dari kendala izin peramban.';
                }
            },

            stopLiveCamera() {
                if (this._liveTimer) {
                    cancelAnimationFrame(this._liveTimer);
                    this._liveTimer = null;
                }
                if (this._liveStream) {
                    this._liveStream.getTracks().forEach(track => track.stop());
                    this._liveStream = null;
                }
                this.isLiveActive = false;
            },

            runLiveScanLoop() {
                if (!this.isLiveActive) return;

                const video = this.$refs.liveVideoEl;
                if (video && video.readyState === video.HAVE_ENOUGH_DATA && !this.isProcessing) {
                    // Coba BarcodeDetector langsung pada video
                    if (typeof window.BarcodeDetector !== 'undefined') {
                        try {
                            const detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                            detector.detect(video).then(barcodes => {
                                if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                                    this.onDetected(barcodes[0].rawValue);
                                    return;
                                }
                            }).catch(() => {});
                        } catch (e) {}
                    }

                    // Fallback jsQR pada frame video
                    if (typeof window.jsQR === 'function') {
                        const c = document.createElement('canvas');
                        c.width = video.videoWidth;
                        c.height = video.videoHeight;
                        const ctx = c.getContext('2d', { willReadFrequently: true });
                        ctx.drawImage(video, 0, 0, c.width, c.height);
                        const imgData = ctx.getImageData(0, 0, c.width, c.height);
                        const res = window.jsQR(imgData.data, c.width, c.height, { inversionAttempts: 'dontInvert' });
                        if (res && res.data) {
                            this.onDetected(res.data);
                            return;
                        }
                    }
                }

                if (this.isLiveActive) {
                    this._liveTimer = requestAnimationFrame(() => this.runLiveScanLoop());
                }
            },

            handleDrop(e) {
                this.dragOver = false;
                const files = e.dataTransfer?.files;
                if (files && files.length > 0) {
                    this.scanPhotoFile(files[0]);
                }
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
