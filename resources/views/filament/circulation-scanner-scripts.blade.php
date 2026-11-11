<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<style>
/* Reset & Styling Khusus Pemindai QR Sirkulasi Filament (Mandiri tanpa dependensi Tailwind) */
.qr-modal-wrapper,
.qr-modal-wrapper * {
    box-sizing: border-box;
}

.qr-modal-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 100%;
    color: #0f172a;
    font-family: inherit;
}

/* 1. Bar Navigasi & Mode Sirkulasi */
.qr-nav-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

.qr-pill-group {
    display: inline-flex;
    align-items: center;
    background: #e2e8f0;
    padding: 3px;
    border-radius: 8px;
    gap: 2px;
}

.qr-pill-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    background: transparent;
    color: #64748b;
    transition: all 0.15s ease;
    line-height: 1.2;
}

.qr-pill-btn:hover {
    color: #0f172a;
}

.qr-pill-btn.active {
    background: #0f172a;
    color: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.qr-action-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.qr-btn-tool {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #1e293b;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: all 0.15s ease;
    min-height: 34px;
}

.qr-btn-tool:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.qr-btn-tool svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}

/* 2. Viewfinder Kamera */
.qr-viewfinder-box {
    position: relative;
    width: 100%;
    max-width: 360px;
    height: 290px;
    margin: 0 auto;
    background: #020617;
    border: 2px solid #334155;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.6);
}

#circulation-camera-reader {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
    position: relative !important;
    background: #020617 !important;
    border-radius: 14px !important;
    overflow: hidden !important;
}

#circulation-camera-reader video {
    width: 100% !important;
    height: 100% !important;
    max-height: 290px !important;
    object-fit: cover !important;
    border-radius: 14px !important;
    background: #020617 !important;
}

#circulation-camera-reader__scan_region {
    background: transparent !important;
    border: none !important;
}

#circulation-camera-reader__scan_region img,
#circulation-camera-reader__header_message,
#circulation-camera-reader__status_span,
#circulation-camera-reader__dashboard,
#circulation-camera-reader input[type="file"],
#circulation-camera-reader button {
    display: none !important;
}

/* Garis Laser & Bingkai Sudut */
@keyframes qr-laser-sweep {
    0%, 100% {
        top: 12%;
        opacity: 0.2;
    }
    50% {
        top: 85%;
        opacity: 1;
    }
}

.qr-laser-line {
    position: absolute;
    left: 8%;
    right: 8%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #10b981, #34d399, transparent);
    box-shadow: 0 0 12px #10b981;
    animation: qr-laser-sweep 2.2s ease-in-out infinite;
    pointer-events: none;
    z-index: 5;
}

.qr-reticle-corners {
    position: absolute;
    top: 24px;
    bottom: 24px;
    left: 24px;
    right: 24px;
    pointer-events: none;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    z-index: 4;
}

.qr-reticle-row {
    display: flex;
    justify-content: space-between;
}

.qr-reticle-corner {
    width: 22px;
    height: 22px;
    border-color: #10b981;
    border-style: solid;
    border-width: 0;
}
.qr-corner-tl { border-top-width: 3px; border-left-width: 3px; border-top-left-radius: 8px; }
.qr-corner-tr { border-top-width: 3px; border-right-width: 3px; border-top-right-radius: 8px; }
.qr-corner-bl { border-bottom-width: 3px; border-left-width: 3px; border-bottom-left-radius: 8px; }
.qr-corner-br { border-bottom-width: 3px; border-right-width: 3px; border-bottom-right-radius: 8px; }

/* 3. Standby & Error Overlay */
.qr-overlay-standby {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    text-align: center;
    gap: 10px;
    z-index: 10;
}

.qr-icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}

.qr-icon-circle svg {
    width: 26px;
    height: 26px;
}

.qr-standby-title {
    font-size: 14px;
    font-weight: 800;
    color: #ffffff;
    margin: 0;
}

.qr-standby-desc {
    font-size: 11.5px;
    color: #94a3b8;
    line-height: 1.45;
    margin: 0;
    max-width: 270px;
}

