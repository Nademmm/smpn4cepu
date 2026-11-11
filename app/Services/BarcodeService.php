<?php

namespace App\Services;

class BarcodeService
{
    /**
     * Pola Barcode Code 128 B (Karakter ASCII 32 - 127).
     * Setiap elemen mewakili ketebalan bar & space (11 modul per karakter).
     */
    private static array $code128Patterns = [
        "212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312", "132212", "221213", // 0 - 9
        "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132", // 10 - 19
        "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211", // 20 - 29
        "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313", // 30 - 39
        "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331", // 40 - 49
        "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111", // 50 - 59
        "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214", // 60 - 69
        "112412", "122114", "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111", // 70 - 79
        "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112", "421211", "212141", // 80 - 89
        "214121", "412121", "111143", "111341", "131141", "114113", "114311", "411113", "411311", "113141", // 90 - 99
        "114131", "311141", "411131", "211412", "211214", "211232", "2331112" // 100 - 106 (106 adalah STOP pattern)
    ];

    /**
     * Generate Barcode Code 128 dalam format SVG murni.
     * 100% responsif, tajam di semua layar, scannable dengan scanner fisik & aplikasi HP.
     */
    public static function generateCode128Svg(string $code, int $height = 65, int $barWidth = 2, bool $showText = true): string
    {
        $code = trim($code);
        if (empty($code)) {
            $code = 'LIB-0000-2026';
        }

        // Mulai dengan Start Code B (index 104)
        $checksum = 104;
        $patterns = [self::$code128Patterns[104]];

        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $ascii = ord($code[$i]);
            // Code 128 Set B mencakup ASCII 32 - 127
            $val = ($ascii >= 32 && $ascii <= 127) ? ($ascii - 32) : 0;
            $patterns[] = self::$code128Patterns[$val];
            $checksum += $val * ($i + 1);
        }

        // Checksum modulo 103
        $checkVal = $checksum % 103;
        $patterns[] = self::$code128Patterns[$checkVal];

        // Stop pattern (index 106)
        $patterns[] = self::$code128Patterns[106];

        // Hitung total modul
        $totalModules = 0;
        foreach ($patterns as $p) {
            for ($j = 0; $j < strlen($p); $j++) {
                $totalModules += (int) $p[$j];
            }
        }

        $quietZone = 10; // margin samping
        $svgWidth = ($totalModules * $barWidth) + ($quietZone * 2 * $barWidth);
        $totalHeight = $showText ? ($height + 22) : $height;

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$svgWidth} {$totalHeight}\" width=\"100%\" height=\"{$totalHeight}\" style=\"max-width: {$svgWidth}px; display: block; margin: 0 auto;\">\n";
        $svg .= "  <rect width=\"100%\" height=\"100%\" fill=\"#FFFFFF\" rx=\"6\"/>\n";

        $currentX = $quietZone * $barWidth;

        foreach ($patterns as $p) {
            $pLen = strlen($p);
            for ($k = 0; $k < $pLen; $k++) {
                $moduleWidth = ((int) $p[$k]) * $barWidth;
                $isBar = ($k % 2 === 0);

                if ($isBar) {
                    $svg .= "  <rect x=\"{$currentX}\" y=\"6\" width=\"{$moduleWidth}\" height=\"{$height}\" fill=\"#0F172A\"/>\n";
                }

                $currentX += $moduleWidth;
            }
        }

        if ($showText) {
            $textY = $height + 17;
            $centerX = $svgWidth / 2;
            $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $svg .= "  <text x=\"{$centerX}\" y=\"{$textY}\" fill=\"#1E293B\" font-family=\"monospace, Courier, sans-serif\" font-size=\"12\" font-weight=\"700\" letter-spacing=\"3\" text-anchor=\"middle\">{$safeCode}</text>\n";
        }

        $svg .= "</svg>";

        return $svg;
    }

    /**
     * Generate QR Code vektor SVG standar ISO & scannable untuk tiket digital perpustakaan.
     */
    public static function generateQrCodeSvg(string $data, int $size = 140): string
    {
        $data = trim($data);
        if (empty($data)) {
            $data = 'LIB-0000-2026';
        }

        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $svg = $writer->writeString($data);

            // Bersihkan xml tag agar aman di-embed langsung di Blade / HTML
            $svg = preg_replace('/<\?xml[^\?]+\?>/', '', $svg);
            // Jadikan width dan height fleksibel 100% dengan viewBox tetap terjaga
            $svg = preg_replace('/width="\d+"/', 'width="100%"', $svg, 1);
            $svg = preg_replace('/height="\d+"/', 'height="100%"', $svg, 1);
            return trim($svg);
        } catch (\Throwable $e) {
            return '<svg viewBox="0 0 ' . $size . ' ' . $size . '" width="100%" height="100%"><rect width="' . $size . '" height="' . $size . '" fill="#f1f5f9"/></svg>';
        }
    }
}

