<?php

namespace App\Services;

use App\Models\ElectionCandidate;
use App\Models\ElectionDevice;
use App\Models\ElectionVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PilketosFingerprintService
{
    private const COOKIE_NAME = 'pilketos_voter_token';

    /**
     * Hitung sidik jari gabungan perangkat: Hardware Fingerprint Hash + IP Subnet (/24) + User Agent Hash.
     * Dibuat konsisten sebelum dan sesudah proses pemungutan suara.
     */
    public function generateFingerprint(Request $request, string $clientHardwareHash): string
    {
        $userAgent = $request->userAgent() ?? 'UNKNOWN_UA';
        $ip = $request->ip() ?? '127.0.0.1';

        $ipSubnet = $this->calculateSubnet($ip);

        // Raw composite string konsisten (hardware + network signature)
        $composite = implode('|', [
            $clientHardwareHash ?: 'NO_HW_HASH',
            $ipSubnet,
            hash('sha256', $userAgent),
        ]);

        return hash('sha256', $composite);
    }

    /**
     * Periksa apakah perangkat ini sudah memberikan suara berdasarkan fingerprint atau token cookie.
     */
    public function hasAlreadyVoted(Request $request, ?string $fingerprint = null): bool
    {
        // 1. Periksa cookie token pemilih
        $cookieToken = $request->cookie(self::COOKIE_NAME);
        if (!empty($cookieToken)) {
            return true;
        }

        // 2. Periksa device fingerprint di basis data jika disediakan
        if ($fingerprint !== null && ElectionDevice::where('device_fingerprint', $fingerprint)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Catat hak suara secara atomik dan anonim.
     */
    public function recordVote(Request $request, int $candidateId, string $clientHardwareHash): bool
    {
        $fingerprint = $this->generateFingerprint($request, $clientHardwareHash);

        if ($this->hasAlreadyVoted($request, $fingerprint)) {
            throw new RuntimeException('Perangkat ini terdeteksi telah memberikan suara pada Pilketos.');
        }

        $ipSubnet = $this->calculateSubnet($request->ip() ?? '127.0.0.1');
        $uaHash = hash('sha256', $request->userAgent() ?? 'UNKNOWN_UA');

        $result = DB::transaction(function () use ($candidateId, $fingerprint, $ipSubnet, $uaHash) {
            if (ElectionDevice::where('device_fingerprint', $fingerprint)->lockForUpdate()->exists()) {
                throw new RuntimeException('Perangkat Anda sudah terdaftar memberikan suara.');
            }

            // 1. Simpan metadata perangkat
            $device = ElectionDevice::create([
                'device_fingerprint' => $fingerprint,
                'ip_subnet' => $ipSubnet,
                'user_agent_hash' => $uaHash,
                'voted_at' => now(),
            ]);

            // 2. Simpan suara secara anonim di ballot box
            ElectionVote::create([
                'candidate_id' => $candidateId,
                'device_id' => $device->id,
                'created_at' => now(),
            ]);

            // 3. Atomik increment cache counter kandidat
            ElectionCandidate::where('id', $candidateId)->increment('total_votes_cached');

            // 4. Set cookie penanda permanen HttpOnly (berlaku 1 tahun)
            $token = Str::random(40);
            Cookie::queue(Cookie::make(self::COOKIE_NAME, $token, 525600, null, null, false, true));

            return true;
        });

        Cache::forget('pilketos_live_chart_data');
        Cache::forget('admin_school_overview_stats');

        return $result;
    }

    /**
     * Masking IP ke /24 (IPv4) atau /64 (IPv6) agar siswa dalam satu Wi-Fi NAT sekolah yang sama tidak terblokir.
     */
    public function calculateSubnet(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return "{$parts[0]}.{$parts[1]}.{$parts[2]}.0/24";
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return count($parts) >= 4 ? "{$parts[0]}:{$parts[1]}:{$parts[2]}:{$parts[3]}::/64" : $ip;
        }

        return '127.0.0.0/24';
    }
}
