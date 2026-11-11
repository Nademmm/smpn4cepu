<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
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
                startTimeoutTimer: null,

                init() {
                    this.isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                    // Tunggu hingga elemen pembaca terlihat di layar (setelah modal selesai transisi)
                    this.waitForVisibleElement('circulation-camera-reader', () => {
                        if (this.isSecure) {
                            this.startCameraDirect();
                        } else {
                            this.cameraNotice = 'Peramban membatasi streaming video langsung pada HTTP lokal. Silakan klik tombol Foto via Kamera HP untuk memindai tiket.';
                        }
                    });
                },

                destroy() {
                    if (this.startTimeoutTimer) {
                        clearTimeout(this.startTimeoutTimer);
                    }
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

                startCameraDirect() {
                    if (this.isCameraRunning) return;
                    this.isStarting = true;
                    this.permissionError = null;
                    this.cameraNotice = null;

                    // Pengaman batas waktu 6 detik: Cegah layar macet di status Menghubungkan
                    if (this.startTimeoutTimer) {
                        clearTimeout(this.startTimeoutTimer);
                    }
                    this.startTimeoutTimer = setTimeout(() => {
                        if (!this.isCameraRunning && this.isStarting) {
                            this.isStarting = false;
                            this.permissionError = 'Koneksi kamera belum aktif. Pastikan klik "Izinkan" (Allow) pada pop-up peramban, atau gunakan tombol Foto via Kamera HP.';
                        }
                    }, 6000);

                    if (!window.Html5Qrcode) {
                        this.isStarting = false;
                        clearTimeout(this.startTimeoutTimer);
                        this.permissionError = 'Pustaka pemindai belum siap. Silakan muat ulang halaman (F5).';
                        return;
                    }

                    const readerEl = document.getElementById('circulation-camera-reader');
                    if (!readerEl) {
                        this.isStarting = false;
                        clearTimeout(this.startTimeoutTimer);
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

                        // Panggil start langsung dengan kamera belakang (environment)
                        // Langkah ini langsung memicu peramban meminta izin kamera kepada pengguna
                        this.html5QrCode.start({ facingMode: 'environment' }, config, onSuccess, onError)
                            .then(() => {
                                if (this.startTimeoutTimer) clearTimeout(this.startTimeoutTimer);
                                this.isCameraRunning = true;
                                this.isStarting = false;
                                this.permissionError = null;

                                // Deteksi kamera untuk tombol ganti kamera
                                if (Html5Qrcode.getCameras) {
                                    Html5Qrcode.getCameras().then(devices => {
                                        if (devices && devices.length) {
                                            this.cameras = devices;
                                        }
                                    }).catch(() => {});
                                }
                            })
                            .catch(err => {
                                // Fallback: jika perangkat adalah laptop/PC yang hanya punya webcam depan
                                this.html5QrCode.start({ facingMode: 'user' }, config, onSuccess, onError)
                                    .then(() => {
                                        if (this.startTimeoutTimer) clearTimeout(this.startTimeoutTimer);
                                        this.isCameraRunning = true;
                                        this.isStarting = false;
                                        this.permissionError = null;
                                    })
                                    .catch(() => {
                                        // Fallback terakhir: coba buka kamera apapun tanpa batasan
                                        this.html5QrCode.start({}, config, onSuccess, onError)
                                            .then(() => {
                                                if (this.startTimeoutTimer) clearTimeout(this.startTimeoutTimer);
                                                this.isCameraRunning = true;
                                                this.isStarting = false;
                                                this.permissionError = null;
                                            })
                                            .catch(finalErr => {
                                                if (this.startTimeoutTimer) clearTimeout(this.startTimeoutTimer);
                                                this.isStarting = false;
                                                this.handleCameraError(finalErr || err);
                                            });
                                    });
                            });

                    } catch (e) {
                        if (this.startTimeoutTimer) clearTimeout(this.startTimeoutTimer);
                        this.isStarting = false;
                        this.handleCameraError(e);
                    }
                },

                handleCameraError(err) {
                    this.isCameraRunning = false;
                    this.isStarting = false;
                    const msg = (err && err.message) ? err.message : String(err || '');

                    if (msg.includes('Permissions policy') || msg.includes('camera is not allowed in this document') || msg.includes('PermissionPolicyViolation')) {
                        this.permissionError = 'Akses kamera dibatasi oleh Permissions Policy (misal di tab preview editor atau HTTP IP lokal). Silakan buka di Google Chrome utama pada http://localhost:8000 atau gunakan tombol Foto via Kamera HP di bawah.';
                    } else if (msg.includes('NotAllowedError') || msg.includes('Permission denied') || msg.includes('PermissionDismissedError')) {
                        this.permissionError = 'Izin kamera belum diberikan. Klik ikon gembok di sebelah kiri bilah URL peramban, ubah Kamera menjadi "Izinkan", lalu klik Nyalakan Kamera di bawah.';
                    } else if (msg.includes('NotFoundError') || msg.includes('DevicesNotFoundError')) {
                        this.permissionError = 'Tidak ada perangkat kamera yang terdeteksi di perangkat Anda.';
                    } else if (msg.includes('NotReadableError') || msg.includes('TrackStartError')) {
                        this.permissionError = 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi yang memakai kamera dan coba lagi.';
                    } else if (!this.isSecure) {
                        this.permissionError = 'Peramban membatasi streaming kamera pada HTTP lokal. Silakan gunakan tombol Foto via Kamera HP.';
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
                        this.startCameraWithId(this.selectedCameraId);
                    }, 250);
                },

                startCameraWithId(cameraId) {
                    if (this.isCameraRunning) return;
                    this.isStarting = true;

                    const config = {
                        fps: 15,
                        qrbox: (w, h) => {
                            const min = Math.min(w, h);
                            const s = Math.max(Math.floor(min * 0.72), 120);
                            return { width: s, height: s };
                        },
                        aspectRatio: 1.0
                    };

                    this.html5QrCode = new Html5Qrcode('circulation-camera-reader', {
                        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                        verbose: false
                    });

                    this.html5QrCode.start(cameraId, config, (code) => this.onCodeDetected(code), () => {})
                        .then(() => {
                            this.isCameraRunning = true;
                            this.isStarting = false;
                        })
                        .catch(err => {
                            this.isStarting = false;
                            this.handleCameraError(err);
                        });
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
