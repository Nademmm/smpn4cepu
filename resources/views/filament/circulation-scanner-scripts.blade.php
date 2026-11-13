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
@keyframes circulationSpin {
    to { transform: rotate(360deg); }
}
.circulation-spinner {
    animation: circulationSpin 0.8s linear infinite;
}
</style>
<script>
(function () {
    const PROCESS_SCAN_URL = '{{ route("admin.circulation.process-scan") }}';
    const FALLBACK_CSRF = '{{ csrf_token() }}';

    let cachedBarcodeDetector = undefined;
    function getBarcodeDetector() {
        if (cachedBarcodeDetector !== undefined) {
            return cachedBarcodeDetector;
        }
        if (typeof window.BarcodeDetector === 'undefined') {
            cachedBarcodeDetector = null;
            return null;
        }
        try {
            cachedBarcodeDetector = new window.BarcodeDetector({
                formats: ['qr_code', 'code_128', 'code_39']
            });
        } catch (e) {
            try {
                cachedBarcodeDetector = new window.BarcodeDetector({ formats: ['qr_code'] });
            } catch (err) {
                cachedBarcodeDetector = null;
            }
        }
        return cachedBarcodeDetector;
    }

    function createWhiteBackedCanvas(source, width, height, padding = 0) {
        const c = document.createElement('canvas');
        c.width = Math.max(1, width + padding * 2);
        c.height = Math.max(1, height + padding * 2);
        const ctx = c.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, c.width, c.height);
        ctx.drawImage(source, padding, padding, width, height);
        return { canvas: c, ctx };
    }

    async function scanFileWithZxing(file) {
        if (typeof window.Html5Qrcode === 'undefined' || !file) {
            return null;
        }
        const tempId = 'circulation-zxing-' + Math.random().toString(36).slice(2);
        const tempEl = document.createElement('div');
        tempEl.id = tempId;
        tempEl.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:2px;height:2px;opacity:0;pointer-events:none;';
        document.body.appendChild(tempEl);

        let html5Qr = null;
        try {
            html5Qr = new window.Html5Qrcode(tempId, { verbose: false });
            const res = await html5Qr.scanFile(file, false);
            return (typeof res === 'string' && res.trim()) ? res.trim() : null;
        } catch (e) {
            return null;
        } finally {
            try {
                if (html5Qr) {
                    html5Qr.clear();
                }
            } catch (err) {}
            tempEl.remove();
        }
    }

    function canvasToJpegFile(canvas, filename = 'frame.jpg') {
        return new Promise((resolve) => {
            if (!canvas.toBlob) {
                resolve(null);
                return;
            }
            canvas.toBlob((blob) => {
                if (!blob) {
                    resolve(null);
                    return;
                }
                try {
                    resolve(new File([blob], filename, { type: 'image/jpeg' }));
                } catch (e) {
                    resolve(blob);
                }
            }, 'image/jpeg', 0.88);
        });
    }

    function tryDecodeJsQrFromCanvas(canvas, enhance = true) {
        if (typeof window.jsQR !== 'function' || !canvas.width || !canvas.height) {
            return null;
        }
        try {
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            const w = canvas.width;
            const h = canvas.height;
            const imgData = ctx.getImageData(0, 0, w, h);

            let res = window.jsQR(imgData.data, w, h, { inversionAttempts: 'attemptBoth' });
            if (res && res.data && res.data.trim()) {
                return res.data.trim();
            }

            if (!enhance) {
                return null;
            }

            // Contrast boost (+60%) untuk foto redup / layar HP
            const d = new Uint8ClampedArray(imgData.data);
            const contrast = 1.6;
            const intercept = 128 * (1 - contrast);
            let sum = 0;
            for (let i = 0; i < d.length; i += 4) {
                d[i] = Math.min(255, Math.max(0, d[i] * contrast + intercept));
                d[i + 1] = Math.min(255, Math.max(0, d[i + 1] * contrast + intercept));
                d[i + 2] = Math.min(255, Math.max(0, d[i + 2] * contrast + intercept));
                sum += (d[i] + d[i + 1] + d[i + 2]) / 3;
            }
            res = window.jsQR(d, w, h, { inversionAttempts: 'attemptBoth' });
            if (res && res.data && res.data.trim()) {
                return res.data.trim();
            }

            // Dynamic black/white thresholding
            const threshold = sum / (w * h);
            for (let i = 0; i < d.length; i += 4) {
                const gray = (d[i] + d[i + 1] + d[i + 2]) / 3;
                const v = gray > threshold ? 255 : 0;
                d[i] = v;
                d[i + 1] = v;
                d[i + 2] = v;
            }
            res = window.jsQR(d, w, h, { inversionAttempts: 'attemptBoth' });
            if (res && res.data && res.data.trim()) {
                return res.data.trim();
            }
        } catch (e) {}

        return null;
    }

    function scanWithMultiScaleJsQr(source) {
        const origW = source.naturalWidth || source.videoWidth || source.width;
        const origH = source.naturalHeight || source.videoHeight || source.height;
        if (!origW || !origH) return null;

        // A. Coba berbagai skala resolusi dengan background putih & padding quiet-zone
        const targetScales = [1000, 720, 1400, 480];
        for (const maxDim of targetScales) {
            const scale = Math.min(1, maxDim / Math.max(origW, origH));
            const w = Math.max(1, Math.round(origW * scale));
            const h = Math.max(1, Math.round(origH * scale));
            const pad = Math.max(16, Math.round(Math.min(w, h) * 0.05));
            const { canvas } = createWhiteBackedCanvas(source, w, h, pad);
            const code = tryDecodeJsQrFromCanvas(canvas, true);
            if (code) return code;
        }

        // B. Center-crop 65% fokus ke tengah frame
        const cropW = Math.max(1, Math.round(origW * 0.65));
        const cropH = Math.max(1, Math.round(origH * 0.65));
        const startX = Math.round((origW - cropW) / 2);
        const startY = Math.round((origH - cropH) / 2);
        const cropScale = Math.min(1, 800 / Math.max(cropW, cropH));
        const cW = Math.max(1, Math.round(cropW * cropScale));
        const cH = Math.max(1, Math.round(cropH * cropScale));
        const cropCanvas = document.createElement('canvas');
        cropCanvas.width = cW;
        cropCanvas.height = cH;
        const cropCtx = cropCanvas.getContext('2d', { willReadFrequently: true });
        cropCtx.fillStyle = '#ffffff';
        cropCtx.fillRect(0, 0, cW, cH);
        cropCtx.drawImage(source, startX, startY, cropW, cropH, 0, 0, cW, cH);
        const cropCode = tryDecodeJsQrFromCanvas(cropCanvas, true);
        if (cropCode) return cropCode;

        // C. Rotasi 90 derajat untuk foto potret/lanskap HP
        const rotScale = Math.min(1, 900 / Math.max(origW, origH));
        const rW = Math.max(1, Math.round(origH * rotScale));
        const rH = Math.max(1, Math.round(origW * rotScale));
        const rotCanvas = document.createElement('canvas');
        rotCanvas.width = rW;
        rotCanvas.height = rH;
        const rotCtx = rotCanvas.getContext('2d', { willReadFrequently: true });
        rotCtx.fillStyle = '#ffffff';
        rotCtx.fillRect(0, 0, rW, rH);
        rotCtx.translate(rW / 2, rH / 2);
        rotCtx.rotate(Math.PI / 2);
        rotCtx.drawImage(
            source,
            -Math.round(origW * rotScale) / 2,
            -Math.round(origH * rotScale) / 2,
            Math.round(origW * rotScale),
            Math.round(origH * rotScale)
        );
        const rotCode = tryDecodeJsQrFromCanvas(rotCanvas, true);
        if (rotCode) return rotCode;

        return null;
    }

    async function decodeQrFromImageSource(source, originalFile = null) {
        const detector = getBarcodeDetector();

        // Tier 1: Hardware BarcodeDetector API
        if (detector) {
            try {
                let results = await detector.detect(source);
                if (results && results.length > 0 && results[0].rawValue) {
                    return results[0].rawValue.trim();
                }

                const origW = source.naturalWidth || source.width || 1;
                const origH = source.naturalHeight || source.height || 1;
                const scale = Math.min(1, 1200 / Math.max(origW, origH));
                const w = Math.max(1, Math.round(origW * scale));
                const h = Math.max(1, Math.round(origH * scale));
                const { canvas } = createWhiteBackedCanvas(source, w, h, 24);
                results = await detector.detect(canvas);
                if (results && results.length > 0 && results[0].rawValue) {
                    return results[0].rawValue.trim();
                }
            } catch (e) {}
        }

        // Tier 2: ZXing via Html5Qrcode (file asli + canvas putih)
        if (originalFile) {
            const zxingDirect = await scanFileWithZxing(originalFile);
            if (zxingDirect) return zxingDirect;
        }

        const origW = source.naturalWidth || source.width || 1;
        const origH = source.naturalHeight || source.height || 1;
        const zxingScale = Math.min(1, 1000 / Math.max(origW, origH));
        const { canvas: paddedCanvas } = createWhiteBackedCanvas(
            source,
            Math.max(1, Math.round(origW * zxingScale)),
            Math.max(1, Math.round(origH * zxingScale)),
            24
        );
        const paddedFile = await canvasToJpegFile(paddedCanvas, 'padded-qr.jpg');
        if (paddedFile) {
            const zxingPadded = await scanFileWithZxing(paddedFile);
            if (zxingPadded) return zxingPadded;
        }

        // Tier 3: Multi-Scale & Contrast/Threshold jsQR
        const jsQrResult = scanWithMultiScaleJsQr(source);
        if (jsQrResult) {
            return jsQrResult;
        }

        return null;
    }

    async function decodeQrFromVideoFrame(video, workCanvas, tickCount) {
        if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
            return null;
        }

        // 1. Hardware BarcodeDetector langsung ke element <video>
        const detector = getBarcodeDetector();
        if (detector) {
            try {
                const barcodes = await detector.detect(video);
                if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                    return barcodes[0].rawValue.trim();
                }
            } catch (e) {}
        }

        // 2. Skalakan frame video maksimal 720px ke satu canvas terpakai ulang
        const vw = video.videoWidth;
        const vh = video.videoHeight;
        const scale = Math.min(1, 720 / Math.max(vw, vh));
        const w = Math.max(1, Math.round(vw * scale));
        const h = Math.max(1, Math.round(vh * scale));

        workCanvas.width = w;
        workCanvas.height = h;
        const ctx = workCanvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(video, 0, 0, w, h);

        // 2a. jsQR cepat (tanpa threshold berat tiap frame; contrast boost setiap 3 tick)
        const enhance = (tickCount % 3 === 0);
        const fullRes = tryDecodeJsQrFromCanvas(workCanvas, enhance);
        if (fullRes) return fullRes;

        // 2b. Setiap 4 tick, potong fokus area tengah 65% + contrast boost
        if (tickCount % 4 === 2) {
            const cw = Math.max(1, Math.round(vw * 0.65));
            const ch = Math.max(1, Math.round(vh * 0.65));
            const sx = Math.round((vw - cw) / 2);
            const sy = Math.round((vh - ch) / 2);
            workCanvas.width = Math.min(540, cw);
            workCanvas.height = Math.min(540, ch);
            ctx.drawImage(video, sx, sy, cw, ch, 0, 0, workCanvas.width, workCanvas.height);
            const cropRes = tryDecodeJsQrFromCanvas(workCanvas, true);
            if (cropRes) return cropRes;
        }

        // 3. Fallback ZXing tiap 6 tick jika browser tidak punya BarcodeDetector (Firefox/Safari)
        if (!detector && tickCount % 6 === 0) {
            const frameFile = await canvasToJpegFile(workCanvas, 'live-frame.jpg');
            if (frameFile) {
                const zxingRes = await scanFileWithZxing(frameFile);
                if (zxingRes) return zxingRes;
            }
        }

        return null;
    }

    async function sendCirculationScanRequest(code, circulationAction = 'auto') {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = (tokenMeta ? tokenMeta.getAttribute('content') : '') || FALLBACK_CSRF;

        const response = await fetch(PROCESS_SCAN_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                code: code,
                circulation_action: circulationAction || 'auto'
            })
        });

        const rawText = await response.text();
        let data = null;
        try {
            data = JSON.parse(rawText);
        } catch (e) {
            data = null;
        }

        if (!response.ok) {
            return {
                success: false,
                message: (data && data.message)
                    ? data.message
                    : ('Gagal memproses permintaan (HTTP ' + response.status + '). Muat ulang halaman jika sesi berakhir.')
            };
        }

        if (window.Livewire) {
            Livewire.dispatch('refresh');
        }

        return data || {
            success: false,
            message: 'Respons server tidak valid.'
        };
    }

    function readImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onerror = () => reject(new Error('Gagal membaca berkas gambar.'));
            reader.onload = (e) => {
                const dataUrl = e.target.result;
                const img = new Image();
                img.onload = () => resolve({ source: img, previewUrl: dataUrl });
                img.onerror = async () => {
                    if (typeof window.createImageBitmap === 'function') {
                        try {
                            const bitmap = await window.createImageBitmap(file);
                            resolve({ source: bitmap, previewUrl: dataUrl });
                            return;
                        } catch (err) {}
                    }
                    reject(new Error('Format foto tidak dapat dibaca peramban. Gunakan JPG, PNG, atau WebP.'));
                };
                img.src = dataUrl;
            };
            reader.readAsDataURL(file);
        });
    }

    async function acquireCameraStream(preferredDeviceId = null) {
        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            throw new Error('Peramban memblokir kamera live pada koneksi HTTP non-localhost (' + location.origin + '). Buka panel lewat HTTPS atau http://localhost, atau gunakan tab Foto Kamera HP.');
        }

        if (document.featurePolicy && typeof document.featurePolicy.allowsFeature === 'function' && !document.featurePolicy.allowsFeature('camera')) {
            throw new Error('Header server Permissions-Policy masih memblokir kamera (camera=()). Lakukan hard refresh (Ctrl+Shift+R) atau deploy pembaruan header camera=(self).');
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Peramban tidak menyediakan API kamera langsung (getUserMedia). Gunakan tab Foto Kamera HP.');
        }

        const attempts = [];
        if (preferredDeviceId) {
            attempts.push({
                video: {
                    deviceId: { exact: preferredDeviceId },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
            attempts.push({
                video: { deviceId: { exact: preferredDeviceId } },
                audio: false
            });
        } else {
            attempts.push({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
            attempts.push({
                video: { facingMode: 'environment' },
                audio: false
            });
            attempts.push({ video: true, audio: false });
        }

        let lastErr = null;
        for (const constraints of attempts) {
            try {
                return await navigator.mediaDevices.getUserMedia(constraints);
            } catch (err) {
                lastErr = err;
                if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
                    throw new Error('Izin akses kamera ditolak. Klik ikon gembok/kamera di address bar browser lalu pilih Izinkan (Allow), kemudian klik Coba Lagi.');
                }
                if (err && (err.name === 'NotReadableError' || err.name === 'TrackStartError')) {
                    throw new Error('Kamera sedang dipakai oleh aplikasi lain (mis. Zoom/Meet/Kamera Windows). Tutup aplikasi tersebut lalu coba lagi.');
                }
            }
        }

        if (lastErr && lastErr.name === 'NotFoundError') {
            throw new Error('Perangkat kamera / webcam tidak ditemukan pada perangkat ini.');
        }

        throw new Error('Kamera live gagal dinyalakan (' + (lastErr?.message || 'Unknown error') + ').');
    }

    function playSuccessBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.22, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.16);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.16);
        } catch (e) {}
    }

    function circulationScannerComponent() {
        return {
            activeTab: 'live',
            actionMode: 'auto',
            isProcessing: false,
            isDecodingPhoto: false,
            statusText: 'Siap memindai tiket sirkulasi',
            lastScannedCode: '',
            lastScannedAt: 0,
            lastResult: null,
            manualCode: '',
            dragOver: false,
            previewImage: null,
            isLiveActive: false,
            isLiveStarting: false,
            liveError: null,
            availableCameras: [],
            selectedCameraId: '',
            _liveStream: null,
            _liveLoopTimer: null,
            _liveSessionId: 0,
            _isDecodingFrame: false,
            _isSubmitting: false,
            _workCanvas: null,
            _tickCount: 0,

            init() {
                this._workCanvas = document.createElement('canvas');
                this._pasteHandler = (e) => {
                    const items = (e.clipboardData || window.clipboardData)?.items;
                    if (!items) return;
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {
                            const file = items[i].getAsFile();
                            if (file) {
                                this.activeTab = 'photo';
                                this.stopLiveCamera();
                                this.scanPhotoFile(file);
                                break;
                            }
                        }
                    }
                };
                window.addEventListener('paste', this._pasteHandler);

                if (navigator.permissions && navigator.permissions.query) {
                    navigator.permissions.query({ name: 'camera' }).then((status) => {
                        if (status && status.state === 'granted' && this.activeTab === 'live') {
                            this.$nextTick(() => this.startLiveCamera());
                        }
                    }).catch(() => {});
                }
            },

            destroy() {
                this.stopLiveCamera();
                if (this._pasteHandler) {
                    window.removeEventListener('paste', this._pasteHandler);
                }
            },

            async submitCode(rawCode) {
                const cleanCode = (rawCode || '').trim();
                if (!cleanCode || this._isSubmitting) return;

                this._isSubmitting = true;
                this.isProcessing = true;
                this.statusText = 'Memproses tiket: ' + cleanCode;

                try {
                    const data = await sendCirculationScanRequest(cleanCode, this.actionMode);
                    this.lastResult = data;
                    this.statusText = data.success ? 'Berhasil memproses tiket' : 'Gagal memproses tiket';
                    if (data.success) {
                        this.manualCode = '';
                    }
                } catch (err) {
                    this.lastResult = {
                        success: false,
                        message: 'Kendala jaringan saat mengirim data: ' + (err?.message || 'Error')
                    };
                    this.statusText = 'Gagal menghubungi server';
                } finally {
                    this._isSubmitting = false;
                    this.isProcessing = false;
                }
            },

            process(code) {
                return this.submitCode(code);
            },

            async onDetected(rawCode) {
                const code = (rawCode || '').trim();
                if (!code) return;

                const now = Date.now();
                if (code === this.lastScannedCode && (now - this.lastScannedAt) < 2500) {
                    return;
                }

                this.lastScannedAt = now;
                this.lastScannedCode = code;
                playSuccessBeep();
                this.stopLiveCamera();
                await this.submitCode(code);
            },

            async scanPhotoFile(file, inputEl = null) {
                if (!file) return;
                if (inputEl) {
                    inputEl.value = '';
                }

                this.isDecodingPhoto = true;
                this.isProcessing = true;
                this.lastResult = null;
                this.statusText = 'Membaca & mendeteksi QR Code dari foto...';

                try {
                    const { source, previewUrl } = await readImageFromFile(file);
                    this.previewImage = previewUrl;

                    const code = await decodeQrFromImageSource(source, file);
                    if (typeof source.close === 'function') {
                        try { source.close(); } catch (e) {}
                    }

                    if (code) {
                        this.statusText = 'QR Code terdeteksi: ' + code;
                        this.lastScannedCode = code;
                        this.lastScannedAt = Date.now();
                        playSuccessBeep();
                        await this.submitCode(code);
                    } else {
                        this.lastResult = {
                            success: false,
                            message: 'QR Code tidak ditemukan pada gambar ini. Pastikan QR Code terlihat utuh, fokus, dan cukup cahaya, atau gunakan Video Live Scanner / Ketik Kode.'
                        };
                        this.statusText = 'QR Code tidak terdeteksi';
                    }
                } catch (err) {
                    this.lastResult = {
                        success: false,
                        message: 'Gagal memproses foto: ' + (err?.message || 'Format berkas tidak valid')
                    };
                    this.statusText = 'Gagal memproses foto';
                } finally {
                    this.isDecodingPhoto = false;
                    if (!this._isSubmitting) {
                        this.isProcessing = false;
                    }
                }
            },

            async refreshCameraList() {
                try {
                    if (!navigator.mediaDevices?.enumerateDevices) return;
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.availableCameras = devices.filter(d => d.kind === 'videoinput');
                } catch (e) {}
            },

            async startLiveCamera(deviceId = null) {
                this.stopLiveCamera();
                this.liveError = null;
                this.isLiveStarting = true;

                const sessionId = ++this._liveSessionId;
                const chosenId = deviceId || this.selectedCameraId || null;

                try {
                    const stream = await acquireCameraStream(chosenId);
                    if (sessionId !== this._liveSessionId) {
                        stream.getTracks().forEach(t => t.stop());
                        return;
                    }

                    this._liveStream = stream;
                    const activeTrack = stream.getVideoTracks()[0];
                    const activeSettings = activeTrack?.getSettings?.();
                    if (activeSettings?.deviceId) {
                        this.selectedCameraId = activeSettings.deviceId;
                    }

                    await this.refreshCameraList();

                    const video = this.$refs.liveVideoEl;
                    if (!video) {
                        throw new Error('Elemen video kamera tidak ditemukan di modal.');
                    }

                    video.srcObject = stream;
                    if (video.readyState < 1) {
                        await new Promise((resolve) => {
                            const done = () => {
                                video.removeEventListener('loadedmetadata', done);
                                resolve();
                            };
                            video.addEventListener('loadedmetadata', done);
                            setTimeout(done, 1200);
                        });
                    }

                    try {
                        await video.play();
                    } catch (e) {}

                    if (sessionId !== this._liveSessionId) {
                        this.stopLiveCamera();
                        return;
                    }

                    this.isLiveStarting = false;
                    this.isLiveActive = true;
                    this._tickCount = 0;
                    this.scheduleNextLiveTick(sessionId);
                } catch (err) {
                    this.isLiveStarting = false;
                    this.isLiveActive = false;
                    this.liveError = err?.message || 'Gagal membuka kamera live.';
                }
            },

            switchLiveCamera() {
                if (this.selectedCameraId) {
                    this.startLiveCamera(this.selectedCameraId);
                }
            },

            scheduleNextLiveTick(sessionId) {
                if (!this.isLiveActive || sessionId !== this._liveSessionId) {
                    return;
                }
                this._liveLoopTimer = setTimeout(() => this.runLiveScanTick(sessionId), 130);
            },

            async runLiveScanTick(sessionId) {
                if (!this.isLiveActive || sessionId !== this._liveSessionId) {
                    return;
                }

                if (this._isDecodingFrame || this.isProcessing || this._isSubmitting) {
                    this.scheduleNextLiveTick(sessionId);
                    return;
                }

                const video = this.$refs.liveVideoEl;
                if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
                    this.scheduleNextLiveTick(sessionId);
                    return;
                }

                this._isDecodingFrame = true;
                this._tickCount++;
                try {
                    const code = await decodeQrFromVideoFrame(video, this._workCanvas, this._tickCount);
                    if (code && this.isLiveActive && sessionId === this._liveSessionId) {
                        await this.onDetected(code);
                        return;
                    }
                } catch (e) {
                } finally {
                    this._isDecodingFrame = false;
                }

                this.scheduleNextLiveTick(sessionId);
            },

            stopLiveCamera() {
                this._liveSessionId++;
                if (this._liveLoopTimer) {
                    clearTimeout(this._liveLoopTimer);
                    this._liveLoopTimer = null;
                }
                if (this._liveStream) {
                    this._liveStream.getTracks().forEach(track => {
                        try { track.stop(); } catch (e) {}
                    });
                    this._liveStream = null;
                }
                if (this.$refs?.liveVideoEl) {
                    try {
                        this.$refs.liveVideoEl.pause();
                        this.$refs.liveVideoEl.srcObject = null;
                    } catch (e) {}
                }
                this.isLiveStarting = false;
                this.isLiveActive = false;
                this._isDecodingFrame = false;
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

    function circulationWidgetScannerComponent() {
        return {
            isCameraRunning: false,
            isCameraOpen: false,
            permissionError: null,
            actionMode: 'auto',
            cameras: [],
            selectedCameraId: '',
            isProcessing: false,
            scannedCode: '',
            lastResult: null,
            _stream: null,
            _loopTimer: null,
            _sessionId: 0,
            _isDecodingFrame: false,
            _workCanvas: null,
            _tickCount: 0,

            init() {
                this._workCanvas = document.createElement('canvas');
            },

            destroy() {
                this.stopCamera();
            },

            toggleCamera() {
                if (this.isCameraRunning) {
                    this.stopCamera();
                } else {
                    this.startCamera(this.selectedCameraId || 'environment');
                }
            },

            async startCamera(target = 'environment') {
                this.stopCamera();
                this.permissionError = null;
                this.isCameraOpen = true;
                const sessionId = ++this._sessionId;
                const deviceId = (target && target !== 'environment' && target !== 'user') ? target : null;

                try {
                    const stream = await acquireCameraStream(deviceId);
                    if (sessionId !== this._sessionId) {
                        stream.getTracks().forEach(t => t.stop());
                        return;
                    }
                    this._stream = stream;

                    if (navigator.mediaDevices?.enumerateDevices) {
                        const devs = await navigator.mediaDevices.enumerateDevices();
                        this.cameras = devs.filter(d => d.kind === 'videoinput').map((d, idx) => ({
                            id: d.deviceId,
                            label: d.label || ('Kamera ' + (idx + 1))
                        }));
                    }

                    const readerHost = document.getElementById('widget-camera-reader');
                    if (readerHost) {
                        readerHost.innerHTML = '';
                        const v = document.createElement('video');
                        v.setAttribute('playsinline', 'true');
                        v.muted = true;
                        v.autoplay = true;
                        v.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
                        v.srcObject = stream;
                        readerHost.appendChild(v);
                        this._videoEl = v;
                        try { await v.play(); } catch (e) {}
                    }

                    this.isCameraRunning = true;
                    this._tickCount = 0;
                    this.scheduleNextTick(sessionId);
                } catch (err) {
                    this.isCameraRunning = false;
                    this.permissionError = err?.message || 'Gagal mengakses kamera.';
                }
            },

            switchCamera() {
                if (this.selectedCameraId) {
                    this.startCamera(this.selectedCameraId);
                }
            },

            scheduleNextTick(sessionId) {
                if (!this.isCameraRunning || sessionId !== this._sessionId) return;
                this._loopTimer = setTimeout(() => this.runTick(sessionId), 140);
            },

            async runTick(sessionId) {
                if (!this.isCameraRunning || sessionId !== this._sessionId) return;
                if (this._isDecodingFrame || this.isProcessing || !this._videoEl) {
                    this.scheduleNextTick(sessionId);
                    return;
                }

                this._isDecodingFrame = true;
                this._tickCount++;
                try {
                    const code = await decodeQrFromVideoFrame(this._videoEl, this._workCanvas, this._tickCount);
                    if (code && this.isCameraRunning && sessionId === this._sessionId) {
                        playSuccessBeep();
                        await this.submitScannedCode(code);
                        return;
                    }
                } catch (e) {
                } finally {
                    this._isDecodingFrame = false;
                }

                this.scheduleNextTick(sessionId);
            },

            stopCamera() {
                this._sessionId++;
                if (this._loopTimer) {
                    clearTimeout(this._loopTimer);
                    this._loopTimer = null;
                }
                if (this._stream) {
                    this._stream.getTracks().forEach(t => { try { t.stop(); } catch (e) {} });
                    this._stream = null;
                }
                if (this._videoEl) {
                    try {
                        this._videoEl.pause();
                        this._videoEl.srcObject = null;
                    } catch (e) {}
                    this._videoEl = null;
                }
                this.isCameraRunning = false;
            },

            async scanSelectedFile(file) {
                if (!file || this.isProcessing) return;
                this.isCameraOpen = true;
                this.isProcessing = true;
                this.scannedCode = 'Menganalisis gambar...';
                this.lastResult = null;
                try {
                    const { source } = await readImageFromFile(file);
                    const code = await decodeQrFromImageSource(source, file);
                    if (typeof source.close === 'function') {
                        try { source.close(); } catch (e) {}
                    }
                    if (!code) {
                        this.lastResult = { success: false, message: 'QR Code tidak terdeteksi pada foto ini.' };
                        return;
                    }
                    playSuccessBeep();
                    await this.submitScannedCode(code);
                } catch (err) {
                    this.lastResult = { success: false, message: err?.message || 'Gagal membaca foto.' };
                } finally {
                    this.isProcessing = false;
                }
            },

            async submitScannedCode(code) {
                this.isProcessing = true;
                this.scannedCode = code;
                try {
                    this.lastResult = await sendCirculationScanRequest(code, this.actionMode);
                } catch (err) {
                    this.lastResult = { success: false, message: 'Kendala jaringan: ' + (err?.message || 'Error') };
                } finally {
                    this.isProcessing = false;
                }
            }
        };
    }

    function registerComponent() {
        if (window.Alpine) {
            Alpine.data('circulationScanner', circulationScannerComponent);
            Alpine.data('circulationNativeScanner', circulationScannerComponent);
            Alpine.data('circulationCameraScanner', circulationScannerComponent);
            Alpine.data('circulationWidgetScanner', circulationWidgetScannerComponent);
        }
    }

    if (window.Alpine) {
        registerComponent();
    } else {
        document.addEventListener('alpine:init', registerComponent);
    }

    window.circulationScanner = circulationScannerComponent;
    window.circulationWidgetScanner = circulationWidgetScannerComponent;
})();
</script>
