<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GradeLevel: int implements HasLabel
{
    case GRADE_7 = 7;
    case GRADE_8 = 8;
    case GRADE_9 = 9;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GRADE_7 => 'Kelas VII (Tujuh)',
            self::GRADE_8 => 'Kelas VIII (Delapan)',
            self::GRADE_9 => 'Kelas IX (Sembilan)',
        };
    }

    public function label(): string
    {
        return $this->getLabel() ?? (string) $this->value;
    }
}
