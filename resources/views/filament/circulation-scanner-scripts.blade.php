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
</style>
<script>
(function () {
    function circulationScannerComponent() {
        return {
            isProcessing: false,
            errorMessage: null,
            statusText: 'Siap memproses tiket sirkulasi',
            lastScannedCode: '',
            lastScannedAt: 0,
            lastResult: null,
            manualCode: '',
            dragOver: false,

            init() {
                // Dengarkan clipboard paste (Ctrl + V) saat modal aktif
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
                this.statusText = 'Membaca QR Code dari foto...';

                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext('2d', { willReadFrequently: true });
                        ctx.drawImage(img, 0, 0);
                        const imgData = ctx.getImageData(0, 0, img.width, img.height);

                        let code = null;

                        // 1. Dekode dengan pustaka jsQR lokal
                        if (typeof jsQR === 'function') {
                            const res = jsQR(imgData.data, img.width, img.height, {
                                inversionAttempts: 'attemptBoth'
                            });
                            if (res && res.data) {
                                code = res.data;
                            }
                        }

                        if (code) {
                            this.onDetected(code);
                        } else {
                            this.isProcessing = false;
                            this.lastResult = {
                                success: false,
                                message: 'QR Code tidak terdeteksi pada gambar. Pastikan posisi tiket tegak, terang, dan tidak buram.'
                            };
                            this.statusText = 'QR Code tidak terdeteksi';
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
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
