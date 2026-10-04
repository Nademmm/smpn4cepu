<?php

namespace App\Enums;

enum GradeLevel: int
{
    case GRADE_7 = 7;
    case GRADE_8 = 8;
    case GRADE_9 = 9;

    public function label(): string
    {
        return match ($this) {
            self::GRADE_7 => 'Kelas VII (Tujuh)',
            self::GRADE_8 => 'Kelas VIII (Delapan)',
            self::GRADE_9 => 'Kelas IX (Sembilan)',
        };
    }
}
