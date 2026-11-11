<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<style>
    /* Styling responsif & pembersihan elemen default html5-qrcode */
    #circulation-camera-reader {
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        position: relative !important;
        background: #020617 !important;
        border-radius: 1rem !important;
        overflow: hidden !important;
    }
    #circulation-camera-reader video {
        width: 100% !important;
        height: 100% !important;
        max-height: 46vh !important;
        object-fit: cover !important;
        border-radius: 1rem !important;
        background: #020617 !important;
    }
    #circulation-camera-reader__scan_region {
        background: transparent !important;
        border: none !important;
    }
    #circulation-camera-reader__scan_region img {
        display: none !important;
    }
    #circulation-camera-reader__header_message {
        display: none !important;
    }
    #circulation-camera-reader__status_span {
        display: none !important;
    }
    #circulation-camera-reader__dashboard {
        display: none !important;
    }
    #circulation-camera-reader input[type="file"] {
        display: none !important;
    }
    #circulation-camera-reader button {
        display: none !important;
    }

    /* Animasi garis pemindai QR Code */
    @keyframes qr-scan-pulse {
        0%, 100% {
            top: 10%;
            opacity: 0.2;
        }
        50% {
            top: 85%;
            opacity: 0.9;
        }
    }
    .scanner-laser-line {
        position: absolute;
        left: 8%;
        right: 8%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #10B981, #34D399, transparent);
        box-shadow: 0 0 12px #10B981;
        animation: qr-scan-pulse 2.2s ease-in-out infinite;
        pointer-events: none;
    }
