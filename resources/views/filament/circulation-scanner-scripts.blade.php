<script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
<script>
    (function () {
        function createCirculationNativeScanner() {
            return {
                stream: null,
                videoEl: null,
                canvasEl: null,
                ctx: null,
                animFrameId: null,
                isCameraRunning: false,
                isStarting: false,
                isProcessing: false,
                facingMode: 'environment',
                permissionError: null,
                cameraNotice: null,
                actionMode: 'auto',
                scannedCode: '',
                lastResult: null,
                lastScannedCode: '',
                lastScannedAt: 0,
                manualCode: '',

                init() {
                    this.$nextTick(() => {
                        this.videoEl = this.$el.querySelector('video.scanner-video-target') || document.getElementById('native-scanner-video');
                        this.canvasEl = this.$el.querySelector('canvas.scanner-canvas-target') || document.getElementById('native-scanner-canvas');
                        if (this.canvasEl) {
                            this.ctx = this.canvasEl.getContext('2d', { willReadFrequently: true });
                        }
                        this.startCamera();
                    });
                },

                destroy() {
                    this.stopCamera();
                },

                async startCamera() {
                    if (this.isCameraRunning) return;
                    this.isStarting = true;
                    this.permissionError = null;
                    this.cameraNotice = null;

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.isStarting = false;
                        this.permissionError = 'Akses video streaming langsung dibatasi oleh peramban (karena koneksi HTTP lokal). Silakan klik tombol "Foto via Kamera HP" di bawah untuk langsung membuka kamera.';
                        return;
                    }

                    try {
                        if (this.stream) {
                            this.stream.getTracks().forEach(t => t.stop());
                        }

                        let acquiredStream = null;

                        // Percobaan 1: Target kamera belakang (environment) dengan resolusi ideal
                        try {
                            acquiredStream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: { ideal: this.facingMode },
                                    width: { ideal: 1280 },
                                    height: { ideal: 720 }
                                },
                                audio: false
                            });
                        } catch (e1) {
                            // Percobaan 2: Target kamera sesuai facingMode tanpa batasan resolusi
                            try {
                                acquiredStream = await navigator.mediaDevices.getUserMedia({
                                    video: { facingMode: this.facingMode },
                                    audio: false
                                });
                            } catch (e2) {
                                // Percobaan 3: Buka sembarang kamera yang tersedia (webcam laptop / webcam USB)
                                acquiredStream = await navigator.mediaDevices.getUserMedia({
                                    video: true,
                                    audio: false
                                });
                            }
                        }

                        this.stream = acquiredStream;

                        if (!this.videoEl) {
                            this.videoEl = this.$el.querySelector('video.scanner-video-target') || document.getElementById('native-scanner-video');
                        }
                        if (!this.canvasEl) {
                            this.canvasEl = this.$el.querySelector('canvas.scanner-canvas-target') || document.getElementById('native-scanner-canvas');
                            if (this.canvasEl) {
                                this.ctx = this.canvasEl.getContext('2d', { willReadFrequently: true });
                            }
                        }

                        if (this.videoEl) {
                            this.videoEl.srcObject = this.stream;
                            await this.videoEl.play();
                        }

                        this.isCameraRunning = true;
                        this.isStarting = false;
                        this.permissionError = null;

                        this.startScanningLoop();

                    } catch (err) {
                        this.isStarting = false;
                        this.isCameraRunning = false;
                        const name = err.name || '';
                        const msg = err.message || '';

                        if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || msg.includes('Permission') || msg.includes('policy')) {
                            this.permissionError = 'Izin kamera belum diizinkan. Silakan izinkan akses kamera di peramban, atau gunakan tombol "Foto via Kamera HP" di bawah.';
                        } else if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                            this.permissionError = 'Tidak ditemukan perangkat kamera yang terpasang.';
                        } else if (name === 'NotReadableError' || name === 'TrackStartError') {
                            this.permissionError = 'Kamera sedang dipakai oleh aplikasi lain.';
                        } else {
                            this.permissionError = 'Gagal membuka kamera: ' + (msg || name);
                        }
                    }
                },

                stopCamera() {
                    if (this.animFrameId) {
                        cancelAnimationFrame(this.animFrameId);
                        this.animFrameId = null;
                    }
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                        this.stream = null;
                    }
                    if (this.videoEl) {
                        this.videoEl.srcObject = null;
                    }
                    this.isCameraRunning = false;
                    this.isStarting = false;
                },

                async flipCamera() {
                    this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
                    this.stopCamera();
                    setTimeout(() => {
                        this.startCamera();
                    }, 200);
                },

                startScanningLoop() {
                    const scanTick = async () => {
                        if (!this.isCameraRunning) return;

                        if (this.videoEl && this.videoEl.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA && !this.isProcessing) {
                            try {
                                const code = await this.detectCode();
                                if (code) {
                                    this.onCodeDetected(code);
                                }
                            } catch (e) {}
                        }

                        if (this.isCameraRunning) {
                            this.animFrameId = requestAnimationFrame(scanTick);
                        }
                    };

                    this.animFrameId = requestAnimationFrame(scanTick);
                },

                async detectCode() {
                    // 1. Coba BarcodeDetector native bawaan peramban
                    if ('BarcodeDetector' in window) {
                        try {
                            const detector = new BarcodeDetector({ formats: ['qr_code'] });
                            const barcodes = await detector.detect(this.videoEl);
                            if (barcodes.length > 0 && barcodes[0].rawValue) {
                                return barcodes[0].rawValue;
                            }
                        } catch (e) {}
                    }

                    // 2. Fallback universal dengan jsQR
                    if (typeof jsQR === 'function' && this.canvasEl && this.ctx && this.videoEl.videoWidth > 0) {
                        const w = this.videoEl.videoWidth;
                        const h = this.videoEl.videoHeight;
                        if (this.canvasEl.width !== w || this.canvasEl.height !== h) {
                            this.canvasEl.width = w;
                            this.canvasEl.height = h;
                        }
                        this.ctx.drawImage(this.videoEl, 0, 0, w, h);
                        const imgData = this.ctx.getImageData(0, 0, w, h);
                        const code = jsQR(imgData.data, w, h, { inversionAttempts: 'dontInvert' });
                        if (code && code.data) {
                            return code.data;
                        }
                    }

                    return null;
                },

                scanSelectedFile(file) {
                    if (!file) return;
                    this.isProcessing = true;
                    this.scannedCode = file.name || 'Foto QR';

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            let cv = this.canvasEl;
                            let ct = this.ctx;
                            if (!cv) {
                                cv = document.createElement('canvas');
                                ct = cv.getContext('2d');
                            }
                            cv.width = img.width;
                            cv.height = img.height;
                            ct.drawImage(img, 0, 0);
                            const imgData = ct.getImageData(0, 0, img.width, img.height);

                            let detected = null;
                            if (typeof jsQR === 'function') {
                                const res = jsQR(imgData.data, img.width, img.height);
                                if (res && res.data) detected = res.data;
                            }

                            if (detected) {
                                this.onCodeDetected(detected);
                            } else {
                                this.isProcessing = false;
                                this.lastResult = {
                                    success: false,
                                    message: 'QR Code tidak terdeteksi pada foto. Pastikan gambar jelas dan tidak buram.'
                                };
                            }
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
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

                onCodeDetected(rawCode) {
                    const now = Date.now();
                    const code = (rawCode || '').trim();

                    if (code === this.lastScannedCode && (now - this.lastScannedAt) < 2500) {
                        return;
                    }

                    this.lastScannedAt = now;
                    this.lastScannedCode = code;
                    this.playBeep();
                    this.processCode(code);
                },

                processCode(code) {
                    const cleanCode = (code || '').trim();
                    if (!cleanCode || this.isProcessing) return;

                    this.isProcessing = true;
                    this.scannedCode = cleanCode;

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
                            circulation_action: this.actionMode
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isProcessing = false;
                        this.lastResult = data;
                        this.manualCode = '';

                        if (window.Livewire) {
                            Livewire.dispatch('refresh');
                        }
                    })
                    .catch(err => {
                        this.isProcessing = false;
                        this.lastResult = {
                            success: false,
                            message: 'Gagal menghubungi server sirkulasi: ' + err.message
                        };
                    });
                }
            };
        }

        function registerComponents() {
            if (window.Alpine) {
                Alpine.data('circulationNativeScanner', createCirculationNativeScanner);
                Alpine.data('circulationCameraScanner', createCirculationNativeScanner);
            }
        }

        if (window.Alpine) {
            registerComponents();
        } else {
            document.addEventListener('alpine:init', registerComponents);
        }

        window.circulationNativeScanner = createCirculationNativeScanner;
        window.circulationCameraScanner = createCirculationNativeScanner;
    })();
</script>
