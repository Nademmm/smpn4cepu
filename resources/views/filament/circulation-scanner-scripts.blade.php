<script src="{{ parse_url(asset('vendor/html5-qrcode/html5-qrcode.min.js'), PHP_URL_PATH) }}"></script>
<script src="{{ parse_url(asset('vendor/jsqr/jsQR.js'), PHP_URL_PATH) }}"></script>
<style>
@keyframes circulationSpin {
    to { transform: rotate(360deg); }
}
.circulation-spinner {
    animation: circulationSpin 0.8s linear infinite;
}
</style>
<script>
(function () {
    const PROCESS_SCAN_URL = '{{ route("admin.circulation.process-scan", [], false) }}';
    const FALLBACK_CSRF = '{{ csrf_token() }}';

    function withTimeout(promise, ms, fallbackValue = null) {
        let timer = null;
        return Promise.race([
            Promise.resolve(promise),
            new Promise((resolve) => {
                timer = setTimeout(() => resolve(fallbackValue), ms);
            })
        ]).finally(() => {
            if (timer) clearTimeout(timer);
        });
    }

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

    function createScaledCanvas(source, maxDim, paddingRatio = 0.05) {
        const origW = source.naturalWidth || source.width || 1;
        const origH = source.naturalHeight || source.height || 1;
        const scale = Math.min(1, maxDim / Math.max(origW, origH));
        const w = Math.max(1, Math.round(origW * scale));
        const h = Math.max(1, Math.round(origH * scale));
        const pad = Math.max(16, Math.round(Math.min(w, h) * paddingRatio));
        return createWhiteBackedCanvas(source, w, h, pad);
    }

    async function scanFileWithZxing(file) {
        if (typeof window.Html5Qrcode === 'undefined' || !file) {
            return null;
        }
        const tempId = 'circulation-zxing-' + Math.random().toString(36).slice(2);
        const tempEl = document.createElement('div');
        tempEl.id = tempId;
        tempEl.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:40px;height:40px;opacity:0;pointer-events:none;';
        document.body.appendChild(tempEl);

        let html5Qr = null;
        try {
            html5Qr = new window.Html5Qrcode(tempId, { verbose: false });
            const res = await withTimeout(html5Qr.scanFile(file, false), 3500, null);
            return (typeof res === 'string' && res.trim()) ? res.trim() : null;
        } catch (e) {
            return null;
        } finally {
            try {
                if (html5Qr) {
                    const clearRes = html5Qr.clear();
                    if (clearRes && typeof clearRes.catch === 'function') {
                        clearRes.catch(() => {});
                    }
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
            }, 'image/jpeg', 0.9);
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
        const origW = source.naturalWidth || source.width;
        const origH = source.naturalHeight || source.height;
        if (!origW || !origH) return null;

        // A. Coba berbagai skala resolusi dengan background putih & padding quiet-zone
        const targetScales = [1000, 720, 1400, 480];
        for (const maxDim of targetScales) {
            const { canvas } = createScaledCanvas(source, maxDim, 0.05);
            const code = tryDecodeJsQrFromCanvas(canvas, true);
            if (code) return code;
        }

        // B. Center-crop 65% fokus ke tengah foto
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
        const { canvas: padded1000 } = createScaledCanvas(source, 1000, 0.06);

        // Tier 1: Hardware BarcodeDetector API pada canvas terukur (dengan batas waktu 1.5 detik)
        const detector = getBarcodeDetector();
        if (detector) {
            try {
                let results = await withTimeout(detector.detect(padded1000), 1500, []);
                if (results && results.length > 0 && results[0].rawValue) {
                    return results[0].rawValue.trim();
                }

                const origW = source.naturalWidth || source.width || 1;
                const origH = source.naturalHeight || source.height || 1;
                if (Math.max(origW, origH) <= 1800) {
                    results = await withTimeout(detector.detect(source), 1500, []);
                    if (results && results.length > 0 && results[0].rawValue) {
                        return results[0].rawValue.trim();
                    }
                }
            } catch (e) {}
        }

        // Tier 2: Fast jsQR langsung pada canvas 1000px & 720px (< 100ms)
        const fastJsQr = tryDecodeJsQrFromCanvas(padded1000, true);
        if (fastJsQr) return fastJsQr;

        // Tier 3: ZXing via Html5Qrcode pada canvas terukur + file asli (dengan batas waktu 3.5 detik)
        const paddedFile = await canvasToJpegFile(padded1000, 'padded-qr.jpg');
        if (paddedFile) {
            const zxingPadded = await scanFileWithZxing(paddedFile);
            if (zxingPadded) return zxingPadded;
        }

        if (originalFile && originalFile.size <= 4 * 1024 * 1024) {
            const zxingDirect = await scanFileWithZxing(originalFile);
            if (zxingDirect) return zxingDirect;
        }

        // Tier 4: Multi-Scale, Center-Crop, & Rotasi jsQR penuh
        const jsQrResult = scanWithMultiScaleJsQr(source);
        if (jsQrResult) {
            return jsQrResult;
        }

        return null;
    }

    async function sendCirculationScanRequest(code, circulationAction = 'hand_over') {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = (tokenMeta ? tokenMeta.getAttribute('content') : '')
            || window.livewireScriptConfig?.csrf
            || FALLBACK_CSRF;

        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 15000);

        try {
            const response = await fetch(PROCESS_SCAN_URL, {
                method: 'POST',
                credentials: 'same-origin',
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    code: code,
                    circulation_action: circulationAction || 'hand_over'
                })
            });

            const rawText = await response.text();
            let data = null;
            try {
                data = JSON.parse(rawText);
            } catch (e) {
                data = null;
            }

            if (response.status === 419) {
                return {
                    success: false,
                    message: 'Sesi keamanan halaman habis (CSRF 419). Muat ulang halaman lalu coba lagi.'
                };
            }

            if (!response.ok) {
                return {
                    success: false,
                    message: (data && data.message)
                        ? data.message
                        : ('Gagal memproses permintaan (HTTP ' + response.status + '). Muat ulang halaman jika sesi berakhir.'),
                    ticket: data?.ticket || null
                };
            }

            return data || {
                success: false,
                message: 'Respons server tidak valid.'
            };
        } catch (err) {
            if (err && err.name === 'AbortError') {
                return {
                    success: false,
                    message: 'Koneksi ke server melebihi batas waktu (15 detik). Periksa jaringan lalu coba lagi.'
                };
            }
            throw err;
        } finally {
            clearTimeout(timer);
        }
    }

    function readImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const objectUrl = (window.URL && URL.createObjectURL) ? URL.createObjectURL(file) : null;
            if (objectUrl) {
                const img = new Image();
                img.onload = () => resolve({ source: img, previewUrl: objectUrl, isObjectUrl: true });
                img.onerror = async () => {
                    if (typeof window.createImageBitmap === 'function') {
                        try {
                            const bitmap = await window.createImageBitmap(file);
                            resolve({ source: bitmap, previewUrl: objectUrl, isObjectUrl: true });
                            return;
                        } catch (err) {}
                    }
                    URL.revokeObjectURL(objectUrl);
                    reject(new Error('Format foto tidak dapat dibaca peramban. Gunakan JPG, PNG, atau WebP.'));
                };
                img.src = objectUrl;
                return;
            }

            const reader = new FileReader();
            reader.onerror = () => reject(new Error('Gagal membaca berkas gambar.'));
            reader.onload = (e) => {
                const dataUrl = e.target.result;
                const img = new Image();
                img.onload = () => resolve({ source: img, previewUrl: dataUrl, isObjectUrl: false });
                img.onerror = () => reject(new Error('Format foto tidak dapat dibaca peramban. Gunakan JPG, PNG, atau WebP.'));
                img.src = dataUrl;
            };
            reader.readAsDataURL(file);
        });
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

    function circulationScannerComponent(initialActionMode = 'hand_over') {
        const validModes = ['auto', 'hand_over', 'return', 'view_only'];
        const resolvedMode = validModes.includes(initialActionMode) ? initialActionMode : 'hand_over';

        return {
            activeTab: 'photo',
            actionMode: resolvedMode,
            isProcessing: false,
            isDecodingPhoto: false,
            statusText: 'Siap memindai foto QR tiket',
            lastScannedCode: '',
            lastScannedAt: 0,
            lastResult: null,
            manualCode: '',
            dragOver: false,
            previewImage: null,
            _previewObjectUrl: null,
            _isSubmitting: false,
            _hasMutated: false,

            init() {
                this._pasteHandler = (e) => {
                    const items = (e.clipboardData || window.clipboardData)?.items;
                    if (!items) return;
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {
                            const file = items[i].getAsFile();
                            if (file) {
                                this.activeTab = 'photo';
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
                if (this._previewObjectUrl && URL.revokeObjectURL) {
                    URL.revokeObjectURL(this._previewObjectUrl);
                    this._previewObjectUrl = null;
                }
                this.refreshOnClose();
            },

            refreshOnClose() {
                if (this._hasMutated && window.Livewire) {
                    this._hasMutated = false;
                    Livewire.dispatch('$refresh');
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
                    this.statusText = data.success
                        ? 'Berhasil memproses tiket: ' + cleanCode
                        : 'Gagal memproses tiket: ' + cleanCode;
                    if (data.success) {
                        this.manualCode = '';
                        if (this.actionMode !== 'view_only') {
                            this._hasMutated = true;
                        }
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

            async scanPhotoFile(file, inputEl = null) {
                if (!file) return;
                if (inputEl) {
                    inputEl.value = '';
                }

                this.isDecodingPhoto = true;
                this.isProcessing = true;
                this.lastResult = null;
                this.statusText = 'Membaca & mendeteksi QR Code dari foto...';

                // Beri kesempatan Alpine merender spinner sebelum komputasi canvas dimulai
                await new Promise((r) => setTimeout(r, 25));

                try {
                    const { source, previewUrl, isObjectUrl } = await readImageFromFile(file);
                    if (this._previewObjectUrl && URL.revokeObjectURL) {
                        URL.revokeObjectURL(this._previewObjectUrl);
                    }
                    this._previewObjectUrl = isObjectUrl ? previewUrl : null;
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
                            message: 'QR Code tidak ditemukan pada gambar ini. Pastikan QR Code terlihat utuh, fokus, dan cukup cahaya, atau gunakan tab Ketik / Alat Barcode.'
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

            handleDrop(e) {
                this.dragOver = false;
                const files = e.dataTransfer?.files;
                if (files && files.length > 0) {
                    this.activeTab = 'photo';
                    this.scanPhotoFile(files[0]);
                }
            }
        };
    }

    function circulationWidgetScannerComponent() {
        return {
            actionMode: 'hand_over',
            isProcessing: false,
            scannedCode: '',
            lastResult: null,

            async scanSelectedFile(file, inputEl = null) {
                if (!file || this.isProcessing) return;
                if (inputEl) {
                    inputEl.value = '';
                }
                this.isProcessing = true;
                this.scannedCode = 'Menganalisis foto QR Code...';
                this.lastResult = null;
                try {
                    const { source, previewUrl, isObjectUrl } = await readImageFromFile(file);
                    const code = await decodeQrFromImageSource(source, file);
                    if (isObjectUrl && previewUrl && URL.revokeObjectURL) {
                        URL.revokeObjectURL(previewUrl);
                    }
                    if (typeof source.close === 'function') {
                        try { source.close(); } catch (e) {}
                    }
                    if (!code) {
                        this.lastResult = { success: false, message: 'QR Code tidak terdeteksi pada foto ini.' };
                        return;
                    }
                    playSuccessBeep();
                    this.scannedCode = code;
                    const result = await sendCirculationScanRequest(code, this.actionMode);
                    this.lastResult = result;
                    if (result?.success && window.Livewire) {
                        Livewire.dispatch('$refresh');
                    }
                } catch (err) {
                    this.lastResult = { success: false, message: err?.message || 'Gagal membaca foto.' };
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
