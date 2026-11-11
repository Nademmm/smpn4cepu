<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
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
                actionMode: 'auto',
                cameras: [],
                selectedCameraId: null,
                manualCode: '',
                scannedCode: '',
                lastResult: null,
                lastScannedAt: 0,
                lastScannedCode: '',

                init() {
                    if (!isWidget) {
                        // Untuk modal, pancing izin dan mulai kamera setelah modal terbuka
                        setTimeout(() => {
                            this.requestPermissionAndStart();
                        }, 300);
                    }
                },

                destroy() {
                    this.stopCamera();
                },

                requestPermissionAndStart() {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.permissionError = 'Peramban Anda tidak mendukung akses kamera (perlu HTTPS atau localhost).';
                        return;
                    }

                    // Pancing izin kamera peramban secara eksplisit
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then((stream) => {
                            stream.getTracks().forEach(track => track.stop());
                            this.permissionError = null;

                            if (window.Html5Qrcode) {
                                Html5Qrcode.getCameras().then(devices => {
                                    if (devices && devices.length) {
                                        this.cameras = devices;
                                        const backCam = devices.find(c => 
                                            c.label.toLowerCase().includes('back') || 
                                            c.label.toLowerCase().includes('belakang') || 
                                            c.label.toLowerCase().includes('environment')
                                        );
                                        this.selectedCameraId = backCam ? backCam.id : devices[0].id;
                                    }
                                    this.startCamera();
                                }).catch(() => {
                                    this.startCamera();
                                });
                            }
                        })
                        .catch((err) => {
                            console.warn('Izin kamera ditolak atau tidak tersedia:', err);
                            this.permissionError = 'Izin kamera belum diberikan. Silakan izinkan akses kamera pada peramban Anda.';
                        });
                },

                startCamera() {
                    if (this.isCameraRunning) return;
                    if (!window.Html5Qrcode) return;

                    const readerElement = document.getElementById(this.targetId);
                    if (!readerElement) return;

                    try {
                        if (!this.html5QrCode) {
                            this.html5QrCode = new Html5Qrcode(this.targetId);
                        }

                        const config = {
                            fps: 12,
                            qrbox: function (viewfinderWidth, viewfinderHeight) {
                                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                                const qrboxSize = Math.floor(minEdge * 0.75);
                                return {
                                    width: Math.max(qrboxSize, 220),
                                    height: Math.max(qrboxSize, 180)
                                };
                            },
                            aspectRatio: 1.333334
                        };

                        const cameraConfig = this.selectedCameraId
                            ? { deviceId: { exact: this.selectedCameraId } }
                            : { facingMode: 'environment' };

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
                        }).catch((err) => {
                            console.error('Gagal menjalankan scanner kamera:', err);
                            this.isCameraRunning = false;
                            this.permissionError = 'Gagal membuka video kamera: ' + (err.message || err);
                        });
                    } catch (e) {
                        console.error('Inisialisasi Html5Qrcode gagal:', e);
                        this.permissionError = e.message;
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
                        setTimeout(() => {
                            this.requestPermissionAndStart();
                        }, 200);
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
