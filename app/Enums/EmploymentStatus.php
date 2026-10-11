<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmploymentStatus: string implements HasLabel
{
    case PNS = 'PNS';
    case PPPK = 'PPPK';
    case HONORER = 'Honorer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PNS => 'Pegawai Negeri Sipil (PNS)',
            self::PPPK => 'Pegawai Pemerintah dengan Perjanjian Kerja (PPPK)',
            self::HONORER => 'Tenaga Honorer / GTT / PTT',
        };
    }

    public function label(): string
    {
        return $this->getLabel() ?? $this->value;
    }
}
