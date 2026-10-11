<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PostCategory: string implements HasLabel
{
    case BERITA = 'berita';
    case PENGUMUMAN = 'pengumuman';
    case AGENDA = 'agenda';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BERITA => 'Berita Sekolah',
            self::PENGUMUMAN => 'Pengumuman Resmi',
            self::AGENDA => 'Agenda Kegiatan',
        };
    }

    public function label(): string
    {
        return $this->getLabel() ?? $this->value;
    }
}