</style>
<script>
    (function () {
        function createCirculationModalScanner() {
            return {
                html5QrCode: null,
                isCameraRunning: false,
                isStarting: false,
                isProcessing: false,
                permissionError: null,
                cameraNotice: null,
                isSecure: true,
                actionMode: 'auto',
                cameras: [],
                currentCameraIndex: 0,
                selectedCameraId: null,
                manualCode: '',
                scannedCode: '',
                lastResult: null,
                lastScannedAt: 0,
                lastScannedCode: '',

                init() {
                    this.isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                    // Tunggu hingga modal Filament selesai melakukan transisi dan elemen pembaca terlihat di layar
                    this.waitForVisibleElement('circulation-camera-reader', () => {
                        if (this.isSecure) {
                            this.startCameraAuto();
                        } else {
                            this.cameraNotice = 'Peramban membatasi streaming video langsung pada HTTP lokal. Silakan gunakan tombol "Ambil Foto QR" untuk memindai lewat kamera HP Anda.';
                        }
                    });
                },

                destroy() {
                    this.stopCamera();
                },

                waitForVisibleElement(elementId, callback, maxTries = 30) {
                    let tries = 0;
                    const check = () => {
                        const el = document.getElementById(elementId);
                        if (el && el.clientWidth > 0 && el.clientHeight > 0) {
                            callback();
                            return;
                        }
                        tries++;
                        if (tries < maxTries) {
                            setTimeout(check, 100);
                        } else if (el) {
                            callback();
                        }
                    };
                    check();
                },

                startCameraAuto() {
                    if (this.isCameraRunning || this.isStarting) return;
                    this.isStarting = true;
                    this.permissionError = null;
                    this.cameraNotice = null;

                    if (!window.Html5Qrcode) {
                        this.permissionError = 'Pustaka pemindai belum siap. Silakan muat ulang halaman.';
                        this.isStarting = false;
                        return;
                    }

                    const readerEl = document.getElementById('circulation-camera-reader');
                    if (!readerEl) {
                        this.isStarting = false;
                        return;
                    }

                    try {
                        if (!this.html5QrCode) {
                            const formats = window.Html5QrcodeSupportedFormats
                                ? [window.Html5QrcodeSupportedFormats.QR_CODE]
                                : undefined;
                            this.html5QrCode = new Html5Qrcode('circulation-camera-reader', {
                                formatsToSupport: formats,
                                verbose: false
                            });
                        }

                        const config = {
                            fps: 15,
                            qrbox: (viewfinderWidth, viewfinderHeight) => {
                                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                const size = Math.max(Math.floor(minEdge * 0.72), 120);
                                return { width: size, height: size };
                            },
                            aspectRatio: 1.0
                        };

                        const onSuccess = (decodedText) => {
                            this.onCodeDetected(decodedText);
                        };

                        const onError = () => {};

                        // Panggil getCameras() terlebih dahulu untuk memicu dialog izin bawaan peramban
                        Html5Qrcode.getCameras().then(devices => {
                            if (devices && devices.length > 0) {
                                this.cameras = devices;

                                // Deteksi kamera belakang untuk HP
                                let targetIndex = 0;
                                for (let i = 0; i < devices.length; i++) {
                                    const label = (devices[i].label || '').toLowerCase();
                                    if (label.includes('back') || label.includes('rear') || label.includes('belakang') || label.includes('environment')) {
                                        targetIndex = i;
                                        break;
                                    }
                                }
                                if (devices.length > 1 && targetIndex === 0) {
                                    targetIndex = devices.length - 1;
                                }

                                this.currentCameraIndex = targetIndex;
                                this.selectedCameraId = devices[targetIndex].id;

                                this.html5QrCode.start(this.selectedCameraId, config, onSuccess, onError)
                                    .then(() => {
                                        this.isCameraRunning = true;
                                        this.isStarting = false;
                                        this.permissionError = null;
                                    })
                                    .catch(err => {
                                        this.fallbackStartFacingMode(config, onSuccess, onError, err);
                                    });
                            } else {
                                this.fallbackStartFacingMode(config, onSuccess, onError, null);
                            }
                        }).catch(err => {
                            this.fallbackStartFacingMode(config, onSuccess, onError, err);
                        });

                    } catch (e) {
                        this.isStarting = false;
                        this.handleCameraError(e);
                    }
                },

                fallbackStartFacingMode(config, onSuccess, onError, previousError) {
                    if (!this.html5QrCode) {
                        this.isStarting = false;
                        this.handleCameraError(previousError);
                        return;
                    }

                    // Percobaan 1: Kamera belakang
                    this.html5QrCode.start({ facingMode: 'environment' }, config, onSuccess, onError)
                        .then(() => {
                            this.isCameraRunning = true;
                            this.isStarting = false;
                            this.permissionError = null;
                        })
                        .catch(() => {
                            // Percobaan 2: Kamera depan / webcam laptop
                            this.html5QrCode.start({ facingMode: 'user' }, config, onSuccess, onError)
                                .then(() => {
                                    this.isCameraRunning = true;
                                    this.isStarting = false;
                                    this.permissionError = null;
                                })
                                .catch(() => {
                                    // Percobaan 3: Kamera apapun tanpa batasan
                                    this.html5QrCode.start({}, config, onSuccess, onError)
                                        .then(() => {
                                            this.isCameraRunning = true;
                                            this.isStarting = false;
                                            this.permissionError = null;
                                        })
                                        .catch(err => {
                                            this.isStarting = false;
                                            this.handleCameraError(previousError || err);
                                        });
                                });
                        });
                },

                handleCameraError(err) {
                    this.isCameraRunning = false;
                    this.isStarting = false;
                    const msg = (err && err.message) ? err.message : String(err || '');

                    if (msg.includes('NotAllowedError') || msg.includes('Permission denied') || msg.includes('PermissionDismissedError')) {
                        this.permissionError = 'Izin kamera belum diizinkan. Silakan klik ikon gembok di sebelah kiri bilah alamat peramban, ubah Kamera menjadi "Izinkan" (Allow), lalu klik tombol "Coba Nyalakan Kamera" di bawah.';
                    } else if (msg.includes('NotFoundError') || msg.includes('DevicesNotFoundError')) {
                        this.permissionError = 'Tidak ada perangkat kamera yang terdeteksi di perangkat Anda.';
                    } else if (msg.includes('NotReadableError') || msg.includes('TrackStartError')) {
                        this.permissionError = 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi yang menggunakan kamera dan coba lagi.';
                    } else if (!this.isSecure) {
                        this.permissionError = 'Peramban membatasi streaming kamera pada protokol HTTP lokal. Silakan gunakan tombol "Ambil Foto QR" untuk memindai via kamera HP.';
                    } else {
                        this.permissionError = 'Tidak dapat mengakses kamera: ' + msg;
                    }
                },

                stopCamera() {
                    if (this.html5QrCode) {
                        if (this.isCameraRunning) {
                            try {
                                this.html5QrCode.stop().then(() => {
                                    try { this.html5QrCode.clear(); } catch(e){}
                                    this.html5QrCode = null;
                                    this.isCameraRunning = false;
                                    this.isStarting = false;
                                }).catch(() => {
                                    try { this.html5QrCode.clear(); } catch(e){}
                                    this.html5QrCode = null;
                                    this.isCameraRunning = false;
                                    this.isStarting = false;
                                });
                            } catch (e) {
                                this.isCameraRunning = false;
                                this.isStarting = false;
                            }
                        } else {
                            try { this.html5QrCode.clear(); } catch(e){}
                            this.html5QrCode = null;
                            this.isStarting = false;
                        }
                    }
                },

                switchCamera() {
                    if (this.cameras.length <= 1) return;
                    this.currentCameraIndex = (this.currentCameraIndex + 1) % this.cameras.length;
                    this.selectedCameraId = this.cameras[this.currentCameraIndex].id;

                    this.stopCamera();
                    setTimeout(() => {
                        this.startCameraAuto();
                    }, 250);
                },

                scanSelectedFile(file) {
                    if (!file) return;
                    this.isProcessing = true;
                    this.scannedCode = file.name || 'Foto QR Tiket';

                    if (!this.html5QrCode) {
                        const formats = window.Html5QrcodeSupportedFormats
                            ? [window.Html5QrcodeSupportedFormats.QR_CODE]
                            : undefined;
                        this.html5QrCode = new Html5Qrcode('circulation-camera-reader', {
                            formatsToSupport: formats,
                            verbose: false
                        });
                    }

                    this.html5QrCode.scanFile(file, true)
                        .then(decodedText => {
                            this.onCodeDetected(decodedText);
                        })
                        .catch(() => {
                            this.isProcessing = false;
                            this.lastResult = {
                                success: false,
                                message: 'QR Code tidak terdeteksi pada foto. Pastikan gambar jelas, fokus, dan tidak buram.'
                            };
                        });
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

                        // Refresh tabel data Filament agar status langsung terupdate
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
                Alpine.data('circulationCameraScanner', createCirculationModalScanner);
                Alpine.data('circulationWidgetScanner', createCirculationModalScanner);
            }
        }

        if (window.Alpine) {
            registerComponents();
        } else {
            document.addEventListener('alpine:init', registerComponents);
        }

        window.circulationCameraScanner = createCirculationModalScanner;
        window.circulationWidgetScanner = createCirculationModalScanner;
    })();
</script>
