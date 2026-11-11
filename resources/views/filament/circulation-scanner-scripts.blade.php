<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<style>
    /* Penyesuaian responsif scanner kamera agar tidak overflow di layar HP */
    #circulation-camera-reader, #widget-camera-reader {
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        position: relative !important;
    }
    #circulation-camera-reader video, #widget-camera-reader video {
        width: 100% !important;
        height: 100% !important;
        max-height: 46vh !important;
        object-fit: cover !important;
        border-radius: 0.75rem !important;
    }
    #circulation-camera-reader__scan_region, #widget-camera-reader__scan_region {
        background: transparent !important;
        border: none !important;
    }
    #circulation-camera-reader__scan_region img, #widget-camera-reader__scan_region img {
        display: none !important;
    }
    #circulation-camera-reader__header_message, #widget-camera-reader__header_message {
        display: none !important;
    }
</style>
<script>
    (function () {
        function createCirculationScanner(readerElementId) {
            const isWidget = readerElementId === 'widget-camera-reader';
            const targetId = readerElementId || 'circulation-camera-reader';

            return {
                targetId: targetId,
                html5QrCode: null,
                isCameraOpen: !isWidget,
                isCameraRunning: false,
                isProcessing: false,
                permissionError: null,
                isSecure: true,
                actionMode: 'auto',
                cameras: [],
                selectedCameraId: null,
                manualCode: '',
                scannedCode: '',
                lastResult: null,
                lastScannedAt: 0,
                lastScannedCode: '',

                init() {
                    this.isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                    if (!this.isSecure) {
                        this.permissionError = 'Streaming kamera langsung peramban membutuhkan HTTPS atau localhost. Jika diakses via HTTP jaringan lokal di HP, silakan gunakan tombol "Ambil Foto QR" untuk memindai lewat kamera HP Anda.';
                    }

                    // Jika berada di modal dan konteks aman, coba nyalakan kamera belakang langsung
                    if (!isWidget && this.isSecure && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        this.startCamera('environment');
                    }
                },

                destroy() {
                    this.stopCamera();
                },

                startCamera(preferFacing) {
                    if (this.isCameraRunning) return;
                    if (!window.Html5Qrcode) {
                        this.permissionError = 'Pustaka pemindai belum siap dimuat.';
                        return;
                    }

                    const readerElement = document.getElementById(this.targetId);
                    if (!readerElement) return;

                    this.permissionError = null;
                    const facing = preferFacing || 'environment';

                    try {
                        if (!this.html5QrCode) {
                            const formats = window.Html5QrcodeSupportedFormats
                                ? [window.Html5QrcodeSupportedFormats.QR_CODE]
                                : undefined;
                            this.html5QrCode = new Html5Qrcode(this.targetId, {
                                formatsToSupport: formats,
                                verbose: false
                            });
                        }

                        const config = {
                            fps: 15,
                            qrbox: (viewfinderWidth, viewfinderHeight) => {
                                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                const size = Math.max(Math.floor(minEdge * 0.72), 170);
                                return { width: size, height: size };
                            },
                            aspectRatio: 1.0
                        };

                        const cameraConfig = this.selectedCameraId
                            ? { deviceId: { exact: this.selectedCameraId } }
                            : { facingMode: facing };

                        this.html5QrCode.start(
                            cameraConfig,
                            config,
                            (decodedText) => {
                                this.onCodeDetected(decodedText);
                            },
                            () => {}
                        ).then(() => {
                            this.isCameraRunning = true;
                            this.permissionError = null;

                            // Muat daftar kamera setelah izin aktif agar label perangkat terbaca
                            if (Html5Qrcode.getCameras) {
                                Html5Qrcode.getCameras().then(devices => {
                                    if (devices && devices.length) {
                                        this.cameras = devices;
                                    }
                                }).catch(() => {});
                            }
                        }).catch((err) => {
                            // Fallback jika kamera belakang tidak ditemukan (misal di PC / webcam depan)
                            if (facing === 'environment' && !this.selectedCameraId) {
                                this.html5QrCode.start(
                                    { facingMode: 'user' },
                                    config,
                                    (decodedText) => this.onCodeDetected(decodedText),
                                    () => {}
                                ).then(() => {
                                    this.isCameraRunning = true;
                                    this.permissionError = null;
                                }).catch((e) => {
                                    this.handleCameraError(e);
                                });
                            } else {
                                this.handleCameraError(err);
                            }
                        });
                    } catch (e) {
                        this.handleCameraError(e);
                    }
                },

                handleCameraError(err) {
                    this.isCameraRunning = false;
                    const msg = (err && err.message) ? err.message : String(err || '');

                    const isLocalOrHttps = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
                    if (!isLocalOrHttps) {
                        this.permissionError = 'Akses kamera peramban dibatasi pada protokol HTTP (IP lokal). Silakan buka melalui HTTPS atau gunakan tombol "Ambil Foto QR" untuk memindai langsung menggunakan kamera HP.';
                        return;
                    }

                    if (msg.includes('NotAllowedError') || msg.includes('Permission denied')) {
                        this.permissionError = 'Izin kamera ditolak. Silakan klik ikon gembok / setelan situs di bilah alamat peramban Anda, lalu ubah izin kamera menjadi "Izinkan" (Allow).';
                    } else if (msg.includes('NotFoundError') || msg.includes('DevicesNotFoundError')) {
                        this.permissionError = 'Tidak ada perangkat kamera yang terdeteksi di perangkat ini.';
                    } else if (msg.includes('NotReadableError') || msg.includes('TrackStartError')) {
                        this.permissionError = 'Kamera sedang digunakan oleh aplikasi lain atau belum siap. Silakan tutup aplikasi lain dan coba lagi.';
                    } else {
                        this.permissionError = 'Tidak dapat membuka kamera: ' + msg;
                    }
                },

                stopCamera() {
                    if (this.html5QrCode && this.isCameraRunning) {
                        try {
                            this.html5QrCode.stop().then(() => {
                                this.isCameraRunning = false;
                            }).catch(() => {
                                this.isCameraRunning = false;
                            });
                        } catch (e) {
                            this.isCameraRunning = false;
                        }
                    } else {
                        this.isCameraRunning = false;
                    }
                },

                toggleCamera() {
                    if (this.isCameraRunning) {
                        this.stopCamera();
                        if (isWidget) {
                            this.isCameraOpen = false;
                        }
                    } else {
                        if (isWidget) {
                            this.isCameraOpen = true;
                        }
                        this.startCamera('environment');
                    }
                },

                switchCamera() {
                    if (this.isCameraRunning) {
                        this.stopCamera();
                        setTimeout(() => this.startCamera('environment'), 250);
                    }
                },

                scanSelectedFile(file) {
                    if (!file) return;
                    this.isProcessing = true;
                    this.scannedCode = file.name || 'Foto QR Tiket';

                    const readerElement = document.getElementById(this.targetId);
                    if (!readerElement) return;

                    if (!this.html5QrCode) {
                        const formats = window.Html5QrcodeSupportedFormats
                            ? [window.Html5QrcodeSupportedFormats.QR_CODE]
                            : undefined;
                        this.html5QrCode = new Html5Qrcode(this.targetId, {
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
                                message: 'QR Code tidak terdeteksi pada foto. Pastikan gambar jelas, fokus, dan tidak terpotong.'
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

                    if (code === this.lastScannedCode && (now - this.lastScannedAt) < 3000) {
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
                Alpine.data('circulationCameraScanner', () => createCirculationScanner('circulation-camera-reader'));
                Alpine.data('circulationWidgetScanner', () => createCirculationScanner('widget-camera-reader'));
            }
        }

        if (window.Alpine) {
            registerComponents();
        } else {
            document.addEventListener('alpine:init', registerComponents);
        }

        window.circulationCameraScanner = function () {
            return createCirculationScanner('circulation-camera-reader');
        };
        window.circulationWidgetScanner = function () {
            return createCirculationScanner('widget-camera-reader');
        };
    })();
</script>
