<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookCategory: string implements HasLabel, HasColor
{
    case KELAS_7 = 'kelas_7';
    case KELAS_8 = 'kelas_8';
    case KELAS_9 = 'kelas_9';
    case LITERASI_UMUM = 'literasi_umum';
    case KARYA_SEKOLAH = 'karya_sekolah';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::KELAS_7 => 'Buku Paket Kelas VII',
            self::KELAS_8 => 'Buku Paket Kelas VIII',
            self::KELAS_9 => 'Buku Paket Kelas IX',
            self::LITERASI_UMUM => 'Literasi & Fiksi Umum',
            self::KARYA_SEKOLAH => 'Karya Guru & Siswa',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::KELAS_7 => 'info',
            self::KELAS_8 => 'primary',
            self::KELAS_9 => 'warning',
            self::LITERASI_UMUM => 'success',
            self::KARYA_SEKOLAH => 'danger',
        };
    }

    public function label(): string
    {
        return $this->getLabel() ?? $this->value;
    }

    public static function options(): array
    {
        return [
            self::KELAS_7->value => self::KELAS_7->getLabel(),
            self::KELAS_8->value => self::KELAS_8->getLabel(),
            self::KELAS_9->value => self::KELAS_9->getLabel(),
            self::LITERASI_UMUM->value => self::LITERASI_UMUM->getLabel(),
            self::KARYA_SEKOLAH->value => self::KARYA_SEKOLAH->getLabel(),
        ];
    }
}