.qr-standby-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    max-width: 250px;
    margin-top: 4px;
}

.qr-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    background: #059669;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
    transition: background 0.15s ease;
    min-height: 40px;
}

.qr-btn-primary:hover {
    background: #10b981;
}

.qr-btn-primary svg {
    width: 16px;
    height: 16px;
}

.qr-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    background: #1e293b;
    color: #f1f5f9;
    border: 1px solid #334155;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
    min-height: 40px;
}

.qr-btn-secondary:hover {
    background: #334155;
}

.qr-btn-secondary svg {
    width: 16px;
    height: 16px;
    color: #10b981;
}

/* 4. Overlay Loading / Processing */
.qr-overlay-loading {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.9);
    backdrop-filter: blur(3px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #ffffff;
    z-index: 20;
}

.qr-spinner {
    width: 34px;
    height: 34px;
    border: 3px solid #10b981;
    border-top-color: transparent;
    border-radius: 50%;
    animation: qr-spin 0.8s linear infinite;
}

@keyframes qr-spin {
    to { transform: rotate(360deg); }
}

.qr-loading-text {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.02em;
}

/* 5. Kartu Hasil Pemindaian */
.qr-result-card {
    border-radius: 12px;
    padding: 12px 14px;
    border-width: 1px;
    border-style: solid;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.qr-result-success {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}

.qr-result-error {
    background: #fff1f2;
    border-color: #fecdd3;
    color: #9f1239;
}

.qr-result-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}

.qr-result-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.qr-badge-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}

.qr-badge-success { background: #059669; }
.qr-badge-error { background: #e11d48; }

.qr-result-heading {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin: 0;
}

.qr-result-message {
    font-size: 12px;
    margin: 2px 0 0 0;
    font-weight: 500;
}

.qr-btn-close-card {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
}

.qr-btn-close-card:hover {
    color: #475569;
}

.qr-ticket-details {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    color: #1e293b;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.qr-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}

.qr-detail-label {
    color: #64748b;
    font-size: 11.5px;
}

.qr-detail-value {
    font-weight: 700;
    text-align: right;
}

.qr-detail-code {
    font-family: monospace;
    font-weight: 800;
    color: #059669;
}

.qr-detail-status {
    font-weight: 800;
    text-transform: uppercase;
    color: #1f5fbf;
}

/* 6. Input Pencarian Cadangan */
.qr-manual-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
}

.qr-input-code {
    flex: 1;
    padding: 9px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 12px;
    background: #ffffff;
    color: #1e293b;
    outline: none;
    min-height: 40px;
    box-sizing: border-box;
}

.qr-input-code:focus {
    border-color: #059669;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.qr-btn-submit {
    padding: 9px 16px;
    background: #0f172a;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    min-height: 40px;
    transition: background 0.15s ease;
}

.qr-btn-submit:hover {
    background: #1e293b;
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

                    // Tunggu hingga elemen pembaca terlihat di layar (setelah modal selesai transisi)
                    this.waitForVisibleElement('circulation-camera-reader', () => {
                        if (this.isSecure) {
                            this.startCameraAuto();
                        } else {
                            this.cameraNotice = 'Peramban membatasi streaming video langsung pada HTTP lokal. Silakan klik tombol Foto via Kamera HP untuk memindai tiket.';
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

                        Html5Qrcode.getCameras().then(devices => {
                            if (devices && devices.length > 0) {
                                this.cameras = devices;

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

                    this.html5QrCode.start({ facingMode: 'environment' }, config, onSuccess, onError)
                        .then(() => {
                            this.isCameraRunning = true;
                            this.isStarting = false;
                            this.permissionError = null;
                        })
                        .catch(() => {
                            this.html5QrCode.start({ facingMode: 'user' }, config, onSuccess, onError)
                                .then(() => {
                                    this.isCameraRunning = true;
                                    this.isStarting = false;
                                    this.permissionError = null;
                                })
                                .catch(() => {
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
                        this.permissionError = 'Izin kamera belum diberikan. Klik ikon gembok di sebelah kiri bilah URL peramban, pilih "Izinkan Kamera", lalu klik Nyalakan Kamera di bawah.';
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
