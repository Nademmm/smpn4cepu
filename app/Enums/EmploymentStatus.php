<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case PNS = 'PNS';
    case PPPK = 'PPPK';
    case HONORER = 'Honorer';

    public function label(): string
    {
        return match ($this) {
            self::PNS => 'Pegawai Negeri Sipil (PNS)',
            self::PPPK => 'Pegawai Pemerintah dengan Perjanjian Kerja (PPPK)',
            self::HONORER => 'Tenaga Honorer / GTT / PTT',
        };
    }
}
