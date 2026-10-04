<?php

namespace App\Enums;

enum PostCategory: string
{
    case BERITA = 'berita';
    case PENGUMUMAN = 'pengumuman';
    case AGENDA = 'agenda';

    public function label(): string
    {
        return match ($this) {
            self::BERITA => 'Berita Sekolah',
            self::PENGUMUMAN => 'Pengumuman Resmi',
            self::AGENDA => 'Agenda Kegiatan',
        };
    }
}
